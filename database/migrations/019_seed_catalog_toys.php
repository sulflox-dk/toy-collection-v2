<?php

return [
    'up' => "
        -- 1. Insert Catalog Toys
        -- We use IDs starting from 100 to avoid conflicting with anything you might manually create while testing.
        -- We are mapping these to existing meta data IDs (Universes, Toy Lines, Manufacturers, etc.) from your database dump.
        INSERT IGNORE INTO `catalog_toys` (`id`, `name`, `slug`, `toy_line_id`, `product_type_id`, `entertainment_source_id`, `manufacturer_id`, `universe_id`, `year_released`, `wave`, `assortment_sku`, `upc`, `description`) VALUES
        (101, 'Luke Skywalker (Tatooine)', 'luke-skywalker-tatooine-kenner', 7, 1, 1, 1, 1, 1978, 'Wave 1 (12-Back)', '38240', '076281382408', 'The original vintage Kenner release of Luke Skywalker with the double-telescoping or standard yellow lightsaber.'),
        (102, 'Han Solo', 'han-solo-tbs', 6, 1, 1, 2, 1, 2013, 'Wave 2', 'A4301', '653569865321', '6-inch highly articulated figure from The Black Series.'),
        (103, 'Millennium Falcon', 'millennium-falcon-lego-7190', 4, 2, 1, 7, 1, 2000, '', '7190', '000000007190', 'Classic original Lego Millennium Falcon release.');

        -- 2. Insert Catalog Toy Items (Parts and accessories that belong to the master toys)
        INSERT IGNORE INTO `catalog_toy_items` (`id`, `catalog_toy_id`, `subject_id`, `name`, `type`, `description`) VALUES
        (101, 101, NULL, 'Yellow Lightsaber', 'Accessory', 'Standard release yellow lightsaber'),
        (102, 102, NULL, 'DL-44 Heavy Blaster Pistol', 'Weapon', 'Standard issue sidearm'),
        (103, 103, 2, 'Han Solo Minifigure', 'Figure', 'Classic yellow-faced Han Solo minifigure'),
        (104, 103, NULL, 'Chewbacca Minifigure', 'Figure', 'Classic brown molded Chewbacca minifigure'),
        (105, 103, NULL, 'Princess Leia Minifigure', 'Figure', 'Classic yellow-faced Princess Leia minifigure');

        -- 3. Insert into Collection Toys to simulate ownership
        -- We are adding Toy 101 (Kenner Luke) and Toy 103 (Lego Falcon) to the collection. 
        -- Toy 102 (Black Series Han) is NOT in the collection, so it will show up when filtering by 'Missing'.
        INSERT IGNORE INTO `collection_toys` (`id`, `catalog_toy_id`, `acquisition_status_id`, `purchase_price`, `purchase_currency`) VALUES
        (101, 101, 1, 45.00, 'USD'),
        (102, 103, 1, 120.00, 'USD');

        -- 4. Attach Media Links to simulate uploaded images
        -- We link existing media files (IDs 16 and 17 from your dump) to toys 101 and 102. 
        -- Toy 103 will have no image to test the 'No Photo' filter.
        INSERT IGNORE INTO `media_links` (`media_file_id`, `entity_id`, `entity_type`, `is_featured`, `sort_order`) VALUES
        (16, 101, 'catalog_toys', 1, 0),
        (17, 102, 'catalog_toys', 1, 0);
    ",

    'down' => "
        -- Remove the seeded data in case of rollback
        -- Delete media links first to keep polymorphic references clean
        DELETE FROM `media_links` WHERE `entity_type` = 'catalog_toys' AND `entity_id` IN (101, 102, 103);
        
        -- Delete collection toys
        DELETE FROM `collection_toys` WHERE `catalog_toy_id` IN (101, 102, 103);
        
        -- Delete catalog toy items (though ON DELETE CASCADE on the table should handle this, it's safer to explicitly declare)
        DELETE FROM `catalog_toy_items` WHERE `catalog_toy_id` IN (101, 102, 103);
        
        -- Finally, delete the master catalog toys
        DELETE FROM `catalog_toys` WHERE `id` IN (101, 102, 103);
    "
];