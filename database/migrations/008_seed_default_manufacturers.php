<?php

return [
    'up' => "
        INSERT IGNORE INTO `meta_manufacturers` (`name`, `slug`) VALUES
            ('Kenner', 'kenner'),
            ('Hasbro', 'hasbro');
    ",

    'down' => "
        DELETE FROM `meta_manufacturers` WHERE `slug` IN ('kenner', 'hasbro');
    ",
];
