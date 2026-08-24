<?php

return [
    'up' => "
        INSERT IGNORE INTO `importer_sources` (`name`, `slug`, `base_url`, `driver_class`, `is_active`) VALUES
            ('Rebelscum', 'rebelscum', 'https://www.rebelscum.com', 'App\\\\Modules\\\\Importer\\\\Drivers\\\\RebelscumDriver', 1);
    ",

    'down' => "
        DELETE FROM `importer_sources` WHERE `slug` = 'rebelscum';
    "
];