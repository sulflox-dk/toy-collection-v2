<?php

return [
    'name' => $_ENV['APP_NAME'] ?? 'Toy Collection',
    'env'  => $_ENV['APP_ENV'] ?? 'development',
    'url'  => $_ENV['APP_URL'] ?? 'http://localhost/toy-collection-v2',

    'paths' => [
        'base' => ROOT_PATH,
        'css'  => ROOT_PATH . '/public/assets/css',
        'js'   => ROOT_PATH . '/public/assets/js',
    ],
];