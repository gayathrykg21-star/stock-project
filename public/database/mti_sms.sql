-- =====================================================
-- MTI_SMS - Total Stock Management System
-- Database Schema & Sample Data
-- Compatible with MySQL 5.5+ / PHP 5.5+
-- =====================================================

-- Create Database
CREATE DATABASE IF NOT EXISTS `mti_sms` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `mti_sms`;

-- =====================================================
-- TABLE: users
-- Stores all system users with role-based access
-- =====================================================
DROP TABLE IF EXISTS `item_requests`;
DROP TABLE IF EXISTS `old_stock`;
DROP TABLE IF EXISTS `stock_out_logs`;
DROP TABLE IF EXISTS `stock_in_logs`;
DROP TABLE IF EXISTS `items`;
DROP TABLE IF EXISTS `subcategories`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `dispatch_counter`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(100) NOT NULL,
  `username` VARCHAR(50) NOT NULL,
  `password` VARCHAR(255) NOT NULL COMMENT 'In production, use password_hash()',
  `email` VARCHAR(100) DEFAULT NULL,
  `role` ENUM('STOCK_ADMIN','HOD','DEPT_IN_CHARGE','STAFF') NOT NULL DEFAULT 'STAFF',
  `department` VARCHAR(100) DEFAULT NULL,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_username` (`username`),
  KEY `idx_role` (`role`),
  KEY `idx_status` (`status`),
  KEY `idx_department` (`department`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Sample Users
INSERT INTO `users` (`id`, `full_name`, `username`, `password`, `email`, `role`, `department`, `status`) VALUES
(1, 'Admin User', 'admin', 'admin123', 'admin@mti.edu.in', 'STOCK_ADMIN', 'Admin', 'active'),
(2, 'Dr. Anand Sharma', 'hod_cs', 'hod123', 'hod_cs@mti.edu.in', 'HOD', 'Computer Science', 'active'),
(3, 'Mr. Ramesh Desai', 'dept_mech', 'dept123', 'dept_mech@mti.edu.in', 'DEPT_IN_CHARGE', 'Mechanical', 'active'),
(4, 'Ms. Priya Kulkarni', 'staff1', 'staff123', 'staff1@mti.edu.in', 'STAFF', 'Computer Science', 'active');

-- =====================================================
-- TABLE: categories
-- Stock item categories
-- =====================================================
CREATE TABLE `categories` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_category_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Sample Categories
INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Electronics'),
(2, 'Furniture'),
(3, 'Stationery'),
(4, 'Lab Equipment'),
(5, 'Sports Goods'),
(6, 'Cleaning Supplies');

-- =====================================================
-- TABLE: subcategories
-- Subcategories linked to parent categories
-- =====================================================
CREATE TABLE `subcategories` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `category_id` INT(11) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_category_id` (`category_id`),
  CONSTRAINT `fk_subcategory_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Sample Subcategories
INSERT INTO `subcategories` (`id`, `category_id`, `name`) VALUES
(1, 1, 'Resistors'),
(2, 1, 'Capacitors'),
(3, 1, 'Microcontrollers'),
(4, 2, 'Chairs'),
(5, 2, 'Tables'),
(6, 2, 'Almirahs'),
(7, 3, 'Pens & Pencils'),
(8, 3, 'Notebooks'),
(9, 4, 'Beakers'),
(10, 4, 'Oscilloscopes'),
(11, 5, 'Balls'),
(12, 6, 'Brooms');

-- =====================================================
-- TABLE: items
-- Master item register
-- =====================================================
CREATE TABLE `items` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(200) NOT NULL,
  `category_id` INT(11) NOT NULL,
  `subcategory_id` INT(11) DEFAULT NULL,
  `department` VARCHAR(100) DEFAULT NULL,
  `quantity` INT(11) NOT NULL DEFAULT 0,
  `description` TEXT,
  `entry_date` DATE DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category_id`),
  KEY `idx_subcategory` (`subcategory_id`),
  KEY `idx_department` (`department`),
  KEY `idx_quantity` (`quantity`),
  CONSTRAINT `fk_item_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_item_subcategory` FOREIGN KEY (`subcategory_id`) REFERENCES `subcategories` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Sample Items
