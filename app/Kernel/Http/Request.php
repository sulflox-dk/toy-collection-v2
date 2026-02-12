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

        // Fjern query string (?foo=bar)
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }

        // Find scriptets sti (fx /toy-collection-v2/public/index.php)
        $scriptName = $_SERVER['SCRIPT_NAME'];
        
        // Find mappen (fx /toy-collection-v2/public)
        $dirname = dirname($scriptName);

        // Hvis vi kører i en undermappe, skal vi fjerne den fra URI'en.
        // Men vi skal være forsigtige med '/public' delen, som .htaccess skjuler.
        
        // 1. Prøv at fjerne hele stien (hvis URL indeholder /public/)
        if (strpos($uri, $dirname) === 0) {
            $uri = substr($uri, strlen($dirname));
        } 
        // 2. Prøv at fjerne stien UDEN '/public' (hvis URL er 'clean')
        elseif (strpos($dirname, '/public') !== false) {
            $parentDir = dirname($dirname); // Gå ét niveau op (fx /toy-collection-v2)
            if (strpos($uri, $parentDir) === 0) {
                $uri = substr($uri, strlen($parentDir));
            }
        }

        return $uri === '' || $uri === '/' ? '/' : '/' . ltrim($uri, '/');
    }

    public function input(string $key, $default = null)
    {
        return $_REQUEST[$key] ?? $default;
    }
}