#!/usr/bin/env php
<?php
// Minutely node-to-node sync daemon (pull-based both directions).
// Cron:  * * * * *  /usr/bin/php /path/to/LMS_PHP/docs/sync-daemon.php >> /dev/null 2>&1
// Exits quietly when SYNC_PEER_URL is empty or the peer is unreachable.
if (php_sapi_name() !== 'cli') { http_response_code(404); exit; }

require __DIR__ . '/../app/Core/Database.php';
require __DIR__ . '/../app/Core/Sync.php';
require __DIR__ . '/../app/Core/SyncApply.php';

use App\Core\Database;
use App\Core\Sync;
use App\Core\SyncApply;

$base = dirname(__DIR__);
$logFile = $base . '/storage/logs/sync-daemon.log';
$lockFile = $base . '/storage/logs/sync-daemon.lock';
@mkdir(dirname($logFile), 0775, true);

$log = function (string $msg) use ($logFile) {
    @file_put_contents($logFile, date('Y-m-d H:i:s') . " $msg\n", FILE_APPEND);
};

$lock = fopen($lockFile, 'c');
if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) exit(0); // previous run still active

$peer = Sync::peerUrl();
if ($peer === '') exit(0);
$me = Sync::nodeId();

function httpReq(string $method, string $url, string $path, string $body = '', int $timeout = 20): array {
    $ts = (string)time();
    $nonce = bin2hex(random_bytes(8));
    $sig = Sync::sign($method, $path, $ts, $nonce, $body);
    $headers = "X-Sync-Timestamp: $ts\r\nX-Sync-Nonce: $nonce\r\nX-Sync-Signature: $sig\r\nAccept: application/json\r\n";
    if ($body !== '') $headers .= "Content-Type: application/json\r\nContent-Length: " . strlen($body) . "\r\n";
    $ctx = stream_context_create(['http' => [
        'method' => $method, 'header' => $headers, 'content' => $body,
        'ignore_errors' => true, 'timeout' => $timeout,
    ]]);
    $resp = @file_get_contents($url, false, $ctx);
    $code = 0;
    foreach ($http_response_header ?? [] as $h) {
        if (preg_match('#HTTP/\S+ (\d+)#', $h, $m)) $code = (int)$m[1];
    }
    return [$code, (string)$resp];
}

// Peer alive?
[$hc] = httpReq('GET', $peer . '/healthz', '/healthz', '', 8);
if ($hc < 200 || $hc >= 300) {
    $log("peer $peer unreachable (http=$hc), cycle skipped");
    exit(0);
}
$peerNode = 'peer';
try {
    $raw = @file_get_contents($peer . '/healthz', false, stream_context_create(['http' => ['timeout' => 8]]));
    $peerNode = json_decode((string)$raw, true)['node'] ?? 'peer';
} catch (\Throwable $e) {
}

$totals = ['inserted' => 0, 'updated' => 0, 'deleted' => 0, 'skipped' => 0, 'conflicts' => 0, 'tables' => 0, 'files' => 0];
$tables = SyncApply::orderedTables();

// Pass 1: rows in FK order (parents before children).
$pendingDeletes = [];
foreach ($tables as $table) {
    $wm = Database::one("SELECT watermark FROM sync_state WHERE node=? AND tbl=?", [$peerNode, $table])['watermark'] ?? '1970-01-01 00:00:00';
    [$code, $resp] = httpReq('GET', $peer . '/sync/pull?table=' . urlencode($table) . '&since=' . urlencode($wm) . '&limit=500', '/sync/pull');
    if ($code !== 200) {
        $log("pull $table failed http=$code");
        continue;
    }
    $d = json_decode($resp, true);
    if (!is_array($d) || !isset($d['rows'])) {
        $log("pull $table bad payload");
        continue;
    }
    foreach ((array)$d['rows'] as $row) {
        if (!is_array($row)) {
            $totals['skipped']++;
            continue;
        }
        try {
            $r = SyncApply::applyRow($table, $row);
        } catch (\Throwable $e) {
            $r = 'skipped';
        }
        if (isset($totals[$r])) $totals[$r]++;
        else $totals['skipped']++;
    }
    foreach ((array)($d['tombstones'] ?? []) as $t) $pendingDeletes[] = $t + ['table' => $table];
    Database::exec(
        "INSERT INTO sync_state (node, tbl, watermark) VALUES (?,?,?) ON DUPLICATE KEY UPDATE watermark=VALUES(watermark)",
        [$peerNode, $table, $d['watermark'] ?? $wm]
    );
    $totals['tables']++;
}

// Pass 2: tombstones in reverse FK order (children vanish before parents).
foreach (array_reverse($pendingDeletes) as $t) {
    try {
        $r = SyncApply::applyDelete($t['table'], (string)($t['row_id'] ?? ''), (string)($t['deleted_at'] ?? '1970-01-01 00:00:00'));
    } catch (\Throwable $e) {
        $r = 'skipped';
    }
    if (isset($totals[$r])) $totals[$r]++;
    else $totals['skipped']++;
}

// Pass 3: files (covers + digital PDFs) by content hash.
foreach (['images', 'pdfs'] as $subdir) {
    [$code, $resp] = httpReq('GET', $peer . '/sync/files?subdir=' . $subdir, '/sync/files');
    if ($code !== 200) {
        $log("files manifest $subdir failed http=$code");
        continue;
    }
    $remote = [];
    foreach ((array)(json_decode($resp, true)['files'] ?? []) as $f) $remote[$f['name']] = $f;
    $dir = $base . '/storage/uploads/' . $subdir;
    if (!is_dir($dir)) mkdir($dir, 0775, true);
    foreach ($remote as $name => $meta) {
        if (!preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]*\.([A-Za-z0-9]+)$/', $name)) continue;
        $local = $dir . '/' . $name;
        if (is_file($local) && sha1_file($local) === ($meta['sha1'] ?? '')) continue;
        if (is_file($local)) {
            // same name, different bytes: keep local, flag for review
            SyncApply::conflict('uploads:' . $subdir, $name, ['sha1' => sha1_file($local)], $meta, 'local', 'same filename diverged');
            $totals['conflicts']++;
            continue;
        }
        [$fc, $bytes] = httpReq('GET', $peer . '/sync/file?subdir=' . $subdir . '&name=' . urlencode($name), '/sync/file', '', 60);
        if ($fc !== 200 || sha1($bytes) !== ($meta['sha1'] ?? '')) {
            $log("file fetch $subdir/$name failed");
            continue;
        }
        $tmp = $dir . '/.sync-' . bin2hex(random_bytes(8)) . '.tmp';
        file_put_contents($tmp, $bytes);
        rename($tmp, $local);
        $totals['files']++;
    }
}

$log("cycle peer=$peerNode tables={$totals['tables']} ins={$totals['inserted']} upd={$totals['updated']} del={$totals['deleted']} skip={$totals['skipped']} conf={$totals['conflicts']} files={$totals['files']}");
flock($lock, LOCK_UN);
