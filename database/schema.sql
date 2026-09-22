SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;

-- users
CREATE TABLE IF NOT EXISTS users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(191) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('librarian','student','staff') NOT NULL,
  must_change_password TINYINT(1) NOT NULL DEFAULT 1,
  display_name VARCHAR(191) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS students (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL UNIQUE,
  enrollment_no VARCHAR(50) NOT NULL UNIQUE,
  library_id VARCHAR(50) NULL UNIQUE,
  barcode_data VARCHAR(50) NULL UNIQUE,
  name VARCHAR(191) NOT NULL,
  contact VARCHAR(50) NULL,
  email VARCHAR(191) NULL,
  father_name VARCHAR(191) NULL,
  branch VARCHAR(100) NOT NULL,
  year TINYINT UNSIGNED NOT NULL,
  address TEXT NULL,
  gender VARCHAR(20) NULL,
  dob DATE NULL,
  admission_date DATE NULL,
  last_library_visit DATETIME NULL,
  status ENUM('active','passed_out','inactive') NOT NULL DEFAULT 'active',
  photo_url VARCHAR(500) NULL,
  thumb_impression_url VARCHAR(500) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_students_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_students_branch (branch),
  INDEX idx_students_status (status),
  CONSTRAINT chk_year CHECK (year BETWEEN 1 AND 8)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS staff_members (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  library_id VARCHAR(50) NULL UNIQUE,
  name VARCHAR(191) NOT NULL,
  loan_category VARCHAR(100) NULL,
  membership_type VARCHAR(100) NULL,
  date_added VARCHAR(50) NULL,
  start_date VARCHAR(50) NULL,
  expiration_date VARCHAR(50) NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS suppliers (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(191) NOT NULL,
  shop_name VARCHAR(191) NULL,
  location VARCHAR(191) NULL,
  mobile VARCHAR(50) NOT NULL,
  email VARCHAR(191) NULL,
  gst_no VARCHAR(100) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS books (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  book_id VARCHAR(20) NOT NULL UNIQUE,
  title VARCHAR(500) NOT NULL,
  author VARCHAR(300) NOT NULL,
  isbn VARCHAR(20) NULL,
  category VARCHAR(100) NULL,
  edition VARCHAR(100) NULL,
  publication VARCHAR(191) NULL,
  num_pages INT UNSIGNED NULL,
  quantity_total INT UNSIGNED NOT NULL DEFAULT 1,
  quantity_available INT UNSIGNED NOT NULL DEFAULT 1,
  shelf_location VARCHAR(191) NULL,
  qr_code_data TEXT NULL,
  purchase_date DATE NULL,
  purchase_price DECIMAL(10,2) NULL,
  supplier_id BIGINT UNSIGNED NULL,
  book_image_url VARCHAR(500) NULL,
  issn VARCHAR(50) NULL,
  contents TEXT NULL,
  notes TEXT NULL,
  barcode_data VARCHAR(50) NULL UNIQUE,
  table_of_contents VARCHAR(500) NULL,
  is_reference TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FULLTEXT idx_ft_title_author (title, author),
  INDEX idx_books_category (category),
  INDEX idx_books_available (quantity_available),
  CONSTRAINT fk_books_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL,
  CONSTRAINT chk_qty CHECK (quantity_available <= quantity_total)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS book_copies (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  master_book_id BIGINT UNSIGNED NOT NULL,
  accession_no VARCHAR(20) NOT NULL UNIQUE,
  barcode_data VARCHAR(50) NULL,
  status ENUM('available','issued','damaged','disposed','reference') NOT NULL DEFAULT 'available',
  purchase_date DATE NULL,
  purchase_price DECIMAL(10,2) NULL,
  supplier_id BIGINT UNSIGNED NULL,
  is_reference TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_copies_book FOREIGN KEY (master_book_id) REFERENCES books(id) ON DELETE CASCADE,
  INDEX idx_copies_master (master_book_id),
  INDEX idx_copies_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS book_categories (
  book_id BIGINT UNSIGNED NOT NULL,
  category VARCHAR(100) NOT NULL,
  PRIMARY KEY (book_id, category),
  CONSTRAINT fk_bcat_book FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
  INDEX idx_bcat_cat (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS issues (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id BIGINT UNSIGNED NULL,
  staff_id BIGINT UNSIGNED NULL,
  member_type ENUM('student','staff') NOT NULL DEFAULT 'student',
  book_id BIGINT UNSIGNED NOT NULL,
  book_copy_id BIGINT UNSIGNED NULL,
  issue_date DATE NOT NULL,
  due_date DATE NOT NULL,
  return_date DATE NULL,
  status ENUM('issued','returned','overdue') NOT NULL DEFAULT 'issued',
  issued_by BIGINT UNSIGNED NULL,
  returned_by BIGINT UNSIGNED NULL,
  thumb_verified TINYINT(1) NOT NULL DEFAULT 0,
  barcode_scanned TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_issues_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_issues_staff FOREIGN KEY (staff_id) REFERENCES staff_members(id) ON DELETE CASCADE,
  CONSTRAINT fk_issues_book FOREIGN KEY (book_id) REFERENCES books(id),
  CONSTRAINT fk_issues_copy FOREIGN KEY (book_copy_id) REFERENCES book_copies(id),
  INDEX idx_issues_student (student_id),
  INDEX idx_issues_staff (staff_id),
  INDEX idx_issues_book (book_id),
  INDEX idx_issues_copy (book_copy_id),
  INDEX idx_issues_status (status),
  INDEX idx_issues_due (due_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- emulate partial unique: one active issue per copy
CREATE UNIQUE INDEX uq_active_copy ON issues (book_copy_id, status);

CREATE TABLE IF NOT EXISTS fines (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  issue_id BIGINT UNSIGNED NOT NULL,
  student_id BIGINT UNSIGNED NULL,
  staff_id BIGINT UNSIGNED NULL,
  member_type ENUM('student','staff') NOT NULL DEFAULT 'student',
  amount DECIMAL(10,2) NOT NULL,
  reason VARCHAR(100) NOT NULL DEFAULT 'late_return',
  status ENUM('pending','paid') NOT NULL DEFAULT 'pending',
  paid_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_fines_issue FOREIGN KEY (issue_id) REFERENCES issues(id) ON DELETE CASCADE,
  CONSTRAINT fk_fines_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_fines_staff FOREIGN KEY (staff_id) REFERENCES staff_members(id) ON DELETE CASCADE,
  INDEX idx_fines_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS library_visits (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id BIGINT UNSIGNED NOT NULL,
  check_in_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  check_out_time DATETIME NULL,
  method ENUM('barcode','manual','thumb') NOT NULL DEFAULT 'barcode',
  thumb_verified TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_visits_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  INDEX idx_visits_student (student_id),
  INDEX idx_visits_time (check_in_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS holidays (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  holiday_date DATE NOT NULL UNIQUE,
  description VARCHAR(191) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS book_reservations (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id BIGINT UNSIGNED NOT NULL,
  book_id BIGINT UNSIGNED NOT NULL,
  reservation_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  status ENUM('pending','notified','fulfilled','cancelled') NOT NULL DEFAULT 'pending',
  notification_date DATETIME NULL,
  expires_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_res_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_res_book FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
  INDEX idx_res_book (book_id),
  INDEX idx_res_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS digital_resources (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(300) NOT NULL,
  category ENUM('newspaper','current_affairs','magazine','ebook') NOT NULL,
  file_path VARCHAR(500) NOT NULL,
  file_size BIGINT UNSIGNED NULL,
  uploaded_by BIGINT UNSIGNED NULL,
  publish_date DATE NOT NULL DEFAULT (CURRENT_DATE),
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_dig_user FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS suggestions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id BIGINT UNSIGNED NOT NULL,
  type ENUM('book','magazine','newspaper','infrastructure','other') NOT NULL DEFAULT 'other',
  title VARCHAR(300) NOT NULL,
  author VARCHAR(191) NULL,
  description TEXT NULL,
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  librarian_notes TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_sugg_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS feedback (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id BIGINT UNSIGNED NOT NULL,
  book_id BIGINT UNSIGNED NULL,
  rating TINYINT UNSIGNED NOT NULL,
  comments TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_fb_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_fb_book FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
  CONSTRAINT chk_rating CHECK (rating BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS system_settings (
  k VARCHAR(191) PRIMARY KEY,
  v TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS announcements (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(300) NULL,
  content TEXT NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS book_images (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  book_id BIGINT UNSIGNED NOT NULL,
  image_url VARCHAR(500) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_bimg_book FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS library_rules (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  rule_text TEXT NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS=1;

-- Multi-node sync foundations (same as database/migrations/001_sync_foundation.sql).
CREATE TABLE IF NOT EXISTS sync_state (
  node VARCHAR(32) NOT NULL,
  tbl VARCHAR(64) NOT NULL,
  watermark DATETIME NULL,
  updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (node, tbl)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sync_tombstones (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tbl VARCHAR(64) NOT NULL,
  row_id VARCHAR(64) NOT NULL,
  node_id VARCHAR(32) NULL,
  deleted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_tomb_table (tbl)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sync_conflicts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tbl VARCHAR(64) NOT NULL,
  row_pk VARCHAR(64) NOT NULL,
  local_json MEDIUMTEXT NULL,
  remote_json MEDIUMTEXT NULL,
  winner ENUM('local','remote') NOT NULL,
  reason VARCHAR(191) NOT NULL DEFAULT '',
  status ENUM('open','resolved') NOT NULL DEFAULT 'open',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_conflict_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sync_nonce (
  nonce VARCHAR(64) NOT NULL PRIMARY KEY,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE users ADD COLUMN updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP, ADD COLUMN node_id VARCHAR(32) NULL;
ALTER TABLE students ADD COLUMN updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP, ADD COLUMN node_id VARCHAR(32) NULL;
ALTER TABLE staff_members ADD COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, ADD COLUMN updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP, ADD COLUMN node_id VARCHAR(32) NULL;
ALTER TABLE suppliers ADD COLUMN updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP, ADD COLUMN node_id VARCHAR(32) NULL;
ALTER TABLE books ADD COLUMN updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP, ADD COLUMN node_id VARCHAR(32) NULL;
ALTER TABLE book_copies ADD COLUMN updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP, ADD COLUMN node_id VARCHAR(32) NULL;
ALTER TABLE book_categories ADD COLUMN updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP, ADD COLUMN node_id VARCHAR(32) NULL;
ALTER TABLE issues ADD COLUMN updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP, ADD COLUMN node_id VARCHAR(32) NULL;
ALTER TABLE fines ADD COLUMN updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP, ADD COLUMN node_id VARCHAR(32) NULL;
ALTER TABLE library_visits ADD COLUMN updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP, ADD COLUMN node_id VARCHAR(32) NULL;
ALTER TABLE book_reservations ADD COLUMN updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP, ADD COLUMN node_id VARCHAR(32) NULL;
ALTER TABLE holidays ADD COLUMN updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP, ADD COLUMN node_id VARCHAR(32) NULL;
ALTER TABLE digital_resources ADD COLUMN updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP, ADD COLUMN node_id VARCHAR(32) NULL;
ALTER TABLE suggestions ADD COLUMN updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP, ADD COLUMN node_id VARCHAR(32) NULL;
ALTER TABLE feedback ADD COLUMN updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP, ADD COLUMN node_id VARCHAR(32) NULL;
ALTER TABLE book_images ADD COLUMN updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP, ADD COLUMN node_id VARCHAR(32) NULL;
ALTER TABLE library_rules ADD COLUMN updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP, ADD COLUMN node_id VARCHAR(32) NULL;
ALTER TABLE announcements ADD COLUMN node_id VARCHAR(32) NULL;
ALTER TABLE system_settings ADD COLUMN updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP, ADD COLUMN node_id VARCHAR(32) NULL;
CREATE TABLE `audit_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `action` text NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_drift_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
;
CREATE TABLE `book_notes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `book_id` bigint(20) unsigned NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `book_id` (`book_id`),
  CONSTRAINT `fk_drift_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
;
CREATE TABLE `purchase_bills` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `bill_no` varchar(100) NOT NULL,
  `supplier_id` bigint(20) unsigned NOT NULL,
  `purchase_date` date NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) DEFAULT 0.00,
  `tax` decimal(10,2) DEFAULT 0.00,
  `payment_mode` enum('cash','cheque','online','credit') DEFAULT 'cash',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `bill_no` (`bill_no`),
  KEY `supplier_id` (`supplier_id`),
  CONSTRAINT `fk_drift_3` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
;
CREATE TABLE `purchase_bill_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `bill_id` bigint(20) unsigned NOT NULL,
  `book_id` bigint(20) unsigned DEFAULT NULL,
  `qty` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `bill_id` (`bill_id`),
  KEY `book_id` (`book_id`),
  CONSTRAINT `fk_drift_4` FOREIGN KEY (`bill_id`) REFERENCES `purchase_bills` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_drift_5` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
;
CREATE TABLE `reminder_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) unsigned NOT NULL,
  `message` text NOT NULL,
  `sent_via` varchar(50) DEFAULT 'email',
  `created_at` datetime DEFAULT current_timestamp(),
  `section` varchar(50) DEFAULT 'general',
  `notification_count` int(11) DEFAULT 1,
  `book_id` bigint(20) unsigned DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `fk_drift_6` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
;
CREATE TABLE `student_barcodes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) unsigned NOT NULL,
  `barcode_data` varchar(191) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_id` (`student_id`),
  UNIQUE KEY `barcode_data` (`barcode_data`),
  CONSTRAINT `fk_drift_7` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
;
CREATE TABLE `backup_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `filename` varchar(191) NOT NULL,
  `size` bigint(20) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'success',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `filename` (`filename`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
;
CREATE TABLE `fine_notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fine_id` bigint(20) unsigned DEFAULT NULL,
  `student_id` bigint(20) unsigned NOT NULL,
  `message` text NOT NULL,
  `sent_via` varchar(50) DEFAULT 'email',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fine_id` (`fine_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `fk_drift_8` FOREIGN KEY (`fine_id`) REFERENCES `fines` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_drift_9` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
;
CREATE TABLE `disposed_books` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `accession_no` varchar(50) DEFAULT NULL,
  `title` varchar(500) DEFAULT NULL,
  `isbn` varchar(50) DEFAULT NULL,
  `publisher` varchar(191) DEFAULT NULL,
  `price` varchar(50) DEFAULT NULL,
  `accession_date` varchar(50) DEFAULT NULL,
  `disposal_date` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Disposed',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
;
CREATE TABLE `student_photos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) unsigned NOT NULL,
  `photo_url` varchar(500) DEFAULT NULL,
  `thumb_impression_url` varchar(500) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_id` (`student_id`),
  CONSTRAINT `fk_drift_10` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
;
CREATE TABLE `notification_counter` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) unsigned NOT NULL,
  `section` enum('overdue','fine','reservation','general') NOT NULL,
  `notification_count` int(11) DEFAULT 1,
  `last_notified` datetime DEFAULT current_timestamp(),
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_id` (`student_id`,`section`),
  CONSTRAINT `fk_drift_11` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
;
CREATE TABLE `book_barcodes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `book_id` bigint(20) unsigned NOT NULL,
  `barcode_data` varchar(191) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `book_id` (`book_id`),
  UNIQUE KEY `barcode_data` (`barcode_data`),
  CONSTRAINT `fk_drift_12` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
;
CREATE TABLE `google_form_imports` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `enrollment_no` varchar(50) NOT NULL,
  `name` varchar(191) NOT NULL,
  `email` varchar(191) DEFAULT NULL,
  `contact` varchar(50) DEFAULT NULL,
  `father_name` varchar(191) DEFAULT NULL,
  `branch` varchar(100) NOT NULL,
  `year` int(11) NOT NULL,
  `address` text DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `blood_group` varchar(20) DEFAULT NULL,
  `aadhar_no` varchar(50) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `enrollment_no` (`enrollment_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
;
