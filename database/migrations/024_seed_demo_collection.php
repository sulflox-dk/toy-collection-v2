<?php

return [
    'up' => "
        -- Migration 021 seeded meta_subjects for five more toys (Darth Vader,
        -- X-Wing Fighter, Tauntaun, Snake Eyes, H.I.S.S. Tank) plus a whole
        -- third franchise (Masters of the Universe: He-Man, Battle Cat, Castle
        -- Grayskull) — but nothing ever turned those subjects into actual
        -- catalog toys, so they've sat unused. This migration finishes that:
        -- 8 catalog toys, their component parts, a few storage locations, a
        -- couple of purchase sources, and enough of an owned collection
        -- (arrived / ordered / pre-ordered / wishlist, one missing part, one
        -- reproduction part) to have something real to click through while
        -- testing and building the rest of the app. SKUs/years are
        -- plausible, not researched — this is demo data, not a price guide.

        -- 1. Two toy lines this data needs that don't exist yet.
        INSERT IGNORE INTO `meta_toy_lines` (`name`, `slug`, `universe_id`, `manufacturer_id`, `show_on_dashboard`)
        VALUES (
            'G.I. Joe: A Real American Hero',
            'gi-joe-a-real-american-hero',
            (SELECT `id` FROM `meta_universes` WHERE `slug` = 'gi-joe'),
            (SELECT `id` FROM `meta_manufacturers` WHERE `slug` = 'hasbro'),
            1
        );
        INSERT IGNORE INTO `meta_toy_lines` (`name`, `slug`, `universe_id`, `manufacturer_id`, `show_on_dashboard`)
        VALUES (
            'Masters of the Universe (Vintage)',
            'motu-vintage',
            (SELECT `id` FROM `meta_universes` WHERE `slug` = 'masters-of-the-universe'),
            (SELECT `id` FROM `meta_manufacturers` WHERE `slug` = 'mattel'),
            1
        );

        -- 2. Purchase sources. This lookup table has been readable in the
        -- collection-toy wizard this whole time with nothing in it and no
        -- admin page to add to it — seeding it here so the dropdown isn't
        -- empty. (Worth building a real Sources admin page separately.)
        INSERT IGNORE INTO `collection_sources` (`id`, `name`, `website`) VALUES
        (1, 'eBay', 'https://www.ebay.com'),
        (2, 'Local Comic Shop', NULL),
        (3, 'Toy Convention', NULL);

        -- 3. Storage locations.
        INSERT IGNORE INTO `collection_storage_units` (`id`, `name`, `box_code`, `location`, `description`) VALUES
        (1, 'Display Shelf A', NULL, 'Living Room', 'Front-facing display shelf'),
        (2, 'Storage Box 1', 'BOX-01', 'Attic', 'Sealed / boxed items'),
        (3, 'Closet Bin 3', 'BIN-03', 'Hall Closet', 'Overflow storage bin');

        -- 4. The 8 catalog toys.
        INSERT IGNORE INTO `catalog_toys` (`id`, `name`, `slug`, `toy_line_id`, `product_type_id`, `manufacturer_id`, `universe_id`, `year_released`, `wave`, `assortment_sku`, `upc`, `description`) VALUES
        (104, 'Darth Vader', 'darth-vader-kenner',
            (SELECT `id` FROM `meta_toy_lines` WHERE `slug` = 'kenner-star-wars-vintage'),
            (SELECT `id` FROM `meta_product_types` WHERE `slug` = 'action-figure'),
            (SELECT `id` FROM `meta_manufacturers` WHERE `slug` = 'kenner'),
            (SELECT `id` FROM `meta_universes` WHERE `slug` = 'star-wars'),
            1978, '12-Back', '38110', '076281381104', 'Vintage Kenner Darth Vader with removable vinyl cape.'),
        (105, 'X-Wing Fighter', 'x-wing-fighter-kenner',
            (SELECT `id` FROM `meta_toy_lines` WHERE `slug` = 'kenner-star-wars-vintage'),
            (SELECT `id` FROM `meta_product_types` WHERE `slug` = 'vehicle'),
            (SELECT `id` FROM `meta_manufacturers` WHERE `slug` = 'kenner'),
            (SELECT `id` FROM `meta_universes` WHERE `slug` = 'star-wars'),
            1978, '', '38080', '076281380800', 'Die-cast wing-mounted laser cannons, retractable landing gear.'),
        (106, 'Tauntaun', 'tauntaun-kenner',
            (SELECT `id` FROM `meta_toy_lines` WHERE `slug` = 'kenner-star-wars-vintage'),
            (SELECT `id` FROM `meta_product_types` WHERE `slug` = 'action-figure'),
            (SELECT `id` FROM `meta_manufacturers` WHERE `slug` = 'kenner'),
            (SELECT `id` FROM `meta_universes` WHERE `slug` = 'star-wars'),
            1980, '', '39250', '076281392506', 'Open-belly variant, with saddle and reins.'),
        (107, 'Snake Eyes', 'snake-eyes-gijoe',
            (SELECT `id` FROM `meta_toy_lines` WHERE `slug` = 'gi-joe-a-real-american-hero'),
            (SELECT `id` FROM `meta_product_types` WHERE `slug` = 'action-figure'),
            (SELECT `id` FROM `meta_manufacturers` WHERE `slug` = 'hasbro'),
            (SELECT `id` FROM `meta_universes` WHERE `slug` = 'gi-joe'),
            1985, 'Series 4', '6430', '653569643004', 'Commando figure with Uzi and filecard.'),
        (108, 'H.I.S.S. Tank', 'hiss-tank-gijoe',
            (SELECT `id` FROM `meta_toy_lines` WHERE `slug` = 'gi-joe-a-real-american-hero'),
            (SELECT `id` FROM `meta_product_types` WHERE `slug` = 'vehicle'),
            (SELECT `id` FROM `meta_manufacturers` WHERE `slug` = 'hasbro'),
            (SELECT `id` FROM `meta_universes` WHERE `slug` = 'gi-joe'),
            1983, 'Series 2', '6015', '653569601508', 'Cobra High Speed Sentry with rotating turret.'),
        (109, 'He-Man', 'he-man-motu',
            (SELECT `id` FROM `meta_toy_lines` WHERE `slug` = 'motu-vintage'),
            (SELECT `id` FROM `meta_product_types` WHERE `slug` = 'action-figure'),
            (SELECT `id` FROM `meta_manufacturers` WHERE `slug` = 'mattel'),
            (SELECT `id` FROM `meta_universes` WHERE `slug` = 'masters-of-the-universe'),
            1982, 'Series 1', '9963', '073774996306', 'Original 1982 release with Power Sword and Battle Axe.'),
        (110, 'Battle Cat', 'battle-cat-motu',
            (SELECT `id` FROM `meta_toy_lines` WHERE `slug` = 'motu-vintage'),
            (SELECT `id` FROM `meta_product_types` WHERE `slug` = 'action-figure'),
            (SELECT `id` FROM `meta_manufacturers` WHERE `slug` = 'mattel'),
            (SELECT `id` FROM `meta_universes` WHERE `slug` = 'masters-of-the-universe'),
            1982, 'Series 1', '9964', '073774996405', 'Fighting Tiger of Eternia, with helmet and saddle.'),
        (111, 'Castle Grayskull', 'castle-grayskull-motu',
            (SELECT `id` FROM `meta_toy_lines` WHERE `slug` = 'motu-vintage'),
            (SELECT `id` FROM `meta_product_types` WHERE `slug` = 'playset'),
            (SELECT `id` FROM `meta_manufacturers` WHERE `slug` = 'mattel'),
            (SELECT `id` FROM `meta_universes` WHERE `slug` = 'masters-of-the-universe'),
            1982, '', '9773', '073774977305', 'Fortress playset with working trap door and laser cannon.');

        -- 5. Catalog toy items — every non-headline subject from migration
        -- 021, attached to the catalog toy it actually belongs to.
        INSERT INTO `catalog_toy_items` (`catalog_toy_id`, `subject_id`, `description`)
        SELECT 104, `id`, NULL FROM `meta_subjects` WHERE `slug` IN ('red-lightsaber', 'darth-vader-vinyl-cape', 'darth-vader-cardback')
        UNION ALL SELECT 105, `id`, NULL FROM `meta_subjects` WHERE `slug` IN ('x-wing-laser-cannon', 'x-wing-box', 'x-wing-instructions')
        UNION ALL SELECT 106, `id`, NULL FROM `meta_subjects` WHERE `slug` IN ('tauntaun-saddle', 'tauntaun-reins', 'tauntaun-box', 'tauntaun-instructions')
        UNION ALL SELECT 107, `id`, NULL FROM `meta_subjects` WHERE `slug` IN ('uzi-submachine-gun', 'explosive-pack', 'snake-eyes-cardback', 'filecard-snake-eyes')
        UNION ALL SELECT 108, `id`, NULL FROM `meta_subjects` WHERE `slug` IN ('hiss-turret', 'hiss-box', 'hiss-blueprints')
        UNION ALL SELECT 109, `id`, NULL FROM `meta_subjects` WHERE `slug` IN ('power-sword-half', 'battle-axe', 'chest-armor', 'he-man-cardback', 'minicomic-king-of-castle-grayskull')
        UNION ALL SELECT 110, `id`, NULL FROM `meta_subjects` WHERE `slug` IN ('battle-cat-helmet', 'battle-cat-saddle', 'battle-cat-box', 'battle-cat-instructions')
        UNION ALL SELECT 111, `id`, NULL FROM `meta_subjects` WHERE `slug` IN ('grayskull-laser-cannon', 'grayskull-trap-door', 'castle-grayskull-box', 'castle-grayskull-manual');

        -- 6. Add these toys to the owned collection, spread across every
        -- acquisition status so status filters have something to show.
        INSERT INTO `collection_toys`
            (`id`, `catalog_toy_id`, `storage_unit_id`, `purchase_source_id`, `acquisition_status_id`, `date_acquired`, `purchase_price`, `purchase_currency`, `current_value`, `packaging_type_id`, `condition_grade_id`)
        VALUES
        (104, 104, 1, 1, (SELECT `id` FROM `meta_acquisition_statuses` WHERE `slug` = 'arrived'), '2025-11-08', 65.00, 'USD', 85.00,
            (SELECT `id` FROM `meta_packaging_types` WHERE `slug` = 'moc'), (SELECT `id` FROM `meta_condition_grades` WHERE `slug` = 'near-mint')),
        (105, 105, 2, 1, (SELECT `id` FROM `meta_acquisition_statuses` WHERE `slug` = 'arrived'), '2026-01-14', 140.00, 'USD', 220.00,
            (SELECT `id` FROM `meta_packaging_types` WHERE `slug` = 'misb'), (SELECT `id` FROM `meta_condition_grades` WHERE `slug` = 'mint')),
        (106, 106, 1, 2, (SELECT `id` FROM `meta_acquisition_statuses` WHERE `slug` = 'arrived'), '2026-02-20', 38.00, 'USD', 55.00,
            (SELECT `id` FROM `meta_packaging_types` WHERE `slug` = 'loose'), (SELECT `id` FROM `meta_condition_grades` WHERE `slug` = 'good')),
        (107, 107, 2, 2, (SELECT `id` FROM `meta_acquisition_statuses` WHERE `slug` = 'arrived'), '2026-03-30', 45.00, 'USD', 70.00,
            (SELECT `id` FROM `meta_packaging_types` WHERE `slug` = 'moc'), (SELECT `id` FROM `meta_condition_grades` WHERE `slug` = 'excellent')),
        (108, 108, NULL, NULL, (SELECT `id` FROM `meta_acquisition_statuses` WHERE `slug` = 'wishlist'), NULL, NULL, 'USD', NULL, NULL, NULL),
        (109, 109, 1, 3, (SELECT `id` FROM `meta_acquisition_statuses` WHERE `slug` = 'arrived'), '2026-04-12', 22.00, 'USD', 35.00,
            (SELECT `id` FROM `meta_packaging_types` WHERE `slug` = 'loose'), (SELECT `id` FROM `meta_condition_grades` WHERE `slug` = 'good')),
        (110, 110, NULL, 1, (SELECT `id` FROM `meta_acquisition_statuses` WHERE `slug` = 'pre-ordered'), NULL, 60.00, 'USD', NULL, NULL, NULL),
        (111, 111, NULL, 2, (SELECT `id` FROM `meta_acquisition_statuses` WHERE `slug` = 'ordered'), NULL, 95.00, 'USD', NULL, NULL, NULL);

        -- 7. A couple of owned-parts records to demonstrate completeness
        -- tracking: Tauntaun is missing its reins and its saddle is a
        -- reproduction; He-Man is missing his half of the Power Sword.
        INSERT INTO `collection_toy_items` (`collection_toy_id`, `catalog_toy_item_id`, `is_present`, `is_repro`)
        SELECT 106, cti.`id`, 0, 0 FROM `catalog_toy_items` cti JOIN `meta_subjects` s ON cti.`subject_id` = s.`id`
            WHERE cti.`catalog_toy_id` = 106 AND s.`slug` = 'tauntaun-reins'
        UNION ALL
        SELECT 106, cti.`id`, 1, 1 FROM `catalog_toy_items` cti JOIN `meta_subjects` s ON cti.`subject_id` = s.`id`
            WHERE cti.`catalog_toy_id` = 106 AND s.`slug` = 'tauntaun-saddle'
        UNION ALL
        SELECT 109, cti.`id`, 0, 0 FROM `catalog_toy_items` cti JOIN `meta_subjects` s ON cti.`subject_id` = s.`id`
            WHERE cti.`catalog_toy_id` = 109 AND s.`slug` = 'power-sword-half';

        -- 8. Backfill the two pre-existing demo collection toys (from
        -- migration 019) with an acquisition date and an estimated current
        -- value, since neither had one — mainly so any future 'spend over
        -- time' / 'value vs. spend' dashboard widget has real data to plot
        -- from day one instead of two rows with a NULL date.
        UPDATE `collection_toys` SET `date_acquired` = '2025-09-05', `current_value` = 65.00
            WHERE `id` = 101 AND `date_acquired` IS NULL;
        UPDATE `collection_toys` SET `date_acquired` = '2025-10-22', `current_value` = 150.00
            WHERE `id` = 102 AND `date_acquired` IS NULL;
    ",

    'down' => "
        DELETE FROM `collection_toy_items` WHERE `collection_toy_id` IN (104, 105, 106, 107, 108, 109, 110, 111);
        DELETE FROM `collection_toys` WHERE `id` IN (104, 105, 106, 107, 108, 109, 110, 111);
        DELETE FROM `catalog_toy_items` WHERE `catalog_toy_id` IN (104, 105, 106, 107, 108, 109, 110, 111);
        DELETE FROM `catalog_toys` WHERE `id` IN (104, 105, 106, 107, 108, 109, 110, 111);
        DELETE FROM `collection_storage_units` WHERE `id` IN (1, 2, 3);
        DELETE FROM `collection_sources` WHERE `id` IN (1, 2, 3);
        DELETE FROM `meta_toy_lines` WHERE `slug` IN ('gi-joe-a-real-american-hero', 'motu-vintage');
    "
];
