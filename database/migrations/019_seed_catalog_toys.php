<?php

return [
    'up' => "
        -- 1. Product types. No prior migration ever seeded meta_product_types,
        -- yet the catalog toy wizard marks Product Type as required — on a
        -- fresh install this left the dropdown empty and blocked catalog toy
        -- creation entirely (and silently broke this migration's own inserts
        -- below, since they referenced a product_type_id that didn't exist).
        INSERT IGNORE INTO `meta_product_types` (`name`, `slug`, `description`) VALUES
            ('Action Figure', 'action-figure', 'Poseable figures, typically 3.75\" to 7\" scale.'),
            ('Vehicle', 'vehicle', 'Cars, ships, speeders, and other ride-on or playset vehicles.'),
            ('Playset', 'playset', 'Multi-piece environments and scenes.'),
            ('Building Set', 'building-set', 'Brick-built or construction-style sets.'),
            ('Statue / Collectible', 'statue-collectible', 'Non-articulated display pieces, busts, and statues.'),
            ('Plush', 'plush', 'Soft, stuffed collectibles.'),
            ('Accessory', 'accessory', 'Standalone weapons, gear, or accessory packs sold separately.'),
            ('Other', 'other', 'Anything that does not fit the categories above.');

        -- 2. Toy lines this demo data needs that aren't part of the base seed.
        -- Looked up dynamically by slug (see migration 009's approach) so this
        -- works regardless of what auto-increment IDs a fresh install assigns.
        INSERT IGNORE INTO `meta_toy_lines` (`name`, `slug`, `universe_id`, `manufacturer_id`, `show_on_dashboard`)
        VALUES (
            'Kenner Star Wars (Vintage)',
            'kenner-star-wars-vintage',
            (SELECT `id` FROM `meta_universes` WHERE `slug` = 'star-wars'),
            (SELECT `id` FROM `meta_manufacturers` WHERE `slug` = 'kenner'),
            1
        );

        INSERT IGNORE INTO `meta_toy_lines` (`name`, `slug`, `universe_id`, `manufacturer_id`, `show_on_dashboard`)
        VALUES (
            'The Black Series',
            'the-black-series',
            (SELECT `id` FROM `meta_universes` WHERE `slug` = 'star-wars'),
            (SELECT `id` FROM `meta_manufacturers` WHERE `slug` = 'hasbro'),
            1
        );

        -- 3. Insert Catalog Toys
        -- We use IDs starting from 100 to avoid conflicting with anything you might manually create while testing.
        -- Every foreign key is resolved by slug at insert time instead of a hardcoded ID,
        -- so this seed no longer depends on any particular database's auto-increment history.
        INSERT IGNORE INTO `catalog_toys` (`id`, `name`, `slug`, `toy_line_id`, `product_type_id`, `manufacturer_id`, `universe_id`, `year_released`, `wave`, `assortment_sku`, `upc`, `description`)
        VALUES
        (
            101, 'Luke Skywalker (Tatooine)', 'luke-skywalker-tatooine-kenner',
            (SELECT `id` FROM `meta_toy_lines` WHERE `slug` = 'kenner-star-wars-vintage'),
            (SELECT `id` FROM `meta_product_types` WHERE `slug` = 'action-figure'),
            (SELECT `id` FROM `meta_manufacturers` WHERE `slug` = 'kenner'),
            (SELECT `id` FROM `meta_universes` WHERE `slug` = 'star-wars'),
            1978, 'Wave 1 (12-Back)', '38240', '076281382408',
            'The original vintage Kenner release of Luke Skywalker with the double-telescoping or standard yellow lightsaber.'
        );

        INSERT IGNORE INTO `catalog_toys` (`id`, `name`, `slug`, `toy_line_id`, `product_type_id`, `manufacturer_id`, `universe_id`, `year_released`, `wave`, `assortment_sku`, `upc`, `description`)
        VALUES
        (
            102, 'Han Solo', 'han-solo-tbs',
            (SELECT `id` FROM `meta_toy_lines` WHERE `slug` = 'the-black-series'),
            (SELECT `id` FROM `meta_product_types` WHERE `slug` = 'action-figure'),
            (SELECT `id` FROM `meta_manufacturers` WHERE `slug` = 'hasbro'),
            (SELECT `id` FROM `meta_universes` WHERE `slug` = 'star-wars'),
            2013, 'Wave 2', 'A4301', '653569865321',
            '6-inch highly articulated figure from The Black Series.'
        );

        INSERT IGNORE INTO `catalog_toys` (`id`, `name`, `slug`, `toy_line_id`, `product_type_id`, `manufacturer_id`, `universe_id`, `year_released`, `wave`, `assortment_sku`, `upc`, `description`)
        VALUES
        (
            103, 'Millennium Falcon', 'millennium-falcon-lego-7190',
            (SELECT `id` FROM `meta_toy_lines` WHERE `slug` = 'lego-star-wars'),
            (SELECT `id` FROM `meta_product_types` WHERE `slug` = 'vehicle'),
            (SELECT `id` FROM `meta_manufacturers` WHERE `slug` = 'lego'),
            (SELECT `id` FROM `meta_universes` WHERE `slug` = 'star-wars'),
            2000, '', '7190', '000000007190',
            'Classic original Lego Millennium Falcon release.'
        );

        -- 4. Insert Catalog Toy Items (Parts and accessories that belong to the master toys)
        INSERT IGNORE INTO `catalog_toy_items` (`id`, `catalog_toy_id`, `subject_id`, `name`, `type`, `description`) VALUES
        (101, 101, NULL, 'Yellow Lightsaber', 'Accessory', 'Standard release yellow lightsaber'),
        (102, 102, NULL, 'DL-44 Heavy Blaster Pistol', 'Weapon', 'Standard issue sidearm'),
        (103, 103, NULL, 'Han Solo Minifigure', 'Figure', 'Classic yellow-faced Han Solo minifigure'),
        (104, 103, NULL, 'Chewbacca Minifigure', 'Figure', 'Classic brown molded Chewbacca minifigure'),
        (105, 103, NULL, 'Princess Leia Minifigure', 'Figure', 'Classic yellow-faced Princess Leia minifigure');

        -- 5. Insert into Collection Toys to simulate ownership
        -- We are adding Toy 101 (Kenner Luke) and Toy 103 (Lego Falcon) to the collection.
        -- Toy 102 (Black Series Han) is NOT in the collection, so it will show up when filtering by 'Missing'.
        INSERT IGNORE INTO `collection_toys` (`id`, `catalog_toy_id`, `acquisition_status_id`, `purchase_price`, `purchase_currency`)
        VALUES
        (101, 101, (SELECT `id` FROM `meta_acquisition_statuses` WHERE `slug` = 'arrived'), 45.00, 'USD'),
        (102, 103, (SELECT `id` FROM `meta_acquisition_statuses` WHERE `slug` = 'arrived'), 120.00, 'USD');

        -- Note: the original version of this migration also linked demo media
        -- files (IDs 16/17 'from your dump') to these toys. That only made sense
        -- against one specific developer database that already had those files
        -- uploaded, so it's intentionally left out here — a fresh install has no
        -- media_files rows to link to, and the FK would just fail silently.
    ",

    'down' => "
        -- Remove the seeded data in case of rollback
        DELETE FROM `media_links` WHERE `entity_type` = 'catalog_toys' AND `entity_id` IN (101, 102, 103);
        DELETE FROM `collection_toys` WHERE `catalog_toy_id` IN (101, 102, 103);
        DELETE FROM `catalog_toy_items` WHERE `catalog_toy_id` IN (101, 102, 103);
        DELETE FROM `catalog_toys` WHERE `id` IN (101, 102, 103);
        DELETE FROM `meta_toy_lines` WHERE `slug` IN ('kenner-star-wars-vintage', 'the-black-series');
        DELETE FROM `meta_product_types` WHERE `slug` IN (
            'action-figure', 'vehicle', 'playset', 'building-set',
            'statue-collectible', 'plush', 'accessory', 'other'
        );
    "
];
