<?php

return [
    'up' => "
        -- Where an imported photo actually came from, so it can be
        -- credited (or excluded from a public showcase) later. NULL for
        -- anything uploaded directly by the user — there's no external
        -- source to credit.
        ALTER TABLE `media_files`
        ADD COLUMN `source_url` VARCHAR(255) NULL DEFAULT NULL AFTER `alt_text`,
        ADD COLUMN `source_name` VARCHAR(255) NULL DEFAULT NULL AFTER `source_url`;
    ",

    'down' => "
        ALTER TABLE `media_files` DROP COLUMN `source_name`;
        ALTER TABLE `media_files` DROP COLUMN `source_url`;
    ",
];