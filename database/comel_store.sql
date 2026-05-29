-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: db:3306
-- Generation Time: May 28, 2026 at 06:25 AM
-- Server version: 8.0.46
-- PHP Version: 8.3.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `comel_store`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int NOT NULL,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `role` enum('superadmin','admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `created_at`, `role`) VALUES
(1, 'admin', '$2y$10$OtRgflMbKAKxVT0eSUWEcutLT06kKu4cRIspzvLR3NSqNKdGg8JGu', '2026-05-19 15:37:59', 'superadmin'),
(2, 'nefizon@gmail.com', '$2y$10$G.GiZwH9.R/NWU6Uf9r/Zuxex/qUYGsqbJkEaERjpVaEd8.p7973K', '2026-05-24 07:10:11', 'superadmin'),
(3, 'comelbabycare@outlook.com', '$2y$10$wHrwgXpXcx2353N9obR83O6pJ9y23RqiXGacdoOBuTk.nxuzGqD0.', '2026-05-26 01:44:54', 'superadmin');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('pakaian','produk') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'produk',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `type`, `created_at`) VALUES
(1, 'Pakaian Bayi', 'pakaian', '2026-05-19 15:37:59'),
(2, 'Produk Bayi', 'produk', '2026-05-19 15:37:59');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `name`, `phone`, `created_at`) VALUES
(1, 'Nefizon Rumyanis', '0169281036', '2026-05-19 22:33:53'),
(2, 'NEFIZON', '0136321806', '2026-05-20 07:58:56'),
(3, 'NEFI', '60136321806', '2026-05-22 09:36:59'),
(4, 'ISKANDAR', '6013621806', '2026-05-23 11:12:03'),
(5, 'NEFIZON RUMYANIS', '60169281036', '2026-05-26 09:21:28'),
(6, 'RAYYAN ISKANDAR BIN NEFIZON KPM-MURID', '60162511036', '2026-05-26 12:58:41');

-- --------------------------------------------------------

--
-- Table structure for table `customer_addresses`
--

