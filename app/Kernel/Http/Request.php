<?php
namespace App\Kernel\Http;

class Request
{
    public function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function getUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'];

        // Strip query string
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }

        // Strip the base path so the app works in subdirectories.
        // SCRIPT_NAME is e.g. /toy-collection-v2/public/index.php
        $basePath = dirname($_SERVER['SCRIPT_NAME']);

        // When .htaccess hides /public, the real URL won't contain it,
        // so fall back to the parent directory.
        if (str_ends_with($basePath, '/public') && !str_starts_with($uri, $basePath)) {
            $basePath = dirname($basePath);
        }

        if ($basePath !== '/' && str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath));
        }

        return $uri === '' || $uri === false ? '/' : '/' . ltrim($uri, '/');
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    /**
     * Retrieve input from $_POST only.
     * Essential for sensitive data like passwords and CSRF tokens.
     */
    public function post(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    /**
     * Retrieve input from $_GET only.
     */
    public function query(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    /**
     * Get the base path the app is served from (e.g. "/toy-collection-v2").
     * Returns "/" if the app is at the document root.
     */
    public static function basePath(): string
    {
        $basePath = dirname($_SERVER['SCRIPT_NAME']);

        if (str_ends_with($basePath, '/public')) {
            $basePath = dirname($basePath);
        }

        return rtrim($basePath, '/');
    }

    /**
     * Build a full URL path including the base path.
     * e.g. url('/login') => '/toy-collection-v2/login'
     */
    public static function url(string $path = '/'): string
    {
        return static::basePath() . '/' . ltrim($path, '/');
    }
}
