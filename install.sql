-- Table: alsendo_order_pickup
CREATE TABLE IF NOT EXISTS `PREFIX_alsendo_order_pickup` (
    `id_cart` INT(11) NOT NULL,
    `id_order` INT(11) DEFAULT NULL,
    `pickup_point` TEXT NOT NULL,
    `pickup_point_display` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id_cart`),
    KEY `id_order` (`id_order`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Table: alsendo_order_sender_address
CREATE TABLE IF NOT EXISTS `PREFIX_alsendo_order_sender_address` (
    `id_order` INT(11) NOT NULL,
    `template_name` VARCHAR(255) DEFAULT NULL,
    `company_name` VARCHAR(255) DEFAULT NULL,
    `first_name` VARCHAR(255) DEFAULT NULL,
    `last_name` VARCHAR(255) DEFAULT NULL,
    `street` VARCHAR(255) DEFAULT NULL,
    `building_number` VARCHAR(64) DEFAULT NULL,
    `apartment_number` VARCHAR(64) DEFAULT NULL,
    `postal_code` VARCHAR(32) DEFAULT NULL,
    `city` VARCHAR(255) DEFAULT NULL,
    `contact_person` VARCHAR(255) DEFAULT NULL,
    `phone_number` VARCHAR(64) DEFAULT NULL,
    `email_address` VARCHAR(255) DEFAULT NULL,
    `bank_account_number` VARCHAR(64) DEFAULT NULL,
    PRIMARY KEY (`id_order`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Table: alsendo_order_shipping_address
CREATE TABLE IF NOT EXISTS `PREFIX_alsendo_order_shipping_address` (
    `id_order` INT(11) NOT NULL,
    `company` VARCHAR(255) DEFAULT NULL,
    `first_name` VARCHAR(255) DEFAULT NULL,
    `last_name` VARCHAR(255) DEFAULT NULL,
    `nip` VARCHAR(64) DEFAULT NULL,
    `address` VARCHAR(255) DEFAULT NULL,
    `address2` VARCHAR(255) DEFAULT NULL,
    `postal_code` VARCHAR(32) DEFAULT NULL,
    `town` VARCHAR(255) DEFAULT NULL,
    `country` VARCHAR(255) DEFAULT NULL,
    `phone` VARCHAR(64) DEFAULT NULL,
    PRIMARY KEY (`id_order`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Table: alsendo_order_package
CREATE TABLE IF NOT EXISTS `PREFIX_alsendo_order_package` (
    `id_order` INT(11) NOT NULL,
    `template_name` VARCHAR(255) DEFAULT NULL,
    `package_type` VARCHAR(255) DEFAULT NULL,
    `width` DECIMAL(10,2) DEFAULT NULL,
    `length` DECIMAL(10,2) DEFAULT NULL,
    `height` DECIMAL(10,2) DEFAULT NULL,
    `weight` DECIMAL(10,2) DEFAULT NULL,
    `cash_on_delivery` DECIMAL(10,2) DEFAULT NULL,
    `declared_value` DECIMAL(10,2) DEFAULT NULL,
    `shipment_content` TEXT DEFAULT NULL,
    `pickup_type` VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (`id_order`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Table: alsendo_order_shipment
CREATE TABLE IF NOT EXISTS `PREFIX_alsendo_order_shipment` (
    `id_order` INT(11) NOT NULL,
    `shipping_method` VARCHAR(255) DEFAULT NULL,
    `courier_service` VARCHAR(255) DEFAULT NULL,
    `pickup_point` VARCHAR(255) DEFAULT NULL,
    `price` DECIMAL(10,2) DEFAULT NULL,
    `waybill_number` VARCHAR(255) DEFAULT NULL,
    `status` VARCHAR(50) DEFAULT 'new',
    `data` LONGTEXT DEFAULT NULL,
    `tracking_url` VARCHAR(255) NULL,
    PRIMARY KEY (`id_order`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Add if not exists to your alsendo_order_details table
CREATE TABLE IF NOT EXISTS `PREFIX_alsendo_order_details` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `id_order` int(11) NOT NULL,
    `data` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `id_order` (`id_order`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add pickup point table for carts
CREATE TABLE IF NOT EXISTS `PREFIX_alsendo_order_pickup` (
    `id_cart` int(11) NOT NULL,
    `pickup_point` TEXT NOT NULL,
    `pickup_point_display` varchar(255) DEFAULT NULL,
    PRIMARY KEY (`id_cart`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table for Alsendo Shipping Methods
CREATE TABLE IF NOT EXISTS `PREFIX_alsendo_shipping_methods` (
    `id_alsendo_shipping_method` INT AUTO_INCREMENT PRIMARY KEY,
    `id_service` VARCHAR(64) DEFAULT NULL,
    `service_name` VARCHAR(128) DEFAULT NULL,
    `method_name` VARCHAR(128) NOT NULL,
    `price` DECIMAL(10,2) DEFAULT 0.00,
    `has_map` TINYINT(1) DEFAULT 0,
    `active` TINYINT(1) DEFAULT 1,
    `id_carrier` INT DEFAULT NULL,
    `id_shop` INT DEFAULT 1,
    `ship_via_pickup_point` TINYINT(1) DEFAULT 0,
    `pickup_request` TINYINT(1) DEFAULT 0,
    INDEX (`id_service`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Batch tracking (main)
CREATE TABLE `PREFIX_alsendo_bulk_send_batch` (
    `id_batch` INT AUTO_INCREMENT PRIMARY KEY,
    `batch_reference` VARCHAR(64) UNIQUE NOT NULL,
    `id_employee` INT NOT NULL,
    `id_shop` INT DEFAULT 1,
    `status` ENUM('pending', 'processing', 'completed', 'partial_error', 'cancelled', 'error') DEFAULT 'pending',
    `total_orders` INT DEFAULT 0,
    `successful_orders` INT DEFAULT 0,
    `failed_orders` INT DEFAULT 0,
    `processing_started_at` DATETIME NULL,
    `completed_at` DATETIME NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX (`status`, `created_at`),
    INDEX (`id_batch`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Individual items in batch
CREATE TABLE `PREFIX_alsendo_bulk_send_item` (
    `id_item` INT AUTO_INCREMENT PRIMARY KEY,
    `id_batch` INT NOT NULL,
    `id_order` INT NOT NULL,
    `status` ENUM('pending', 'processing', 'success', 'failed', 'cancelled') DEFAULT 'pending',
    `error_type` ENUM('validation', 'api', 'duplicate', 'timeout', 'other') NULL,
    `error_message` LONGTEXT NULL,
    `alsendo_order_id` VARCHAR(255) NULL,
    `waybill_number` VARCHAR(255) NULL,
    `tracking_url` VARCHAR(255) NULL,
    `shipment_data` LONGTEXT NULL,
    `attempted_at` DATETIME NULL,
    `completed_at` DATETIME NULL,
    `retry_count` INT DEFAULT 0,
    FOREIGN KEY (`id_batch`) REFERENCES `PREFIX_alsendo_bulk_send_batch`(`id_batch`) ON DELETE CASCADE,
    UNIQUE KEY `order_batch` (`id_order`, `id_batch`),
    INDEX (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;