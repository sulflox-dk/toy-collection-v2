<?php

return [
    'up' => "
        ALTER TABLE `meta_subjects`
            ADD COLUMN `external_url` VARCHAR(255) NULL DEFAULT NULL AFTER `description`;
    ",

    'down' => "
        ALTER TABLE `meta_subjects` DROP COLUMN `external_url`;
    ",
];
