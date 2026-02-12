<?php

/**
 * Bootstrap file — defines root path and PSR-4 autoloader.
 * Include this once from any entry point (index.php, migrate.php, CLI tools, etc.)
 */

define('ROOT_PATH', dirname(__DIR__));

// PSR-4 autoloader for the App\ namespace
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    $baseDir = ROOT_PATH . '/app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});
