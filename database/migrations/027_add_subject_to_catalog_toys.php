<?php

return [
    'up' => "
        -- Ties a catalog toy to the character/vehicle/etc it represents
        -- (e.g. 'Luke Skywalker'), separate from the accessories it comes
        -- with (which link via catalog_toy_items.subject_id).
        ALTER TABLE `catalog_toys`
        ADD COLUMN `subject_id` INT(11) NULL DEFAULT NULL AFTER `universe_id`,
        ADD INDEX `idx_cat_toy_subject` (`subject_id`);

        ALTER TABLE `catalog_toys`
        ADD CONSTRAINT `fk_cat_toy_subject`
        FOREIGN KEY (`subject_id`) REFERENCES `meta_subjects` (`id`)
        ON DELETE SET NULL;
    ",

    'down' => "
        ALTER TABLE `catalog_toys` DROP FOREIGN KEY `fk_cat_toy_subject`;
        ALTER TABLE `catalog_toys` DROP COLUMN `subject_id`;
    ",
];