<?php
// Counter alignment for ID parity. Run on each node after snapshot restore:
//   php docs/sync-cutover.php
// Uses this node's NODE_ID (.env): library -> odd, college -> even.
require __DIR__ . '/../app/Core/Database.php';
require __DIR__ . '/../app/Core/Sync.php';

use App\Core\Database;
use App\Core\Sync;

Sync::loadEnv();
$node = Sync::nodeId();
$parity = ($node === 'college') ? 0 : 1; // even vs odd
$pdo = Database::pdo();

$tables = $pdo->query(
    "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_TYPE='BASE TABLE'"
)->fetchAll(PDO::FETCH_COLUMN);

foreach ($tables as $t) {
    $row = Database::one("SELECT AUTO_INCREMENT AS ai FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?", [$t]);
    if (!$row || $row['ai'] === null) continue; // no auto-inc column
    $max = (int)(Database::one("SELECT COALESCE(MAX(id),0) AS m FROM `$t`")['m'] ?? 0);
    $next = max($max + 1, (int)$row['ai']);
    if (($next % 2) !== $parity) $next++;
    Database::exec("ALTER TABLE `$t` AUTO_INCREMENT = $next");
    echo str_pad($t, 24) . " max=$max next=$next (" . ($parity ? 'odd' : 'even') . ")\n";
}
echo "node=$node done\n";
