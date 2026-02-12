<?php
namespace App\Kernel\Http;

class Router
{
    private array $routes = [];

    public function get(string $uri, callable|array $action): void
    {
        $this->register('GET', $uri, $action);
    }

    public function post(string $uri, callable|array $action): void
    {
        $this->register('POST', $uri, $action);
    }

    public function put(string $uri, callable|array $action): void
    {
        $this->register('PUT', $uri, $action);
    }

    public function delete(string $uri, callable|array $action): void
    {
        $this->register('DELETE', $uri, $action);
    }

    private function register(string $method, string $uri, callable|array $action): void
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

        // Support _method override (POST with _method=PUT/DELETE)
        if ($method === 'POST' && !empty($_POST['_method'])) {
            $override = strtoupper($_POST['_method']);
            if (in_array($override, ['PUT', 'DELETE'], true)) {
                $method = $override;
            }
        }

        $uri = $request->getUri();

        foreach ($this->routes[$method] ?? [] as $route) {
            if (preg_match($route['pattern'], $uri, $matches)) {
                // CSRF check on state-changing requests
                if (in_array($method, ['POST', 'PUT', 'DELETE'], true)
                    && !Csrf::validate($request)
                ) {
                    http_response_code(403);
                    header('Content-Type: application/json');
                    echo json_encode(['error' => 'CSRF token mismatch']);
                    return null;
                }

                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                return $this->call($route['action'], $request, array_values($params));
            }
        }

        http_response_code(404);
        $safeUri = htmlspecialchars($uri, ENT_QUOTES, 'UTF-8');
        echo "<h1>404 - Not Found</h1>";
        echo "<p>The page '{$safeUri}' does not exist.</p>";
        return null;
    }

    /**
     * Call a route action — supports closures and [Controller::class, 'method'] arrays.
     */
    private function call(callable|array $action, Request $request, array $params): mixed
    {
        // [Controller::class, 'method'] — instantiate and call
        if (is_array($action)) {
            [$class, $method] = $action;
            $controller = new $class();
            return $controller->$method($request, ...$params);
        }

        return $action($request, ...$params);
    }

    /**
     * Convert a URI pattern like /toys/{id} into a regex.
     */
    private function compilePattern(string $uri): string
    {
        $escaped = preg_quote($uri, '#');
        $pattern = preg_replace('/\\\\{(\w+)\\\\}/', '(?P<$1>[^/]+)', $escaped);
        return '#^' . $pattern . '$#';
    }
}
