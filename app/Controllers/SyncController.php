<?php
namespace App\Controllers;

use App\Core\Database;
use App\Core\Sync;
use App\Core\SyncApply;

/**
 * Node-to-node sync endpoints. Guarded by HMAC (Sync::verify), NOT by
 * session login — daemon to daemon has no cookie. Portable SQL only.
 */
class SyncController {
    /** Lower-cased request headers (works on php -S, FPM and Apache alike). */
    private static function headers(): array {
        $h = [];
        foreach ($_SERVER as $k => $v) {
            if (str_starts_with($k, 'HTTP_') && is_string($v)) {
                $h[strtolower(str_replace('_', '-', substr($k, 5)))] = $v;
            }
        }
        return $h;
    }

    private static function deny(string $msg, int $code = 401): void {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['error' => $msg]);
        exit;
    }

    private static function validSince(string $s): bool {
        return (bool)preg_match('/^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}:\d{2})?$/', $s);
    }

    /** GET /sync/pull?table=T&since=ISO&limit=N — changed rows + tombstones. */
    public function pull(): void {
        $err = Sync::verify('GET', '/sync/pull', self::headers(), '');
        if ($err) self::deny('sync auth failed: ' . $err, 401);
        $table = $_GET['table'] ?? '';
        if (!isset(SyncApply::TABLES[$table])) self::deny('unknown table', 400);
        $since = $_GET['since'] ?? '1970-01-01 00:00:00';
        if (!self::validSince($since)) self::deny('bad since', 400);
        if (strlen($since) === 10) $since .= ' 00:00:00';
        $limit = min(1000, max(1, (int)($_GET['limit'] ?? 500)));

        $tsExpr = SyncApply::TABLES[$table][1];
        $pks = SyncApply::TABLES[$table][0];
        $pkOrder = implode(',', array_map(fn($k) => "`$k` ASC", $pks));
        if ($tsExpr) {
            $rows = Database::query("SELECT * FROM `$table` WHERE $tsExpr > ? ORDER BY $tsExpr ASC, $pkOrder LIMIT $limit", [$since]);
        } else {
            $rows = Database::query("SELECT * FROM `$table` ORDER BY $pkOrder LIMIT $limit");
        }
        $watermark = $since;
        foreach ($rows as $r) {
            $ts = (string)($r['updated_at'] ?? $r['created_at'] ?? '');
            if ($ts !== '' && $ts > $watermark) $watermark = $ts;
        }
        $tombs = Database::query(
            "SELECT tbl AS `table`, row_id, node_id, deleted_at FROM sync_tombstones WHERE tbl=? AND deleted_at > ? ORDER BY deleted_at ASC LIMIT $limit",
            [$table, $since]
        );
        header('Content-Type: application/json');
        echo json_encode(['table' => $table, 'rows' => $rows, 'tombstones' => $tombs, 'watermark' => $watermark]);
        exit;
    }

    /** POST /sync/push {table, rows:[...], deletes:[{pk, deleted_at}]} — idempotent apply. */    public function push(): void {
        $body = (string)file_get_contents('php://input');
        if (strlen($body) > 10 * 1024 * 1024) self::deny('payload too large', 413);
        $err = Sync::verify('POST', '/sync/push', self::headers(), $body);
        if ($err) self::deny('sync auth failed: ' . $err, 401);
        $d = json_decode($body, true);
        $table = is_array($d) ? ($d['table'] ?? '') : '';
        if (!isset(SyncApply::TABLES[$table])) self::deny('unknown table', 400);
        $counts = ['inserted' => 0, 'updated' => 0, 'deleted' => 0, 'skipped' => 0, 'conflicts' => 0];
        foreach ((array)($d['rows'] ?? []) as $row) {
            if (!is_array($row)) {
                $counts['skipped']++;
                continue;
            }
            try {
                $r = SyncApply::applyRow($table, $row);
            } catch (\Throwable $e) {
                $r = 'skipped';
            }
            if (isset($counts[$r])) $counts[$r]++;
            else $counts['skipped']++;
        }
        foreach ((array)($d['deletes'] ?? []) as $del) {
            try {
                $r = SyncApply::applyDelete($table, (string)($del['pk'] ?? ''), (string)($del['deleted_at'] ?? '1970-01-01 00:00:00'));
            } catch (\Throwable $e) {
                $r = 'skipped';
            }
            if (isset($counts[$r])) $counts[$r]++;
            else $counts['skipped']++;
        }
        header('Content-Type: application/json');
        echo json_encode(['table' => $table, 'counts' => $counts]);
        exit;
    }

    private const FILE_DIRS = ['images' => ['jpg', 'png', 'webp'], 'pdfs' => ['pdf']];
    private const FILE_MAX_BYTES = 20 * 1024 * 1024;

    private static function uploadsBase(): string {
        return dirname(__DIR__, 2) . '/storage/uploads';
    }

    private static function safeFileName(string $name, string $subdir): ?string {
        $safe = basename($name);
        if ($safe !== $name || !preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]*\.([A-Za-z0-9]+)$/', $safe)) return null;
        $ext = strtolower(pathinfo($safe, PATHINFO_EXTENSION));
        if (!in_array($ext, self::FILE_DIRS[$subdir], true)) return null;
        return $safe;
    }

    /** GET /sync/files?subdir=images|pdfs — manifest of name/size/sha1. */
    public function files(): void {
        $err = Sync::verify('GET', '/sync/files', self::headers(), '');
        if ($err) self::deny('sync auth failed: ' . $err, 401);
        $subdir = $_GET['subdir'] ?? '';
        if (!isset(self::FILE_DIRS[$subdir])) self::deny('unknown subdir', 400);
        $dir = self::uploadsBase() . '/' . $subdir;
        $out = [];
        if (is_dir($dir)) {
            foreach (scandir($dir) as $f) {
                if ($f === '.' || $f === '..') continue;
                $p = $dir . '/' . $f;
                if (!is_file($p)) continue;
                $out[] = ['name' => $f, 'size' => filesize($p), 'sha1' => sha1_file($p)];
            }
        }
        header('Content-Type: application/json');
        echo json_encode(['subdir' => $subdir, 'files' => $out]);
        exit;
    }

    /** GET /sync/file?subdir=..&name=.. — raw bytes for a listed file. */
    public function file(): void {
        $err = Sync::verify('GET', '/sync/file', self::headers(), '');
        if ($err) self::deny('sync auth failed: ' . $err, 401);
        $subdir = $_GET['subdir'] ?? '';
        if (!isset(self::FILE_DIRS[$subdir])) self::deny('unknown subdir', 400);
        $safe = self::safeFileName($_GET['name'] ?? '', $subdir);
        if (!$safe) self::deny('bad name', 400);
        $path = self::uploadsBase() . "/$subdir/$safe";
        if (!is_file($path)) self::deny('not found', 404);
        $mime = $subdir === 'pdfs' ? 'application/pdf' : match (strtolower(pathinfo($safe, PATHINFO_EXTENSION))) {
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'image/jpeg',
        };
        header('Content-Type: ' . $mime);
        header('Cache-Control: no-store');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }

    /** POST /sync/file {subdir, name, sha1, content_base64} — store a pushed file. */
    public function filePush(): void {
        $body = (string)file_get_contents('php://input');
        if (strlen($body) > 28 * 1024 * 1024) self::deny('payload too large', 413);
        $err = Sync::verify('POST', '/sync/file', self::headers(), $body);
        if ($err) self::deny('sync auth failed: ' . $err, 401);
        $d = json_decode($body, true);
        $subdir = is_array($d) ? ($d['subdir'] ?? '') : '';
        if (!isset(self::FILE_DIRS[$subdir])) self::deny('unknown subdir', 400);
        $safe = self::safeFileName(is_array($d) ? ($d['name'] ?? '') : '', $subdir);
        if (!$safe) self::deny('bad name', 400);
        $raw = base64_decode(is_array($d) ? ($d['content_base64'] ?? '') : '', true);
        if ($raw === false || strlen($raw) > self::FILE_MAX_BYTES) self::deny('bad content', 400);
        if (sha1($raw) !== (is_array($d) ? ($d['sha1'] ?? '') : '')) self::deny('hash mismatch', 400);
        $dir = self::uploadsBase() . '/' . $subdir;
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        $tmp = $dir . '/.sync-' . bin2hex(random_bytes(8)) . '.tmp';
        file_put_contents($tmp, $raw);
        rename($tmp, $dir . '/' . $safe);
        header('Content-Type: application/json');
        echo json_encode(['stored' => "$subdir/$safe", 'size' => strlen($raw)]);
        exit;
    }
}
