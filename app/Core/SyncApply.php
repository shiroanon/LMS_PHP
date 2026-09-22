<?php
namespace App\Core;

/**
 * Shared row applier for node-to-node sync (used by SyncController::push
 * and the sync daemon). Portable SQL only (MySQL + MariaDB).
 *
 * Conflict policy:
 *  - tombstone always wins over a concurrent row version (deletes beat edits)
 *  - otherwise last-write-wins by (updated_at, node_id); equal timestamps
 *    with differing content go to sync_conflicts for librarian review
 *  - circulation compensation: after any issues row lands, derived stock
 *    (book_copies.status, books.quantity_available) is recomputed from
 *    live issues so counters converge instead of drifting
 *  - fines de-dupe: a second pending fine for the same (issue, reason)
 *    becomes a conflict instead of double-charging
 */
class SyncApply {
    /** table => [pk columns, timestamp expression or null for full-sync] */
    public const TABLES = [
        'users' => [['id'], 'COALESCE(updated_at, created_at)'],
        'students' => [['id'], 'COALESCE(updated_at, created_at)'],
        'staff_members' => [['id'], 'COALESCE(updated_at, created_at)'],
        'suppliers' => [['id'], 'COALESCE(updated_at, created_at)'],
        'books' => [['id'], 'COALESCE(updated_at, created_at)'],
        'book_copies' => [['id'], 'COALESCE(updated_at, created_at)'],
        'book_categories' => [['book_id', 'category'], null],
        'issues' => [['id'], 'COALESCE(updated_at, created_at)'],
        'fines' => [['id'], 'COALESCE(updated_at, created_at)'],
        'library_visits' => [['id'], 'COALESCE(updated_at, created_at)'],
        'book_reservations' => [['id'], 'COALESCE(updated_at, created_at)'],
        'holidays' => [['id'], 'COALESCE(updated_at, created_at)'],
        'suggestions' => [['id'], 'COALESCE(updated_at, created_at)'],
        'feedback' => [['id'], 'COALESCE(updated_at, created_at)'],
        'digital_resources' => [['id'], 'COALESCE(updated_at, created_at)'],
        'book_images' => [['id'], 'COALESCE(updated_at, created_at)'],
        'library_rules' => [['id'], 'COALESCE(updated_at, created_at)'],
        'announcements' => [['id'], 'COALESCE(updated_at, created_at)'],
        'system_settings' => [['k'], null],
    ];

    /** Ordered for FK-safe apply (parents before children). */
    public static function orderedTables(): array {
        return array_keys(self::TABLES);
    }

    private static array $columnsCache = [];

    /** Local column names for a table (used to strip unknown keys). */
    public static function columns(string $table): array {
        if (!isset(self::$columnsCache[$table])) {
            $cols = [];
            foreach (Database::query("SHOW COLUMNS FROM `$table`") as $c) $cols[] = $c['Field'];
            self::$columnsCache[$table] = $cols;
        }
        return self::$columnsCache[$table];
    }

    private static function tsOf(?array $row): string {
        return (string)($row['updated_at'] ?? $row['created_at'] ?? '1970-01-01 00:00:00');
    }

    private static function pkWhere(string $table, array $row): array {
        $w = [];
        foreach (self::TABLES[$table][0] as $pk) $w[$pk] = $row[$pk] ?? null;
        return $w;
    }

    public static function conflict(string $table, string $pk, $local, $remote, string $winner, string $reason): void {
        try {
            Database::exec(
                "INSERT INTO sync_conflicts (tbl, row_pk, local_json, remote_json, winner, reason) VALUES (?,?,?,?,?,?)",
                [$table, $pk, json_encode($local), json_encode($remote), $winner, substr($reason, 0, 191)]
            );
        } catch (\Throwable $e) { /* review log must never break apply */
        }
    }

    private static function pkString(string $table, array $row): string {
        $parts = [];
        foreach (self::TABLES[$table][0] as $pk) $parts[] = (string)($row[$pk] ?? '');
        return implode(':', $parts);
    }

