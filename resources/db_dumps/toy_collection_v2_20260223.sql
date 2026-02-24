-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Vært: 127.0.0.1:3306
-- Genereringstid: 23. 02 2026 kl. 04:13:11
-- Serverversion: 8.4.7
-- PHP-version: 8.5.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `toy_collection_v2`
--

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `catalog_toys`
--

DROP TABLE IF EXISTS `catalog_toys`;
CREATE TABLE IF NOT EXISTS `catalog_toys` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `toy_line_id` int NOT NULL,
  `product_type_id` int DEFAULT NULL,
  `entertainment_source_id` int DEFAULT NULL,
  `manufacturer_id` int DEFAULT NULL,
  `universe_id` int DEFAULT NULL,
  `year_released` year DEFAULT NULL,
  `wave` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assortment_sku` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `upc` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `fk_cat_toy_line` (`toy_line_id`),
  KEY `fk_cat_toy_manuf` (`manufacturer_id`),
  KEY `idx_cat_toy_name` (`name`),
  KEY `idx_cat_toy_year` (`year_released`),
  KEY `idx_cat_toy_deleted` (`deleted_at`),
  KEY `fk_cat_toy_prod` (`product_type_id`),
  KEY `fk_cat_toy_ent` (`entertainment_source_id`),
  KEY `idx_catalog_toys_created` (`created_at`),
  KEY `idx_cat_toy_universe` (`universe_id`)
) ENGINE=InnoDB AUTO_INCREMENT=114 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data dump for tabellen `catalog_toys`
--

INSERT INTO `catalog_toys` (`id`, `name`, `slug`, `toy_line_id`, `product_type_id`, `entertainment_source_id`, `manufacturer_id`, `universe_id`, `year_released`, `wave`, `assortment_sku`, `upc`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES
(101, 'Luke Skywalker (Tatooine)', 'luke-skywalker-tatooine-kenner', 7, 1, 1, 1, 1, '1978', 'Wave 1 (12-Back)', '38240', '076281382408', 'The original vintage Kenner release of Luke Skywalker with the double-telescoping or standard yellow lightsaber.', '2026-02-18 20:35:18', '2026-02-18 20:35:18', NULL),
(102, 'Han Solo', 'han-solo-tbs', 6, 1, 2, 2, 1, '2013', 'Wave 2', 'A4301', '653569865321', '6-inch highly articulated figure from The Black Series.', '2026-02-18 20:35:18', '2026-02-19 18:55:26', NULL),
(103, 'Millennium Falcon', 'millennium-falcon-lego-7190', 4, 2, 1, 7, 1, '2000', '', '7190', '000000007190', 'Classic original Lego Millennium Falcon release.', '2026-02-18 20:35:18', '2026-02-18 20:35:18', NULL),
(104, 'Darth Vader', 'darth-vader-1771607782', 7, 1, 1, 1, 1, '1978', '', '', '', NULL, '2026-02-20 17:16:22', '2026-02-20 17:16:22', NULL),
(105, 'Taun Taun', 'taun-taun-1771608326', 7, 1, 2, 1, 1, '1981', '', '', '', NULL, '2026-02-20 17:25:26', '2026-02-20 17:25:26', NULL),
(106, 'Darth Vader (Kenobi)', 'darth-vader-kenobi-1771622371', 5, 1, NULL, 2, 1, '2024', '', '', '', NULL, '2026-02-20 21:19:31', '2026-02-20 21:19:31', NULL),
(107, 'aewrewrqwer', 'aewrewrqwer-1771623116', 6, 1, NULL, 2, 1, '0000', '', '', '', NULL, '2026-02-20 21:31:56', '2026-02-20 21:31:56', NULL),
(108, 'wrqrqr wqer wewer w', 'wrqrqr-wqer-wewer-w-1771623588', 5, 3, NULL, 2, 1, '0000', '', '', '', NULL, '2026-02-20 21:39:48', '2026-02-20 21:39:48', NULL),
(109, '54543 52 t2t4', '54543-52-t2t4-1771624643', 6, 2, NULL, 2, 1, '0000', '', '', '', NULL, '2026-02-20 21:57:23', '2026-02-20 21:57:23', NULL),
(110, 'Lucky Luke', 'lucky-luke-1771625007', 7, 1, 5, 1, 1, '1981', '', '', '', NULL, '2026-02-20 22:03:27', '2026-02-20 22:03:27', NULL),
(111, 'Captain Haddock1', 'captain-haddock-1771625280', 6, 1, 3, 2, 1, '1979', '1', '2', '3', NULL, '2026-02-20 22:08:00', '2026-02-21 16:46:06', NULL),
(112, 'Bib', 'bib-1771625348', 5, 1, 3, 2, 1, '1984', '', '', '', NULL, '2026-02-20 22:09:08', '2026-02-20 22:09:08', NULL),
(113, 'jafsd fljdsæ ldsfj ælkdf', 'jafsd-fljds-ldsfj-lkdf-1771626277', 7, 1, NULL, 1, 1, '0000', '', '', '', NULL, '2026-02-20 22:24:37', '2026-02-20 22:24:37', NULL);

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `catalog_toy_items`
--

DROP TABLE IF EXISTS `catalog_toy_items`;
CREATE TABLE IF NOT EXISTS `catalog_toy_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `catalog_toy_id` int NOT NULL,
  `subject_id` int NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `fk_cat_item_toy` (`catalog_toy_id`),
  KEY `fk_cat_item_subj` (`subject_id`)
) ENGINE=InnoDB AUTO_INCREMENT=120 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data dump for tabellen `catalog_toy_items`
--

INSERT INTO `catalog_toy_items` (`id`, `catalog_toy_id`, `subject_id`, `description`) VALUES
(103, 103, 2, 'Classic yellow-faced Han Solo minifigure'),
(106, 104, 101, ''),
(107, 104, 104, ''),
(108, 105, 109, ''),
(109, 105, 112, ''),
(110, 106, 101, '1'),
(111, 106, 102, '2'),
(112, 107, 101, ''),
(113, 108, 106, ''),
(114, 109, 1, ''),
(115, 110, 1, ''),
(116, 111, 103, ''),
(117, 111, 105, ''),
(118, 112, 110, ''),
(119, 113, 2, '');

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `collection_sources`
--

