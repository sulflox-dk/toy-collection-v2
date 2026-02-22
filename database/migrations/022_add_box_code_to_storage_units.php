<?php

return [
    'up' => "
        -- Add box_code field after the id column
        ALTER TABLE `collection_storage_units` 
        ADD COLUMN `box_code` VARCHAR(50) COLLATE utf8mb4_unicode_ci NULL AFTER `id`;
    ",

    'down' => "
        -- Remove the box_code field
        ALTER TABLE `collection_storage_units` 
        DROP COLUMN `box_code`;
    "
];