    /**
     * Apply one remote row. Returns 'inserted'|'updated'|'skipped'|'conflict'.
     */
    public static function applyRow(string $table, array $row): string {
        if (!isset(self::TABLES[$table])) return 'skipped';
        $cols = array_intersect(array_keys($row), self::columns($table));
        if (!$cols) return 'skipped';
        $row = array_intersect_key($row, array_flip($cols));
        foreach (self::TABLES[$table][0] as $pk) {
            if (!isset($row[$pk]) || $row[$pk] === '') return 'skipped';
        }
        $pkStr = self::pkString($table, $row);

        // deletes beat concurrent edits
        $tomb = Database::one("SELECT * FROM sync_tombstones WHERE tbl=? AND row_id=? ORDER BY deleted_at DESC LIMIT 1", [$table, $pkStr]);
        if ($tomb && self::tsOf($row) <= (string)$tomb['deleted_at']) return 'skipped';

        $where = [];
        $params = [];
        foreach (self::TABLES[$table][0] as $pk) {
            $where[] = "`$pk`=?";
            $params[] = $row[$pk];
        }
        $local = Database::one("SELECT * FROM `$table` WHERE " . implode(' AND ', $where) . " LIMIT 1", $params);

        if (!$local) {
            if ($table === 'fines' && self::fineDuplicate($row)) {
                self::conflict($table, $pkStr, null, $row, 'local', 'duplicate pending fine suppressed');
                return 'conflict';
            }
            $keys = array_keys($row);
            $ph = implode(',', array_fill(0, count($keys), '?'));
            $ks = implode(',', array_map(fn($k) => "`$k`", $keys));
            Database::exec("INSERT INTO `$table` ($ks) VALUES ($ph)", array_values($row));
            if ($table === 'issues') self::recomputeStock($row);
            return 'inserted';
        }

        $rTs = self::tsOf($row);
        $lTs = self::tsOf($local);
        if ($rTs > $lTs) {
            $sets = [];
            $vals = [];
            foreach ($row as $k => $v) {
                if (in_array($k, self::TABLES[$table][0], true)) continue;
                $sets[] = "`$k`=?";
                $vals[] = $v;
            }
            if ($sets) {
                Database::exec("UPDATE `$table` SET " . implode(',', $sets) . " WHERE " . implode(' AND ', $where), array_merge($vals, $params));
                if ($table === 'issues') self::recomputeStock($row);
            }
            return 'updated';
        }
        if ($rTs < $lTs) return 'skipped'; // peer will catch up from us later
        // equal timestamps: same content => nothing to do, else review
        $a = $row;
        $b = array_intersect_key($local, $a);
        unset($a['updated_at']);
        unset($b['updated_at']);
        if ($a == $b) return 'skipped';
        $winner = strcmp((string)($row['node_id'] ?? ''), (string)($local['node_id'] ?? '')) < 0 ? 'remote' : 'local';
        if ($winner === 'remote') {
            $sets = [];
            $vals = [];
            foreach ($row as $k => $v) {
                if (in_array($k, self::TABLES[$table][0], true) || $k === 'updated_at') continue;
                $sets[] = "`$k`=?";
                $vals[] = $v;
            }
            if ($sets) Database::exec("UPDATE `$table` SET " . implode(',', $sets) . " WHERE " . implode(' AND ', $where), array_merge($vals, $params));
            if ($table === 'issues') self::recomputeStock($row);
        }
        self::conflict($table, $pkStr, $local, $row, $winner, 'same-timestamp divergence');
        return 'conflict';
    }

    /** Apply a remote tombstone: delete local row unless it is newer. */
    public static function applyDelete(string $table, string $pkStr, string $deletedAt): string {
        if (!isset(self::TABLES[$table])) return 'skipped';
        $pks = self::TABLES[$table][0];
        $ids = explode(':', $pkStr, count($pks));
        if (count($ids) !== count($pks)) return 'skipped';
        $where = [];
        foreach ($pks as $i => $pk) $where[] = "`$pk`='" . str_replace("'", "''", $ids[$i]) . "'";
        $local = Database::one("SELECT * FROM `$table` WHERE " . implode(' AND ', $where) . " LIMIT 1");
        if (!$local) return 'skipped';
        if (self::tsOf($local) > $deletedAt) {
            self::conflict($table, $pkStr, $local, null, 'local', 'local edit newer than remote delete');
            return 'conflict';
        }
        Database::exec("DELETE FROM `$table` WHERE " . implode(' AND ', $where) . " LIMIT 1");
        if ($table === 'issues') {
            $copyId = $local['book_copy_id'] ?? null;
            $bookId = $local['book_id'] ?? null;
            if ($copyId) self::recomputeStock(['book_copy_id' => $copyId, 'book_id' => $bookId]);
        }
        return 'deleted';
    }

    /** A pending fine for the same (issue, reason) already exists locally. */
    private static function fineDuplicate(array $row): bool {
        if (($row['status'] ?? '') !== 'pending' || empty($row['issue_id'])) return false;
        return (bool)Database::one(
            "SELECT id FROM fines WHERE issue_id=? AND `reason`=? AND status='pending' LIMIT 1",
            [$row['issue_id'], $row['reason'] ?? 'late_return']
        );
    }

    /**
     * Recompute derived stock from live issues so counters converge after
     * merges instead of drifting (both nodes decremented, etc.).
     */
    public static function recomputeStock(array $issueRow): void {
        $copyId = $issueRow['book_copy_id'] ?? null;
        $bookId = $issueRow['book_id'] ?? null;
        try {
            if ($copyId) {
                Database::exec(
                    "UPDATE book_copies SET status=CASE WHEN EXISTS(SELECT 1 FROM issues WHERE book_copy_id=? AND status IN ('issued','overdue')) THEN 'issued' ELSE 'available' END WHERE id=? AND (is_reference IS NULL OR is_reference=0)",
                    [$copyId, $copyId]
                );
            }
            if ($bookId) {
                Database::exec(
                    "UPDATE books SET quantity_available=GREATEST(0, quantity_total-(SELECT COUNT(*) FROM issues WHERE book_id=? AND status IN ('issued','overdue'))) WHERE id=?",
                    [$bookId, $bookId]
                );
            }
        } catch (\Throwable $e) { /* convergence is best-effort */
        }
    }
}