DROP TABLE IF EXISTS `collection_sources`;
CREATE TABLE IF NOT EXISTS `collection_sources` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `collection_storage_units`
--

DROP TABLE IF EXISTS `collection_storage_units`;
CREATE TABLE IF NOT EXISTS `collection_storage_units` (
  `id` int NOT NULL AUTO_INCREMENT,
  `box_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data dump for tabellen `collection_storage_units`
--

INSERT INTO `collection_storage_units` (`id`, `box_code`, `name`, `location`, `description`) VALUES
(1, 'B00001', 'Kenner, Star Wars', 'Home office', 'From the first wave only');

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `collection_toys`
--

DROP TABLE IF EXISTS `collection_toys`;
CREATE TABLE IF NOT EXISTS `collection_toys` (
  `id` int NOT NULL AUTO_INCREMENT,
  `catalog_toy_id` int NOT NULL,
  `storage_unit_id` int DEFAULT NULL,
  `purchase_source_id` int DEFAULT NULL,
  `acquisition_status_id` int DEFAULT NULL,
  `date_acquired` date DEFAULT NULL,
  `purchase_price` decimal(10,2) DEFAULT NULL,
  `purchase_currency` char(3) COLLATE utf8mb4_unicode_ci DEFAULT 'USD',
  `current_value` decimal(10,2) DEFAULT NULL,
  `packaging_type_id` int DEFAULT NULL,
  `condition_grade_id` int DEFAULT NULL,
  `grader_company_id` int DEFAULT NULL,
  `grader_tier_id` int DEFAULT NULL,
  `grade_serial` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `grade_score` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_col_toy_cat` (`catalog_toy_id`),
  KEY `idx_col_toy_deleted` (`deleted_at`),
  KEY `fk_col_toy_store` (`storage_unit_id`),
  KEY `fk_col_toy_src` (`purchase_source_id`),
  KEY `fk_col_cond_grade` (`condition_grade_id`),
  KEY `fk_col_grad_comp` (`grader_company_id`),
  KEY `fk_col_grad_tier` (`grader_tier_id`),
  KEY `idx_collection_toys_created` (`created_at`),
  KEY `idx_collection_toys_acquisition` (`acquisition_status_id`),
  KEY `idx_collection_toys_packaging` (`packaging_type_id`),
  KEY `idx_collection_toys_deleted` (`deleted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=103 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data dump for tabellen `collection_toys`
--

INSERT INTO `collection_toys` (`id`, `catalog_toy_id`, `storage_unit_id`, `purchase_source_id`, `acquisition_status_id`, `date_acquired`, `purchase_price`, `purchase_currency`, `current_value`, `packaging_type_id`, `condition_grade_id`, `grader_company_id`, `grader_tier_id`, `grade_serial`, `grade_score`, `notes`, `created_at`, `updated_at`, `deleted_at`) VALUES
(101, 101, NULL, NULL, 1, NULL, 45.00, 'USD', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 20:35:18', '2026-02-18 20:35:18', NULL),
(102, 103, NULL, NULL, 1, NULL, 120.00, 'USD', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 20:35:18', '2026-02-18 20:35:18', NULL);

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `collection_toy_items`
--

DROP TABLE IF EXISTS `collection_toy_items`;
CREATE TABLE IF NOT EXISTS `collection_toy_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `collection_toy_id` int NOT NULL,
  `catalog_toy_item_id` int NOT NULL,
  `is_present` tinyint(1) DEFAULT '1',
  `is_repro` tinyint(1) DEFAULT '0',
  `acquisition_status_id` int DEFAULT NULL,
  `packaging_type_id` int DEFAULT NULL,
  `condition_grade_id` int DEFAULT NULL,
  `grader_company_id` int DEFAULT NULL,
  `grader_tier_id` int DEFAULT NULL,
  `grade_serial` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `grade_score` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `condition_notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_col_item_toy` (`collection_toy_id`),
  KEY `fk_col_item_cat` (`catalog_toy_item_id`),
  KEY `idx_col_item_acq` (`acquisition_status_id`),
  KEY `idx_col_item_pack` (`packaging_type_id`),
  KEY `idx_col_item_cond` (`condition_grade_id`),
  KEY `idx_col_item_grad_comp` (`grader_company_id`),
  KEY `idx_col_item_grad_tier` (`grader_tier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `importer_items`
--

DROP TABLE IF EXISTS `importer_items`;
CREATE TABLE IF NOT EXISTS `importer_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `source_id` int NOT NULL,
  `external_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `external_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `catalog_toy_id` int DEFAULT NULL,
  `last_imported_at` timestamp NULL DEFAULT NULL,
  `import_data_hash` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `source_external` (`source_id`,`external_id`),
  KEY `fk_imp_cat` (`catalog_toy_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `importer_logs`
--

DROP TABLE IF EXISTS `importer_logs`;
CREATE TABLE IF NOT EXISTS `importer_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `source_id` int NOT NULL,
  `importer_item_id` int DEFAULT NULL,
  `status` enum('Success','Warning','Error') COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_log_source` (`source_id`),
  KEY `fk_log_item` (`importer_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `importer_sources`
--

DROP TABLE IF EXISTS `importer_sources`;
CREATE TABLE IF NOT EXISTS `importer_sources` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `base_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `driver_class` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `media_files`
--

DROP TABLE IF EXISTS `media_files`;
CREATE TABLE IF NOT EXISTS `media_files` (
  `id` int NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `alt_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `filepath` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_media_deleted` (`deleted_at`),
  KEY `idx_media_files_deleted` (`deleted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data dump for tabellen `media_files`
--

INSERT INTO `media_files` (`id`, `filename`, `title`, `description`, `alt_text`, `original_name`, `filepath`, `file_type`, `file_size`, `created_at`, `deleted_at`) VALUES
(3, '006dafc4d5a93f5740994f9d9af0f83c.jpg', 'anton-lysykh-leshy-shole', NULL, 'anton-lysykh-leshy-shole', 'anton-lysykh-leshy-shole.jpg', 'uploads/media/006dafc4d5a93f5740994f9d9af0f83c.jpg', 'image/jpeg', 620055, '2026-02-18 06:07:50', NULL),
(4, 'c74fce4445d1499fba948d150f5e9c6b.jpg', 'anton-lysykh-leshy-shole', NULL, 'anton-lysykh-leshy-shole', 'anton-lysykh-leshy-shole.jpg', 'uploads/media/c74fce4445d1499fba948d150f5e9c6b.jpg', 'image/jpeg', 620055, '2026-02-18 06:07:50', NULL),
(5, 'ae419f1a52587a7cb03a941876f3a854.jpg', 'daniel-thomas-minealley', NULL, 'daniel-thomas-minealley', 'daniel-thomas-minealley.jpg', 'uploads/media/ae419f1a52587a7cb03a941876f3a854.jpg', 'image/jpeg', 281410, '2026-02-18 06:09:21', NULL),
(6, '6453fdfe5224e7692629c4a415a98ec3.jpg', 'daniel-thomas-minealley', NULL, 'daniel-thomas-minealley', 'daniel-thomas-minealley.jpg', 'uploads/media/6453fdfe5224e7692629c4a415a98ec3.jpg', 'image/jpeg', 281410, '2026-02-18 06:09:22', NULL),
(7, '42d7763bdf7e9e0f25ff85cd98d822dd.jpg', 'anton-lysykh-cat-syard-logos', '', 'anton-lysykh-cat-syard-logos', 'anton-lysykh-cat-syard-logos.jpg', 'uploads/media/42d7763bdf7e9e0f25ff85cd98d822dd.jpg', 'image/jpeg', 841498, '2026-02-18 06:09:35', NULL),
(8, '77a65ee7432fa1894f9cd7d02efdffbe.jpg', 'anton-lysykh-cat-syard-logos', NULL, 'anton-lysykh-cat-syard-logos', 'anton-lysykh-cat-syard-logos.jpg', 'uploads/media/77a65ee7432fa1894f9cd7d02efdffbe.jpg', 'image/jpeg', 841498, '2026-02-18 06:09:35', NULL),
(9, 'ac7fee65371a9099be92fa01c867d97f.jpeg', 'c9d01f9d-7fd2-4844-be9d-2c1252a69d14', '', 'c9d01f9d-7fd2-4844-be9d-2c1252a69d14', 'c9d01f9d-7fd2-4844-be9d-2c1252a69d14.jpeg', 'uploads/media/ac7fee65371a9099be92fa01c867d97f.jpeg', 'image/jpeg', 1610035, '2026-02-18 06:16:24', NULL),
(10, '0d16cbcd6f4216ba623eebcfda63e649.png', 'Amiga_Redux_e000007_00_20241202135918', '', 'Amiga_Redux_e000007_00_20241202135918', 'Amiga_Redux_e000007_00_20241202135918.png', 'uploads/media/0d16cbcd6f4216ba623eebcfda63e649.png', 'image/png', 527441, '2026-02-18 06:18:21', NULL),
(11, 'e8112aa0bd53dc3577118d1b828ca4dd.png', 'uai0aoq3b9h95uv0k9a6ka339l', NULL, 'uai0aoq3b9h95uv0k9a6ka339l', 'uai0aoq3b9h95uv0k9a6ka339l.png', 'uploads/media/e8112aa0bd53dc3577118d1b828ca4dd.png', 'image/png', 170861, '2026-02-18 06:33:58', NULL),
(12, '1166e94d3b417829d95257e2cbc53c71.jpg', '1600x1200-thumb_COLOURBOX13201364', NULL, '1600x1200-thumb_COLOURBOX13201364', '1600x1200-thumb_COLOURBOX13201364.jpg', 'uploads/media/1166e94d3b417829d95257e2cbc53c71.jpg', 'image/jpeg', 183783, '2026-02-18 06:35:30', NULL),
(13, 'b2dcb10983642b55b325584053cced32.jpg', 'super heroes - dc - fanart', NULL, 'super heroes - dc - fanart', 'super heroes - dc - fanart.jpg', 'uploads/media/b2dcb10983642b55b325584053cced32.jpg', 'image/jpeg', 374248, '2026-02-18 06:41:45', NULL),
(14, 'af8708fb9c9b6715704aa473924f59f7.jpeg', 'super heroes - dc - poster 2', '', 'super heroes - dc - poster 2', 'super heroes - dc - poster 2.jpeg', 'uploads/media/af8708fb9c9b6715704aa473924f59f7.jpeg', 'image/jpeg', 93620, '2026-02-18 06:41:45', NULL),
(15, '42b7ca2e4244b959f13c1f7171575f96.jpeg', 'super heroes - dc - poster', NULL, 'super heroes - dc - poster', 'super heroes - dc - poster.jpeg', 'uploads/media/42b7ca2e4244b959f13c1f7171575f96.jpeg', 'image/jpeg', 97425, '2026-02-18 06:41:45', NULL),
(16, 'f5b1e72bef38ec0916689b8d5713bba2.jpg', 'super heroes - marvel - poster', NULL, 'super heroes - marvel - poster', 'super heroes - marvel - poster.jpg', 'uploads/media/f5b1e72bef38ec0916689b8d5713bba2.jpg', 'image/jpeg', 799777, '2026-02-18 06:41:49', NULL),
(17, 'ad44aa5c9e1ecebcb0083639ad8c1eca.jpg', '9788740030556', NULL, '9788740030556', '9788740030556.jpg', 'uploads/media/ad44aa5c9e1ecebcb0083639ad8c1eca.jpg', 'image/jpeg', 705412, '2026-02-18 11:28:19', NULL),
(18, '9c96ce8e4389d4e5b991e78ec82ce6e8.jpg', '276154', NULL, '276154', '276154.jpg', 'uploads/media/9c96ce8e4389d4e5b991e78ec82ce6e8.jpg', 'image/jpeg', 611976, '2026-02-18 11:56:43', NULL),
(19, 'e9339fbab60f5369c50b8bf8017d97aa.jpg', 'poster - star wars i', NULL, 'poster - star wars i', 'poster - star wars i.jpg', 'uploads/media/e9339fbab60f5369c50b8bf8017d97aa.jpg', 'image/jpeg', 459633, '2026-02-20 21:57:32', NULL),
(20, 'd936b46c28526e8fdc52e027a728d9bd.jpg', 'poster - star wars music by john williams', NULL, 'poster - star wars music by john williams', 'poster - star wars music by john williams.jpg', 'uploads/media/d936b46c28526e8fdc52e027a728d9bd.jpg', 'image/jpeg', 519138, '2026-02-20 22:00:02', NULL),
(21, 'b49123359b05902cd3812343ab125198.jpg', 'poster - star wars i', NULL, 'poster - star wars i', 'poster - star wars i.jpg', 'uploads/media/b49123359b05902cd3812343ab125198.jpg', 'image/jpeg', 459633, '2026-02-20 22:03:37', NULL),
(22, '22f2a9bd9251954e4959ca94d7753c12.jpg', 'poster - indiana jones crystal skull making of', NULL, 'poster - indiana jones crystal skull making of', 'poster - indiana jones crystal skull making of.jpg', 'uploads/media/22f2a9bd9251954e4959ca94d7753c12.jpg', 'image/jpeg', 941107, '2026-02-20 22:03:44', NULL),
(23, '110769a902687b89892541a90671cb83.jpg', 'poster - goodfellas making of', NULL, 'poster - goodfellas making of', 'poster - goodfellas making of.jpg', 'uploads/media/110769a902687b89892541a90671cb83.jpg', 'image/jpeg', 347845, '2026-02-20 22:03:44', NULL),
(24, '55b5cbc7b73b5011315697176d59735c.jpg', '', '', '', 'poster - star wars i.jpg', 'uploads/media/55b5cbc7b73b5011315697176d59735c.jpg', 'image/jpeg', 459633, '2026-02-20 22:08:11', NULL),
(25, 'b24646655eafcd681d08d014f12ea93f.jpg', 'poster - star wars music by john williams', '', 'poster - star wars music by john williams', 'poster - star wars music by john williams.jpg', 'uploads/media/b24646655eafcd681d08d014f12ea93f.jpg', 'image/jpeg', 519138, '2026-02-20 22:08:29', NULL),
(26, '9231fbd31be2f1bff130a622056e032f.jpg', 'poster - the party', NULL, 'poster - the party', 'poster - the party.jpg', 'uploads/media/9231fbd31be2f1bff130a622056e032f.jpg', 'image/jpeg', 647655, '2026-02-20 22:09:13', NULL),
(27, 'b21d76ce4a64289d3061b4451f0eea39.jpg', 'poster - vertigo', NULL, 'poster - vertigo', 'poster - vertigo.jpg', 'uploads/media/b21d76ce4a64289d3061b4451f0eea39.jpg', 'image/jpeg', 447037, '2026-02-20 22:25:09', NULL);

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `media_file_tags`
--

DROP TABLE IF EXISTS `media_file_tags`;
CREATE TABLE IF NOT EXISTS `media_file_tags` (
  `media_file_id` int NOT NULL,
  `media_tag_id` int NOT NULL,
  PRIMARY KEY (`media_file_id`,`media_tag_id`),
  KEY `fk_tag_tag` (`media_tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data dump for tabellen `media_file_tags`
--

INSERT INTO `media_file_tags` (`media_file_id`, `media_tag_id`) VALUES
(24, 2),
(25, 2),
(25, 22),
(14, 23),
(24, 23),
(24, 24),
(25, 24),
(18, 25),
(7, 29),
(9, 29),
(10, 29),
(18, 39),
(14, 49);

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `media_links`
--

DROP TABLE IF EXISTS `media_links`;
CREATE TABLE IF NOT EXISTS `media_links` (
  `id` int NOT NULL AUTO_INCREMENT,
  `media_file_id` int NOT NULL,
  `entity_id` int NOT NULL,
  `entity_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_featured` tinyint(1) DEFAULT '0',
  `sort_order` int DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_link` (`media_file_id`,`entity_id`,`entity_type`),
  KEY `idx_entity_lookup` (`entity_type`,`entity_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data dump for tabellen `media_links`
--

INSERT INTO `media_links` (`id`, `media_file_id`, `entity_id`, `entity_type`, `is_featured`, `sort_order`) VALUES
(1, 16, 101, 'catalog_toys', 1, 0),
(2, 17, 102, 'catalog_toys', 1, 0),
(3, 13, 106, 'catalog_toys', 0, 0),
(4, 19, 109, 'catalog_toys', 0, 0),
(5, 20, 114, 'catalog_toy_items', 0, 0),
(6, 21, 110, 'catalog_toys', 0, 0),
(7, 22, 115, 'catalog_toy_items', 0, 0),
(8, 23, 115, 'catalog_toy_items', 0, 0),
(9, 24, 116, 'catalog_toy_items', 0, 0),
(10, 25, 111, 'catalog_toys', 0, 0),
(11, 26, 112, 'catalog_toys', 0, 0),
(12, 22, 113, 'catalog_toys', 0, 0);

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `media_tags`
--

DROP TABLE IF EXISTS `media_tags`;
CREATE TABLE IF NOT EXISTS `media_tags` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data dump for tabellen `media_tags`
--

INSERT INTO `media_tags` (`id`, `name`, `slug`) VALUES
(1, 'Box (Top)', 'box-top'),
(2, 'Box (Bottom)', 'box-bottom'),
(3, 'Card (Front)', 'card-front'),
(4, 'Figure (Front)', 'figure-front'),
(5, 'Figure (Back)', 'figure-back'),
(18, 'Figure', 'figure'),
(19, 'Vehicle', 'vehicle'),
(20, 'Playset', 'playset'),
(21, 'Box (Front)', 'box-front'),
(22, 'Box (Back)', 'box-back'),
(23, 'Box (Left Side)', 'box-left-side'),
(24, 'Box (Right Side)', 'box-right-side'),
(25, 'Card (Back)', 'card-back'),
(26, 'Insert/Tray', 'insert-tray'),
(27, 'Bubble/Blister', 'bubble-blister'),
(28, 'Loose Item', 'loose-item'),
(29, 'Accessories', 'accessories'),
(30, 'Weapons', 'weapons'),
(31, 'Instructions', 'instructions'),
(32, 'Sticker Sheet', 'sticker-sheet'),
(33, 'Proof of Purchase/Points', 'proof-of-purchase-points'),
(34, 'Damage Detail', 'damage-detail'),
(35, 'Variation Detail', 'variation-detail'),
(36, 'Group Shot', 'group-shot'),
(38, 'Action Pose', 'action-pose'),
(39, 'Close-up / Macro', 'close-up-macro'),
(40, 'Packaging (Sealed)', 'packaging-sealed'),
(41, 'Comic / Minicomic', 'comic-minicomic'),
(42, 'Diorama / Display', 'diorama-display'),
(43, 'Promo / Advertisement', 'promo-advertisement'),
(44, 'Prototype', 'prototype'),
(45, 'Mail-away', 'mail-away'),
(46, 'Scale Reference', 'scale-reference'),
(47, 'Catalog Image', 'catalog-image'),
(48, 'Card', 'card'),
(49, 'Box', 'box');

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `meta_acquisition_statuses`
--

DROP TABLE IF EXISTS `meta_acquisition_statuses`;
CREATE TABLE IF NOT EXISTS `meta_acquisition_statuses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data dump for tabellen `meta_acquisition_statuses`
--

INSERT INTO `meta_acquisition_statuses` (`id`, `name`, `slug`, `sort_order`) VALUES
(1, 'Arrived', 'arrived', 10),
(2, 'Ordered', 'ordered', 20),
(3, 'Pre-ordered', 'pre-ordered', 30),
(4, 'Wishlist', 'wishlist', 40);

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `meta_condition_grades`
--

DROP TABLE IF EXISTS `meta_condition_grades`;
CREATE TABLE IF NOT EXISTS `meta_condition_grades` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abbreviation` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data dump for tabellen `meta_condition_grades`
--

INSERT INTO `meta_condition_grades` (`id`, `name`, `slug`, `abbreviation`, `description`, `sort_order`) VALUES
(1, 'Mint', 'mint', 'M', 'Virtually perfect. No visible wear, tight joints, original gloss. Looks factory fresh.', 90),
(2, 'Near Mint', 'near-mint', 'NM', 'Almost perfect. Tiny flaws visible only on close inspection (e.g., micro paint rub).', 80),
(3, 'Excellent', 'excellent', 'EX', 'Light signs of wear. Minor paint rubs on high points or slightly loose joints. Displays well.', 70),
(4, 'Very Good', 'very-good', 'VG', 'Clearly played with. Noticeable paint wear, loose joints, or moderate discoloration.', 60),
(5, 'Good', 'good', 'G', 'Heavy play wear. Significant paint loss, very loose joints. A placeholder copy.', 50),
(6, 'Fair', 'fair', 'F', 'Heavy damage. Extreme discoloration, very loose limbs, or chewed parts.', 40),
(7, 'Poor', 'poor', 'P', 'Broken, incomplete, or severely damaged. Useful only for parts.', 30);

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `meta_entertainment_sources`
--

DROP TABLE IF EXISTS `meta_entertainment_sources`;
CREATE TABLE IF NOT EXISTS `meta_entertainment_sources` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `type` enum('Movie','TV Show','Video Game','Book','Other') COLLATE utf8mb4_unicode_ci DEFAULT 'Movie',
  `release_year` year DEFAULT NULL,
  `universe_id` int DEFAULT NULL,
  `show_on_dashboard` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_ent_source_slug` (`slug`),
  KEY `idx_ent_source_universe` (`universe_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data dump for tabellen `meta_entertainment_sources`
--

INSERT INTO `meta_entertainment_sources` (`id`, `name`, `slug`, `description`, `type`, `release_year`, `universe_id`, `show_on_dashboard`) VALUES
(1, 'Star Wars', 'star-wars', '', 'Movie', '1977', 1, 0),
(2, 'The Empire Strikes Back', 'the-empire-strikes-back', '', 'Movie', '1980', 1, 1),
(3, 'Return of the Jedi', 'return-of-the-jedi', '', 'Movie', '1983', 1, 0),
(4, 'The Clone Wars', 'the-clone-wars', '', 'TV Show', '2007', 1, 0),
(5, 'Shadows of the Empire', 'shadows-of-the-empire', '', 'Book', '1988', 1, 0),
(6, 'The Force Awakens', 'the-force-awakens', '', 'Movie', '2017', 1, 0);

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `meta_grader_tiers`
--

DROP TABLE IF EXISTS `meta_grader_tiers`;
CREATE TABLE IF NOT EXISTS `meta_grader_tiers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data dump for tabellen `meta_grader_tiers`
--

INSERT INTO `meta_grader_tiers` (`id`, `name`, `slug`, `sort_order`) VALUES
(1, 'Gold', 'gold', 90),
(2, 'Silver', 'silver', 80),
(3, 'Bronze', 'bronze', 70),
(4, 'Standard', 'standard', 50),
(5, 'Uncirculated', 'uncirculated', 100),
(6, 'Qualified', 'qualified', 40);

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `meta_grading_companies`
--

DROP TABLE IF EXISTS `meta_grading_companies`;
CREATE TABLE IF NOT EXISTS `meta_grading_companies` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data dump for tabellen `meta_grading_companies`
--

INSERT INTO `meta_grading_companies` (`id`, `name`, `slug`, `website`) VALUES
(1, 'None', 'none', NULL),
(2, 'Action Figure Authority', 'afa', NULL),
(3, 'UK Graders', 'ukg', NULL),
(4, 'Collector Archive Services', 'cas', NULL);

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `meta_manufacturers`
--

DROP TABLE IF EXISTS `meta_manufacturers`;
CREATE TABLE IF NOT EXISTS `meta_manufacturers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `show_on_dashboard` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data dump for tabellen `meta_manufacturers`
--

INSERT INTO `meta_manufacturers` (`id`, `name`, `slug`, `show_on_dashboard`) VALUES
(1, 'Kenner', 'kenner', 1),
(2, 'Hasbro', 'hasbro', 1),
(4, 'Mattel', 'mattel', 0),
(7, 'Lego', 'lego', 1),
(8, 'Something', 'something', 0),
(9, 'Hepo', 'hepo', 0),
(10, 'BR', 'br', 0);

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `meta_packaging_types`
--

DROP TABLE IF EXISTS `meta_packaging_types`;
CREATE TABLE IF NOT EXISTS `meta_packaging_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data dump for tabellen `meta_packaging_types`
--

INSERT INTO `meta_packaging_types` (`id`, `name`, `slug`, `description`, `sort_order`) VALUES
(1, 'Loose', 'loose', 'Item is loose, no packaging', 10),
(2, 'MOC', 'moc', 'Mint on Card', 20),
(3, 'MIB', 'mib', 'Mint in Box', 30),
(4, 'MISB', 'misb', 'Mint in Sealed Box', 40);

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `meta_product_types`
--

DROP TABLE IF EXISTS `meta_product_types`;
CREATE TABLE IF NOT EXISTS `meta_product_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data dump for tabellen `meta_product_types`
--

INSERT INTO `meta_product_types` (`id`, `name`, `slug`, `description`) VALUES
(1, 'Action Figure', 'action-figure', ''),
(2, 'Vehicle', 'vehicle', ''),
(3, 'Playset', 'playset', '');

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `meta_subjects`
--

DROP TABLE IF EXISTS `meta_subjects`;
CREATE TABLE IF NOT EXISTS `meta_subjects` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` enum('Character','Vehicle','Environment','Creature','Accessory','Packaging','Paperwork') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Character',
  `universe_id` int DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_subject_slug` (`slug`),
  KEY `idx_subject_type` (`type`),
  KEY `idx_subject_universe` (`universe_id`)
) ENGINE=InnoDB AUTO_INCREMENT=139 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data dump for tabellen `meta_subjects`
--

INSERT INTO `meta_subjects` (`id`, `name`, `slug`, `type`, `universe_id`, `description`) VALUES
(1, 'Luke Skywalker', 'luke-skywalker', 'Character', 1, ''),
(2, 'Han Solo', 'han-solo', 'Character', 1, 'Scoundrel and anti-hero'),
(101, 'Darth Vader', 'darth-vader', 'Character', 1, 'Dark Lord of the Sith'),
(102, 'Red Lightsaber', 'red-lightsaber', 'Accessory', 1, 'Standard red lightsaber'),
(103, 'Vinyl Cape', 'darth-vader-vinyl-cape', 'Accessory', 1, 'Original release vinyl cape'),
(104, 'Darth Vader Cardback', 'darth-vader-cardback', 'Packaging', 1, 'Standard character cardback'),
(105, 'X-Wing Fighter', 'x-wing-fighter', 'Vehicle', 1, 'Incom T-65 X-Wing'),
(106, 'Laser Cannon', 'x-wing-laser-cannon', 'Accessory', 1, 'Wing-mounted laser cannon'),
(107, 'X-Wing Box', 'x-wing-box', 'Packaging', 1, 'Vehicle packaging box'),
(108, 'X-Wing Instructions', 'x-wing-instructions', 'Paperwork', 1, 'Assembly manual'),
(109, 'Tauntaun', 'tauntaun', 'Creature', 1, 'Snow lizard of Hoth'),
(110, 'Tauntaun Saddle', 'tauntaun-saddle', 'Accessory', 1, 'Riding saddle'),
(111, 'Tauntaun Reins', 'tauntaun-reins', 'Accessory', 1, 'Saddle reins'),
(112, 'Tauntaun Box', 'tauntaun-box', 'Packaging', 1, 'Creature packaging box'),
(113, 'Tauntaun Instructions', 'tauntaun-instructions', 'Paperwork', 1, 'Instruction sheet'),
(114, 'Snake Eyes', 'snake-eyes', 'Character', 2, 'Commando'),
(115, 'Uzi Submachine Gun', 'uzi-submachine-gun', '', 2, 'Standard issue Uzi'),
(116, 'Explosive Pack', 'explosive-pack', 'Accessory', 2, 'Satchel charge'),
(117, 'Snake Eyes Cardback', 'snake-eyes-cardback', 'Packaging', 2, 'Standard character cardback'),
(118, 'Filecard (Snake Eyes)', 'filecard-snake-eyes', 'Paperwork', 2, 'Character bio filecard'),
(119, 'H.I.S.S. Tank', 'hiss-tank', 'Vehicle', 2, 'Cobra High Speed Sentry'),
(120, 'H.I.S.S. Turret', 'hiss-turret', '', 2, 'Top mounted dual-cannon turret'),
(121, 'H.I.S.S. Box', 'hiss-box', 'Packaging', 2, 'Vehicle packaging box'),
(122, 'H.I.S.S. Blueprints', 'hiss-blueprints', 'Paperwork', 2, 'Vehicle assembly blueprints'),
(123, 'He-Man', 'he-man', 'Character', 4, 'Most Powerful Man in the Universe'),
(124, 'Power Sword (Half)', 'power-sword-half', '', 4, 'Silver half-sword'),
(125, 'Battle Axe', 'battle-axe', '', 4, 'Standard silver battle axe'),
(126, 'Chest Armor', 'chest-armor', 'Accessory', 4, 'Removable chest harness'),
(127, 'He-Man Cardback', 'he-man-cardback', 'Packaging', 4, 'Standard character cardback'),
(128, 'King of Castle Grayskull', 'minicomic-king-of-castle-grayskull', 'Paperwork', 4, 'Included minicomic'),
(129, 'Battle Cat', 'battle-cat', 'Creature', 4, 'Fighting Tiger of Eternia'),
(130, 'Battle Cat Helmet', 'battle-cat-helmet', 'Accessory', 4, 'Red armored helmet'),
(131, 'Battle Cat Saddle', 'battle-cat-saddle', 'Accessory', 4, 'Red riding saddle'),
(132, 'Battle Cat Box', 'battle-cat-box', 'Packaging', 4, 'Creature packaging box'),
(133, 'Battle Cat Instructions', 'battle-cat-instructions', 'Paperwork', 4, 'Instruction sheet'),
(134, 'Castle Grayskull', 'castle-grayskull', 'Environment', 4, 'Fortress of Mystery and Power'),
(135, 'Laser Cannon (Grayskull)', 'grayskull-laser-cannon', 'Accessory', 4, 'Turret gun'),
(136, 'Trap Door', 'grayskull-trap-door', 'Accessory', 4, 'Floor trap door'),
(137, 'Castle Grayskull Box', 'castle-grayskull-box', 'Packaging', 4, 'Playset packaging box'),
(138, 'Castle Grayskull Manual', 'castle-grayskull-manual', 'Paperwork', 4, 'Playset assembly manual');

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `meta_toy_lines`
--

DROP TABLE IF EXISTS `meta_toy_lines`;
CREATE TABLE IF NOT EXISTS `meta_toy_lines` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_year` year DEFAULT NULL,
  `end_year` year DEFAULT NULL,
  `universe_id` int NOT NULL,
  `manufacturer_id` int NOT NULL,
  `show_on_dashboard` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_meta_toy_lines_slug` (`slug`),
  KEY `fk_line_universe` (`universe_id`),
  KEY `fk_line_manufacturer` (`manufacturer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data dump for tabellen `meta_toy_lines`
--

INSERT INTO `meta_toy_lines` (`id`, `name`, `slug`, `start_year`, `end_year`, `universe_id`, `manufacturer_id`, `show_on_dashboard`) VALUES
(3, 'Lego Marvel', 'lego-marvel', NULL, NULL, 2, 7, 1),
(4, 'Lego Star Wars', 'lego-star-wars', NULL, NULL, 1, 7, 1),
(5, 'The Vintage Collection', 'the-vintage-collection', NULL, NULL, 1, 2, 1),
(6, 'The Black Series', 'the-black-series', NULL, NULL, 1, 2, 0),
(7, 'Kenner', 'kenner', NULL, NULL, 1, 1, 0);

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `meta_universes`
--

DROP TABLE IF EXISTS `meta_universes`;
CREATE TABLE IF NOT EXISTS `meta_universes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `show_on_dashboard` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data dump for tabellen `meta_universes`
--

INSERT INTO `meta_universes` (`id`, `name`, `slug`, `description`, `show_on_dashboard`) VALUES
(1, 'Star Wars', 'star-wars', 'Star Wars is an American epic space opera media franchise created by George Lucas. The franchise began with the original Star Wars film (1977)[a] and quickly became a worldwide pop culture phenomenon. It has expanded into various films and other media, including television series, video games, novels, comic books, theme park attractions, and themed areas, comprising an all-encompassing fictional universe.[b] Star Wars is the fourth highest-grossing media franchise of all time.', 1),
(2, 'G.I. Joe', 'gi-joe', '', 1),
(4, 'Masters of the Universe', 'masters-of-the-universe', 'Its made for toys', 0),
(5, 'Another universe', 'another-universe', '', 0),
(6, 'Test', 'test', '', 0);

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data dump for tabellen `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`, `created_at`) VALUES
(1, '001_create_meta_tables.php', 1, '2026-02-13 12:41:17'),
(2, '002_create_catalog_and_collection.php', 1, '2026-02-13 12:41:18'),
(3, '003_create_media_and_importer.php', 1, '2026-02-13 12:41:18'),
(4, '004_refactor_enums_to_tables.php', 1, '2026-02-13 12:41:19'),
(5, '005_add_dashboard_visibility.php', 1, '2026-02-13 12:41:19'),
(6, '006_add_performance_indexes.php', 1, '2026-02-13 12:41:20'),
(7, '007_add_slug_to_toy_lines.php', 1, '2026-02-13 12:41:20'),
(8, '008_seed_default_manufacturers.php', 1, '2026-02-13 12:41:20'),
(9, '009_seed_test_data.php', 2, '2026-02-13 12:48:35'),
(10, '010_add_universe_to_catalog_toys.php', 3, '2026-02-14 14:21:45'),
(11, '011_update_entertainment_sources.php', 4, '2026-02-15 14:29:20'),
(12, '012_add_description_to_product_types.php', 5, '2026-02-15 19:26:12'),
(13, '013_update_subjects_table.php', 6, '2026-02-15 19:51:11'),
(14, '014_add_grading_to_collection_toy_items.php', 7, '2026-02-15 20:16:41'),
(15, '015_upgrade_media_polymorphic.php', 8, '2026-02-17 20:45:11'),
(16, '016_seed_media_tags.php', 9, '2026-02-18 11:37:06'),
(17, '017_create_users_table.php', 10, '2026-02-18 13:00:52'),
(18, '018_add_role_to_users.php', 11, '2026-02-18 19:31:24'),
(19, '019_seed_catalog_toys.php', 12, '2026-02-18 20:35:18'),
(20, '020_update_catalog_toy_items.php', 13, '2026-02-19 20:41:44'),
(21, '021_seed_more_subjects.php', 14, '2026-02-19 20:51:39'),
(22, '022_add_box_code_to_storage_units.php', 15, '2026-02-22 16:03:18');

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','user') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data dump for tabellen `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `role`, `password`, `created_at`, `updated_at`) VALUES
(1, 'brian', 'bvendeltorp@gmail.com', 'admin', '$2y$12$wqCmE5hoUkyT7jd8G.naoejiZnym6B.1AGO92xXJtMuxlbVsvvvMG', '2026-02-18 13:01:31', '2026-02-18 19:31:24');

--
-- Begrænsninger for dumpede tabeller
--

--
-- Begrænsninger for tabel `catalog_toys`
--
ALTER TABLE `catalog_toys`
  ADD CONSTRAINT `fk_cat_toy_ent` FOREIGN KEY (`entertainment_source_id`) REFERENCES `meta_entertainment_sources` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_cat_toy_line` FOREIGN KEY (`toy_line_id`) REFERENCES `meta_toy_lines` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cat_toy_manuf` FOREIGN KEY (`manufacturer_id`) REFERENCES `meta_manufacturers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_cat_toy_prod` FOREIGN KEY (`product_type_id`) REFERENCES `meta_product_types` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_cat_toy_universe` FOREIGN KEY (`universe_id`) REFERENCES `meta_universes` (`id`) ON DELETE SET NULL;

--
-- Begrænsninger for tabel `catalog_toy_items`
--
ALTER TABLE `catalog_toy_items`
  ADD CONSTRAINT `fk_cat_item_subj` FOREIGN KEY (`subject_id`) REFERENCES `meta_subjects` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_cat_item_toy` FOREIGN KEY (`catalog_toy_id`) REFERENCES `catalog_toys` (`id`) ON DELETE CASCADE;

--
-- Begrænsninger for tabel `collection_toys`
--
ALTER TABLE `collection_toys`
  ADD CONSTRAINT `fk_col_acq_status` FOREIGN KEY (`acquisition_status_id`) REFERENCES `meta_acquisition_statuses` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_col_cond_grade` FOREIGN KEY (`condition_grade_id`) REFERENCES `meta_condition_grades` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_col_grad_comp` FOREIGN KEY (`grader_company_id`) REFERENCES `meta_grading_companies` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_col_grad_tier` FOREIGN KEY (`grader_tier_id`) REFERENCES `meta_grader_tiers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_col_pack_type` FOREIGN KEY (`packaging_type_id`) REFERENCES `meta_packaging_types` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_col_toy_cat` FOREIGN KEY (`catalog_toy_id`) REFERENCES `catalog_toys` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_col_toy_src` FOREIGN KEY (`purchase_source_id`) REFERENCES `collection_sources` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_col_toy_store` FOREIGN KEY (`storage_unit_id`) REFERENCES `collection_storage_units` (`id`) ON DELETE SET NULL;

--
-- Begrænsninger for tabel `collection_toy_items`
--
ALTER TABLE `collection_toy_items`
  ADD CONSTRAINT `fk_col_item_acq_status` FOREIGN KEY (`acquisition_status_id`) REFERENCES `meta_acquisition_statuses` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_col_item_cat` FOREIGN KEY (`catalog_toy_item_id`) REFERENCES `catalog_toy_items` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_col_item_cond_grade` FOREIGN KEY (`condition_grade_id`) REFERENCES `meta_condition_grades` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_col_item_grad_comp` FOREIGN KEY (`grader_company_id`) REFERENCES `meta_grading_companies` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_col_item_grad_tier` FOREIGN KEY (`grader_tier_id`) REFERENCES `meta_grader_tiers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_col_item_pack_type` FOREIGN KEY (`packaging_type_id`) REFERENCES `meta_packaging_types` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_col_item_toy` FOREIGN KEY (`collection_toy_id`) REFERENCES `collection_toys` (`id`) ON DELETE CASCADE;

--
-- Begrænsninger for tabel `importer_items`
--
ALTER TABLE `importer_items`
  ADD CONSTRAINT `fk_imp_cat` FOREIGN KEY (`catalog_toy_id`) REFERENCES `catalog_toys` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_imp_source` FOREIGN KEY (`source_id`) REFERENCES `importer_sources` (`id`) ON DELETE CASCADE;

--
-- Begrænsninger for tabel `importer_logs`
--
ALTER TABLE `importer_logs`
  ADD CONSTRAINT `fk_log_item` FOREIGN KEY (`importer_item_id`) REFERENCES `importer_items` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_log_source` FOREIGN KEY (`source_id`) REFERENCES `importer_sources` (`id`) ON DELETE CASCADE;

--
-- Begrænsninger for tabel `media_file_tags`
--
ALTER TABLE `media_file_tags`
  ADD CONSTRAINT `fk_tag_file` FOREIGN KEY (`media_file_id`) REFERENCES `media_files` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tag_tag` FOREIGN KEY (`media_tag_id`) REFERENCES `media_tags` (`id`) ON DELETE CASCADE;

--
-- Begrænsninger for tabel `media_links`
--
ALTER TABLE `media_links`
  ADD CONSTRAINT `fk_media_link_file` FOREIGN KEY (`media_file_id`) REFERENCES `media_files` (`id`) ON DELETE CASCADE;

--
-- Begrænsninger for tabel `meta_entertainment_sources`
--
ALTER TABLE `meta_entertainment_sources`
  ADD CONSTRAINT `fk_ent_source_universe` FOREIGN KEY (`universe_id`) REFERENCES `meta_universes` (`id`) ON DELETE SET NULL;

--
-- Begrænsninger for tabel `meta_subjects`
--
ALTER TABLE `meta_subjects`
  ADD CONSTRAINT `fk_subject_universe` FOREIGN KEY (`universe_id`) REFERENCES `meta_universes` (`id`) ON DELETE SET NULL;

--
-- Begrænsninger for tabel `meta_toy_lines`
--
ALTER TABLE `meta_toy_lines`
  ADD CONSTRAINT `fk_line_manufacturer` FOREIGN KEY (`manufacturer_id`) REFERENCES `meta_manufacturers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_line_universe` FOREIGN KEY (`universe_id`) REFERENCES `meta_universes` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
