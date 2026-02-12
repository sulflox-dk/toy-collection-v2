<?php

use App\Kernel\Http\Router;
use App\Modules\Meta\Controllers\ManufacturerController;

/** @var Router $router */

// ── Meta: Manufacturers ──────────────────────────────────────
$router->get('/',                   [ManufacturerController::class, 'index']);
$router->get('/manufacturer/list',  [ManufacturerController::class, 'list']);
$router->get('/manufacturer/{id}',  [ManufacturerController::class, 'show']);
$router->post('/manufacturer',      [ManufacturerController::class, 'store']);
$router->put('/manufacturer/{id}',  [ManufacturerController::class, 'store']);
$router->delete('/manufacturer/{id}', [ManufacturerController::class, 'destroy']);