INSERT INTO `items` (`id`, `name`, `category_id`, `subcategory_id`, `department`, `quantity`, `description`, `entry_date`) VALUES
(1, 'Arduino Uno Board', 1, 3, 'Electronics', 25, 'Microcontroller board', '2024-08-15'),
(2, 'Wooden Chair', 2, 4, 'Admin', 120, 'Standard classroom chair', '2024-06-10'),
(3, 'Lab Notebook 200pg', 3, 8, 'Computer Science', 500, '200 page ruled notebook', '2024-07-01'),
(4, 'Glass Beaker 500ml', 4, 9, 'Mechanical', 40, 'Borosilicate glass beaker', '2024-07-20'),
(5, 'Digital Oscilloscope', 4, 10, 'Electronics', 8, '2-channel 100MHz scope', '2024-05-12'),
(6, 'Cricket Ball (Leather)', 5, 11, 'Admin', 3, 'Standard leather ball', '2024-09-01'),
(7, 'Steel Table 4x3ft', 2, 5, 'Civil', 60, 'Steel office table', '2024-04-22'),
(8, '10K Resistor Pack', 1, 1, 'Electronics', 200, 'Pack of 100 resistors', '2024-08-05'),
(9, 'Blue Pen Box (10)', 3, 7, 'Admin', 5, 'Box of 10 blue pens', '2024-09-10'),
(10, 'Plastic Broom', 6, 12, 'Admin', 15, 'Standard floor broom', '2024-07-30');

-- =====================================================
-- TABLE: stock_in_logs
-- Records of stock additions
-- =====================================================
CREATE TABLE `stock_in_logs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `item_id` INT(11) NOT NULL,
  `qty` INT(11) NOT NULL,
  `log_date` DATE NOT NULL,
  `remarks` TEXT,
  `created_by` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_item_id` (`item_id`),
  KEY `idx_log_date` (`log_date`),
  KEY `idx_created_by` (`created_by`),
  CONSTRAINT `fk_stockin_item` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Sample Stock In Logs
INSERT INTO `stock_in_logs` (`id`, `item_id`, `qty`, `log_date`, `remarks`, `created_by`) VALUES
(1, 1, 10, '2024-09-15', 'New purchase', 'admin'),
(2, 2, 50, '2024-09-10', 'Bulk order received', 'admin'),
(3, 3, 200, '2024-09-12', 'Semester stock', 'admin');

-- =====================================================
-- TABLE: stock_out_logs
-- Records of dispatched / issued items
-- =====================================================
CREATE TABLE `stock_out_logs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `item_id` INT(11) NOT NULL,
  `qty` INT(11) NOT NULL,
  `issued_to` VARCHAR(200) NOT NULL,
  `dispatch_code` VARCHAR(50) NOT NULL,
  `log_date` DATE NOT NULL,
  `remarks` TEXT,
  `created_by` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_dispatch_code` (`dispatch_code`),
  KEY `idx_item_id` (`item_id`),
  KEY `idx_log_date` (`log_date`),
  KEY `idx_created_by` (`created_by`),
  CONSTRAINT `fk_stockout_item` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Sample Stock Out Logs
INSERT INTO `stock_out_logs` (`id`, `item_id`, `qty`, `issued_to`, `dispatch_code`, `log_date`, `remarks`, `created_by`) VALUES
(1, 2, 10, 'Mechanical Dept', 'DSP-001', '2024-09-18', 'Lab setup', 'admin'),
(2, 3, 100, 'CS Students Batch A', 'DSP-002', '2024-09-20', 'Lab distribution', 'admin');

