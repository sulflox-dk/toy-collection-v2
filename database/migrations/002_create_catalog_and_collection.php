<?php

return [
    'up' => "
        -- ==========================================
        -- CATALOG (Blueprints)
        -- ==========================================

        -- 5. Entertainment Sources (Movies, TV) - Flyttet fra Meta da den hører tættere på Catalog
        CREATE TABLE IF NOT EXISTS `meta_entertainment_sources` (
            `id` int NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `type` enum('Movie','TV Show','Video Game','Book','Other') DEFAULT 'Movie',
            `release_year` year DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        -- 6. Subjects (Characters like 'Luke Skywalker')
        CREATE TABLE IF NOT EXISTS `meta_subjects` (
            `id` int NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `description` text,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        -- 7. Catalog Toys (Was: master_toys)
        CREATE TABLE IF NOT EXISTS `catalog_toys` (
            `id` int NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `slug` varchar(255) NOT NULL,
            `toy_line_id` int NOT NULL,
            `product_type_id` int DEFAULT NULL,
            `entertainment_source_id` int DEFAULT NULL,
            `manufacturer_id` int DEFAULT NULL, -- Redundant men god for performance
            `year_released` year DEFAULT NULL,
            `wave` varchar(100) DEFAULT NULL,
            `assortment_sku` varchar(100) DEFAULT NULL,
            `upc` varchar(50) DEFAULT NULL,
            `description` text,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `deleted_at` timestamp NULL DEFAULT NULL, -- Soft Delete
            PRIMARY KEY (`id`),
            UNIQUE KEY `slug` (`slug`),
            KEY `fk_cat_toy_line` (`toy_line_id`),
            KEY `fk_cat_toy_manuf` (`manufacturer_id`),
            CONSTRAINT `fk_cat_toy_line` FOREIGN KEY (`toy_line_id`) REFERENCES `meta_toy_lines` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_cat_toy_manuf` FOREIGN KEY (`manufacturer_id`) REFERENCES `meta_manufacturers` (`id`) ON DELETE SET NULL,
            CONSTRAINT `fk_cat_toy_prod` FOREIGN KEY (`product_type_id`) REFERENCES `meta_product_types` (`id`) ON DELETE SET NULL,
            CONSTRAINT `fk_cat_toy_ent` FOREIGN KEY (`entertainment_source_id`) REFERENCES `meta_entertainment_sources` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        -- 8. Catalog Items (Accessories blueprint)
        CREATE TABLE IF NOT EXISTS `catalog_toy_items` (
            `id` int NOT NULL AUTO_INCREMENT,
            `catalog_toy_id` int NOT NULL,
            `subject_id` int DEFAULT NULL, -- Hvad er det? (Ex: Lightsaber)
            `name` varchar(255) NOT NULL,  -- Specifikt navn (Ex: Blue Lightsaber)
            `description` text,
            PRIMARY KEY (`id`),
            KEY `fk_cat_item_toy` (`catalog_toy_id`),
            CONSTRAINT `fk_cat_item_toy` FOREIGN KEY (`catalog_toy_id`) REFERENCES `catalog_toys` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_cat_item_subj` FOREIGN KEY (`subject_id`) REFERENCES `meta_subjects` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        -- ==========================================
        -- COLLECTION (Physical Inventory)
        -- ==========================================

        -- 9. Storage Units
        CREATE TABLE IF NOT EXISTS `collection_storage_units` (
            `id` int NOT NULL AUTO_INCREMENT,
            `name` varchar(100) NOT NULL, -- Ex: Bin 1, Shelf A
            `location` varchar(100) DEFAULT NULL, -- Ex: Garage, Office
            `description` text,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        -- 10. Purchase Sources (Stores)
        CREATE TABLE IF NOT EXISTS `collection_sources` (
            `id` int NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `website` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        -- 11. Collection Toys (Din faktiske samling)
        CREATE TABLE IF NOT EXISTS `collection_toys` (
            `id` int NOT NULL AUTO_INCREMENT,
            `catalog_toy_id` int NOT NULL,
            `storage_unit_id` int DEFAULT NULL,
            `purchase_source_id` int DEFAULT NULL,
            
            -- Status & Dates
            `acquisition_status` enum('Arrived','Ordered','Pre-ordered','Wishlist') DEFAULT 'Arrived',
            `date_acquired` date DEFAULT NULL,
            `purchase_price` decimal(10,2) DEFAULT NULL,
            `purchase_currency` char(3) DEFAULT 'USD', -- NY: Currency support
            `current_value` decimal(10,2) DEFAULT NULL,
            
            -- Condition & Grading (NY STANDARD)
            `packaging_status` enum('Loose','MOC','MIB','MISB') DEFAULT 'Loose',
            `condition_grade` enum('Mint','Near Mint','Excellent','Very Good','Good','Fair','Poor') DEFAULT NULL,
            
            -- Professional Grading
            `grader_company` enum('None','AFA','UKG','CAS') DEFAULT 'None',
            `grader_tier` enum('Gold','Silver','Bronze','Standard','Uncirculated','Qualified') DEFAULT NULL,
            `grade_serial` varchar(50) DEFAULT NULL, -- Ex: AFA serial number
            `grade_score` varchar(20) DEFAULT NULL,  -- Ex: 85 NM+

            `notes` text,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `deleted_at` timestamp NULL DEFAULT NULL, -- Soft Delete

            PRIMARY KEY (`id`),
            KEY `fk_col_toy_cat` (`catalog_toy_id`),
            CONSTRAINT `fk_col_toy_cat` FOREIGN KEY (`catalog_toy_id`) REFERENCES `catalog_toys` (`id`) ON DELETE RESTRICT,
            CONSTRAINT `fk_col_toy_store` FOREIGN KEY (`storage_unit_id`) REFERENCES `collection_storage_units` (`id`) ON DELETE SET NULL,
            CONSTRAINT `fk_col_toy_src` FOREIGN KEY (`purchase_source_id`) REFERENCES `collection_sources` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        -- 12. Collection Items (Dine fysiske accessories)
        CREATE TABLE IF NOT EXISTS `collection_toy_items` (
            `id` int NOT NULL AUTO_INCREMENT,
            `collection_toy_id` int NOT NULL,
            `catalog_toy_item_id` int NOT NULL, -- Linker til blueprint
            `is_present` boolean DEFAULT true,
            `is_repro` boolean DEFAULT false,
            `condition_notes` varchar(255) DEFAULT NULL,
            
            PRIMARY KEY (`id`),
            KEY `fk_col_item_toy` (`collection_toy_id`),
            CONSTRAINT `fk_col_item_toy` FOREIGN KEY (`collection_toy_id`) REFERENCES `collection_toys` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_col_item_cat` FOREIGN KEY (`catalog_toy_item_id`) REFERENCES `catalog_toy_items` (`id`) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    "
];