<?php

return [
    'up' => "
        -- 1. Add columns to meta_subjects
        ALTER TABLE `meta_subjects`
        ADD COLUMN `slug` VARCHAR(255) NULL AFTER `name`,
        ADD COLUMN `type` ENUM('Character','Vehicle','Environment','Creature','Accessory','Packaging','Paperwork') NOT NULL DEFAULT 'Character' AFTER `slug`,
        ADD COLUMN `universe_id` INT(11) NULL AFTER `type`;

        -- 2. Add Indexes
        ALTER TABLE `meta_subjects`
        ADD UNIQUE KEY `idx_subject_slug` (`slug`),
        ADD INDEX `idx_subject_type` (`type`),
        ADD INDEX `idx_subject_universe` (`universe_id`);

        -- 3. Add Foreign Key to Universe
        ALTER TABLE `meta_subjects`
        ADD CONSTRAINT `fk_subject_universe`
        FOREIGN KEY (`universe_id`) REFERENCES `meta_universes` (`id`)
        ON DELETE SET NULL;
    ",

    'down' => "
        ALTER TABLE `meta_subjects` DROP FOREIGN KEY `fk_subject_universe`;
        ALTER TABLE `meta_subjects` 
        DROP COLUMN `slug`,
        DROP COLUMN `type`,
        DROP COLUMN `universe_id`;
    ",
];