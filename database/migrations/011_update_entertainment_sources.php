<?php

return [
    'up' => "
        -- 1. Add missing columns to meta_entertainment_sources
        ALTER TABLE `meta_entertainment_sources`
        ADD COLUMN `slug` VARCHAR(255) NULL AFTER `name`,
        ADD COLUMN `description` TEXT NULL AFTER `slug`,
        ADD COLUMN `universe_id` INT(11) NULL AFTER `release_year`,
        ADD COLUMN `show_on_dashboard` TINYINT(1) NOT NULL DEFAULT 0 AFTER `universe_id`;

        -- 2. Add Indexes
        ALTER TABLE `meta_entertainment_sources`
        ADD UNIQUE KEY `idx_ent_source_slug` (`slug`),
        ADD INDEX `idx_ent_source_universe` (`universe_id`);

        -- 3. Add Foreign Key to Universe
        ALTER TABLE `meta_entertainment_sources`
        ADD CONSTRAINT `fk_ent_source_universe`
        FOREIGN KEY (`universe_id`) REFERENCES `meta_universes` (`id`)
        ON DELETE SET NULL;
    ",

    'down' => "
        ALTER TABLE `meta_entertainment_sources` DROP FOREIGN KEY `fk_ent_source_universe`;
        ALTER TABLE `meta_entertainment_sources` 
        DROP COLUMN `slug`,
        DROP COLUMN `description`,
        DROP COLUMN `universe_id`,
        DROP COLUMN `show_on_dashboard`;
    ",
];