<?php

return [
    'up' => "
        -- ==========================================
        -- MEDIA SYSTEM
        -- ==========================================

        -- 13. Media Files (central file registry)
        CREATE TABLE IF NOT EXISTS `media_files` (
            `id` int NOT NULL AUTO_INCREMENT,
            `filename` varchar(255) NOT NULL,
            `original_name` varchar(255) DEFAULT NULL,
            `filepath` varchar(255) NOT NULL,
            `file_type` varchar(50) NOT NULL,
            `file_size` int NOT NULL,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `deleted_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_media_deleted` (`deleted_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        -- 14. Media Tags (Box Front, Loose Photo, Damage Detail, etc.)
        CREATE TABLE IF NOT EXISTS `media_tags` (
            `id` int NOT NULL AUTO_INCREMENT,
            `name` varchar(50) NOT NULL,
            `slug` varchar(50) NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `slug` (`slug`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        -- 15. Media File <-> Tag pivot table
        CREATE TABLE IF NOT EXISTS `media_file_tags` (
            `media_file_id` int NOT NULL,
            `media_tag_id` int NOT NULL,
            PRIMARY KEY (`media_file_id`, `media_tag_id`),
            CONSTRAINT `fk_tag_file` FOREIGN KEY (`media_file_id`) REFERENCES `media_files` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_tag_tag` FOREIGN KEY (`media_tag_id`) REFERENCES `media_tags` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        -- 16. Catalog Toy <-> Media pivot (official / reference images)
        CREATE TABLE IF NOT EXISTS `catalog_toy_media` (
            `catalog_toy_id` int NOT NULL,
            `media_file_id` int NOT NULL,
            `is_primary` boolean DEFAULT false,
            `sort_order` int DEFAULT 0,
            PRIMARY KEY (`catalog_toy_id`, `media_file_id`),
            CONSTRAINT `fk_ctm_toy` FOREIGN KEY (`catalog_toy_id`) REFERENCES `catalog_toys` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_ctm_media` FOREIGN KEY (`media_file_id`) REFERENCES `media_files` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        -- 17. Collection Toy <-> Media pivot (your own photos)
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

        -- 18. Import Sources (external websites like Galactic Figures)
        CREATE TABLE IF NOT EXISTS `importer_sources` (
            `id` int NOT NULL AUTO_INCREMENT,
            `name` varchar(100) NOT NULL,
            `slug` varchar(50) NOT NULL,
            `base_url` varchar(255) NOT NULL,
            `driver_class` varchar(255) NOT NULL,
            `is_active` boolean DEFAULT true,
            PRIMARY KEY (`id`),
            UNIQUE KEY `slug` (`slug`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        -- 19. Import Items (link between external URL and our catalog)
        CREATE TABLE IF NOT EXISTS `importer_items` (
            `id` int NOT NULL AUTO_INCREMENT,
            `source_id` int NOT NULL,
            `external_id` varchar(255) NOT NULL,
            `external_url` varchar(255) NOT NULL,
            `catalog_toy_id` int DEFAULT NULL,
            `last_imported_at` timestamp NULL DEFAULT NULL,
            `import_data_hash` varchar(32) DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `source_external` (`source_id`, `external_id`),
            CONSTRAINT `fk_imp_source` FOREIGN KEY (`source_id`) REFERENCES `importer_sources` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_imp_cat` FOREIGN KEY (`catalog_toy_id`) REFERENCES `catalog_toys` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        -- 20. Import Logs (audit trail for debugging)
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
    ",

    'down' => "
        DROP TABLE IF EXISTS `importer_logs`;
        DROP TABLE IF EXISTS `importer_items`;
        DROP TABLE IF EXISTS `importer_sources`;
        DROP TABLE IF EXISTS `collection_toy_media`;
        DROP TABLE IF EXISTS `catalog_toy_media`;
        DROP TABLE IF EXISTS `media_file_tags`;
        DROP TABLE IF EXISTS `media_tags`;
        DROP TABLE IF EXISTS `media_files`;
    "
];
