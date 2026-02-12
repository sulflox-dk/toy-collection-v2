<?php
namespace App\Kernel\View;

use App\Kernel\Core\Config;
use RuntimeException;

class Template
{
    private string $viewsPath;
    private ?string $layout = null;

    public function __construct(string $viewsPath)
    {
        $this->viewsPath = rtrim($viewsPath, '/');
    }

    /**
     * Set the layout file (relative to app/views/).
     */
    public function setLayout(string $layout): void
    {
        $this->layout = $layout;
    }

    /**
     * Render a view file with the given data.
     *
     * @param string $path  Absolute path to the view file
     * @param array  $data  Variables to extract into the view scope
     * @return string       The rendered HTML
     */
    public function render(string $path, array $data = []): string
    {
        if (!file_exists($path)) {
            throw new RuntimeException("View not found: {$path}");
        }

        // Escape helper — available as $e() in every template
        $e = static fn(string $text): string =>
            htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

        $content = $this->capture($path, array_merge($data, ['e' => $e]));

        if ($this->layout !== null) {
            $layoutPath = $this->viewsPath . '/' . $this->layout;

            if (!file_exists($layoutPath)) {
                throw new RuntimeException("Layout not found: {$layoutPath}");
            }

            $content = $this->capture($layoutPath, [
                'content' => $content,
                'e'       => $e,
                'title'   => $data['title'] ?? Config::get('app.name', 'Toy Collection'),
            ]);
        }

        return $content;
    }

    /**
     * Capture output from a PHP template file.
     */
    private function capture(string $__file, array $__data): string
    {
        extract($__data, EXTR_SKIP);
        ob_start();
        require $__file;
        return ob_get_clean();
    }
}
