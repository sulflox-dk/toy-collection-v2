<?php

return [
    'up' => "
        ALTER TABLE `meta_product_types`
        ADD COLUMN `description` TEXT NULL AFTER `slug`;
    ",

    'down' => "
        ALTER TABLE `meta_product_types`
        DROP COLUMN `description`;
    ",
];