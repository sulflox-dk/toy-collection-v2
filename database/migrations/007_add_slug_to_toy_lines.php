<?php

return [
    'up' => "
        ALTER TABLE `meta_toy_lines` ADD COLUMN `slug` VARCHAR(255) DEFAULT NULL AFTER `name`;
        CREATE UNIQUE INDEX `idx_meta_toy_lines_slug` ON `meta_toy_lines` (`slug`);
    ",

    'down' => "
        DROP INDEX `idx_meta_toy_lines_slug` ON `meta_toy_lines`;
        ALTER TABLE `meta_toy_lines` DROP COLUMN `slug`;
    ",
];