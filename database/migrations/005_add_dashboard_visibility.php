<?php

return [
    'up' => "
        ALTER TABLE `meta_manufacturers` ADD COLUMN `show_on_dashboard` TINYINT(1) NOT NULL DEFAULT 1;

        ALTER TABLE `meta_universes` ADD COLUMN `show_on_dashboard` TINYINT(1) NOT NULL DEFAULT 1;

        ALTER TABLE `meta_toy_lines` ADD COLUMN `show_on_dashboard` TINYINT(1) NOT NULL DEFAULT 1;
    ",

    'down' => "
        ALTER TABLE `meta_manufacturers` DROP COLUMN `show_on_dashboard`;
        ALTER TABLE `meta_universes` DROP COLUMN `show_on_dashboard`;
        ALTER TABLE `meta_toy_lines` DROP COLUMN `show_on_dashboard`;
    "
];