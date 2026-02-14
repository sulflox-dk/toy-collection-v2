<?php

// 1. Load Autoloader
require_once __DIR__ . '/../bootstrap/autoload.php';

use App\Kernel\Http\Request;
use App\Kernel\Http\Router;

// --- SECURITY: GLOBAL ERROR HANDLER 🛡️ ---
set_exception_handler(function (Throwable $e) {
    // 1. Log the full error (private)
    error_log(sprintf(
        "[%s] Uncaught Exception: %s in %s:%d\nStack Trace:\n%s",
        date('Y-m-d H:i:s'),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        $e->getTraceAsString()
    ));

    // 2. Decide what to show the user
    // Determine if we are running locally or in production
    $isDebug = (getenv('APP_ENV') === 'local');

    // Clean the output buffer so we don't show half-rendered HTML
    while (ob_get_level()) {
        ob_end_clean();
    }

    http_response_code(500);

    // If it's an AJAX request (JSON), return JSON
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode([
            'error' => $isDebug ? $e->getMessage() : 'Server Error',
            'debug' => $isDebug ? $e->getTrace() : null
        ]);
        exit;
    }

    // Otherwise, show a nice HTML page
    if ($isDebug) {
        // Developer Mode: Show the ugliness
        echo "<h1>🔥 Exception Thrown</h1>";
        echo "<p><strong>" . htmlspecialchars($e->getMessage()) . "</strong></p>";
        echo "<p>in " . $e->getFile() . ":" . $e->getLine() . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    } else {
        // Production Mode: Show a generic error
        echo "<h1>500 - Server Error</h1>";
        echo "<p>Something went wrong on our end. Please try again later.</p>";
    }
    
    exit;
});
// ------------------------------------------

// --- SECURITY: HEADERS 🛡️ ---
$csp = [
    "default-src 'self'",
    // script-src: Allow local scripts, inline scripts (needed for your EntityManager init), and the CDN
    "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net",
    // style-src: Allow local CSS, inline styles, and our CDNs
    "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://use.fontawesome.com https://fonts.googleapis.com",
    // font-src: Specifically for FontAwesome and Google Fonts
    "font-src 'self' https://use.fontawesome.com https://cdn.jsdelivr.net https://fonts.gstatic.com",
    // img-src: Allow local images and data URIs (often used for small icons)
    "img-src 'self' data: https:",
    // connect-src: This allows the browser to download the .map files and handle AJAX
    "connect-src 'self' https://cdn.jsdelivr.net",
    "frame-ancestors 'none'",
    "object-src 'none'",
];

header("Content-Security-Policy: " . implode('; ', $csp));
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("X-XSS-Protection: 1; mode=block");
// ----------------------------

// 2. Initialize Core Objects
$request = new Request();
$router = new Router();

// 3. Load Routes
require_once __DIR__ . '/../routes/web.php';

// 4. Dispatch the Request
$router->dispatch($request);