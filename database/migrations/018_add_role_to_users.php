<?php

return [
    'up' => "
        ALTER TABLE users
            ADD COLUMN role ENUM('admin', 'user') NOT NULL DEFAULT 'user'
            AFTER email;

        -- Promote the first user (id=1) to admin
        UPDATE users SET role = 'admin' WHERE id = 1;
    ",
    'down' => "
        ALTER TABLE users DROP COLUMN role;
    "
];
