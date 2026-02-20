<?php

return [
    'up' => "
        -- 1. Remove existing dummy items that have no subject (from our previous seed) so the NOT NULL constraint doesn't fail
        DELETE FROM `catalog_toy_items` WHERE `subject_id` IS NULL;

        -- 2. Drop the old foreign key first
        ALTER TABLE `catalog_toy_items` DROP FOREIGN KEY `fk_cat_item_subj`;

        -- 3. Drop redundant columns and make subject_id required
        ALTER TABLE `catalog_toy_items` 
            DROP COLUMN `name`, 
            DROP COLUMN `type`,
            MODIFY COLUMN `subject_id` int NOT NULL;

        -- 4. Re-add the foreign key (RESTRICT prevents deleting a subject if a toy is using it)
        ALTER TABLE `catalog_toy_items` 
            ADD CONSTRAINT `fk_cat_item_subj` FOREIGN KEY (`subject_id`) REFERENCES `meta_subjects` (`id`) ON DELETE RESTRICT;
    ",

    'down' => "
        ALTER TABLE `catalog_toy_items` DROP FOREIGN KEY `fk_cat_item_subj`;
        
        ALTER TABLE `catalog_toy_items` 
            ADD COLUMN `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL AFTER `subject_id`,
            ADD COLUMN `type` enum('Figure','Accessory','Weapon','Vehicle Part','Display Stand','Other') COLLATE utf8mb4_unicode_ci DEFAULT 'Accessory' AFTER `name`,
            MODIFY COLUMN `subject_id` int DEFAULT NULL;

        ALTER TABLE `catalog_toy_items` 
            ADD CONSTRAINT `fk_cat_item_subj` FOREIGN KEY (`subject_id`) REFERENCES `meta_subjects` (`id`) ON DELETE SET NULL;
    "
];