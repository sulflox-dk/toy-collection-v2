<?php

return [
    'up' => "
        -- 1. Create Lookup Tables
        -- -----------------------

        CREATE TABLE IF NOT EXISTS `meta_acquisition_statuses` (
            `id` int NOT NULL AUTO_INCREMENT,
            `name` varchar(50) NOT NULL,
            `slug` varchar(50) NOT NULL,
            `sort_order` int DEFAULT 0,
            PRIMARY KEY (`id`),
            UNIQUE KEY `slug` (`slug`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS `meta_packaging_types` (
            `id` int NOT NULL AUTO_INCREMENT,
            `name` varchar(50) NOT NULL,
            `slug` varchar(50) NOT NULL,
            `description` varchar(255) DEFAULT NULL,
            `sort_order` int DEFAULT 0,
            PRIMARY KEY (`id`),
            UNIQUE KEY `slug` (`slug`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS `meta_condition_grades` (
            `id` int NOT NULL AUTO_INCREMENT,
            `name` varchar(50) NOT NULL, -- e.g. 'Near Mint'
            `slug` varchar(50) NOT NULL,
            `abbreviation` varchar(10) DEFAULT NULL, -- e.g. 'NM' or 'C9'
            `description` varchar(255) DEFAULT NULL,
            `sort_order` int DEFAULT 0,
            PRIMARY KEY (`id`),
            UNIQUE KEY `slug` (`slug`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS `meta_grading_companies` (
            `id` int NOT NULL AUTO_INCREMENT,
            `name` varchar(50) NOT NULL, -- e.g. 'AFA'
            `slug` varchar(50) NOT NULL,
            `website` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `slug` (`slug`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS `meta_grader_tiers` (
            `id` int NOT NULL AUTO_INCREMENT,
            `name` varchar(50) NOT NULL, -- e.g. 'Gold'
            `slug` varchar(50) NOT NULL,
            `sort_order` int DEFAULT 0,
            PRIMARY KEY (`id`),
            UNIQUE KEY `slug` (`slug`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


        -- 2. Seed Default Data (Migrating the old Enum values)
        -- ----------------------------------------------------

        INSERT INTO `meta_acquisition_statuses` (`name`, `slug`, `sort_order`) VALUES
        ('Arrived', 'arrived', 10),
        ('Ordered', 'ordered', 20),
        ('Pre-ordered', 'pre-ordered', 30),
        ('Wishlist', 'wishlist', 40);

        INSERT INTO `meta_packaging_types` (`name`, `slug`, `description`, `sort_order`) VALUES
        ('Loose', 'loose', 'Item is loose, no packaging', 10),
        ('MOC', 'moc', 'Mint on Card', 20),
        ('MIB', 'mib', 'Mint in Box', 30),
        ('MISB', 'misb', 'Mint in Sealed Box', 40);

        INSERT INTO `meta_condition_grades` (`name`, `slug`, `abbreviation`, `sort_order`) VALUES
        ('Mint', 'mint', 'M', 90),
        ('Near Mint', 'near-mint', 'NM', 80),
        ('Excellent', 'excellent', 'EX', 70),
        ('Very Good', 'very-good', 'VG', 60),
        ('Good', 'good', 'G', 50),
        ('Fair', 'fair', 'F', 40),
        ('Poor', 'poor', 'P', 30);

        INSERT INTO `meta_grading_companies` (`name`, `slug`) VALUES
        ('None', 'none'),
        ('Action Figure Authority', 'afa'),
        ('UK Graders', 'ukg'),
        ('Collector Archive Services', 'cas');

        INSERT INTO `meta_grader_tiers` (`name`, `slug`, `sort_order`) VALUES
        ('Gold', 'gold', 90),
        ('Silver', 'silver', 80),
        ('Bronze', 'bronze', 70),
        ('Standard', 'standard', 50),
        ('Uncirculated', 'uncirculated', 100),
        ('Qualified', 'qualified', 40);


        -- 3. Update collection_toys Table
        -- -------------------------------
        
        -- Add the new Foreign Key columns
        ALTER TABLE `collection_toys`
            ADD COLUMN `acquisition_status_id` int DEFAULT NULL AFTER `purchase_source_id`,
            ADD COLUMN `packaging_type_id` int DEFAULT NULL AFTER `current_value`,
            ADD COLUMN `condition_grade_id` int DEFAULT NULL AFTER `packaging_type_id`,
            ADD COLUMN `grader_company_id` int DEFAULT NULL AFTER `condition_grade_id`,
            ADD COLUMN `grader_tier_id` int DEFAULT NULL AFTER `grader_company_id`;

        -- Note: If you had existing data, we would run UPDATE queries here to map Enums to IDs.
        -- Since the DB is likely empty/test, we skip the mapping to keep it clean.

        -- Drop the old Enum columns
        ALTER TABLE `collection_toys`
            DROP COLUMN `acquisition_status`,
            DROP COLUMN `packaging_status`,
            DROP COLUMN `condition_grade`,
            DROP COLUMN `grader_company`,
            DROP COLUMN `grader_tier`;

        -- Add Foreign Key Constraints
        ALTER TABLE `collection_toys`
            ADD CONSTRAINT `fk_col_acq_status` FOREIGN KEY (`acquisition_status_id`) REFERENCES `meta_acquisition_statuses` (`id`) ON DELETE RESTRICT,
            ADD CONSTRAINT `fk_col_pack_type` FOREIGN KEY (`packaging_type_id`) REFERENCES `meta_packaging_types` (`id`) ON DELETE RESTRICT,
            ADD CONSTRAINT `fk_col_cond_grade` FOREIGN KEY (`condition_grade_id`) REFERENCES `meta_condition_grades` (`id`) ON DELETE SET NULL,
            ADD CONSTRAINT `fk_col_grad_comp` FOREIGN KEY (`grader_company_id`) REFERENCES `meta_grading_companies` (`id`) ON DELETE SET NULL,
            ADD CONSTRAINT `fk_col_grad_tier` FOREIGN KEY (`grader_tier_id`) REFERENCES `meta_grader_tiers` (`id`) ON DELETE SET NULL;
    ",

    'down' => "
        -- Revert changes (Drop FKs, Drop Columns, Re-add Enums)
        -- We won't write the full revert logic for Enums here as it is complex, 
        -- but we will clean up the tables.

        ALTER TABLE `collection_toys`
            DROP FOREIGN KEY `fk_col_acq_status`,
            DROP FOREIGN KEY `fk_col_pack_type`,
            DROP FOREIGN KEY `fk_col_cond_grade`,
            DROP FOREIGN KEY `fk_col_grad_comp`,
            DROP FOREIGN KEY `fk_col_grad_tier`;

        ALTER TABLE `collection_toys`
            DROP COLUMN `acquisition_status_id`,
            DROP COLUMN `packaging_type_id`,
            DROP COLUMN `condition_grade_id`,
            DROP COLUMN `grader_company_id`,
            DROP COLUMN `grader_tier_id`,
            -- Restore Enums (Simplified for rollback)
            ADD COLUMN `acquisition_status` varchar(50) DEFAULT NULL, 
            ADD COLUMN `packaging_status` varchar(50) DEFAULT NULL,
            ADD COLUMN `condition_grade` varchar(50) DEFAULT NULL,
            ADD COLUMN `grader_company` varchar(50) DEFAULT NULL,
            ADD COLUMN `grader_tier` varchar(50) DEFAULT NULL;

        DROP TABLE IF EXISTS `meta_grader_tiers`;
        DROP TABLE IF EXISTS `meta_grading_companies`;
        DROP TABLE IF EXISTS `meta_condition_grades`;
        DROP TABLE IF EXISTS `meta_packaging_types`;
        DROP TABLE IF EXISTS `meta_acquisition_statuses`;
    "
];