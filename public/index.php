<?php

// 1. Load Autoloader
require_once __DIR__ . '/../bootstrap/autoload.php';

use App\Kernel\Http\Request;
use App\Kernel\Http\Router;

// 2. Initialize Core Objects
$request = new Request();
$router = new Router();

// 3. Load Routes
require_once __DIR__ . '/../routes/web.php';

// 4. Dispatch the Request
$router->dispatch($request);