-- =====================================================
-- TABLE: old_stock
-- Records of damaged / obsolete / expired stock
-- =====================================================
CREATE TABLE `old_stock` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `item_id` INT(11) NOT NULL,
  `qty` INT(11) NOT NULL,
  `reason` VARCHAR(100) NOT NULL,
  `log_date` DATE NOT NULL,
  `created_by` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_item_id` (`item_id`),
  KEY `idx_reason` (`reason`),
  KEY `idx_log_date` (`log_date`),
  CONSTRAINT `fk_oldstock_item` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Sample Old Stock
INSERT INTO `old_stock` (`id`, `item_id`, `qty`, `reason`, `log_date`, `created_by`) VALUES
(1, 5, 2, 'Non-functional', '2024-09-15', 'admin'),
(2, 2, 5, 'Damaged', '2024-09-10', 'dept_mech');

-- =====================================================
-- TABLE: item_requests
-- Item request and approval workflow
-- =====================================================
CREATE TABLE `item_requests` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `item_id` INT(11) NOT NULL,
  `qty` INT(11) NOT NULL,
  `requested_by` VARCHAR(50) NOT NULL,
  `reason` TEXT NOT NULL,
  `request_date` DATE NOT NULL,
  `status` ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `approved_by` VARCHAR(50) DEFAULT NULL,
  `approved_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_item_id` (`item_id`),
  KEY `idx_requested_by` (`requested_by`),
  KEY `idx_status` (`status`),
  KEY `idx_request_date` (`request_date`),
  CONSTRAINT `fk_request_item` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Sample Requests
INSERT INTO `item_requests` (`id`, `item_id`, `qty`, `requested_by`, `reason`, `request_date`, `status`) VALUES
(1, 1, 5, 'staff1', 'For IoT workshop', '2024-10-01', 'pending'),
(2, 3, 50, 'staff1', 'Lab assignment notebooks', '2024-10-02', 'pending'),
(3, 6, 2, 'staff1', 'Annual sports meet', '2024-10-03', 'pending');

-- =====================================================
-- TABLE: dispatch_counter
-- Auto-increment counter for dispatch codes
-- =====================================================
CREATE TABLE `dispatch_counter` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `counter_value` INT(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `dispatch_counter` (`id`, `counter_value`) VALUES (1, 3);

-- =====================================================
-- VIEWS for common queries
-- =====================================================

-- View: Items with category and subcategory names
CREATE OR REPLACE VIEW `v_items_detail` AS
SELECT
  i.id,
  i.name AS item_name,
  c.name AS category_name,
  IFNULL(sc.name, '-') AS subcategory_name,
  i.department,
  i.quantity,
  i.description,
  i.entry_date,
  CASE
    WHEN i.quantity <= 0 THEN 'OUT_OF_STOCK'
    WHEN i.quantity <= 5 THEN 'LOW_STOCK'
    ELSE 'IN_STOCK'
  END AS stock_status
FROM items i
LEFT JOIN categories c ON i.category_id = c.id
LEFT JOIN subcategories sc ON i.subcategory_id = sc.id;

-- View: Department-wise stock summary
CREATE OR REPLACE VIEW `v_dept_stock_summary` AS
SELECT
  department,
  COUNT(*) AS total_items,
  SUM(quantity) AS total_quantity,
  SUM(CASE WHEN quantity <= 5 THEN 1 ELSE 0 END) AS low_stock_count
FROM items
GROUP BY department;

-- View: Stock In summary
CREATE OR REPLACE VIEW `v_stock_in_summary` AS
SELECT
  sl.id,
  i.name AS item_name,
  sl.qty,
  sl.log_date,
  sl.remarks,
  sl.created_by
FROM stock_in_logs sl
LEFT JOIN items i ON sl.item_id = i.id
ORDER BY sl.log_date DESC;

-- View: Stock Out summary
CREATE OR REPLACE VIEW `v_stock_out_summary` AS
SELECT
  sol.id,
  i.name AS item_name,
  sol.qty,
  sol.issued_to,
  sol.dispatch_code,
  sol.log_date,
  sol.remarks,
  sol.created_by
FROM stock_out_logs sol
LEFT JOIN items i ON sol.item_id = i.id
ORDER BY sol.log_date DESC;

-- View: Pending requests
CREATE OR REPLACE VIEW `v_pending_requests` AS
SELECT
  ir.id,
  i.name AS item_name,
  ir.qty,
  ir.requested_by,
  ir.reason,
  ir.request_date,
  ir.status
FROM item_requests ir
LEFT JOIN items i ON ir.item_id = i.id
WHERE ir.status = 'pending'
ORDER BY ir.request_date DESC;

-- =====================================================
-- STORED PROCEDURES (MySQL 5.5 compatible)
-- =====================================================

DELIMITER //

-- Procedure: Process Stock In
CREATE PROCEDURE `sp_stock_in`(
  IN p_item_id INT,
  IN p_qty INT,
  IN p_log_date DATE,
  IN p_remarks TEXT,
  IN p_created_by VARCHAR(50)
)
BEGIN
  -- Update item quantity
  UPDATE items SET quantity = quantity + p_qty WHERE id = p_item_id;
  -- Insert log
  INSERT INTO stock_in_logs (item_id, qty, log_date, remarks, created_by)
  VALUES (p_item_id, p_qty, p_log_date, p_remarks, p_created_by);
END //

-- Procedure: Process Stock Out (Dispatch)
CREATE PROCEDURE `sp_stock_out`(
  IN p_item_id INT,
  IN p_qty INT,
  IN p_issued_to VARCHAR(200),
  IN p_log_date DATE,
  IN p_remarks TEXT,
  IN p_created_by VARCHAR(50),
  OUT p_dispatch_code VARCHAR(50)
)
BEGIN
  DECLARE v_counter INT;
  -- Get and increment counter
  SELECT counter_value INTO v_counter FROM dispatch_counter WHERE id = 1 FOR UPDATE;
  SET v_counter = v_counter + 1;
  UPDATE dispatch_counter SET counter_value = v_counter WHERE id = 1;
  SET p_dispatch_code = CONCAT('DSP-', LPAD(v_counter, 3, '0'));
  -- Update item quantity
  UPDATE items SET quantity = quantity - p_qty WHERE id = p_item_id;
  -- Insert log
  INSERT INTO stock_out_logs (item_id, qty, issued_to, dispatch_code, log_date, remarks, created_by)
  VALUES (p_item_id, p_qty, p_issued_to, p_dispatch_code, p_log_date, p_remarks, p_created_by);
END //

-- Procedure: Process Old Stock
CREATE PROCEDURE `sp_old_stock`(
  IN p_item_id INT,
  IN p_qty INT,
  IN p_reason VARCHAR(100),
  IN p_log_date DATE,
  IN p_created_by VARCHAR(50)
)
BEGIN
  UPDATE items SET quantity = quantity - p_qty WHERE id = p_item_id;
  INSERT INTO old_stock (item_id, qty, reason, log_date, created_by)
  VALUES (p_item_id, p_qty, p_reason, p_log_date, p_created_by);
END //

-- Procedure: Approve Request
CREATE PROCEDURE `sp_approve_request`(
  IN p_request_id INT,
  IN p_approved_by VARCHAR(50)
)
BEGIN
  DECLARE v_item_id INT;
  DECLARE v_qty INT;
  DECLARE v_requested_by VARCHAR(50);
  DECLARE v_reason TEXT;
  DECLARE v_counter INT;
  DECLARE v_dispatch_code VARCHAR(50);

  -- Get request details
  SELECT item_id, qty, requested_by, reason
  INTO v_item_id, v_qty, v_requested_by, v_reason
  FROM item_requests WHERE id = p_request_id AND status = 'pending';

  -- Update request status
  UPDATE item_requests
  SET status = 'approved', approved_by = p_approved_by, approved_at = NOW()
  WHERE id = p_request_id;

  -- Update item quantity
  UPDATE items SET quantity = quantity - v_qty WHERE id = v_item_id;

  -- Generate dispatch code
  SELECT counter_value INTO v_counter FROM dispatch_counter WHERE id = 1 FOR UPDATE;
  SET v_counter = v_counter + 1;
  UPDATE dispatch_counter SET counter_value = v_counter WHERE id = 1;
  SET v_dispatch_code = CONCAT('REQ-', LPAD(v_counter, 3, '0'));

  -- Create stock out log
  INSERT INTO stock_out_logs (item_id, qty, issued_to, dispatch_code, log_date, remarks, created_by)
  VALUES (v_item_id, v_qty, CONCAT('Request #', p_request_id, ' by ', v_requested_by),
          v_dispatch_code, CURDATE(), v_reason, p_approved_by);
END //

-- Procedure: Reject Request
CREATE PROCEDURE `sp_reject_request`(
  IN p_request_id INT,
  IN p_rejected_by VARCHAR(50)
)
BEGIN
  UPDATE item_requests
  SET status = 'rejected', approved_by = p_rejected_by, approved_at = NOW()
  WHERE id = p_request_id AND status = 'pending';
END //

DELIMITER ;

-- =====================================================
-- END OF DATABASE SCHEMA
-- =====================================================

-- To import this file:
-- mysql -u root -p < mti_sms.sql
-- Or via phpMyAdmin: Import > Choose File > mti_sms.sql
