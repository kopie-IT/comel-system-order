<?php

// -------------------------------------------------------
// Database Configuration
// For cPanel: fill in your actual DB credentials below.
// For Docker: values are read from environment variables.
// -------------------------------------------------------
define('DB_HOST',    getenv('DB_HOST')    ?: 'localhost');
define('DB_PORT',    getenv('DB_PORT')    ?: '3306');
define('DB_NAME',    getenv('DB_NAME')    ?: 'your_cpanel_dbname');
define('DB_USER',    getenv('DB_USER')    ?: 'your_cpanel_dbuser');
define('DB_PASS',    getenv('DB_PASS')    ?: 'your_cpanel_dbpass');
define('DB_CHARSET', 'utf8mb4');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
        );
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            // Auto-migrate: ensure product_images table exists
            $pdo->exec("CREATE TABLE IF NOT EXISTS `product_images` (
                `id`         INT(11) NOT NULL AUTO_INCREMENT,
                `product_id` INT(11) NOT NULL,
                `image`      VARCHAR(255) NOT NULL,
                `sort_order` INT(11) NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`),
                KEY `product_id` (`product_id`),
                CONSTRAINT `fk_product_images_product`
                    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            // Auto-migrate: ensure customer_addresses table exists
            $pdo->exec("CREATE TABLE IF NOT EXISTS `customer_addresses` (
                `id`          INT(11) NOT NULL AUTO_INCREMENT,
                `customer_id` INT(11) NOT NULL,
                `name`        VARCHAR(255) NOT NULL DEFAULT '',
                `address`     TEXT NOT NULL,
                `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `customer_id` (`customer_id`),
                CONSTRAINT `fk_customer_addresses_customer`
                    FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            // Add name column to existing customer_addresses installations
            try {
                $pdo->exec("ALTER TABLE `customer_addresses` ADD COLUMN `name` VARCHAR(255) NOT NULL DEFAULT '' AFTER `customer_id`");
            } catch (PDOException $ignored) {
                // Column already exists — safe to ignore
            }
            // Add state column to customer_addresses (Malaysian state)
            try {
                $pdo->exec("ALTER TABLE `customer_addresses` ADD COLUMN `state` VARCHAR(50) NOT NULL DEFAULT '' AFTER `address`");
            } catch (PDOException $ignored) {
                // Column already exists — safe to ignore
            }
            // Add state + postage columns to orders
            try {
                $pdo->exec("ALTER TABLE `orders` ADD COLUMN `state` VARCHAR(50) NOT NULL DEFAULT '' AFTER `address`");
            } catch (PDOException $ignored) {
                // Column already exists — safe to ignore
            }
            try {
                $pdo->exec("ALTER TABLE `orders` ADD COLUMN `postage` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `state`");
            } catch (PDOException $ignored) {
                // Column already exists — safe to ignore
            }
            // Auto-migrate: product_variants table
            $pdo->exec("CREATE TABLE IF NOT EXISTS `product_variants` (
                `id`            INT(11) NOT NULL AUTO_INCREMENT,
                `product_id`    INT(11) NOT NULL,
                `variant_label` VARCHAR(255) NOT NULL,
                `price`         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                `stock`         INT(11) NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`),
                KEY `product_id` (`product_id`),
                CONSTRAINT `fk_product_variants_product`
                    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            // Add variant columns to order_items
            try { $pdo->exec("ALTER TABLE `order_items` ADD COLUMN `variant_id` INT(11) NULL AFTER `size_id`"); } catch (PDOException $ignored2) {}
            try { $pdo->exec("ALTER TABLE `order_items` ADD COLUMN `variant_label` VARCHAR(255) NULL AFTER `variant_id`"); } catch (PDOException $ignored3) {}
            // Auto-migrate: whatsapp_templates table
            $pdo->exec("CREATE TABLE IF NOT EXISTS `whatsapp_templates` (
                `id`         INT(11) NOT NULL AUTO_INCREMENT,
                `slug`       VARCHAR(100) NOT NULL UNIQUE,
                `name`       VARCHAR(255) NOT NULL,
                `body`       TEXT NOT NULL,
                `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            // Auto-migrate: whatsapp_logs table
            $pdo->exec("CREATE TABLE IF NOT EXISTS `whatsapp_logs` (
                `id`           INT(11) NOT NULL AUTO_INCREMENT,
                `template_slug` VARCHAR(100) NOT NULL DEFAULT '',
                `recipient`    VARCHAR(50) NOT NULL,
                `message`      TEXT NOT NULL,
                `status`       ENUM('sent','failed') NOT NULL DEFAULT 'sent',
                `error`        VARCHAR(500) NOT NULL DEFAULT '',
                `sent_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `template_slug` (`template_slug`),
                KEY `sent_at` (`sent_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            // Seed default templates if table is empty
            $count = (int)$pdo->query("SELECT COUNT(*) FROM `whatsapp_templates`")->fetchColumn();
            if ($count === 0) {
                $defaults = [
                    [
                        'slug' => 'order_confirmation',
                        'name' => 'Pengesahan Pesanan (Auto)',
                        'body' => "Hi! Salam 😊\n\nTerima kasih kerana membuat pesanan di {{app_name}}! Kami dah terima order anda.\n\n📦 *No. Pesanan: {{order_number}}*\n👤 Nama: {{customer_name}}\n📍 Alamat: {{address}}\n\n*Senarai Pesanan:*\n{{item_lines}}\n💰 *Jumlah Keseluruhan: RM{{total}}*\n\nBayaran boleh dibuat melalui QR code yang kami hantar selepas ini ya 👇\n\nSelepas buat payment, mohon hantar slip payment kepada kami ya. Disebabkan permintaan produk yang tinggi, kami hanya boleh hold barang sekejap je. Kalau payment lebih dari 2 jam belum dibuat, order ni terpaksa kami batalkan untuk beri laluan kepada customer lain yang nak order produk yang sama. Terima kasih faham! 🙏",
                    ],
                    [
                        'slug' => 'qr_payment',
                        'name' => 'Caption QR Pembayaran',
                        'body' => "Sila buat bayaran sebanyak *RM{{total}}* menggunakan QR code ni ya 👆\n\nSelepas bayar, hantar slip payment kepada kami. TQ! 😊",
                    ],
                    [
                        'slug' => 'courier_notify',
                        'name' => 'Notifikasi Penghantaran (Slip Kurier)',
                        'body' => "Hi! Salam 😊\n\nPesanan anda *{{order_number}}* dah kami hantar ya!\n\n*Item Pesanan:*\n{{item_lines}}{{tracking_line}}\nNi slip penghantaran untuk pesanan anda 📦\n\nRujukan pesanan: {{ref_url}}\n\nTerima kasih kerana membeli dengan kami! Kalau ada apa-apa pertanyaan, jangan segan tanya ya 🙏",
                    ],
                    [
                        'slug' => 'blast',
                        'name' => 'Blast Promosi',
                        'body' => "Hi! Salam 😊\n\nAda tawaran menarik untuk anda hari ini!\n\nJangan lepaskan peluang ini. Hubungi kami untuk maklumat lanjut. TQ! 🙏",
                    ],
                ];
                $ins = $pdo->prepare("INSERT INTO `whatsapp_templates` (`slug`, `name`, `body`) VALUES (?, ?, ?)");
                foreach ($defaults as $tpl) {
                    $ins->execute([$tpl['slug'], $tpl['name'], $tpl['body']]);
                }
            }
        } catch (PDOException $e) {
            http_response_code(500);
            die('Database connection failed. Please try again later.');
        }
    }
    return $pdo;
}
