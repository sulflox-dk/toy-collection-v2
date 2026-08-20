<?php

return [
    'up' => "
        -- 0. This migration originally hardcoded universe_id 1/2/4, assuming
        -- one specific developer database where those IDs happened to be
        -- Star Wars, G.I. Joe, and Masters of the Universe. On a fresh install
        -- (which only seeds Star Wars, G.I. Joe, and Marvel — see migration
        -- 009) universe_id 4 doesn't exist at all, so every 'Masters of the
        -- Universe' subject below silently failed to insert. Fixed by
        -- resolving every universe by slug, and seeding MOTU if it's missing.
        INSERT IGNORE INTO `meta_universes` (`name`, `slug`, `show_on_dashboard`) VALUES
            ('Masters of the Universe', 'masters-of-the-universe', 1);

        -- 1. Insert Star Wars Subjects
        INSERT IGNORE INTO `meta_subjects` (`id`, `name`, `slug`, `type`, `universe_id`, `description`) VALUES
        -- Characters & their parts
        (101, 'Darth Vader', 'darth-vader', 'Character', (SELECT `id` FROM `meta_universes` WHERE `slug` = 'star-wars'), 'Dark Lord of the Sith'),
        (102, 'Red Lightsaber', 'red-lightsaber', 'Accessory', (SELECT `id` FROM `meta_universes` WHERE `slug` = 'star-wars'), 'Standard red lightsaber'),
        (103, 'Vinyl Cape', 'darth-vader-vinyl-cape', 'Accessory', (SELECT `id` FROM `meta_universes` WHERE `slug` = 'star-wars'), 'Original release vinyl cape'),
        (104, 'Darth Vader Cardback', 'darth-vader-cardback', 'Packaging', (SELECT `id` FROM `meta_universes` WHERE `slug` = 'star-wars'), 'Standard character cardback'),

        -- Vehicles & their parts
        (105, 'X-Wing Fighter', 'x-wing-fighter', 'Vehicle', (SELECT `id` FROM `meta_universes` WHERE `slug` = 'star-wars'), 'Incom T-65 X-Wing'),
        (106, 'Laser Cannon', 'x-wing-laser-cannon', 'Accessory', (SELECT `id` FROM `meta_universes` WHERE `slug` = 'star-wars'), 'Wing-mounted laser cannon'),
        (107, 'X-Wing Box', 'x-wing-box', 'Packaging', (SELECT `id` FROM `meta_universes` WHERE `slug` = 'star-wars'), 'Vehicle packaging box'),
        (108, 'X-Wing Instructions', 'x-wing-instructions', 'Paperwork', (SELECT `id` FROM `meta_universes` WHERE `slug` = 'star-wars'), 'Assembly manual'),

        -- Creatures & their parts
        (109, 'Tauntaun', 'tauntaun', 'Creature', (SELECT `id` FROM `meta_universes` WHERE `slug` = 'star-wars'), 'Snow lizard of Hoth'),
        (110, 'Tauntaun Saddle', 'tauntaun-saddle', 'Accessory', (SELECT `id` FROM `meta_universes` WHERE `slug` = 'star-wars'), 'Riding saddle'),
        (111, 'Tauntaun Reins', 'tauntaun-reins', 'Accessory', (SELECT `id` FROM `meta_universes` WHERE `slug` = 'star-wars'), 'Saddle reins'),
        (112, 'Tauntaun Box', 'tauntaun-box', 'Packaging', (SELECT `id` FROM `meta_universes` WHERE `slug` = 'star-wars'), 'Creature packaging box'),
        (113, 'Tauntaun Instructions', 'tauntaun-instructions', 'Paperwork', (SELECT `id` FROM `meta_universes` WHERE `slug` = 'star-wars'), 'Instruction sheet');


        -- 2. Insert G.I. Joe Subjects
        INSERT IGNORE INTO `meta_subjects` (`id`, `name`, `slug`, `type`, `universe_id`, `description`) VALUES
        -- Characters & their parts
        (114, 'Snake Eyes', 'snake-eyes', 'Character', (SELECT `id` FROM `meta_universes` WHERE `slug` = 'gi-joe'), 'Commando'),
        (115, 'Uzi Submachine Gun', 'uzi-submachine-gun', 'Weapon', (SELECT `id` FROM `meta_universes` WHERE `slug` = 'gi-joe'), 'Standard issue Uzi'),
        (116, 'Explosive Pack', 'explosive-pack', 'Accessory', (SELECT `id` FROM `meta_universes` WHERE `slug` = 'gi-joe'), 'Satchel charge'),
        (117, 'Snake Eyes Cardback', 'snake-eyes-cardback', 'Packaging', (SELECT `id` FROM `meta_universes` WHERE `slug` = 'gi-joe'), 'Standard character cardback'),
        (118, 'Filecard (Snake Eyes)', 'filecard-snake-eyes', 'Paperwork', (SELECT `id` FROM `meta_universes` WHERE `slug` = 'gi-joe'), 'Character bio filecard'),

        -- Vehicles & their parts
        (119, 'H.I.S.S. Tank', 'hiss-tank', 'Vehicle', (SELECT `id` FROM `meta_universes` WHERE `slug` = 'gi-joe'), 'Cobra High Speed Sentry'),
        (120, 'H.I.S.S. Turret', 'hiss-turret', 'Vehicle Part', (SELECT `id` FROM `meta_universes` WHERE `slug` = 'gi-joe'), 'Top mounted dual-cannon turret'),
        (121, 'H.I.S.S. Box', 'hiss-box', 'Packaging', (SELECT `id` FROM `meta_universes` WHERE `slug` = 'gi-joe'), 'Vehicle packaging box'),
        (122, 'H.I.S.S. Blueprints', 'hiss-blueprints', 'Paperwork', (SELECT `id` FROM `meta_universes` WHERE `slug` = 'gi-joe'), 'Vehicle assembly blueprints');


        -- 3. Insert Masters of the Universe Subjects
        INSERT IGNORE INTO `meta_subjects` (`id`, `name`, `slug`, `type`, `universe_id`, `description`) VALUES
        -- Characters & their parts
        (123, 'He-Man', 'he-man', 'Character', (SELECT `id` FROM `meta_universes` WHERE `slug` = 'masters-of-the-universe'), 'Most Powerful Man in the Universe'),
        (124, 'Power Sword (Half)', 'power-sword-half', 'Weapon', (SELECT `id` FROM `meta_universes` WHERE `slug` = 'masters-of-the-universe'), 'Silver half-sword'),
        (125, 'Battle Axe', 'battle-axe', 'Weapon', (SELECT `id` FROM `meta_universes` WHERE `slug` = 'masters-of-the-universe'), 'Standard silver battle axe'),
        (126, 'Chest Armor', 'chest-armor', 'Accessory', (SELECT `id` FROM `meta_universes` WHERE `slug` = 'masters-of-the-universe'), 'Removable chest harness'),
        (127, 'He-Man Cardback', 'he-man-cardback', 'Packaging', (SELECT `id` FROM `meta_universes` WHERE `slug` = 'masters-of-the-universe'), 'Standard character cardback'),
        (128, 'King of Castle Grayskull', 'minicomic-king-of-castle-grayskull', 'Paperwork', (SELECT `id` FROM `meta_universes` WHERE `slug` = 'masters-of-the-universe'), 'Included minicomic'),

        -- Creatures & their parts
        (129, 'Battle Cat', 'battle-cat', 'Creature', (SELECT `id` FROM `meta_universes` WHERE `slug` = 'masters-of-the-universe'), 'Fighting Tiger of Eternia'),
        (130, 'Battle Cat Helmet', 'battle-cat-helmet', 'Accessory', (SELECT `id` FROM `meta_universes` WHERE `slug` = 'masters-of-the-universe'), 'Red armored helmet'),
        (131, 'Battle Cat Saddle', 'battle-cat-saddle', 'Accessory', (SELECT `id` FROM `meta_universes` WHERE `slug` = 'masters-of-the-universe'), 'Red riding saddle'),
        (132, 'Battle Cat Box', 'battle-cat-box', 'Packaging', (SELECT `id` FROM `meta_universes` WHERE `slug` = 'masters-of-the-universe'), 'Creature packaging box'),
        (133, 'Battle Cat Instructions', 'battle-cat-instructions', 'Paperwork', (SELECT `id` FROM `meta_universes` WHERE `slug` = 'masters-of-the-universe'), 'Instruction sheet'),

        -- Environments & their parts
        (134, 'Castle Grayskull', 'castle-grayskull', 'Environment', (SELECT `id` FROM `meta_universes` WHERE `slug` = 'masters-of-the-universe'), 'Fortress of Mystery and Power'),
        (135, 'Laser Cannon (Grayskull)', 'grayskull-laser-cannon', 'Accessory', (SELECT `id` FROM `meta_universes` WHERE `slug` = 'masters-of-the-universe'), 'Turret gun'),
        (136, 'Trap Door', 'grayskull-trap-door', 'Accessory', (SELECT `id` FROM `meta_universes` WHERE `slug` = 'masters-of-the-universe'), 'Floor trap door'),
        (137, 'Castle Grayskull Box', 'castle-grayskull-box', 'Packaging', (SELECT `id` FROM `meta_universes` WHERE `slug` = 'masters-of-the-universe'), 'Playset packaging box'),
        (138, 'Castle Grayskull Manual', 'castle-grayskull-manual', 'Paperwork', (SELECT `id` FROM `meta_universes` WHERE `slug` = 'masters-of-the-universe'), 'Playset assembly manual');
    ",

    'down' => "
        -- Remove the seeded subjects (100 through 150)
        -- ON DELETE RESTRICT might prevent this if they are already assigned to toys,
        -- but for rollback purposes on a fresh DB, this cleans them up perfectly.
        DELETE FROM `meta_subjects` WHERE `id` BETWEEN 101 AND 138;
        DELETE FROM `meta_universes` WHERE `slug` = 'masters-of-the-universe';
    "
];
