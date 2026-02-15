<?php
namespace App\Kernel\Http;

use App\Kernel\View\Template;
use ReflectionClass;

abstract class Controller
{
    private Template $template;

    public function __construct()
    {
        $this->template = new Template(ROOT_PATH . '/app/views');
        $this->template->setLayout('layout.php');
    }

    /**
     * Render a view from the calling module's Views/ directory.
     *
     * Usage inside a module controller:
     *   $this->render('manufacturer_index', ['items' => $items]);
     *
     * Resolves to: app/Modules/<Module>/Views/manufacturer_index.php
     */
    protected function render(string $view, array $data = []): void
    {
        $path = $this->resolveViewPath($view);
        echo $this->template->render($path, $data);
    }

    /**
     * Render a view without the layout wrapper.
     */
    protected function renderPartial(string $view, array $data = []): void
    {
        $prevLayout = $this->template;

        $template = new Template(ROOT_PATH . '/app/views');
        $path = $this->resolveViewPath($view);
        echo $template->render($path, $data);
    }

    /**
     * Send a JSON response.
     */
    protected function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_THROW_ON_ERROR);
    }

    /**
     * Redirect to another URL.
     * Includes security checks to prevent Open Redirect vulnerabilities.
     */
    protected function redirect(string $url, int $status = 302): void
    {
        // 1. Prevent Header Injection (CRLF)
        // Remove any newline characters that could split the header
        $url = str_replace(["\r", "\n", "%0d", "%0a"], '', $url);

        // 2. Validate Internal Redirect
        // Parse the URL to check its components
        $parsed = parse_url($url);
        
        // If parsing fails, default to root for safety
        if ($parsed === false) {
            $url = '/';
        } else {
            // Check if the URL has a host (domain name)
            $host = $parsed['host'] ?? null;
            
            // If there is a host, it MUST match our current server's host
            // (This prevents http://evil.com)
            if ($host && $host !== ($_SERVER['HTTP_HOST'] ?? 'localhost')) {
                // External domain detected! Force redirect to root.
                $url = '/';
            }
            
            // Check for Protocol-Relative URLs (e.g. //google.com)
            // These have no scheme but have a host, which creates a vulnerability 
            // if not caught by the host check above.
            // Just to be extra safe, we ensure relative paths don't start with //
            if (!$host && substr($url, 0, 2) === '//') {
                $url = '/';
            }
        }

        http_response_code($status);
        header("Location: {$url}");
        exit; // IMPORTANT: Always exit after a redirect header to stop script execution
    }

    /**
     * Abort with an HTTP status code.
     */
    protected function abort(int $code = 404, string $message = 'Not Found'): void
    {
        http_response_code($code);
        echo "<h1>{$code} — " . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . "</h1>";
    }

    /**
     * Resolve a view name to its absolute file path.
     *
     * Uses the controller's own directory to find the sibling Views/ folder.
     * e.g. app/Modules/Meta/Controllers/ManufacturerController.php
     *   → app/Modules/Meta/Views/manufacturer_index.php
     * 
     * 1. Checks module-specific path (e.g. app/Modules/Meta/Views/toy_line_index.php)
     * 2. Checks global path (e.g. app/Views/common/index_header.php)
     */
    private function resolveViewPath(string $view): string
    {
        // 1. Try Module-Specific View (Current Behavior)
        $reflector = new ReflectionClass(static::class);
        $controllerDir = dirname($reflector->getFileName());
        
        // Go up one level from Controllers to find Views
        // e.g. app/Modules/Meta/Controllers/../Views/
        $moduleViewPath = $controllerDir . '/../Views/' . $view . '.php';
        
        if (file_exists($moduleViewPath)) {
            return $moduleViewPath;
        }

        // 2. Try Global View (New Fallback)
        // This handles "common/index_header" -> app/Views/common/index_header.php
        $globalViewPath = ROOT_PATH . '/app/Views/' . $view . '.php';

        if (file_exists($globalViewPath)) {
            return $globalViewPath;
        }

        // 3. Error
        throw new \RuntimeException("View file not found: {$view}. Checked:\n - {$moduleViewPath}\n - {$globalViewPath}");
    }

}
