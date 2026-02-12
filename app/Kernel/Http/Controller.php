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
     */
    protected function redirect(string $url, int $status = 302): void
    {
        http_response_code($status);
        header("Location: {$url}");
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
     */
    private function resolveViewPath(string $view): string
    {
        $reflector = new ReflectionClass(static::class);
        $controllerDir = dirname($reflector->getFileName());
        $moduleDir = dirname($controllerDir); // up from Controllers/

        return $moduleDir . '/Views/' . $view . '.php';
    }
}
