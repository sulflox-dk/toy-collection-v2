<?php

return [
    'up' => "
        -- 1. Insert Universes
        INSERT IGNORE INTO `meta_universes` (`name`, `slug`, `show_on_dashboard`) VALUES
            ('Star Wars', 'star-wars', 1),
            ('G.I. Joe', 'gi-joe', 1),
            ('Marvel', 'marvel', 1);

        -- 2. Insert Manufacturers
        INSERT IGNORE INTO `meta_manufacturers` (`name`, `slug`, `show_on_dashboard`) VALUES
            ('Mattel', 'mattel', 1),
            ('Lego', 'lego', 1);

        -- 3. Insert Toy Lines
        -- Note: We use subqueries to get the correct IDs based on the slugs 
        -- so we don't have to guess what IDs the auto-increment assigned!
        
        INSERT IGNORE INTO `meta_toy_lines` (`name`, `slug`, `universe_id`, `manufacturer_id`, `show_on_dashboard`) 
        VALUES (
            'Lego Marvel', 
            'lego-marvel', 
            (SELECT `id` FROM `meta_universes` WHERE `slug` = 'marvel'), 
            (SELECT `id` FROM `meta_manufacturers` WHERE `slug` = 'lego'),
            1
        );

        INSERT IGNORE INTO `meta_toy_lines` (`name`, `slug`, `universe_id`, `manufacturer_id`, `show_on_dashboard`) 
        VALUES (
            'Lego Star Wars', 
            'lego-star-wars', 
            (SELECT `id` FROM `meta_universes` WHERE `slug` = 'star-wars'), 
            (SELECT `id` FROM `meta_manufacturers` WHERE `slug` = 'lego'),
            1
        );

        INSERT IGNORE INTO `meta_toy_lines` (`name`, `slug`, `universe_id`, `manufacturer_id`, `show_on_dashboard`) 
        VALUES (
            'The Vintage Collection', 
            'the-vintage-collection', 
            (SELECT `id` FROM `meta_universes` WHERE `slug` = 'star-wars'), 
            (SELECT `id` FROM `meta_manufacturers` WHERE `slug` = 'hasbro'), -- Hasbro was seeded in 008
            1
        );
    ",

    'down' => "
        -- Delete the test data by matching slugs
        DELETE FROM `meta_toy_lines` WHERE `slug` IN ('lego-marvel', 'lego-star-wars', 'the-vintage-collection');
        DELETE FROM `meta_manufacturers` WHERE `slug` IN ('mattel', 'lego');
        DELETE FROM `meta_universes` WHERE `slug` IN ('star-wars', 'gi-joe', 'marvel');
    ",
];