# RJIT Central Library — PHP + MariaDB (From-Scratch Redesign)

**Stack:** PHP 8.5 + MariaDB 12 + vanilla MVC, no React. Design token `Ink #0F1D2E / Paper #FDF8F0 / Maroon #8B2D3B / Brass #C1A45A` — signature is perforated catalog card + brass hole + accession stamp.

## Run (dev)

```bash
# DB (MariaDB running, user lms/lms_dev_2026, database lms)
mariadb -u lms -plms_dev_2026 lms < database/schema.sql

# Seed minimal
php -d extension=pdo_mysql.so -r "/* see public/index.php seed */"

# Server — note pdo_mysql must be enabled (Arch: php.ini has no extension= line by default)
php -d extension=pdo_mysql.so -S 127.0.0.1:8765 -t public

# Login
# librarian: admin / admin123   (or librarian1 / lib1@2026)
# student:   0901CS221001 / 0901CS221001  (enrollment == initial password, must_change_password=1)
```

Env in `.env` (`DB_HOST/PORT/DATABASE/USERNAME/PASSWORD`, `FINE_RATE_PER_DAY=2`, `LOAN_PERIOD_DAYS=14`, `MAX_BOOKS_PER_STUDENT=3`). Deliverable is the whole `LMS_PHP/` folder — deployer runs `mariadb < database/schema.sql` and points vhost docroot to `public/`.

## What was rebuilt

- **32 tables** → `database/schema.sql` (InnoDB, utf8mb4, FKs, `FULLTEXT(title,author)`, holiday-aware fines, per-copy unique accession via `book_copies.accession_no UNIQUE` + transactional `SELECT ... FOR UPDATE` in `CirculationController` — mirrors `server/routes/issues.js:49-255` + `server/config/db.js`).
- **Auth:** session httpOnly `Lax`, `must_change_password` flow, RBAC `librarian|student` (`app/Core/Auth.php` replaces `middleware/auth.js` JWT).
- **Layout:** `resources/views/layouts/app.php` + `partials/sidebar.php` (5 librarian sections + 2 student, brass rail) + `partials/topbar.php` (barcode scanner buffer `app.js:keydown`).
- **Pages:** login (split hero with card-stack), dashboard (slip cards + recent accessions + gate), books (perforated `book-card` grid, category filter, accession stamp), students (ledger table), issue/return (due-slip preview, `CirculationController` holiday-aware), fines (pending/paid), visits (gate + peak chart), reports (CSV export), digital-library, suggestions, profile, generic stubs for suppliers/bills/holidays/etc. — all share `tokens.css` + `app.css`.
- **Files:** `public/assets/css/{tokens,app}.css` (Fraunces + IBM Plex Sans + JetBrains Mono), `public/assets/js/app.js` (drawer, flash, barcode hook).

## Design rationale (frontend-design skill)

Thesis: card catalog is the subject. Hero opens with two stacked catalog slabs, not a big-number gradient. Type: Fraunces display (high-contrast archival) + IBM Plex Sans body (technical) + JetBrains Mono for accession. Signature: perforated top edge (`repeating-linear-gradient` dashes), brass hole, maroon rubber stamp — one risk, quiet surroundings. Rejected defaults: warm cream+terracotta, black+acid, broadsheet hairlines — kept RJIT maroon but deepened and paired with brass instead of terracotta.

## Handover checklist for deployer

- Import `database/schema.sql`
- Create MariaDB user/db as in `.env`
- Set `public/` as docroot, ensure `storage/uploads/{images,pdfs}` writable
- Configure cron for overdue/visit/reservation if needed (08:00 sweep, 00:00 expiry — currently in `CirculationController`)
