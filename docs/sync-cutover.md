# ID parity cutover — odd/even auto-increment across nodes

## Why
Both nodes mint rows independently. With plain auto-increment the two
databases generate colliding `id`s and merged rows destroy each other.
Fix: **library mints odd ids, college mints even ids**, enforced per
session by the app (`Database::pdo()` reads `NODE_ID` and sets
`auto_increment_increment=2` + offset 1 or 2). Portable to MySQL and
MariaDB, zero schema churn.

## Provisioning a new node (college example)
1. Snapshot this database and restore it on the college server, so both
   start byte-identical:
   `mariadb-dump -u lms -p lms | mariadb -h college -u lms -p lms`
2. Verify identical row counts per table on both sides.
3. On the college copy set `.env`: `NODE_ID=college`, `NODE_ROLE=primary`,
   `SYNC_PEER_URL=http://<library-pc>:8765`.
4. Run `docs/sync-triggers-college.sql` on the college database.
5. Run the counter alignment below **on each node** (library script mints
   odd from its max, college mints even from its max). Because both start
   from identical data, fresh ids can never overlap afterwards.
6. Confirm: insert one row on each side, check odd vs even, delete them.

## Counter alignment (run per node after snapshot restore)
For every AUTO_INCREMENT table, raise `AUTO_INCREMENT` to the next value
with this node's parity above the current maximum:

```sql
-- library node (odd). Repeat per table, replacing students/max as needed:
SELECT MAX(id) FROM students;  -- say 1063
ALTER TABLE students AUTO_INCREMENT = 1065;  -- next odd above max
```

```sql
-- college node (even), same snapshot (max also 1063):
ALTER TABLE students AUTO_INCREMENT = 1066;  -- next even above max
```

`ALTER TABLE … AUTO_INCREMENT` only ever raises the counter, so this is
safe to re-run. A ready script is in `docs/sync-cutover.php`:
`php docs/sync-cutover.php` — reads every auto-increment table, prints
and applies the aligned value for this node's `NODE_ID`.

## Notes
- `BIGINT` space halved per node is still effectively infinite.
- Never change `NODE_ID` on a database that already minted rows — parity
  is a per-database lifetime property.
- `book_categories` (composite PK) and `system_settings` (string PK) need
  no counter; natural keys merge by value.
