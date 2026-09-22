<?php
namespace App\Core;

/**
 * Multi-node sync primitives: node identity + HMAC request auth.
 * Shared secret = APP_KEY. Portable: hash_hmac only, no engine specifics.
 */
class Sync {
    public const SKEW_SECONDS = 300;

    /** Load .env into $_ENV (same rules as Database::pdo, safe to call twice). Real environment wins (CLI $_ENV is often empty). */
    public static function loadEnv(): void {
        foreach (['NODE_ID', 'NODE_ROLE', 'SYNC_PEER_URL', 'APP_KEY'] as $k) {
            $g = getenv($k);
            if ($g !== false && $g !== '') $_ENV[$k] = $g;
        }
        $envFile = dirname(__DIR__, 2) . '/.env';
        if (!file_exists($envFile)) return;
        foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if (str_starts_with(trim($line), '#')) continue;
            if (!str_contains($line, '=')) continue;
            [$k, $v] = explode('=', $line, 2);
            $k = trim($k);
            if (!isset($_ENV[$k])) $_ENV[$k] = trim($v);
        }
    }

    public static function nodeId(): string {
        self::loadEnv();
        $id = preg_replace('/[^A-Za-z0-9_-]/', '', (string)($_ENV['NODE_ID'] ?? 'library'));
        return $id !== '' ? substr($id, 0, 32) : 'library';
    }

    public static function role(): string {
        self::loadEnv();
        return (($_ENV['NODE_ROLE'] ?? 'secondary') === 'primary') ? 'primary' : 'secondary';
    }

    public static function peerUrl(): string {
        self::loadEnv();
        return rtrim(trim((string)($_ENV['SYNC_PEER_URL'] ?? '')), '/');
    }

    public static function appKey(): string {
        self::loadEnv();
        return (string)($_ENV['APP_KEY'] ?? '');
    }

    /** Canonical signature over method, path, timestamp, nonce and raw body.
     *  $path is the URI path WITHOUT query string, identical on both sides. */
    public static function sign(string $method, string $path, string $ts, string $nonce, string $body): string {
        $payload = implode("\n", [strtoupper($method), $path, $ts, $nonce, hash('sha256', $body)]);
        return hash_hmac('sha256', $payload, self::appKey());
    }

    /**
     * Log a hard delete for replication (call BEFORE the DELETE runs).
     * Fail-open: sync metadata must never break desk operations.
     */
    public static function tombstone(string $table, $rowId): void {
        try {
            Database::exec(
                "INSERT INTO sync_tombstones (tbl, row_id, node_id) VALUES (?,?,?)",
                [$table, (string)$rowId, self::nodeId()]
            );
        } catch (\Throwable $e) { /* old deploy without sync tables: desk still works */
        }
    }
    /**
     * Verify sync headers. Returns null on success, error string on failure.
     * Fail-closed: empty APP_KEY, bad signature, stale timestamp or reused nonce all reject.
     */
    public static function verify(string $method, string $path, array $headers, string $body): ?string {
        if (self::appKey() === '') return 'sync not configured';
        $get = fn($k) => $headers[$k] ?? $headers[strtolower($k)] ?? null;
        $ts = $get('X-Sync-Timestamp');
        $nonce = $get('X-Sync-Nonce');
        $sig = $get('X-Sync-Signature');
        if (!$ts || !$nonce || !$sig) return 'missing sync headers';
        if (!ctype_digit($ts) || abs(time() - (int)$ts) > self::SKEW_SECONDS) return 'stale sync timestamp';
        if (!preg_match('/^[A-Za-z0-9_-]{8,64}$/', $nonce)) return 'bad sync nonce';
        if (!hash_equals(self::sign($method, $path, $ts, $nonce, $body), (string)$sig)) return 'bad sync signature';
        try {
            $exists = Database::one("SELECT nonce FROM sync_nonce WHERE nonce=?", [$nonce]);
            if ($exists) return 'replayed sync nonce';
            Database::exec("INSERT INTO sync_nonce (nonce) VALUES (?)", [$nonce]);
            Database::exec("DELETE FROM sync_nonce WHERE created_at < (NOW() - INTERVAL 15 MINUTE)");
        } catch (\Throwable $e) {
            return 'nonce store unavailable';
        }
        return null;
    }
}
