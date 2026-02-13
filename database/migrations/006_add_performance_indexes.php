<?php

return [
    'up' => "
        CREATE INDEX `idx_catalog_toys_created` ON `catalog_toys` (`created_at`);
        
        -- Removed the old packaging_status index here since that column was dropped in 004!

        CREATE INDEX `idx_collection_toys_created`      ON `collection_toys` (`created_at`);
        CREATE INDEX `idx_collection_toys_acquisition`   ON `collection_toys` (`acquisition_status_id`);
        CREATE INDEX `idx_collection_toys_packaging`     ON `collection_toys` (`packaging_type_id`);
        CREATE INDEX `idx_collection_toys_deleted`       ON `collection_toys` (`deleted_at`);
        CREATE INDEX `idx_media_files_deleted` ON `media_files` (`deleted_at`);
    ",

    'down' => "
        DROP INDEX `idx_catalog_toys_created`          ON `catalog_toys`;
        
        -- Also removed the reference to 'idx_catalog_toys_status' since it wasn't created above
        
        DROP INDEX `idx_collection_toys_created`        ON `collection_toys`;
        DROP INDEX `idx_collection_toys_acquisition`    ON `collection_toys`;
        DROP INDEX `idx_collection_toys_packaging`      ON `collection_toys`;
        DROP INDEX `idx_collection_toys_deleted`        ON `collection_toys`;
        DROP INDEX `idx_media_files_deleted`            ON `media_files`;
    ",
];