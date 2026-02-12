<?php

return [
    'up' => "
        -- 1. Universes (Star Wars, G.I. Joe, Marvel, etc.)
        CREATE TABLE IF NOT EXISTS `meta_universes` (
            `id` int NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `slug` varchar(255) NOT NULL,
            `description` text,
            PRIMARY KEY (`id`),
            UNIQUE KEY `slug` (`slug`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        -- 2. Manufacturers (Kenner, Hasbro, Mattel, etc.)
        CREATE TABLE IF NOT EXISTS `meta_manufacturers` (
            `id` int NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `slug` varchar(255) NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `slug` (`slug`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        -- 3. Toy Lines (The Vintage Collection, Black Series, etc.)
        CREATE TABLE IF NOT EXISTS `meta_toy_lines` (
            `id` int NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `start_year` year DEFAULT NULL,
            `end_year` year DEFAULT NULL,
            `universe_id` int NOT NULL,
            `manufacturer_id` int NOT NULL,
            PRIMARY KEY (`id`),
            KEY `fk_line_universe` (`universe_id`),
            KEY `fk_line_manufacturer` (`manufacturer_id`),
            CONSTRAINT `fk_line_universe` FOREIGN KEY (`universe_id`) REFERENCES `meta_universes` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_line_manufacturer` FOREIGN KEY (`manufacturer_id`) REFERENCES `meta_manufacturers` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        -- 4. Product Types (Figure, Vehicle, Playset, etc.)
        CREATE TABLE IF NOT EXISTS `meta_product_types` (
            `id` int NOT NULL AUTO_INCREMENT,
            `name` varchar(100) NOT NULL,
            `slug` varchar(100) NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `slug` (`slug`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",

    'down' => "
        DROP TABLE IF EXISTS `meta_product_types`;
        DROP TABLE IF EXISTS `meta_toy_lines`;
        DROP TABLE IF EXISTS `meta_manufacturers`;
        DROP TABLE IF EXISTS `meta_universes`;
    "
];
