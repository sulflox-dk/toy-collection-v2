<?php

return [
    'up' => "
        -- 1. Insert Star Wars Subjects (Universe ID: 1)
        INSERT IGNORE INTO `meta_subjects` (`id`, `name`, `slug`, `type`, `universe_id`, `description`) VALUES
        -- Characters & their parts
        (101, 'Darth Vader', 'darth-vader', 'Character', 1, 'Dark Lord of the Sith'),
        (102, 'Red Lightsaber', 'red-lightsaber', 'Accessory', 1, 'Standard red lightsaber'),
        (103, 'Vinyl Cape', 'darth-vader-vinyl-cape', 'Accessory', 1, 'Original release vinyl cape'),
        (104, 'Darth Vader Cardback', 'darth-vader-cardback', 'Packaging', 1, 'Standard character cardback'),
        
        -- Vehicles & their parts
        (105, 'X-Wing Fighter', 'x-wing-fighter', 'Vehicle', 1, 'Incom T-65 X-Wing'),
        (106, 'Laser Cannon', 'x-wing-laser-cannon', 'Accessory', 1, 'Wing-mounted laser cannon'),
        (107, 'X-Wing Box', 'x-wing-box', 'Packaging', 1, 'Vehicle packaging box'),
        (108, 'X-Wing Instructions', 'x-wing-instructions', 'Paperwork', 1, 'Assembly manual'),

        -- Creatures & their parts
        (109, 'Tauntaun', 'tauntaun', 'Creature', 1, 'Snow lizard of Hoth'),
        (110, 'Tauntaun Saddle', 'tauntaun-saddle', 'Accessory', 1, 'Riding saddle'),
        (111, 'Tauntaun Reins', 'tauntaun-reins', 'Accessory', 1, 'Saddle reins'),
        (112, 'Tauntaun Box', 'tauntaun-box', 'Packaging', 1, 'Creature packaging box'),
        (113, 'Tauntaun Instructions', 'tauntaun-instructions', 'Paperwork', 1, 'Instruction sheet');


        -- 2. Insert G.I. Joe Subjects (Universe ID: 2)
        INSERT IGNORE INTO `meta_subjects` (`id`, `name`, `slug`, `type`, `universe_id`, `description`) VALUES
        -- Characters & their parts
        (114, 'Snake Eyes', 'snake-eyes', 'Character', 2, 'Commando'),
        (115, 'Uzi Submachine Gun', 'uzi-submachine-gun', 'Weapon', 2, 'Standard issue Uzi'),
        (116, 'Explosive Pack', 'explosive-pack', 'Accessory', 2, 'Satchel charge'),
        (117, 'Snake Eyes Cardback', 'snake-eyes-cardback', 'Packaging', 2, 'Standard character cardback'),
        (118, 'Filecard (Snake Eyes)', 'filecard-snake-eyes', 'Paperwork', 2, 'Character bio filecard'),

        -- Vehicles & their parts
        (119, 'H.I.S.S. Tank', 'hiss-tank', 'Vehicle', 2, 'Cobra High Speed Sentry'),
        (120, 'H.I.S.S. Turret', 'hiss-turret', 'Vehicle Part', 2, 'Top mounted dual-cannon turret'),
        (121, 'H.I.S.S. Box', 'hiss-box', 'Packaging', 2, 'Vehicle packaging box'),
        (122, 'H.I.S.S. Blueprints', 'hiss-blueprints', 'Paperwork', 2, 'Vehicle assembly blueprints');


        -- 3. Insert Masters of the Universe Subjects (Universe ID: 4)
        INSERT IGNORE INTO `meta_subjects` (`id`, `name`, `slug`, `type`, `universe_id`, `description`) VALUES
        -- Characters & their parts
        (123, 'He-Man', 'he-man', 'Character', 4, 'Most Powerful Man in the Universe'),
        (124, 'Power Sword (Half)', 'power-sword-half', 'Weapon', 4, 'Silver half-sword'),
        (125, 'Battle Axe', 'battle-axe', 'Weapon', 4, 'Standard silver battle axe'),
        (126, 'Chest Armor', 'chest-armor', 'Accessory', 4, 'Removable chest harness'),
        (127, 'He-Man Cardback', 'he-man-cardback', 'Packaging', 4, 'Standard character cardback'),
        (128, 'King of Castle Grayskull', 'minicomic-king-of-castle-grayskull', 'Paperwork', 4, 'Included minicomic'),

        -- Creatures & their parts
        (129, 'Battle Cat', 'battle-cat', 'Creature', 4, 'Fighting Tiger of Eternia'),
        (130, 'Battle Cat Helmet', 'battle-cat-helmet', 'Accessory', 4, 'Red armored helmet'),
        (131, 'Battle Cat Saddle', 'battle-cat-saddle', 'Accessory', 4, 'Red riding saddle'),
        (132, 'Battle Cat Box', 'battle-cat-box', 'Packaging', 4, 'Creature packaging box'),
        (133, 'Battle Cat Instructions', 'battle-cat-instructions', 'Paperwork', 4, 'Instruction sheet'),

        -- Environments & their parts
        (134, 'Castle Grayskull', 'castle-grayskull', 'Environment', 4, 'Fortress of Mystery and Power'),
        (135, 'Laser Cannon (Grayskull)', 'grayskull-laser-cannon', 'Accessory', 4, 'Turret gun'),
        (136, 'Trap Door', 'grayskull-trap-door', 'Accessory', 4, 'Floor trap door'),
        (137, 'Castle Grayskull Box', 'castle-grayskull-box', 'Packaging', 4, 'Playset packaging box'),
        (138, 'Castle Grayskull Manual', 'castle-grayskull-manual', 'Paperwork', 4, 'Playset assembly manual');
    ",

    'down' => "
        -- Remove the seeded subjects (100 through 150)
        -- ON DELETE RESTRICT might prevent this if they are already assigned to toys, 
        -- but for rollback purposes on a fresh DB, this cleans them up perfectly.
        DELETE FROM `meta_subjects` WHERE `id` BETWEEN 101 AND 138;
    "
];