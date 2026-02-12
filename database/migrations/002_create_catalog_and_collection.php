<?php

return [
    'up' => "
        -- ==========================================
        -- CATALOG (Blueprints / reference data)
        -- ==========================================

        -- 5. Entertainment Sources (Movies, TV Shows, Video Games, etc.)
        CREATE TABLE IF NOT EXISTS `meta_entertainment_sources` (
            `id` int NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `type` enum('Movie','TV Show','Video Game','Book','Other') DEFAULT 'Movie',
            `release_year` year DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        -- 6. Subjects (Characters / entities, e.g. 'Luke Skywalker', 'Lightsaber')
        CREATE TABLE IF NOT EXISTS `meta_subjects` (
            `id` int NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `description` text,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        -- 7. Catalog Toys (product definitions / blueprints)
        CREATE TABLE IF NOT EXISTS `catalog_toys` (
            `id` int NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `slug` varchar(255) NOT NULL,
            `toy_line_id` int NOT NULL,
            `product_type_id` int DEFAULT NULL,
            `entertainment_source_id` int DEFAULT NULL,
            `manufacturer_id` int DEFAULT NULL,
            `year_released` year DEFAULT NULL,
            `wave` varchar(100) DEFAULT NULL,
            `assortment_sku` varchar(100) DEFAULT NULL,
            `upc` varchar(50) DEFAULT NULL,
            `description` text,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `deleted_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `slug` (`slug`),
            KEY `fk_cat_toy_line` (`toy_line_id`),
            KEY `fk_cat_toy_manuf` (`manufacturer_id`),
            KEY `idx_cat_toy_name` (`name`),
            KEY `idx_cat_toy_year` (`year_released`),
            KEY `idx_cat_toy_deleted` (`deleted_at`),
            CONSTRAINT `fk_cat_toy_line` FOREIGN KEY (`toy_line_id`) REFERENCES `meta_toy_lines` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_cat_toy_manuf` FOREIGN KEY (`manufacturer_id`) REFERENCES `meta_manufacturers` (`id`) ON DELETE SET NULL,
            CONSTRAINT `fk_cat_toy_prod` FOREIGN KEY (`product_type_id`) REFERENCES `meta_product_types` (`id`) ON DELETE SET NULL,
            CONSTRAINT `fk_cat_toy_ent` FOREIGN KEY (`entertainment_source_id`) REFERENCES `meta_entertainment_sources` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        -- 8. Catalog Toy Items (standard accessories / components that ship with a toy)
        CREATE TABLE IF NOT EXISTS `catalog_toy_items` (
            `id` int NOT NULL AUTO_INCREMENT,
            `catalog_toy_id` int NOT NULL,
            `subject_id` int DEFAULT NULL,
            `name` varchar(255) NOT NULL,
            `type` enum('Figure','Accessory','Weapon','Vehicle Part','Display Stand','Other') DEFAULT 'Accessory',
            `description` text,
            PRIMARY KEY (`id`),
            KEY `fk_cat_item_toy` (`catalog_toy_id`),
            CONSTRAINT `fk_cat_item_toy` FOREIGN KEY (`catalog_toy_id`) REFERENCES `catalog_toys` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_cat_item_subj` FOREIGN KEY (`subject_id`) REFERENCES `meta_subjects` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        -- ==========================================
        -- COLLECTION (Physical inventory)
        -- ==========================================

        -- 9. Storage Units (where you keep your toys)
        CREATE TABLE IF NOT EXISTS `collection_storage_units` (
            `id` int NOT NULL AUTO_INCREMENT,
            `name` varchar(100) NOT NULL,
            `location` varchar(100) DEFAULT NULL,
            `description` text,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        -- 10. Purchase Sources (stores / sellers)
        CREATE TABLE IF NOT EXISTS `collection_sources` (
            `id` int NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `website` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        -- 11. Collection Toys (your actual physical toys)
        CREATE TABLE IF NOT EXISTS `collection_toys` (
            `id` int NOT NULL AUTO_INCREMENT,
            `catalog_toy_id` int NOT NULL,
            `storage_unit_id` int DEFAULT NULL,
            `purchase_source_id` int DEFAULT NULL,

            -- Status & Dates
            `acquisition_status` enum('Arrived','Ordered','Pre-ordered','Wishlist') DEFAULT 'Arrived',
            `date_acquired` date DEFAULT NULL,
            `purchase_price` decimal(10,2) DEFAULT NULL,
            `purchase_currency` char(3) DEFAULT 'USD',
            `current_value` decimal(10,2) DEFAULT NULL,

            -- Condition & Packaging
            `packaging_status` enum('Loose','MOC','MIB','MISB') DEFAULT 'Loose',
            `condition_grade` enum('Mint','Near Mint','Excellent','Very Good','Good','Fair','Poor') DEFAULT NULL,

            -- Professional Grading
            `grader_company` enum('None','AFA','UKG','CAS') DEFAULT 'None',
            `grader_tier` enum('Gold','Silver','Bronze','Standard','Uncirculated','Qualified') DEFAULT NULL,
            `grade_serial` varchar(50) DEFAULT NULL,
            `grade_score` varchar(20) DEFAULT NULL,

            `notes` text,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `deleted_at` timestamp NULL DEFAULT NULL,

            PRIMARY KEY (`id`),
            KEY `fk_col_toy_cat` (`catalog_toy_id`),
            KEY `idx_col_toy_status` (`acquisition_status`),
            KEY `idx_col_toy_deleted` (`deleted_at`),
            CONSTRAINT `fk_col_toy_cat` FOREIGN KEY (`catalog_toy_id`) REFERENCES `catalog_toys` (`id`) ON DELETE RESTRICT,
            CONSTRAINT `fk_col_toy_store` FOREIGN KEY (`storage_unit_id`) REFERENCES `collection_storage_units` (`id`) ON DELETE SET NULL,
            CONSTRAINT `fk_col_toy_src` FOREIGN KEY (`purchase_source_id`) REFERENCES `collection_sources` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        -- 12. Collection Toy Items (your physical accessories, linked to catalog blueprints)
        CREATE TABLE IF NOT EXISTS `collection_toy_items` (
            `id` int NOT NULL AUTO_INCREMENT,
            `collection_toy_id` int NOT NULL,
            `catalog_toy_item_id` int NOT NULL,
            `is_present` boolean DEFAULT true,
            `is_repro` boolean DEFAULT false,
            `condition_notes` varchar(255) DEFAULT NULL,

            PRIMARY KEY (`id`),
            KEY `fk_col_item_toy` (`collection_toy_id`),
            CONSTRAINT `fk_col_item_toy` FOREIGN KEY (`collection_toy_id`) REFERENCES `collection_toys` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_col_item_cat` FOREIGN KEY (`catalog_toy_item_id`) REFERENCES `catalog_toy_items` (`id`) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",

    'down' => "
        DROP TABLE IF EXISTS `collection_toy_items`;
        DROP TABLE IF EXISTS `collection_toys`;
        DROP TABLE IF EXISTS `collection_sources`;
        DROP TABLE IF EXISTS `collection_storage_units`;
        DROP TABLE IF EXISTS `catalog_toy_items`;
        DROP TABLE IF EXISTS `catalog_toys`;
        DROP TABLE IF EXISTS `meta_subjects`;
        DROP TABLE IF EXISTS `meta_entertainment_sources`;
    "
];
