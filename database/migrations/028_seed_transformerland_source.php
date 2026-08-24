<?php

return [
    'up' => "
        INSERT IGNORE INTO `importer_sources` (`name`, `slug`, `base_url`, `driver_class`, `is_active`) VALUES
            ('Transformerland', 'transformerland', 'https://www.transformerland.com', 'App\\\\Modules\\\\Importer\\\\Drivers\\\\TransformerlandDriver', 1);
    ",

    'down' => "
        DELETE FROM `importer_sources` WHERE `slug` = 'transformerland';
    "
];