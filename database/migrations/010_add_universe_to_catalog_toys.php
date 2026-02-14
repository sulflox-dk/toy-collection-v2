<?php

return [
    'up' => "
        -- Add universe_id column to catalog_toys
        -- FIXED: Removed 'UNSIGNED' to match the meta_universes.id definition
        ALTER TABLE `catalog_toys` 
        ADD COLUMN `universe_id` INT(11) NULL DEFAULT NULL AFTER `manufacturer_id`,
        ADD INDEX `idx_cat_toy_universe` (`universe_id`);

        -- Add Foreign Key Constraint (Links to meta_universes)
        ALTER TABLE `catalog_toys`
        ADD CONSTRAINT `fk_cat_toy_universe` 
        FOREIGN KEY (`universe_id`) REFERENCES `meta_universes` (`id`) 
        ON DELETE SET NULL;
    ",

    'down' => "
        -- Drop Foreign Key first
        ALTER TABLE `catalog_toys` DROP FOREIGN KEY `fk_cat_toy_universe`;
        
        -- Drop the column
        ALTER TABLE `catalog_toys` DROP COLUMN `universe_id`;
    ",
];