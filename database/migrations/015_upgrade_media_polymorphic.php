<?php

return [
    'up' => "
        -- 1. Upgrade Media Files Table
        ALTER TABLE `media_files`
        ADD COLUMN `title` VARCHAR(255) NULL AFTER `filename`,
        ADD COLUMN `description` TEXT NULL AFTER `title`,
        ADD COLUMN `alt_text` VARCHAR(255) NULL AFTER `description`;

        -- 2. Create the new Master Link Table
        CREATE TABLE `media_links` (
            `id` int NOT NULL AUTO_INCREMENT,
            `media_file_id` int NOT NULL,
            `entity_id` int NOT NULL,
            `entity_type` varchar(50) NOT NULL,
            `is_featured` tinyint(1) DEFAULT '0', -- Maps to 'is_primary'
            `sort_order` int DEFAULT '0',
            PRIMARY KEY (`id`),
            UNIQUE KEY `unique_link` (`media_file_id`, `entity_id`, `entity_type`),
            KEY `idx_entity_lookup` (`entity_type`, `entity_id`),
            CONSTRAINT `fk_media_link_file` FOREIGN KEY (`media_file_id`) REFERENCES `media_files` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        -- 3. MIGRATE DATA (The important part!)
        -- Copy connections from Catalog Toys
        INSERT INTO media_links (media_file_id, entity_id, entity_type, is_featured, sort_order)
        SELECT media_file_id, catalog_toy_id, 'catalog_toy', is_primary, sort_order
        FROM catalog_toy_media;

        -- Copy connections from Collection Toys
        INSERT INTO media_links (media_file_id, entity_id, entity_type, is_featured, sort_order)
        SELECT media_file_id, collection_toy_id, 'collection_toy', is_primary, sort_order
        FROM collection_toy_media;

        -- 4. Now safe to drop old tables
        DROP TABLE `catalog_toy_media`;
        DROP TABLE `collection_toy_media`;
    ",

    'down' => "
        -- Re-create old tables
        CREATE TABLE `catalog_toy_media` (
            `catalog_toy_id` int NOT NULL,
            `media_file_id` int NOT NULL,
            `is_primary` tinyint(1) DEFAULT '0',
            `sort_order` int DEFAULT '0',
            PRIMARY KEY (`catalog_toy_id`,`media_file_id`)
        );
        CREATE TABLE `collection_toy_media` (
            `collection_toy_id` int NOT NULL,
            `media_file_id` int NOT NULL,
            `is_primary` tinyint(1) DEFAULT '0',
            `sort_order` int DEFAULT '0',
            PRIMARY KEY (`collection_toy_id`,`media_file_id`)
        );

        -- Restore data (Reverse migration)
        INSERT INTO catalog_toy_media (catalog_toy_id, media_file_id, is_primary, sort_order)
        SELECT entity_id, media_file_id, is_featured, sort_order
        FROM media_links WHERE entity_type = 'catalog_toy';

        INSERT INTO collection_toy_media (collection_toy_id, media_file_id, is_primary, sort_order)
        SELECT entity_id, media_file_id, is_featured, sort_order
        FROM media_links WHERE entity_type = 'collection_toy';

        DROP TABLE `media_links`;
        ALTER TABLE `media_files` DROP COLUMN `title`, DROP COLUMN `description`, DROP COLUMN `alt_text`;
    ",
];