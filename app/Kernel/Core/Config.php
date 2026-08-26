<?php

namespace App\Kernel\Core;

class Config
{
    private static array $config = [];

    /**
     * Load all .php files from the config directory.
     */
    public static function load(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        $files = glob($path . '/*.php');
        foreach ($files as $file) {
            $key = basename($file, '.php');
            // We use require to get the array from the config file
            self::$config[$key] = require $file;
        }
    }

    /**
     * Get a config value using dot notation (e.g., 'app.url')
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $data = self::$config;

        foreach ($segments as $segment) {
            if (!isset($data[$segment])) {
                return $default;
            }
            $data = $data[$segment];
        }

        return $data;
    }

    /**
     * Cache-busting query value for a static asset, keyed to the file's own
     * last-modified time so a browser is forced to re-fetch it the moment
     * its content actually changes — unlike 'app.version', which is a
     * fixed fallback ('1.0.0') that nothing in this app ever bumps, so it
     * can never actually invalidate a stale cached copy.
     */
    public static function assetVersion(string $relativePath): string
    {
        $fullPath = ROOT_PATH . '/public/' . ltrim($relativePath, '/');
        $mtime = @filemtime($fullPath);
        return $mtime !== false ? (string) $mtime : self::get('app.version', '1.0.0');
    }
}