<?php

return [
    'up' => "
        -- ==========================================
        -- MEDIA SYSTEM
        -- ==========================================

        -- 13. Media Files (Central register over filer)
        CREATE TABLE IF NOT EXISTS `media_files` (
            `id` int NOT NULL AUTO_INCREMENT,
            `filename` varchar(255) NOT NULL,
            `original_name` varchar(255) DEFAULT NULL,
            `filepath` varchar(255) NOT NULL, -- Relativ sti (fx 'uploads/2026/02/pic.jpg')
            `file_type` varchar(50) NOT NULL, -- mime type
            `file_size` int NOT NULL, -- bytes
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        -- 14. Media Tags (Box Front, Loose, Damage Detail)
        CREATE TABLE IF NOT EXISTS `media_tags` (
            `id` int NOT NULL AUTO_INCREMENT,
            `name` varchar(50) NOT NULL,
            `slug` varchar(50) NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `slug` (`slug`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        -- 15. Media File Tags Map (Many-to-Many)
        CREATE TABLE IF NOT EXISTS `media_file_tags` (
            `media_file_id` int NOT NULL,
            `media_tag_id` int NOT NULL,
            PRIMARY KEY (`media_file_id`, `media_tag_id`),
            CONSTRAINT `fk_tag_file` FOREIGN KEY (`media_file_id`) REFERENCES `media_files` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_tag_tag` FOREIGN KEY (`media_tag_id`) REFERENCES `media_tags` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        -- 16. LINK TABELLER (Kobler billeder til legetøj)
        
        -- Link til Catalog Toy (Officielle billeder)
        CREATE TABLE IF NOT EXISTS `catalog_toy_media` (
            `catalog_toy_id` int NOT NULL,
            `media_file_id` int NOT NULL,
            `is_primary` boolean DEFAULT false,
            `sort_order` int DEFAULT 0,
            PRIMARY KEY (`catalog_toy_id`, `media_file_id`),
            CONSTRAINT `fk_ctm_toy` FOREIGN KEY (`catalog_toy_id`) REFERENCES `catalog_toys` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_ctm_media` FOREIGN KEY (`media_file_id`) REFERENCES `media_files` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        -- Link til Collection Toy (Dine egne billeder)
        CREATE TABLE IF NOT EXISTS `collection_toy_media` (
            `collection_toy_id` int NOT NULL,
            `media_file_id` int NOT NULL,
            `is_primary` boolean DEFAULT false,
            `sort_order` int DEFAULT 0,
            PRIMARY KEY (`collection_toy_id`, `media_file_id`),
            CONSTRAINT `fk_coltm_toy` FOREIGN KEY (`collection_toy_id`) REFERENCES `collection_toys` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_coltm_media` FOREIGN KEY (`media_file_id`) REFERENCES `media_files` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


        -- ==========================================
        -- IMPORTER SYSTEM
        -- ==========================================

        -- 17. Import Sources (Websites som Galactic Figures)
        CREATE TABLE IF NOT EXISTS `importer_sources` (
            `id` int NOT NULL AUTO_INCREMENT,
            `name` varchar(100) NOT NULL,
            `slug` varchar(50) NOT NULL, -- fx 'swc', 'gc', 'af411'
            `base_url` varchar(255) NOT NULL,
            `driver_class` varchar(255) NOT NULL, -- App\\Modules\\Importer\\Drivers\\GalacticFiguresDriver
            `is_active` boolean DEFAULT true,
            PRIMARY KEY (`id`),
            UNIQUE KEY `slug` (`slug`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        -- 18. Import Items (Link mellem URL og Catalog)
        CREATE TABLE IF NOT EXISTS `importer_items` (
            `id` int NOT NULL AUTO_INCREMENT,
            `source_id` int NOT NULL,
            `external_id` varchar(255) NOT NULL, -- ID på deres site (fx 'vc01-dengar')
            `external_url` varchar(255) NOT NULL,
            `catalog_toy_id` int DEFAULT NULL, -- Linket til vores system hvis importeret
            `last_imported_at` timestamp NULL DEFAULT NULL,
            `import_data_hash` varchar(32) DEFAULT NULL, -- Til at tjekke om data har ændret sig
            PRIMARY KEY (`id`),
            UNIQUE KEY `source_external` (`source_id`, `external_id`),
            CONSTRAINT `fk_imp_source` FOREIGN KEY (`source_id`) REFERENCES `importer_sources` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_imp_cat` FOREIGN KEY (`catalog_toy_id`) REFERENCES `catalog_toys` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        -- 19. Import Logs (Debugging)
        CREATE TABLE IF NOT EXISTS `importer_logs` (
            `id` int NOT NULL AUTO_INCREMENT,
            `source_id` int NOT NULL,
            `importer_item_id` int DEFAULT NULL,
            `status` enum('Success','Warning','Error') NOT NULL,
            `message` text,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            CONSTRAINT `fk_log_source` FOREIGN KEY (`source_id`) REFERENCES `importer_sources` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_log_item` FOREIGN KEY (`importer_item_id`) REFERENCES `importer_items` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    "
];