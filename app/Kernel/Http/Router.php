<?php
namespace App\Kernel\Http;

class Router
{
    private array $routes = [];

    public function get(string $uri, callable $action): void
    {
        $this->register('GET', $uri, $action);
    }

    public function post(string $uri, callable $action): void
    {
        $this->register('POST', $uri, $action);
    }

    private function register(string $method, string $uri, callable $action): void
    {
        $uri = '/' . ltrim($uri, '/');
        $this->routes[$method][] = [
            'pattern' => $this->compilePattern($uri),
            'action'  => $action,
        ];
    }

    public function dispatch(Request $request): mixed
    {
        $method = $request->getMethod();
        $uri = $request->getUri();

        foreach ($this->routes[$method] ?? [] as $route) {
            if (preg_match($route['pattern'], $uri, $matches)) {
                // Keep only named captures (route parameters)
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                return ($route['action'])($request, ...array_values($params));
            }
        }

        // 404 Not Found
        http_response_code(404);
        $safeUri = htmlspecialchars($uri, ENT_QUOTES, 'UTF-8');
        echo "<h1>404 - Not Found</h1>";
        echo "<p>The page '{$safeUri}' does not exist.</p>";
        return null;
    }

    /**
     * Convert a URI pattern like /toys/{id} into a regex.
     * Supports {param} segments which match one path segment (no slashes).
     */
    private function compilePattern(string $uri): string
    {
        // Escape literal regex characters, then replace {param} placeholders
        $escaped = preg_quote($uri, '#');
        $pattern = preg_replace('/\\\\{(\w+)\\\\}/', '(?P<$1>[^/]+)', $escaped);
        return '#^' . $pattern . '$#';
    }
}
