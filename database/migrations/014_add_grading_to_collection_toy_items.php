<?php

return [
    'up' => "
        -- 1. Add Columns (Grading + Acquisition Status)
        ALTER TABLE `collection_toy_items`
        ADD COLUMN `acquisition_status_id` INT DEFAULT NULL AFTER `is_repro`,
        ADD COLUMN `packaging_type_id` INT DEFAULT NULL AFTER `acquisition_status_id`,
        ADD COLUMN `condition_grade_id` INT DEFAULT NULL AFTER `packaging_type_id`,
        ADD COLUMN `grader_company_id` INT DEFAULT NULL AFTER `condition_grade_id`,
        ADD COLUMN `grader_tier_id` INT DEFAULT NULL AFTER `grader_company_id`,
        ADD COLUMN `grade_serial` VARCHAR(50) DEFAULT NULL AFTER `grader_tier_id`,
        ADD COLUMN `grade_score` VARCHAR(20) DEFAULT NULL AFTER `grade_serial`;

        -- 2. Add Indexes
        ALTER TABLE `collection_toy_items`
        ADD INDEX `idx_col_item_acq` (`acquisition_status_id`),
        ADD INDEX `idx_col_item_pack` (`packaging_type_id`),
        ADD INDEX `idx_col_item_cond` (`condition_grade_id`),
        ADD INDEX `idx_col_item_grad_comp` (`grader_company_id`),
        ADD INDEX `idx_col_item_grad_tier` (`grader_tier_id`);

        -- 3. Add Foreign Keys
        ALTER TABLE `collection_toy_items`
        ADD CONSTRAINT `fk_col_item_acq_status`
            FOREIGN KEY (`acquisition_status_id`) REFERENCES `meta_acquisition_statuses` (`id`) 
            ON DELETE RESTRICT,
        ADD CONSTRAINT `fk_col_item_pack_type`
            FOREIGN KEY (`packaging_type_id`) REFERENCES `meta_packaging_types` (`id`) 
            ON DELETE RESTRICT,
        ADD CONSTRAINT `fk_col_item_cond_grade`
            FOREIGN KEY (`condition_grade_id`) REFERENCES `meta_condition_grades` (`id`) 
            ON DELETE SET NULL,
        ADD CONSTRAINT `fk_col_item_grad_comp`
            FOREIGN KEY (`grader_company_id`) REFERENCES `meta_grading_companies` (`id`) 
            ON DELETE SET NULL,
        ADD CONSTRAINT `fk_col_item_grad_tier`
            FOREIGN KEY (`grader_tier_id`) REFERENCES `meta_grader_tiers` (`id`) 
            ON DELETE SET NULL;
    ",

    'down' => "
        ALTER TABLE `collection_toy_items`
        DROP FOREIGN KEY `fk_col_item_acq_status`,
        DROP FOREIGN KEY `fk_col_item_pack_type`,
        DROP FOREIGN KEY `fk_col_item_cond_grade`,
        DROP FOREIGN KEY `fk_col_item_grad_comp`,
        DROP FOREIGN KEY `fk_col_item_grad_tier`;

        ALTER TABLE `collection_toy_items`
        DROP COLUMN `acquisition_status_id`,
        DROP COLUMN `packaging_type_id`,
        DROP COLUMN `condition_grade_id`,
        DROP COLUMN `grader_company_id`,
        DROP COLUMN `grader_tier_id`,
        DROP COLUMN `grade_serial`,
        DROP COLUMN `grade_score`;
    ",
];