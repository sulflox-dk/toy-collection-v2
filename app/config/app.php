<?php

return [
    'name' => $_ENV['APP_NAME'] ?? 'Toy Collection',
    'env'  => $_ENV['APP_ENV'] ?? 'development',
    'url'  => $_ENV['APP_URL'] ?? 'http://localhost/toy-collection-v2',
    
    'paths' => [
        'base'  => dirname(__DIR__), 
        'css' => dirname(__DIR__) . '/public/assets/css',
        'js' => dirname(__DIR__) . '/public/assets/js'
    ]
];