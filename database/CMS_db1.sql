-- Create database if not exists
CREATE DATABASE IF NOT EXISTS `cms_db1`;
USE `cms_db1`;

-- Create branches table
CREATE TABLE `branches` (
  `id` int(30) NOT NULL AUTO_INCREMENT,
  `branch_code` varchar(50) NOT NULL,
  `street` text NOT NULL,
  `city` text NOT NULL,
  `state` text NOT NULL,
  `zip_code` varchar(50) NOT NULL,
  `country` text NOT NULL,
  `contact` varchar(100) NOT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create users table
CREATE TABLE `users` (
  `id` int(30) NOT NULL AUTO_INCREMENT,
  `firstname` varchar(200) NOT NULL,
  `lastname` varchar(200) NOT NULL,
  `email` varchar(200) NOT NULL,
  `password` text NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT 2 COMMENT '1 = admin, 2 = staff',
  `branch_id` int(30) DEFAULT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `branch_id` (`branch_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create parcels table
CREATE TABLE `parcels` (
  `id` int(30) NOT NULL AUTO_INCREMENT,
  `reference_number` varchar(100) NOT NULL,
  `sender_name` text NOT NULL,
  `sender_address` text NOT NULL,
  `sender_contact` text NOT NULL,
  `recipient_name` text NOT NULL,
  `recipient_address` text NOT NULL,
  `recipient_contact` text NOT NULL,
  `type` int(1) NOT NULL COMMENT '1 = Deliver, 2=Pickup',
  `from_branch_id` int(30) NOT NULL,
  `to_branch_id` int(30) NOT NULL,
  `weight` varchar(100) NOT NULL,
  `height` varchar(100) NOT NULL,
  `width` varchar(100) NOT NULL,
  `length` varchar(100) NOT NULL,
  `price` float NOT NULL,
  `status` int(2) NOT NULL DEFAULT 0,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `from_branch_id` (`from_branch_id`),
  KEY `to_branch_id` (`to_branch_id`),
  CONSTRAINT `parcels_ibfk_1` FOREIGN KEY (`from_branch_id`) REFERENCES `branches` (`id`),
  CONSTRAINT `parcels_ibfk_2` FOREIGN KEY (`to_branch_id`) REFERENCES `branches` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create payments table
CREATE TABLE `payments` (
  `id` int(30) NOT NULL AUTO_INCREMENT,
  `parcel_id` int(30) NOT NULL,
  `amount` float NOT NULL,
  `payment_method` varchar(100) NOT NULL,
  `payment_status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=pending, 1=paid, 2=refunded',
  `reference_number` varchar(100) DEFAULT NULL,
  `payment_date` datetime DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `parcel_id` (`parcel_id`),
  CONSTRAINT `fk_payment_parcel_id` FOREIGN KEY (`parcel_id`) REFERENCES `parcels` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create system_settings table
CREATE TABLE `system_settings` (
  `id` int(30) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `email` varchar(200) NOT NULL,
  `contact` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `cover_img` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default system settings
INSERT INTO `system_settings` (`id`, `name`, `email`, `contact`, `address`, `cover_img`) VALUES
(1, 'Courier Management System', 'info@cms.com', '+91 9876543200', 'Kankanadi Main Road, Mangalore, Karnataka, India', '');

-- Add Kankanadi and Mumbai branches
INSERT INTO `branches` (`branch_code`, `street`, `city`, `state`, `zip_code`, `country`, `contact`, `date_created`) VALUES
('KKD001', 'Kankanadi Main Road', 'Kankanadi', 'Karnataka', '575002', 'India', '+91 9876543210', NOW()),
('MUM001', 'Marine Drive', 'Mumbai', 'Maharashtra', '400001', 'India', '+91 9876543211', NOW());

-- Insert staff users with proper branch assignments
INSERT INTO `users` (`firstname`, `lastname`, `email`, `password`, `type`, `branch_id`, `date_created`) VALUES
('Administrator', '', 'admin@admin.com', md5('admin123'), 1, NULL, NOW()),
('John', 'Smith', 'staff1@test.com', md5('123'), 2, 1, NOW()),  -- Kankanadi Branch
('Jane', 'Doe', 'staff2@test.com', md5('123'), 2, 2, NOW());    -- Mumbai Branch

-- Insert sample parcels
INSERT INTO `parcels` (
    `reference_number`, 
    `sender_name`, 
    `sender_address`, 
    `sender_contact`, 
    `recipient_name`, 
    `recipient_address`, 
    `recipient_contact`, 
    `type`, 
    `from_branch_id`, 
    `to_branch_id`, 
    `weight`, 
    `height`, 
    `width`, 
    `length`, 
    `price`, 
    `status`, 
    `date_created`
) VALUES
('KM24010001', 'Rahul Kumar', 'Kankanadi Main Road', '+91 9876543001', 'Amit Shah', 'Marine Drive, Mumbai', '+91 9876543101', 1, 1, 2, '2.5', '30', '20', '15', 1500.00, 1, '2024-01-05 10:30:00'),
('KM24010002', 'Priya Singh', 'MG Road, Kankanadi', '+91 9876543002', 'Neha Patel', 'Colaba, Mumbai', '+91 9876543102', 1, 1, 2, '3.0', '25', '25', '20', 1800.00, 1, '2024-01-15 14:45:00'),
('KM24010003', 'Suresh Rao', 'Car Street, Kankanadi', '+91 9876543003', 'Raj Malhotra', 'Bandra West, Mumbai', '+91 9876543103', 1, 1, 2, '1.5', '20', '15', '10', 1200.00, 1, '2024-01-25 11:20:00');

-- Insert payments for Kankanadi-Mumbai parcels
INSERT INTO `payments` (
    `parcel_id`,
    `amount`,
    `payment_method`,
    `payment_status`,
    `reference_number`,
    `payment_date`,
    `remarks`,
    `date_created`
) VALUES
-- January Payments
(1, 1500.00, 'UPI', 1, 'KM-PAY-24010001', '2024-01-05 10:35:00', 'Payment via Google Pay', '2024-01-05 10:35:00'),
(2, 1800.00, 'Credit Card', 1, 'KM-PAY-24010002', '2024-01-15 15:00:00', 'HDFC Bank Credit Card', '2024-01-15 15:00:00'),
(3, 1200.00, 'Bank Transfer', 1, 'KM-PAY-24010003', '2024-01-25 11:30:00', 'NEFT Transfer', '2024-01-25 11:30:00');

-- Add more parcels first
INSERT INTO `parcels` (
    `reference_number`, 
    `sender_name`, 
    `sender_address`, 
    `sender_contact`, 
    `recipient_name`, 
    `recipient_address`, 
    `recipient_contact`, 
    `type`, 
    `from_branch_id`, 
    `to_branch_id`, 
    `weight`, 
    `height`, 
    `width`, 
    `length`, 
    `price`, 
    `status`, 
    `date_created`
) VALUES
-- February Parcels
('KM24020001', 'Ankit Shah', 'Kankanadi Main Road', '+91 9876543004', 'Ravi Patel', 'Marine Drive, Mumbai', '+91 9876543104', 1, 1, 2, '4.0', '35', '25', '20', 2200.00, 1, '2024-02-05 09:30:00'),
('KM24020002', 'Maya Singh', 'MG Road, Kankanadi', '+91 9876543005', 'Sonia Shah', 'Colaba, Mumbai', '+91 9876543105', 1, 1, 2, '2.8', '28', '22', '18', 1400.00, 1, '2024-02-15 16:45:00'),

-- March Parcels
('KM24030001', 'Kiran Kumar', 'Car Street, Kankanadi', '+91 9876543006', 'Anil Mehta', 'Bandra West, Mumbai', '+91 9876543106', 1, 1, 2, '3.5', '32', '24', '19', 1900.00, 1, '2024-03-10 14:00:00'),
('KM24030002', 'Neha Reddy', 'Temple Road, Kankanadi', '+91 9876543007', 'Priya Shah', 'Andheri, Mumbai', '+91 9876543107', 1, 1, 2, '3.2', '30', '23', '17', 1700.00, 1, '2024-03-20 10:15:00');

-- Then insert their corresponding payments
INSERT INTO `payments` (
    `parcel_id`,
    `amount`,
    `payment_method`,
    `payment_status`,
    `reference_number`,
    `payment_date`,
    `remarks`,
    `date_created`
) VALUES
-- February Payments
(4, 2200.00, 'Debit Card', 1, 'KM-PAY-24020001', '2024-02-05 09:30:00', 'SBI Debit Card', '2024-02-05 09:30:00'),
(5, 1400.00, 'Cash', 1, 'KM-PAY-24020002', '2024-02-15 16:45:00', 'Cash payment at branch', '2024-02-15 16:45:00'),

-- March Payments
(6, 1900.00, 'UPI', 1, 'KM-PAY-24030001', '2024-03-10 14:00:00', 'PhonePe payment', '2024-03-10 14:00:00'),
(7, 1700.00, 'Credit Card', 1, 'KM-PAY-24030002', '2024-03-20 10:15:00', 'ICICI Credit Card', '2024-03-20 10:15:00');

-- Add indexes for better performance
ALTER TABLE `payments` 
ADD INDEX `idx_payment_status` (`payment_status`),
ADD INDEX `idx_payment_date` (`payment_date`);

-- Update the staff users to ensure they have branch assignments
UPDATE users SET branch_id = 1 WHERE email = 'staff1@test.com';
UPDATE users SET branch_id = 2 WHERE email = 'staff2@test.com';

-- Verify staff-branch assignments
SELECT u.id, u.firstname, u.lastname, u.email, u.type, 
       u.branch_id, b.branch_code, b.city
FROM users u
LEFT JOIN branches b ON u.branch_id = b.id
WHERE u.type = 2;

-- Create parcel_tracks table if not exists
CREATE TABLE IF NOT EXISTS `parcel_tracks` (
  `id` int(30) NOT NULL AUTO_INCREMENT,
  `parcel_id` int(30) NOT NULL,
  `status` int(2) NOT NULL,
  `from_branch_id` int(30) DEFAULT NULL,
  `to_branch_id` int(30) DEFAULT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `parcel_id` (`parcel_id`),
  CONSTRAINT `parcel_tracks_ibfk_1` FOREIGN KEY (`parcel_id`) REFERENCES `parcels` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add some sample tracking data
INSERT INTO `parcel_tracks` (`parcel_id`, `status`, `from_branch_id`, `to_branch_id`, `date_created`) VALUES
(1, 1, 1, 2, '2024-01-05 11:00:00'),
(1, 2, 1, 2, '2024-01-05 14:00:00'),
(1, 3, 1, 2, '2024-01-06 09:00:00'),
(1, 4, 1, 2, '2024-01-07 10:00:00');

COMMIT;
