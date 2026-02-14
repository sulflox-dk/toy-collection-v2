-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Vært: 127.0.0.1:3306
-- Genereringstid: 14. 02 2026 kl. 09:58:55
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
  KEY `idx_catalog_toys_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `catalog_toy_items`
--

DROP TABLE IF EXISTS `catalog_toy_items`;
CREATE TABLE IF NOT EXISTS `catalog_toy_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `catalog_toy_id` int NOT NULL,
  `subject_id` int DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('Figure','Accessory','Weapon','Vehicle Part','Display Stand','Other') COLLATE utf8mb4_unicode_ci DEFAULT 'Accessory',
  `description` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `fk_cat_item_toy` (`catalog_toy_id`),
  KEY `fk_cat_item_subj` (`subject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `catalog_toy_media`
--

DROP TABLE IF EXISTS `catalog_toy_media`;
CREATE TABLE IF NOT EXISTS `catalog_toy_media` (
  `catalog_toy_id` int NOT NULL,
  `media_file_id` int NOT NULL,
  `is_primary` tinyint(1) DEFAULT '0',
  `sort_order` int DEFAULT '0',
  PRIMARY KEY (`catalog_toy_id`,`media_file_id`),
  KEY `fk_ctm_media` (`media_file_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `condition_notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_col_item_toy` (`collection_toy_id`),
  KEY `fk_col_item_cat` (`catalog_toy_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `collection_toy_media`
--

DROP TABLE IF EXISTS `collection_toy_media`;
CREATE TABLE IF NOT EXISTS `collection_toy_media` (
  `collection_toy_id` int NOT NULL,
  `media_file_id` int NOT NULL,
  `is_primary` tinyint(1) DEFAULT '0',
  `sort_order` int DEFAULT '0',
  PRIMARY KEY (`collection_toy_id`,`media_file_id`),
  KEY `fk_coltm_media` (`media_file_id`)
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
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `filepath` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_media_deleted` (`deleted_at`),
  KEY `idx_media_files_deleted` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(1, 'Mint', 'mint', 'M', NULL, 90),
(2, 'Near Mint', 'near-mint', 'NM', NULL, 80),
(3, 'Excellent', 'excellent', 'EX', NULL, 70),
(4, 'Very Good', 'very-good', 'VG', NULL, 60),
(5, 'Good', 'good', 'G', NULL, 50),
(6, 'Fair', 'fair', 'F', NULL, 40),
(7, 'Poor', 'poor', 'P', NULL, 30);

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `meta_entertainment_sources`
--

DROP TABLE IF EXISTS `meta_entertainment_sources`;
CREATE TABLE IF NOT EXISTS `meta_entertainment_sources` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('Movie','TV Show','Video Game','Book','Other') COLLATE utf8mb4_unicode_ci DEFAULT 'Movie',
  `release_year` year DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(10, 'BRjo', 'br', 0);

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
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `meta_subjects`
--

DROP TABLE IF EXISTS `meta_subjects`;
CREATE TABLE IF NOT EXISTS `meta_subjects` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data dump for tabellen `meta_toy_lines`
--

INSERT INTO `meta_toy_lines` (`id`, `name`, `slug`, `start_year`, `end_year`, `universe_id`, `manufacturer_id`, `show_on_dashboard`) VALUES
(3, 'Lego Marvel', 'lego-marvel', NULL, NULL, 3, 7, 1),
(4, 'Lego Star Wars', 'lego-star-wars', NULL, NULL, 1, 7, 1),
(5, 'The Vintage Collection', 'the-vintage-collection', NULL, NULL, 1, 2, 1);

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data dump for tabellen `meta_universes`
--

INSERT INTO `meta_universes` (`id`, `name`, `slug`, `description`, `show_on_dashboard`) VALUES
(1, 'Star Wars', 'star-wars', NULL, 1),
(2, 'G.I. Joe', 'gi-joe', NULL, 1),
(3, 'Marvel', 'marvel', NULL, 1);

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
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(9, '009_seed_test_data.php', 2, '2026-02-13 12:48:35');

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
  ADD CONSTRAINT `fk_cat_toy_prod` FOREIGN KEY (`product_type_id`) REFERENCES `meta_product_types` (`id`) ON DELETE SET NULL;

--
-- Begrænsninger for tabel `catalog_toy_items`
--
ALTER TABLE `catalog_toy_items`
  ADD CONSTRAINT `fk_cat_item_subj` FOREIGN KEY (`subject_id`) REFERENCES `meta_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_cat_item_toy` FOREIGN KEY (`catalog_toy_id`) REFERENCES `catalog_toys` (`id`) ON DELETE CASCADE;

--
-- Begrænsninger for tabel `catalog_toy_media`
--
ALTER TABLE `catalog_toy_media`
  ADD CONSTRAINT `fk_ctm_media` FOREIGN KEY (`media_file_id`) REFERENCES `media_files` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ctm_toy` FOREIGN KEY (`catalog_toy_id`) REFERENCES `catalog_toys` (`id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `fk_col_item_cat` FOREIGN KEY (`catalog_toy_item_id`) REFERENCES `catalog_toy_items` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_col_item_toy` FOREIGN KEY (`collection_toy_id`) REFERENCES `collection_toys` (`id`) ON DELETE CASCADE;

--
-- Begrænsninger for tabel `collection_toy_media`
--
ALTER TABLE `collection_toy_media`
  ADD CONSTRAINT `fk_coltm_media` FOREIGN KEY (`media_file_id`) REFERENCES `media_files` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_coltm_toy` FOREIGN KEY (`collection_toy_id`) REFERENCES `collection_toys` (`id`) ON DELETE CASCADE;

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
-- Begrænsninger for tabel `meta_toy_lines`
--
ALTER TABLE `meta_toy_lines`
  ADD CONSTRAINT `fk_line_manufacturer` FOREIGN KEY (`manufacturer_id`) REFERENCES `meta_manufacturers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_line_universe` FOREIGN KEY (`universe_id`) REFERENCES `meta_universes` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
