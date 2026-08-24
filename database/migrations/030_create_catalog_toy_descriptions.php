<?php

return [
    'up' => "
        -- Multiple attributed descriptions per catalog toy — one per
        -- contributing import source, each keeping its own source name and
        -- URL for credit (or so a public showcase can choose to only ever
        -- show the collector's own writing). catalog_toys.description
        -- itself is untouched and stays the collector's own free-text
        -- notes field, separate from these.
        CREATE TABLE `catalog_toy_descriptions` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `catalog_toy_id` INT(11) NOT NULL,
            `description` TEXT NOT NULL,
            `source_name` VARCHAR(255) NULL DEFAULT NULL,
            `source_url` VARCHAR(255) NULL DEFAULT NULL,
            `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            INDEX `idx_ctd_catalog_toy` (`catalog_toy_id`),
            CONSTRAINT `fk_ctd_catalog_toy`
                FOREIGN KEY (`catalog_toy_id`) REFERENCES `catalog_toys` (`id`)
                ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ",

    'down' => "
        DROP TABLE IF EXISTS `catalog_toy_descriptions`;
    ",
];