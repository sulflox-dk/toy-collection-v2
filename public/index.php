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
    $isDebug = (getenv('APP_ENV') === 'local');

    while (ob_get_level()) {
        ob_end_clean();
    }

    http_response_code(500);

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode([
            'error' => $isDebug ? $e->getMessage() : 'Internal Server Error'
        ]);
    } else {
        echo "<h1>500 Internal Server Error</h1>";
        if ($isDebug) {
            echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        } else {
            echo "<p>Something went wrong. Please try again later.</p>";
        }
    }
    
    exit;
});
// ------------------------------------------

// --- SECURITY: HEADERS 🛡️ ---
$csp = [
    "default-src 'self'",
    // script-src: Added cdnjs just in case you load JS from there too
    "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
    
    // style-src: ADDED https://cdnjs.cloudflare.com
    "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://use.fontawesome.com https://fonts.googleapis.com https://cdnjs.cloudflare.com",
    
    // font-src: ADDED https://cdnjs.cloudflare.com
    "font-src 'self' https://use.fontawesome.com https://cdn.jsdelivr.net https://fonts.gstatic.com https://cdnjs.cloudflare.com",
    
    "img-src 'self' data: https:",
    "connect-src 'self' https://cdn.jsdelivr.net",
    "frame-ancestors 'self'",
    "object-src 'none'",
];

header("Content-Security-Policy: " . implode('; ', $csp));
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("X-XSS-Protection: 1; mode=block");

// ------------------------------------------

// 2. Dispatch Request
$request = new Request();
$router = new Router();

// Load Routes
require_once ROOT_PATH . '/routes/web.php';

// Dispatch
$router->dispatch($request);