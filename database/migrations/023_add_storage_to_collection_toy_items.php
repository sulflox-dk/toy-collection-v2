<?php

return [
    'up' => "
        ALTER TABLE `collection_toy_items`
        ADD COLUMN `storage_unit_id` INT DEFAULT NULL AFTER `condition_notes`;

        ALTER TABLE `collection_toy_items`
        ADD INDEX `idx_col_item_storage` (`storage_unit_id`);

        ALTER TABLE `collection_toy_items`
        ADD CONSTRAINT `fk_col_item_storage`
            FOREIGN KEY (`storage_unit_id`) REFERENCES `collection_storage_units` (`id`)
            ON DELETE SET NULL;
    ",

    'down' => "
        ALTER TABLE `collection_toy_items`
        DROP FOREIGN KEY `fk_col_item_storage`;

        ALTER TABLE `collection_toy_items`
        DROP COLUMN `storage_unit_id`;
    ",
];
