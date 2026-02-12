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
        // Ensure URI starts with /
        $uri = '/' . ltrim($uri, '/');
        $this->routes[$method][$uri] = $action;
    }

    public function dispatch(Request $request)
    {
        $method = $request->getMethod();
        $uri = $request->getUri();

        // Check if route exists
        if (isset($this->routes[$method][$uri])) {
            $action = $this->routes[$method][$uri];
            
            // Execute the action (Closure)
            return call_user_func($action, $request);
        }

        // 404 Not Found
        http_response_code(404);
        echo "<h1>404 - Not Found</h1>";
        echo "<p>The page '$uri' does not exist.</p>";
    }
}