<?php

return [
    'up' => "
        -- catalog_toys: sorting and filtering
        CREATE INDEX `idx_catalog_toys_created` ON `catalog_toys` (`created_at`);
        CREATE INDEX `idx_catalog_toys_status`  ON `catalog_toys` (`status`);

        -- collection_toys: sorting and filtering
        CREATE INDEX `idx_collection_toys_created`      ON `collection_toys` (`created_at`);
        CREATE INDEX `idx_collection_toys_acquisition`   ON `collection_toys` (`acquisition_status_id`);
        CREATE INDEX `idx_collection_toys_packaging`     ON `collection_toys` (`packaging_type_id`);
        CREATE INDEX `idx_collection_toys_deleted`       ON `collection_toys` (`deleted_at`);

        -- media_files: soft-delete filtering
        CREATE INDEX `idx_media_files_deleted` ON `media_files` (`deleted_at`);
    ",

    'down' => "
        DROP INDEX `idx_catalog_toys_created`          ON `catalog_toys`;
        DROP INDEX `idx_catalog_toys_status`            ON `catalog_toys`;
        DROP INDEX `idx_collection_toys_created`        ON `collection_toys`;
        DROP INDEX `idx_collection_toys_acquisition`    ON `collection_toys`;
        DROP INDEX `idx_collection_toys_packaging`      ON `collection_toys`;
        DROP INDEX `idx_collection_toys_deleted`        ON `collection_toys`;
        DROP INDEX `idx_media_files_deleted`            ON `media_files`;
    ",
];
