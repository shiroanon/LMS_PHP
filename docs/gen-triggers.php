<?php
// One-off generator for per-node origin triggers. Run: php docs/gen-triggers.php
$tables = ['users','students','staff_members','suppliers','books','book_copies','book_categories','issues','fines','library_visits','book_reservations','holidays','digital_resources','suggestions','feedback','book_images','library_rules','announcements','system_settings'];
foreach (['library','college'] as $node) {
    $s = "-- sync-triggers-$node.sql — origin stamps for the $node node (MySQL + MariaDB portable)\n"
       . "-- Run once on THIS node only.\n"
       . "-- Sync-applied rows carry explicit node_id, so triggers never clobber them.\n";
    foreach ($tables as $t) {
        $q = "'" . $node . "'";
        $s .= "DELIMITER $$\n"
            . "DROP TRIGGER IF EXISTS trg_{$t}_origin_ins$$\n"
            . "CREATE TRIGGER trg_{$t}_origin_ins BEFORE INSERT ON $t FOR EACH ROW BEGIN IF NEW.node_id IS NULL THEN SET NEW.node_id = $q; END IF; END$$\n"
            . "DROP TRIGGER IF EXISTS trg_{$t}_origin_upd$$\n"
            . "CREATE TRIGGER trg_{$t}_origin_upd BEFORE UPDATE ON $t FOR EACH ROW BEGIN IF NEW.node_id IS NULL THEN SET NEW.node_id = $q; END IF; END$$\n"
            . "DELIMITER ;\n";
    }
    file_put_contents(__DIR__ . "/sync-triggers-$node.sql", $s);
    echo "$node: " . substr_count($s, 'CREATE TRIGGER') . " triggers\n";
}
