-- sync-triggers-college.sql — origin stamps for the college node (MySQL + MariaDB portable)
-- Run once on THIS node only.
-- Sync-applied rows carry explicit node_id, so triggers never clobber them.
DELIMITER $$
DROP TRIGGER IF EXISTS trg_users_origin_ins$$
CREATE TRIGGER trg_users_origin_ins BEFORE INSERT ON users FOR EACH ROW BEGIN IF NEW.node_id IS NULL THEN SET NEW.node_id = 'college'; END IF; END$$
DROP TRIGGER IF EXISTS trg_users_origin_upd$$
CREATE TRIGGER trg_users_origin_upd BEFORE UPDATE ON users FOR EACH ROW BEGIN IF NEW.node_id IS NULL THEN SET NEW.node_id = 'college'; END IF; END$$
DELIMITER ;
DELIMITER $$
DROP TRIGGER IF EXISTS trg_students_origin_ins$$
CREATE TRIGGER trg_students_origin_ins BEFORE INSERT ON students FOR EACH ROW BEGIN IF NEW.node_id IS NULL THEN SET NEW.node_id = 'college'; END IF; END$$
DROP TRIGGER IF EXISTS trg_students_origin_upd$$
CREATE TRIGGER trg_students_origin_upd BEFORE UPDATE ON students FOR EACH ROW BEGIN IF NEW.node_id IS NULL THEN SET NEW.node_id = 'college'; END IF; END$$
DELIMITER ;
DELIMITER $$
DROP TRIGGER IF EXISTS trg_staff_members_origin_ins$$
CREATE TRIGGER trg_staff_members_origin_ins BEFORE INSERT ON staff_members FOR EACH ROW BEGIN IF NEW.node_id IS NULL THEN SET NEW.node_id = 'college'; END IF; END$$
DROP TRIGGER IF EXISTS trg_staff_members_origin_upd$$
CREATE TRIGGER trg_staff_members_origin_upd BEFORE UPDATE ON staff_members FOR EACH ROW BEGIN IF NEW.node_id IS NULL THEN SET NEW.node_id = 'college'; END IF; END$$
DELIMITER ;
DELIMITER $$
DROP TRIGGER IF EXISTS trg_suppliers_origin_ins$$
CREATE TRIGGER trg_suppliers_origin_ins BEFORE INSERT ON suppliers FOR EACH ROW BEGIN IF NEW.node_id IS NULL THEN SET NEW.node_id = 'college'; END IF; END$$
DROP TRIGGER IF EXISTS trg_suppliers_origin_upd$$
CREATE TRIGGER trg_suppliers_origin_upd BEFORE UPDATE ON suppliers FOR EACH ROW BEGIN IF NEW.node_id IS NULL THEN SET NEW.node_id = 'college'; END IF; END$$
DELIMITER ;
DELIMITER $$
DROP TRIGGER IF EXISTS trg_books_origin_ins$$
CREATE TRIGGER trg_books_origin_ins BEFORE INSERT ON books FOR EACH ROW BEGIN IF NEW.node_id IS NULL THEN SET NEW.node_id = 'college'; END IF; END$$
DROP TRIGGER IF EXISTS trg_books_origin_upd$$
CREATE TRIGGER trg_books_origin_upd BEFORE UPDATE ON books FOR EACH ROW BEGIN IF NEW.node_id IS NULL THEN SET NEW.node_id = 'college'; END IF; END$$
DELIMITER ;
DELIMITER $$
DROP TRIGGER IF EXISTS trg_book_copies_origin_ins$$
CREATE TRIGGER trg_book_copies_origin_ins BEFORE INSERT ON book_copies FOR EACH ROW BEGIN IF NEW.node_id IS NULL THEN SET NEW.node_id = 'college'; END IF; END$$
DROP TRIGGER IF EXISTS trg_book_copies_origin_upd$$
CREATE TRIGGER trg_book_copies_origin_upd BEFORE UPDATE ON book_copies FOR EACH ROW BEGIN IF NEW.node_id IS NULL THEN SET NEW.node_id = 'college'; END IF; END$$
DELIMITER ;
DELIMITER $$
DROP TRIGGER IF EXISTS trg_book_categories_origin_ins$$
CREATE TRIGGER trg_book_categories_origin_ins BEFORE INSERT ON book_categories FOR EACH ROW BEGIN IF NEW.node_id IS NULL THEN SET NEW.node_id = 'college'; END IF; END$$
DROP TRIGGER IF EXISTS trg_book_categories_origin_upd$$
CREATE TRIGGER trg_book_categories_origin_upd BEFORE UPDATE ON book_categories FOR EACH ROW BEGIN IF NEW.node_id IS NULL THEN SET NEW.node_id = 'college'; END IF; END$$
DELIMITER ;
DELIMITER $$
DROP TRIGGER IF EXISTS trg_issues_origin_ins$$
CREATE TRIGGER trg_issues_origin_ins BEFORE INSERT ON issues FOR EACH ROW BEGIN IF NEW.node_id IS NULL THEN SET NEW.node_id = 'college'; END IF; END$$
DROP TRIGGER IF EXISTS trg_issues_origin_upd$$
CREATE TRIGGER trg_issues_origin_upd BEFORE UPDATE ON issues FOR EACH ROW BEGIN IF NEW.node_id IS NULL THEN SET NEW.node_id = 'college'; END IF; END$$
DELIMITER ;
DELIMITER $$
DROP TRIGGER IF EXISTS trg_fines_origin_ins$$
CREATE TRIGGER trg_fines_origin_ins BEFORE INSERT ON fines FOR EACH ROW BEGIN IF NEW.node_id IS NULL THEN SET NEW.node_id = 'college'; END IF; END$$
DROP TRIGGER IF EXISTS trg_fines_origin_upd$$
CREATE TRIGGER trg_fines_origin_upd BEFORE UPDATE ON fines FOR EACH ROW BEGIN IF NEW.node_id IS NULL THEN SET NEW.node_id = 'college'; END IF; END$$
DELIMITER ;
DELIMITER $$
DROP TRIGGER IF EXISTS trg_library_visits_origin_ins$$
CREATE TRIGGER trg_library_visits_origin_ins BEFORE INSERT ON library_visits FOR EACH ROW BEGIN IF NEW.node_id IS NULL THEN SET NEW.node_id = 'college'; END IF; END$$
DROP TRIGGER IF EXISTS trg_library_visits_origin_upd$$
CREATE TRIGGER trg_library_visits_origin_upd BEFORE UPDATE ON library_visits FOR EACH ROW BEGIN IF NEW.node_id IS NULL THEN SET NEW.node_id = 'college'; END IF; END$$
DELIMITER ;
DELIMITER $$
DROP TRIGGER IF EXISTS trg_book_reservations_origin_ins$$
CREATE TRIGGER trg_book_reservations_origin_ins BEFORE INSERT ON book_reservations FOR EACH ROW BEGIN IF NEW.node_id IS NULL THEN SET NEW.node_id = 'college'; END IF; END$$
DROP TRIGGER IF EXISTS trg_book_reservations_origin_upd$$
CREATE TRIGGER trg_book_reservations_origin_upd BEFORE UPDATE ON book_reservations FOR EACH ROW BEGIN IF NEW.node_id IS NULL THEN SET NEW.node_id = 'college'; END IF; END$$
DELIMITER ;
DELIMITER $$
DROP TRIGGER IF EXISTS trg_holidays_origin_ins$$
CREATE TRIGGER trg_holidays_origin_ins BEFORE INSERT ON holidays FOR EACH ROW BEGIN IF NEW.node_id IS NULL THEN SET NEW.node_id = 'college'; END IF; END$$
DROP TRIGGER IF EXISTS trg_holidays_origin_upd$$
CREATE TRIGGER trg_holidays_origin_upd BEFORE UPDATE ON holidays FOR EACH ROW BEGIN IF NEW.node_id IS NULL THEN SET NEW.node_id = 'college'; END IF; END$$
DELIMITER ;
DELIMITER $$
DROP TRIGGER IF EXISTS trg_digital_resources_origin_ins$$
CREATE TRIGGER trg_digital_resources_origin_ins BEFORE INSERT ON digital_resources FOR EACH ROW BEGIN IF NEW.node_id IS NULL THEN SET NEW.node_id = 'college'; END IF; END$$
DROP TRIGGER IF EXISTS trg_digital_resources_origin_upd$$
CREATE TRIGGER trg_digital_resources_origin_upd BEFORE UPDATE ON digital_resources FOR EACH ROW BEGIN IF NEW.node_id IS NULL THEN SET NEW.node_id = 'college'; END IF; END$$
DELIMITER ;
DELIMITER $$
DROP TRIGGER IF EXISTS trg_suggestions_origin_ins$$
CREATE TRIGGER trg_suggestions_origin_ins BEFORE INSERT ON suggestions FOR EACH ROW BEGIN IF NEW.node_id IS NULL THEN SET NEW.node_id = 'college'; END IF; END$$
DROP TRIGGER IF EXISTS trg_suggestions_origin_upd$$
CREATE TRIGGER trg_suggestions_origin_upd BEFORE UPDATE ON suggestions FOR EACH ROW BEGIN IF NEW.node_id IS NULL THEN SET NEW.node_id = 'college'; END IF; END$$
DELIMITER ;
DELIMITER $$
DROP TRIGGER IF EXISTS trg_feedback_origin_ins$$
CREATE TRIGGER trg_feedback_origin_ins BEFORE INSERT ON feedback FOR EACH ROW BEGIN IF NEW.node_id IS NULL THEN SET NEW.node_id = 'college'; END IF; END$$
DROP TRIGGER IF EXISTS trg_feedback_origin_upd$$
CREATE TRIGGER trg_feedback_origin_upd BEFORE UPDATE ON feedback FOR EACH ROW BEGIN IF NEW.node_id IS NULL THEN SET NEW.node_id = 'college'; END IF; END$$
DELIMITER ;
DELIMITER $$
DROP TRIGGER IF EXISTS trg_book_images_origin_ins$$
CREATE TRIGGER trg_book_images_origin_ins BEFORE INSERT ON book_images FOR EACH ROW BEGIN IF NEW.node_id IS NULL THEN SET NEW.node_id = 'college'; END IF; END$$
DROP TRIGGER IF EXISTS trg_book_images_origin_upd$$
CREATE TRIGGER trg_book_images_origin_upd BEFORE UPDATE ON book_images FOR EACH ROW BEGIN IF NEW.node_id IS NULL THEN SET NEW.node_id = 'college'; END IF; END$$
DELIMITER ;
DELIMITER $$
DROP TRIGGER IF EXISTS trg_library_rules_origin_ins$$
CREATE TRIGGER trg_library_rules_origin_ins BEFORE INSERT ON library_rules FOR EACH ROW BEGIN IF NEW.node_id IS NULL THEN SET NEW.node_id = 'college'; END IF; END$$
DROP TRIGGER IF EXISTS trg_library_rules_origin_upd$$
CREATE TRIGGER trg_library_rules_origin_upd BEFORE UPDATE ON library_rules FOR EACH ROW BEGIN IF NEW.node_id IS NULL THEN SET NEW.node_id = 'college'; END IF; END$$
DELIMITER ;
DELIMITER $$
DROP TRIGGER IF EXISTS trg_announcements_origin_ins$$
CREATE TRIGGER trg_announcements_origin_ins BEFORE INSERT ON announcements FOR EACH ROW BEGIN IF NEW.node_id IS NULL THEN SET NEW.node_id = 'college'; END IF; END$$
DROP TRIGGER IF EXISTS trg_announcements_origin_upd$$
CREATE TRIGGER trg_announcements_origin_upd BEFORE UPDATE ON announcements FOR EACH ROW BEGIN IF NEW.node_id IS NULL THEN SET NEW.node_id = 'college'; END IF; END$$
DELIMITER ;
DELIMITER $$
DROP TRIGGER IF EXISTS trg_system_settings_origin_ins$$
CREATE TRIGGER trg_system_settings_origin_ins BEFORE INSERT ON system_settings FOR EACH ROW BEGIN IF NEW.node_id IS NULL THEN SET NEW.node_id = 'college'; END IF; END$$
DROP TRIGGER IF EXISTS trg_system_settings_origin_upd$$
CREATE TRIGGER trg_system_settings_origin_upd BEFORE UPDATE ON system_settings FOR EACH ROW BEGIN IF NEW.node_id IS NULL THEN SET NEW.node_id = 'college'; END IF; END$$
DELIMITER ;
