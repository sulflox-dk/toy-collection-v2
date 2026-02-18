<?php

return [
    'name'    => $_ENV['APP_NAME'] ?? 'Toy Collection',
    'version' => $_ENV['APP_VERSION'] ?? '1.0.0',
    'env'     => $_ENV['APP_ENV'] ?? 'development',
    'url'     => $_ENV['APP_URL'] ?? 'http://localhost/toy-collection-v2',

    'paths' => [
        'base' => ROOT_PATH,
        'css'  => ROOT_PATH . '/public/assets/css',
        'js'   => ROOT_PATH . '/public/assets/js',
        'media_uploads' => ROOT_PATH . '/public/uploads/media'
    ],
];