CREATE TABLE `customer_addresses` (
  `id` int NOT NULL,
  `customer_id` int NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `address` text NOT NULL,
  `state` varchar(50) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `customer_addresses`
--

INSERT INTO `customer_addresses` (`id`, `customer_id`, `name`, `address`, `state`, `created_at`) VALUES
(1, 3, '', '27, JALAN ADENIUM 4', '', '2026-05-22 09:36:59'),
(2, 3, '', 'TEST MESAGE', '', '2026-05-22 09:52:47'),
(3, 3, 'NEFI', '27 JALAN ADENIUM 4', '', '2026-05-22 10:20:17'),
(4, 3, 'NEFI3', '27 JALAN ADENIUM', '', '2026-05-22 11:01:18'),
(5, 4, 'ISKANDAR', '27 JALAN ADENIUM', '', '2026-05-23 11:12:03'),
(6, 3, 'NEFI', 'NO 27 JLN ADENIUM 4\r\nBANDAR BUKIT BERUNTUNG\r\n48300', '', '2026-05-24 08:31:40'),
(7, 3, 'NEFIZON BIN RUMYANIS', '27 JALAN ADENIUM 4 SEKSYEN BB5 48300 BUKIT BERUNTUNG', 'Selangor', '2026-05-26 08:56:03'),
(8, 5, 'NEFIZON BIN RUMYANIS', '27 JALAN ADENIUM 4, BANDAR BUK\r\nBANDAR BUKIT BERUNTUNG', 'Selangor', '2026-05-26 09:21:28'),
(9, 6, 'RAYYAN ISKANDAR BIN NEFIZON KPM-MURID', 'NO 27 JALAN ADENIUM 4 BANDAR BUKIT BERUNTUNG', 'Selangor', '2026-05-26 12:58:41'),
(10, 5, 'NEFI', 'NO 27 JLN ADENIUM 4\r\nBANDAR BUKIT BERUNTUNG\r\n48300', 'Selangor', '2026-05-27 00:31:22'),
(11, 5, 'NEFIZON RUMYANIS', 'NO 27 JALAN ADENIUM', 'Selangor', '2026-05-28 03:15:00');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `order_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_id` int NOT NULL,
  `address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `state` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `postage` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` enum('pending','confirmed','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `tracking_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `courier_slip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `courier_notified_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `product_id` int DEFAULT NULL,
  `size_id` int DEFAULT NULL,
  `variant_id` int DEFAULT NULL,
  `variant_label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size_label` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `category_id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `stock` int NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `image`, `price`, `stock`, `created_at`, `updated_at`, `deleted_at`) VALUES
(4, 2, 'Minyak Telon My Baby (90ml)', 'Minyak telon untuk bayi yang membantu menghangatkan badan dan melegakan kembung perut. 90ml.', '/uploads/products/product_1779794849_168ca8d4.jpg', 10.00, 1000, '2026-05-19 16:04:51', '2026-05-28 01:35:19', NULL),
(8, 2, 'Tuam baby', '', '/uploads/products/product_1779374637_7a068421.jpg', 5.00, 93, '2026-05-21 14:43:57', '2026-05-28 01:34:07', NULL),
(9, 1, 'Stokin Pantang', '', '/uploads/products/product_1779939937_0451a3a7.jpg', 2.00, 97, '2026-05-21 14:49:40', '2026-05-28 03:59:44', NULL),
(10, 1, 'Pyjamas Kanak² - Play All Day', '', '/uploads/products/product_1779434233_d5c9773a.jpg', 0.00, 0, '2026-05-22 07:17:13', '2026-05-28 01:34:59', '2026-05-28 01:34:59'),
(11, 1, 'Swaddle wrap baby', '', '/uploads/products/product_1779932351_db0fe4ff.jpg', 10.00, 1, '2026-05-28 01:39:11', '2026-05-28 03:59:44', NULL),
(12, 2, 'Pigeon Wide Neck bottle 5oz', '', '/uploads/products/product_1779938454_9e2aec30.jpg', 35.00, 1, '2026-05-28 03:20:54', '2026-05-28 03:20:54', NULL),
(13, 2, 'Tommee Tippee 5oz RM25/sebotol', '', '/uploads/products/product_1779939727_6741b3fc.jpg', 25.00, 1000, '2026-05-28 03:42:07', '2026-05-28 03:44:54', NULL),
(14, 2, 'Tommee Tippee 9oz RM25/sebotol', 'Botol 9oz', '/uploads/products/product_1779939843_8a160e1d.jpg', 25.00, 0, '2026-05-28 03:44:03', '2026-05-28 03:44:43', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int NOT NULL,
  `product_id` int NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image`, `sort_order`) VALUES
(1, 10, '/uploads/products/product_1779434233_d5c9773a.jpg', 0),
(2, 4, '/uploads/products/product_1779794849_168ca8d4.jpg', 0),
(3, 11, '/uploads/products/product_1779932351_db0fe4ff.jpg', 0),
(4, 12, '/uploads/products/product_1779938454_9e2aec30.jpg', 0),
(5, 12, '/uploads/products/product_1779938478_350b01ec.jpg', 1),
(6, 13, '/uploads/products/product_1779939727_6741b3fc.jpg', 0),
(7, 14, '/uploads/products/product_1779939843_8a160e1d.jpg', 0),
(8, 9, '/uploads/products/product_1779939937_0451a3a7.jpg', 0);

-- --------------------------------------------------------

--
-- Table structure for table `product_sizes`
--

CREATE TABLE `product_sizes` (
  `id` int NOT NULL,
  `product_id` int NOT NULL,
  `size_label` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `stock` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_sizes`
--

INSERT INTO `product_sizes` (`id`, `product_id`, `size_label`, `price`, `stock`) VALUES
(19, 10, '1', 10.00, 0),
(20, 10, '2', 10.00, 1),
(21, 10, '3', 10.00, 1),
(22, 10, '4', 10.00, 1),
(23, 10, '5', 10.00, 1),
(24, 10, '6', 10.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `id` int NOT NULL,
  `product_id` int NOT NULL,
  `variant_label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `stock` int NOT NULL DEFAULT '0',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_variants`
--

INSERT INTO `product_variants` (`id`, `product_id`, `variant_label`, `price`, `stock`, `image`) VALUES
(3, 11, 'Cartoon', 10.00, 0, '/uploads/products/variant_1779936292_5518db13.jpg'),
(4, 11, 'Tent', 10.00, 1, '/uploads/products/variant_1779936292_a08adad6.jpg'),
(5, 11, 'Dinosaur', 10.00, 1, '/uploads/products/variant_1779936292_752839d2.jpg'),
(8, 14, 'Biru', 25.00, 1000, NULL),
(9, 14, 'Hijau', 25.00, 1000, NULL),
(10, 9, 'Merah', 5.00, 1, '/uploads/products/variant_1779940278_764ef3df.jpg'),
(11, 9, 'Hitam', 5.00, 1, '/uploads/products/variant_1779940278_5e8eab1b.jpg'),
(12, 9, 'Pink', 5.00, 1, '/uploads/products/variant_1779940278_5d7e9751.jpg'),
(13, 9, 'Grey', 5.00, 1, '/uploads/products/variant_1779940278_91594b29.jpg'),
(14, 9, 'Purple', 5.00, 1, '/uploads/products/variant_1779940279_63778430.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int NOT NULL,
  `key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `updated_at`) VALUES
(1, 'whatsapp_number', '60162559207', '2026-05-27 00:10:04'),
(2, 'qr_code_image', '/uploads/qr/qr_1779244774.jpg', '2026-05-20 02:39:34'),
(3, 'payment_instructions', 'Sila buat pembayaran melalui QR code di atas. Pastikan nama yang digunakan semasa pembayaran adalah sama dengan nama yang didaftarkan semasa membuat pesanan.', '2026-05-19 15:37:59'),
(4, 'ai_base_url', 'https://core.fiqstr.com/v1', '2026-05-20 05:15:36'),
(5, 'ai_api_key', 'fiq-4pBQ9WUe_-5xttashhTHZzNzR6KfmFRPlkJUfSPBEAA', '2026-05-20 05:15:36'),
(6, 'ai_model', 'fiqstr/claude-sonnet-4.6', '2026-05-20 05:15:36'),
(7, 'ai_provider', 'openai', '2026-05-20 00:20:09'),
(36, 'wawp_api_endpoint', 'https://api.wawp.net/v2/send/text', '2026-05-22 09:41:55'),
(37, 'wawp_api_key', 'mEmJfLROBJt2dE', '2026-05-22 05:20:42'),
(38, 'wawp_sender_id', '50301A738029', '2026-05-27 00:10:04'),
(79, 'notification_method', 'wawp', '2026-05-22 09:52:04'),
(80, 'app_name', 'Comel Baby Care', '2026-05-22 11:15:34'),
(88, 'base_url', 'https://comel.frenflo.com', '2026-05-26 09:27:41'),
(99, 'postage_fee', '7.00', '2026-05-26 09:27:41'),
(100, 'postage_fee_east', '12.00', '2026-05-26 09:27:41'),
(115, 'auto_cancel_enabled', '1', '2026-05-28 03:18:46'),
(116, 'auto_cancel_hours', '1', '2026-05-28 03:18:46');

-- --------------------------------------------------------

--
-- Table structure for table `whatsapp_logs`
--

CREATE TABLE `whatsapp_logs` (
  `id` int NOT NULL,
  `template_slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `recipient` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('sent','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sent',
  `error` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `sent_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `whatsapp_logs`
--

INSERT INTO `whatsapp_logs` (`id`, `template_slug`, `recipient`, `message`, `status`, `error`, `sent_at`) VALUES
(1, 'order_confirmation', '60136321806', 'Hi! Salam 😊\r\n\r\nTerima kasih kerana membuat pesanan di Comel Baby Care! Kami dah terima order anda.\r\n\r\n📦 *No. Pesanan: CML-000008*\r\n👤 Nama: NEFI\r\n📍 Alamat: NO 27 JLN ADENIUM 4\r\nBANDAR BUKIT BERUNTUNG\r\n48300, LABUAN\r\n\r\n*Senarai Pesanan:*\r\n1. Minyak Telon My Baby (90ml} x1 — RM10.00\n2. Tuam baby x3 — RM15.00\n\r\n💰 *Jumlah Keseluruhan: RM37.00*\r\n\r\nBayaran boleh dibuat melalui QR code yang kami hantar selepas ini ya 👇\r\n\r\nSelepas buat payment, mohon hantar slip payment kepada kami ya. Disebabkan permintaan produk yang tinggi, *kami hanya boleh hold barang* sekejap je. Kalau payment lebih dari *2 jam* belum dibuat, order ni terpaksa kami batalkan untuk beri laluan kepada customer lain yang nak order produk yang sama. Terima kasih faham! 🙏', 'sent', '', '2026-05-26 08:47:42'),
(2, 'order_confirmation', '60136321806', 'Hi! Salam 😊\r\n\r\nTerima kasih kerana membuat pesanan di Comel Baby Care! Kami dah terima order anda.\r\n\r\n📦 *No. Pesanan: CML-000009*\r\n👤 Nama: NEFIZON BIN RUMYANIS\r\n📍 Alamat: 27 JALAN ADENIUM 4 SEKSYEN BB5 48300 BUKIT BERUNTUNG, SELANGOR\r\n\r\n*Senarai Pesanan:*\r\n1. Minyak Telon My Baby (90ml} x1 — RM10.00\n\r\n💰 *Jumlah Keseluruhan: RM17.00*\r\n\r\nBayaran boleh dibuat melalui QR code yang kami hantar selepas ini ya 👇\r\n\r\nSelepas buat payment, mohon hantar slip payment kepada kami ya. Disebabkan permintaan produk yang tinggi, *kami hanya boleh hold barang* sekejap je. Kalau payment lebih dari *2 jam* belum dibuat, order ni terpaksa kami batalkan untuk beri laluan kepada customer lain yang nak order produk yang sama. Terima kasih faham! 🙏', 'sent', '', '2026-05-26 08:56:04'),
(3, 'qr_payment', '60136321806', 'Sila buat bayaran sebanyak *RM17.00* menggunakan QR code ni ya 👆\n\nSelepas bayar, hantar slip payment kepada kami. TQ! 😊', 'sent', '', '2026-05-26 08:56:07'),
(4, 'order_confirmation', '60136321806', 'Hi! Salam 😊\r\n\r\nTerima kasih kerana membuat pesanan di Comel Baby Care! Kami dah terima order anda.\r\n\r\n📦 *No. Pesanan: CML-000010*\r\n👤 Nama: NEFIZON BIN RUMYANIS\r\n📍 Alamat: 27 JALAN ADENIUM 4 SEKSYEN BB5 48300 BUKIT BERUNTUNG, SELANGOR\r\n\r\n*Senarai Pesanan:*\r\n1. Tuam baby x1 — RM5.00\n\r\n💰 *Jumlah Keseluruhan: RM12.00*\r\n\r\nBayaran boleh dibuat melalui QR code yang kami hantar selepas ini ya 👇\r\n\r\nSelepas buat payment, mohon hantar slip payment kepada kami ya. Disebabkan permintaan produk yang tinggi, *kami hanya boleh hold barang* sekejap je. Kalau payment lebih dari *2 jam* belum dibuat, order ni terpaksa kami batalkan untuk beri laluan kepada customer lain yang nak order produk yang sama. Terima kasih faham! 🙏', 'sent', '', '2026-05-26 09:12:54'),
(5, 'qr_payment', '60136321806', 'Sila buat bayaran sebanyak *RM12.00* menggunakan QR code ni ya 👆\n\nSelepas bayar, hantar slip payment kepada kami. TQ! 😊', 'sent', '', '2026-05-26 09:12:56'),
(6, 'order_confirmation', '60169281036', 'Hi! Salam 😊\r\n\r\nTerima kasih kerana membuat pesanan di Comel Baby Care! Kami dah terima order anda.\r\n\r\n📦 *No. Pesanan: CML-000011*\r\n👤 Nama: NEFIZON BIN RUMYANIS\r\n📍 Alamat: 27 JALAN ADENIUM 4, BANDAR BUK\r\nBANDAR BUKIT BERUNTUNG, SELANGOR\r\n\r\n*Senarai Pesanan:*\r\n1. Tuam baby x1 — RM5.00\n\r\n💰 *Jumlah Keseluruhan: RM12.00*\r\n\r\nBayaran boleh dibuat melalui QR code yang kami hantar selepas ini ya 👇\r\n\r\nSelepas buat payment, mohon hantar slip payment kepada kami ya. Disebabkan permintaan produk yang tinggi, *kami hanya boleh hold barang* sekejap je. Kalau payment lebih dari *2 jam* belum dibuat, order ni terpaksa kami batalkan untuk beri laluan kepada customer lain yang nak order produk yang sama. Terima kasih faham! 🙏', 'sent', '', '2026-05-26 09:21:30'),
(7, 'qr_payment', '60169281036', 'Sila buat bayaran sebanyak *RM12.00* menggunakan QR code ni ya 👆\n\nSelepas bayar, hantar slip payment kepada kami. TQ! 😊', 'sent', '', '2026-05-26 09:21:34'),
(8, 'order_confirmation', '60136321806', 'Hi! Salam 😊\r\n\r\nTerima kasih kerana membuat pesanan di Comel Baby Care! Kami dah terima order anda.\r\n\r\n📦 *No. Pesanan: CML-000012*\r\n👤 Nama: NEFI\r\n📍 Alamat: NO 27 JLN ADENIUM 4\r\nBANDAR BUKIT BERUNTUNG\r\n48300, SELANGOR\r\n\r\n*Senarai Pesanan:*\r\n1. Tuam baby x1 — RM5.00\n\r\n💰 *Jumlah Keseluruhan: RM12.00*\r\n\r\nBayaran boleh dibuat melalui QR code yang kami hantar selepas ini ya 👇\r\n\r\nSelepas buat payment, mohon hantar slip payment kepada kami ya. Disebabkan permintaan produk yang tinggi, *kami hanya boleh hold barang* sekejap je. Kalau payment lebih dari *2 jam* belum dibuat, order ni terpaksa kami batalkan untuk beri laluan kepada customer lain yang nak order produk yang sama. Terima kasih faham! 🙏', 'sent', '', '2026-05-26 09:25:32'),
(9, 'qr_payment', '60136321806', 'Sila buat bayaran sebanyak *RM12.00* menggunakan QR code ni ya 👆\n\nSelepas bayar, hantar slip payment kepada kami. TQ! 😊', 'sent', '', '2026-05-26 09:25:36'),
(10, 'order_confirmation', '60136321806', 'Hi! Salam 😊\r\n\r\nTerima kasih kerana membuat pesanan di Comel Baby Care! Kami dah terima order anda.\r\n\r\n📦 *No. Pesanan: CML-000013*\r\n👤 Nama: NEFI\r\n📍 Alamat: NO 27 JLN ADENIUM 4\r\nBANDAR BUKIT BERUNTUNG\r\n48300, SARAWAK\r\n\r\n*Senarai Pesanan:*\r\n1. Stokin Pantang x1 — RM2.00\n\r\n💰 *Jumlah Keseluruhan: RM14.00*\r\n\r\nBayaran boleh dibuat melalui QR code yang kami hantar selepas ini ya 👇\r\n\r\nSelepas buat payment, mohon hantar slip payment kepada kami ya. Disebabkan permintaan produk yang tinggi, *kami hanya boleh hold barang* sekejap je. Kalau payment lebih dari *2 jam* belum dibuat, order ni terpaksa kami batalkan untuk beri laluan kepada customer lain yang nak order produk yang sama. Terima kasih faham! 🙏', 'sent', '', '2026-05-26 09:49:23'),
(11, 'qr_payment', '60136321806', 'Sila buat bayaran sebanyak *RM14.00* menggunakan QR code ni ya 👆\n\nSelepas bayar, hantar slip payment kepada kami. TQ! 😊', 'sent', '', '2026-05-26 09:49:26'),
(12, 'order_confirmation', '60162511036', 'Hi! Salam 😊\r\n\r\nTerima kasih kerana membuat pesanan di Comel Baby Care! Kami dah terima order anda.\r\n\r\n📦 *No. Pesanan: CML-000014*\r\n👤 Nama: RAYYAN ISKANDAR BIN NEFIZON KPM-MURID\r\n📍 Alamat: NO 27 JALAN ADENIUM 4 BANDAR BUKIT BERUNTUNG, SELANGOR\r\n\r\n*Senarai Pesanan:*\r\n1. Pyjamas Kanak² - Play All Day (2) x1 — RM10.00\n2. Minyak Telon My Baby (90ml) x1 — RM10.00\n\r\n💰 *Jumlah Keseluruhan: RM27.00*\r\n\r\nBayaran boleh dibuat melalui QR code yang kami hantar selepas ini ya 👇\r\n\r\nSelepas buat payment, mohon hantar slip payment kepada kami ya. Disebabkan permintaan produk yang tinggi, *kami hanya boleh hold barang* sekejap je. Kalau payment lebih dari *2 jam* belum dibuat, order ni terpaksa kami batalkan untuk beri laluan kepada customer lain yang nak order produk yang sama. Terima kasih faham! 🙏', 'sent', '', '2026-05-26 12:58:43'),
(13, 'qr_payment', '60162511036', 'Sila buat bayaran sebanyak *RM27.00* menggunakan QR code ni ya 👆\n\nSelepas bayar, hantar slip payment kepada kami. TQ! 😊', 'sent', '', '2026-05-26 12:58:46'),
(14, 'order_confirmation', '60169281036', 'Hi! Salam 😊\r\n\r\nTerima kasih kerana membuat pesanan di Comel Baby Care! Kami dah terima order anda.\r\n\r\n📦 *No. Pesanan: CML-000015*\r\n👤 Nama: NEFI\r\n📍 Alamat: NO 27 JLN ADENIUM 4\r\nBANDAR BUKIT BERUNTUNG\r\n48300, SELANGOR\r\n\r\n*Senarai Pesanan:*\r\n1. Tuam baby x1 — RM5.00\n\r\n💰 *Jumlah Keseluruhan: RM12.00*\r\n\r\nBayaran boleh dibuat melalui QR code yang kami hantar selepas ini ya 👇\r\n\r\nSelepas buat payment, mohon hantar slip payment kepada kami ya. Disebabkan permintaan produk yang tinggi, *kami hanya boleh hold barang* sekejap je. Kalau payment lebih dari *2 jam* belum dibuat, order ni terpaksa kami batalkan untuk beri laluan kepada customer lain yang nak order produk yang sama. Terima kasih faham! 🙏', 'sent', '', '2026-05-27 00:31:24'),
(15, 'qr_payment', '60169281036', 'Sila buat bayaran sebanyak *RM12.00* menggunakan QR code ni ya 👆\n\nSelepas bayar, hantar slip payment kepada kami. TQ! 😊', 'sent', '', '2026-05-27 00:31:27'),
(16, 'order_confirmation', '60136321806', 'Hi! Salam 😊\r\n\r\nTerima kasih kerana membuat pesanan di Comel Baby Care! Kami dah terima order anda.\r\n\r\n📦 *No. Pesanan: CML-000016*\r\n👤 Nama: NEFI\r\n📍 Alamat: NO 27 JLN ADENIUM 4\r\nBANDAR BUKIT BERUNTUNG\r\n48300, SELANGOR\r\n\r\n*Senarai Pesanan:*\r\n1. Stokin Pantang x1 — RM2.00\n2. Tuam baby x2 — RM10.00\n\r\n💰 *Jumlah Keseluruhan: RM19.00*\r\n\r\nBayaran boleh dibuat melalui QR code yang kami hantar selepas ini ya 👇\r\n\r\nSelepas buat payment, mohon hantar slip payment kepada kami ya. Disebabkan permintaan produk yang tinggi, *kami hanya boleh hold barang* sekejap je. Kalau payment lebih dari *2 jam* belum dibuat, order ni terpaksa kami batalkan untuk beri laluan kepada customer lain yang nak order produk yang sama. Terima kasih faham! 🙏', 'sent', '', '2026-05-27 04:57:25'),
(17, 'qr_payment', '60136321806', 'Sila buat bayaran sebanyak *RM19.00* menggunakan QR code ni ya 👆\n\nSelepas bayar, hantar slip payment kepada kami. TQ! 😊', 'sent', '', '2026-05-27 04:57:28'),
(18, 'order_confirmation', '60169281036', 'Hi! Salam 😊\r\n\r\nTerima kasih kerana membuat pesanan di Comel Baby Care! Kami dah terima order anda.\r\n\r\n📦 *No. Pesanan: CML-000001*\r\n👤 Nama: NEFIZON RUMYANIS\r\n📍 Alamat: NO 27 JALAN ADENIUM, SELANGOR\r\n\r\n*Senarai Pesanan:*\r\n1. Stokin Pantang x2 — RM4.00\n2. Swaddle wrap baby [Cartoon] x1 — RM10.00\n\r\n💰 *Jumlah Keseluruhan: RM21.00*\r\n\r\nBayaran boleh dibuat melalui QR code yang kami hantar selepas ini ya 👇\r\n\r\nSelepas buat payment, mohon hantar slip payment kepada kami ya. Disebabkan permintaan produk yang tinggi, *kami hanya boleh hold barang* sekejap je. Kalau payment lebih dari *2 jam* belum dibuat, order ni terpaksa kami batalkan untuk beri laluan kepada customer lain yang nak order produk yang sama. Terima kasih faham! 🙏', 'sent', '', '2026-05-28 03:15:04'),
(19, 'qr_payment', '60169281036', 'Sila buat bayaran sebanyak *RM21.00* menggunakan QR code ni ya 👆\n\nSelepas bayar, hantar slip payment kepada kami. TQ! 😊', 'sent', '', '2026-05-28 03:15:08');

-- --------------------------------------------------------

--
-- Table structure for table `whatsapp_templates`
--

CREATE TABLE `whatsapp_templates` (
  `id` int NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `whatsapp_templates`
--

INSERT INTO `whatsapp_templates` (`id`, `slug`, `name`, `body`, `updated_at`) VALUES
(1, 'order_confirmation', 'Pengesahan Pesanan (Auto)', 'Hi! Salam 😊\r\n\r\nTerima kasih kerana membuat pesanan di {{app_name}}! Kami dah terima order anda.\r\n\r\n📦 *No. Pesanan: {{order_number}}*\r\n👤 Nama: {{customer_name}}\r\n📍 Alamat: {{address}}\r\n\r\n*Senarai Pesanan:*\r\n{{item_lines}}\r\n💰 *Jumlah Keseluruhan: RM{{total}}*\r\n\r\nBayaran boleh dibuat melalui QR code yang kami hantar selepas ini ya 👇\r\n\r\nSelepas buat payment, mohon hantar slip payment kepada kami ya. Disebabkan permintaan produk yang tinggi, *kami hanya boleh hold barang* sekejap je. Kalau payment lebih dari *2 jam* belum dibuat, order ni terpaksa kami batalkan untuk beri laluan kepada customer lain yang nak order produk yang sama. Terima kasih faham! 🙏', '2026-05-25 13:38:10'),
(2, 'qr_payment', 'Caption QR Pembayaran', 'Sila buat bayaran sebanyak *RM{{total}}* menggunakan QR code ni ya 👆\n\nSelepas bayar, hantar slip payment kepada kami. TQ! 😊', '2026-05-25 13:17:13'),
(3, 'courier_notify', 'Notifikasi Penghantaran (Slip Kurier)', 'Hi! Salam 😊\r\n\r\nPesanan anda *{{order_number}}* dah kami hantar ya!\r\n\r\n*Item Pesanan:*\r\n{{item_lines}}\r\n{{tracking_line}}\r\nNi slip penghantaran untuk pesanan anda 📦\r\n\r\nRujukan pesanan: {{ref_url}}\r\n\r\nTerima kasih kerana membeli dengan kami! Kalau ada apa-apa pertanyaan, jangan segan tanya ya 🙏', '2026-05-25 13:39:38'),
(4, 'blast', 'Blast Promosi', 'Hi! Salam 😊\n\nAda tawaran menarik untuk anda hari ini!\n\nJangan lepaskan peluang ini. Hubungi kami untuk maklumat lanjut. TQ! 🙏', '2026-05-25 13:17:13');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `phone` (`phone`);

--
-- Indexes for table `customer_addresses`
--
ALTER TABLE `customer_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `size_id` (`size_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `product_sizes`
--
ALTER TABLE `product_sizes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key` (`key`);

--
-- Indexes for table `whatsapp_logs`
--
ALTER TABLE `whatsapp_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `template_slug` (`template_slug`),
  ADD KEY `sent_at` (`sent_at`);

--
-- Indexes for table `whatsapp_templates`
--
ALTER TABLE `whatsapp_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `customer_addresses`
--
ALTER TABLE `customer_addresses`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `product_sizes`
--
ALTER TABLE `product_sizes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=117;

--
-- AUTO_INCREMENT for table `whatsapp_logs`
--
ALTER TABLE `whatsapp_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `whatsapp_templates`
--
ALTER TABLE `whatsapp_templates`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `customer_addresses`
--
ALTER TABLE `customer_addresses`
  ADD CONSTRAINT `customer_addresses_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_items_size` FOREIGN KEY (`size_id`) REFERENCES `product_sizes` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `fk_product_images_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_sizes`
--
ALTER TABLE `product_sizes`
  ADD CONSTRAINT `fk_sizes_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `fk_product_variants_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
