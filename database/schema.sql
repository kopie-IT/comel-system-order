-- Comel Baby Store - Database Schema
-- MySQL 8.0 / MariaDB 10.4+
-- Compatible with phpMyAdmin import
-- Generated: 2026-05-27

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET NAMES utf8mb4;
SET time_zone = "+08:00";

-- --------------------------------------------------------
-- Table: admins
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admins` (
  `id`         INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username`   VARCHAR(100) NOT NULL,
  `password`   VARCHAR(255) NOT NULL,
  `role`       ENUM('admin','superadmin') NOT NULL DEFAULT 'admin',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_admins_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default admin: username=admin, password=admin123 (bcrypt)
INSERT INTO `admins` (`username`, `password`, `role`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin');

-- --------------------------------------------------------
-- Table: categories
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categories` (
  `id`         INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(100) NOT NULL,
  `type`       ENUM('pakaian','produk') NOT NULL DEFAULT 'produk',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed categories
INSERT INTO `categories` (`name`, `type`) VALUES
('Pakaian Bayi', 'pakaian'),
('Produk Bayi',  'produk');

-- --------------------------------------------------------
-- Table: products
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `products` (
  `id`          INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` INT(11) UNSIGNED NOT NULL,
  `name`        VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `image`       VARCHAR(255) DEFAULT NULL,
  `price`       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `stock`       INT(11) NOT NULL DEFAULT 0,
  `deleted_at`  TIMESTAMP NULL DEFAULT NULL,
  `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_products_category` (`category_id`),
  KEY `idx_products_deleted`  (`deleted_at`),
  CONSTRAINT `fk_products_category`
    FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: product_sizes
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `product_sizes` (
  `id`         INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) UNSIGNED NOT NULL,
  `size_label` VARCHAR(50) NOT NULL,
  `price`      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `stock`      INT(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_product_sizes_product` (`product_id`),
  CONSTRAINT `fk_sizes_product`
    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: product_images
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `product_images` (
  `id`         INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) UNSIGNED NOT NULL,
  `image`      VARCHAR(255) NOT NULL,
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_product_images_product` (`product_id`),
  CONSTRAINT `fk_product_images_product`
    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: product_variants
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `product_variants` (
  `id`            INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id`    INT(11) UNSIGNED NOT NULL,
  `variant_label` VARCHAR(255) NOT NULL,
  `price`         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `stock`         INT(11) NOT NULL DEFAULT 0,
  `image`         VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_product_variants_product` (`product_id`),
  CONSTRAINT `fk_product_variants_product`
    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: customers
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `customers` (
  `id`         INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(255) NOT NULL,
  `phone`      VARCHAR(20) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_customers_phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: customer_addresses
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `customer_addresses` (
  `id`          INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_id` INT(11) UNSIGNED NOT NULL,
  `name`        VARCHAR(255) NOT NULL DEFAULT '',
  `address`     TEXT NOT NULL,
  `state`       VARCHAR(50) NOT NULL DEFAULT '',
  `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_customer_addresses_customer` (`customer_id`),
  CONSTRAINT `fk_customer_addresses_customer`
    FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: orders
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `orders` (
  `id`                  INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_number`        VARCHAR(50) NOT NULL,
  `customer_id`         INT(11) UNSIGNED NOT NULL,
  `address`             TEXT NOT NULL,
  `state`               VARCHAR(50) NOT NULL DEFAULT '',
  `postage`             DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total`               DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `status`              ENUM('pending','confirmed','completed','cancelled') NOT NULL DEFAULT 'pending',
  `tracking_number`     VARCHAR(255) DEFAULT NULL,
  `courier_slip`        VARCHAR(255) DEFAULT NULL,
  `courier_notified_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at`          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_orders_order_number` (`order_number`),
  KEY `idx_orders_customer`   (`customer_id`),
  KEY `idx_orders_status`     (`status`),
  KEY `idx_orders_created_at` (`created_at`),
  CONSTRAINT `fk_orders_customer`
    FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: order_items
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `order_items` (
  `id`            INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id`      INT(11) UNSIGNED NOT NULL,
  `product_id`    INT(11) UNSIGNED NOT NULL,
  `size_id`       INT(11) UNSIGNED DEFAULT NULL,
  `variant_id`    INT(11) UNSIGNED DEFAULT NULL,
  `product_name`  VARCHAR(255) NOT NULL,
  `size_label`    VARCHAR(50) DEFAULT NULL,
  `variant_label` VARCHAR(255) DEFAULT NULL,
  `unit_price`    DECIMAL(10,2) NOT NULL,
  `quantity`      INT(11) NOT NULL DEFAULT 1,
  `subtotal`      DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_order_items_order`   (`order_id`),
  KEY `idx_order_items_product` (`product_id`),
  KEY `idx_order_items_size`    (`size_id`),
  KEY `idx_order_items_variant` (`variant_id`),
  CONSTRAINT `fk_items_order`
    FOREIGN KEY (`order_id`)   REFERENCES `orders` (`id`)        ON DELETE CASCADE  ON UPDATE CASCADE,
  CONSTRAINT `fk_items_product`
    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)      ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_items_size`
    FOREIGN KEY (`size_id`)    REFERENCES `product_sizes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_items_variant`
    FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: settings
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `settings` (
  `id`         INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `key`        VARCHAR(100) NOT NULL,
  `value`      TEXT DEFAULT NULL,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_settings_key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default settings
INSERT INTO `settings` (`key`, `value`) VALUES
('app_name',              'Comel Baby Store'),
('base_url',              ''),
('whatsapp_number',       '601234567890'),
('qr_code_image',         NULL),
('postage_fee',           '7.00'),
('postage_fee_east',      '12.00'),
('notification_method',   'wa_link'),
('wawp_api_endpoint',     ''),
('wawp_api_key',          ''),
('wawp_sender_id',        ''),
('auto_cancel_enabled',   '1'),
('auto_cancel_hours',     '2'),
('ai_base_url',           ''),
('ai_api_key',            ''),
('ai_model',              ''),
('payment_instructions',  'Sila buat pembayaran melalui QR code di atas. Pastikan nama yang digunakan semasa pembayaran adalah sama dengan nama yang didaftarkan semasa membuat pesanan.');

-- --------------------------------------------------------
-- Table: whatsapp_templates
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `whatsapp_templates` (
  `id`         INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug`       VARCHAR(100) NOT NULL,
  `name`       VARCHAR(255) NOT NULL,
  `body`       TEXT NOT NULL,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_whatsapp_templates_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: whatsapp_logs
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `whatsapp_logs` (
  `id`            INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `template_slug` VARCHAR(100) NOT NULL DEFAULT '',
  `recipient`     VARCHAR(50) NOT NULL,
  `message`       TEXT NOT NULL,
  `status`        ENUM('sent','failed') NOT NULL DEFAULT 'sent',
  `error`         VARCHAR(500) NOT NULL DEFAULT '',
  `sent_at`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_whatsapp_logs_slug`    (`template_slug`),
  KEY `idx_whatsapp_logs_sent_at` (`sent_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Sample products for testing
-- --------------------------------------------------------

-- Pakaian Bayi (category_id = 1)
INSERT INTO `products` (`category_id`, `name`, `description`, `image`, `price`, `stock`) VALUES
(1, 'Baju Romper Bayi Comel',  'Romper bayi yang lembut dan selesa, sesuai untuk bayi 0-12 bulan. Bahan cotton 100%.', NULL, 0.00, 0),
(1, 'Pyjama Set Bayi Bintang', 'Set pyjama bayi corak bintang yang comel. Bahan lembut dan mudah dicuci.',             NULL, 0.00, 0),
(1, 'Baju Kurung Mini Bayi',   'Baju kurung mini untuk bayi perempuan. Sesuai untuk majlis keluarga.',                 NULL, 0.00, 0);

-- Sizes for Romper (product_id = 1)
INSERT INTO `product_sizes` (`product_id`, `size_label`, `price`, `stock`) VALUES
(1, '0-3 bulan',  25.00, 10),
(1, '3-6 bulan',  27.00, 10),
(1, '6-9 bulan',  29.00,  8),
(1, '9-12 bulan', 31.00,  5);

-- Sizes for Pyjama Set (product_id = 2)
INSERT INTO `product_sizes` (`product_id`, `size_label`, `price`, `stock`) VALUES
(2, '0-3 bulan',  35.00, 8),
(2, '3-6 bulan',  37.00, 8),
(2, '6-12 bulan', 39.00, 6);

-- Sizes for Baju Kurung (product_id = 3)
INSERT INTO `product_sizes` (`product_id`, `size_label`, `price`, `stock`) VALUES
(3, '3-6 bulan',   45.00, 5),
(3, '6-12 bulan',  48.00, 5),
(3, '12-18 bulan', 52.00, 3);

-- Produk Bayi (category_id = 2)
INSERT INTO `products` (`category_id`, `name`, `description`, `image`, `price`, `stock`) VALUES
(2, 'Minyak Telon Bayi',   'Minyak telon untuk bayi yang membantu menghangatkan badan dan melegakan kembung perut. 100ml.', NULL, 12.90, 50),
(2, 'Bedak Bayi Lembut',   'Bedak bayi yang lembut dan selamat untuk kulit sensitif bayi. Bebas bahan kimia berbahaya. 200g.', NULL, 8.50, 40),
(2, 'Botol Susu Anti-Kolik', 'Botol susu dengan sistem anti-kolik untuk mengurangkan masalah gas pada bayi. 150ml.',          NULL, 32.00, 25);

SET FOREIGN_KEY_CHECKS = 1;
