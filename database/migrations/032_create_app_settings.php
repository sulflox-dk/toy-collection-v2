<?php

return [
    'up' => "
        -- Single-row table for cross-system settings. Starting with just
        -- the collection's default currency, so purchase price / current
        -- value are always entered in one consistent currency — needed to
        -- add money invested up across universes/manufacturers/toy lines
        -- later without a currency conversion step.
        CREATE TABLE `app_settings` (
            `id` TINYINT(1) NOT NULL,
            `currency` CHAR(3) NOT NULL DEFAULT 'USD',
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        INSERT IGNORE INTO `app_settings` (`id`, `currency`) VALUES (1, 'USD');
    ",

    'down' => "
        DROP TABLE IF EXISTS `app_settings`;
    ",
];
