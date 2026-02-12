<?php
namespace App\Kernel\View;


use App\Kernel\Core\Config;
use App\Kernel\Http\Csrf;

use RuntimeException;

class Template
{
    private string $viewsPath;
    private ?string $layout = null;
    
    // NEW: Tracks the file currently being rendered so partials find their siblings
    private ?string $currentViewFile = null;

    public function __construct(string $viewsPath)
    {
        $this->viewsPath = rtrim($viewsPath, '/');
    }

    public function setLayout(string $layout): void
    {
        $this->layout = $layout;
    }

    /**
     * Render a full page view (with layout support)
     */
    public function render(string $path, array $data = []): string
    {
        if (!file_exists($path)) {
            throw new RuntimeException("View not found: {$path}");
        }


        // Escape helper — available as $e() in every template
        $e = static fn(string $text): string =>
            htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

        // CSRF helpers — available as $csrfToken and $csrfField() in every template
        $csrfToken = Csrf::token();
        $csrfField = static fn(): string => Csrf::field();

        $content = $this->capture($path, array_merge($data, [
            'e'         => $e,
            'csrfToken' => $csrfToken,
            'csrfField' => $csrfField,
        ]));


        // 2. Wrap in layout if exists
        if ($this->layout !== null) {
            $layoutPath = $this->viewsPath . '/' . $this->layout;

            if (!file_exists($layoutPath)) {
                throw new RuntimeException("Layout not found: {$layoutPath}");
            }


            $content = $this->capture($layoutPath, [
                'content'   => $content,
                'e'         => $e,
                'csrfToken' => $csrfToken,
                'csrfField' => $csrfField,
                'title'     => $data['title'] ?? '',
                'scripts'   => $data['scripts'] ?? [],
            ]);

        }

        return $content;
    }

    /**
     * NEW: Render a sub-view (partial) without layout.
     * Looks in the same directory as the current view first.
     */
    public function renderPartial(string $viewName, array $data = []): string
    {
        // 1. Try finding it relative to the current file (e.g. manufacturer_row next to manufacturer_list)
        if ($this->currentViewFile) {
            $currentDir = dirname($this->currentViewFile);
            $localPath = $currentDir . '/' . ltrim($viewName, '/') . '.php';
            
            if (file_exists($localPath)) {
                return $this->capture($localPath, $data);
            }
        }

        // 2. Fallback: absolute path or check root views (optional, strict for now)
        throw new RuntimeException("Partial view not found: {$viewName} (looked in " . dirname($this->currentViewFile ?? '') . ")");
    }

    /**
     * Capture output from a PHP template file.
     * Handles variable extraction and Context Switching.
     */
    private function capture(string $__file, array $__data): string
    {
        // Save previous context (for nested partials)
        $previousViewFile = $this->currentViewFile;
        $this->currentViewFile = $__file;

        // Ensure the escape helper $e() is always available
        if (!isset($__data['e'])) {
            $__data['e'] = static fn(string $text): string => htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
        }

        extract($__data, EXTR_SKIP);
        ob_start();

        try {
            require $__file;
        } catch (\Throwable $e) {
            // Clean buffer if error occurs so we don't output half-broken HTML
            ob_end_clean();
            throw $e;
        } finally {
            // Restore context (important!)
            $this->currentViewFile = $previousViewFile;
        }

        return ob_get_clean();
    }
}