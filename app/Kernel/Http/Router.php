<?php
namespace App\Kernel\Http;

class Router
{
    private array $routes = [];
    private array $guestRoutes = [];

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

    /**
     * Mark a URI as accessible without authentication.
     */
    public function guest(string $uri): void
    {
        $this->guestRoutes[] = '/' . ltrim($uri, '/');
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
        // Ensure session is started (needed for auth check and CSRF)
        Csrf::token();

        $method = $request->getMethod();

        // 1. Handle Method Override
        // We check $_POST directly here to handle the override before anything else.
        if ($method === 'POST' && !empty($_POST['_method'])) {
            $override = strtoupper($_POST['_method']);
            if (in_array($override, ['PUT', 'DELETE', 'PATCH'], true)) {
                $method = $override;
            }
        }

        // 2. Global CSRF Check 🛡️
        // We perform this check BEFORE routing. If it's a state-changing method,
        // we mandate a valid token immediately. 
        if (in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'], true)) {
            
            // Note: This relies on the Csrf::validate() fix we made earlier 
            // (checking $_POST/Headers only, ignoring the URL query string).
            if (!Csrf::validate($request)) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'CSRF token mismatch. Please refresh the page.']);
                return null; // Stop execution
            }
        }

        $uri = $request->getUri();

        // 3. Authentication Gate
        if (!$this->isGuestRoute($uri) && !\App\Kernel\Auth\Auth::check()) {
            // Store intended URL for redirect after login
            $_SESSION['intended_url'] = $uri;

            // For AJAX requests, return 401 JSON
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
                && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Unauthenticated. Please log in.']);
                return null;
            }

            http_response_code(302);
            header('Location: /login');
            exit;
        }

        // 4. Find & Execute Route
        foreach ($this->routes[$method] ?? [] as $route) {
            if (preg_match($route['pattern'], $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                return $this->call($route['action'], $request, array_values($params));
            }
        }

        // 5. 404 Not Found
        http_response_code(404);
        $safeUri = htmlspecialchars($uri, ENT_QUOTES, 'UTF-8');
        echo "<h1>404 - Not Found</h1>";
        echo "<p>The page '{$safeUri}' does not exist.</p>";
        return null;
    }

    /**
     * Check if a URI is marked as a guest (no-auth) route.
     */
    private function isGuestRoute(string $uri): bool
    {
        return in_array($uri, $this->guestRoutes, true);
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
