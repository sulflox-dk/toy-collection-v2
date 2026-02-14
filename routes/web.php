<?php

use App\Kernel\Http\Router;
use App\Modules\Meta\Controllers\UniverseController;
use App\Modules\Meta\Controllers\ManufacturerController;

/** @var Router $router */

$router->get('/',                   [ManufacturerController::class, 'index']);

// ── Meta: Universes ──────────────────────────────────────
$router->get('/universe',       [UniverseController::class, 'index']);
$router->get('/universe/list',  [UniverseController::class, 'list']);
$router->post('/universe',      [UniverseController::class, 'store']);
$router->put('/universe/{id}',  [UniverseController::class, 'update']);
$router->get('/universe/migrate-on-delete-options', [UniverseController::class, 'migrateOnDeleteOptions']);
$router->delete('/universe/{id}', [UniverseController::class, 'destroy']);

// ── Meta: Manufacturers ──────────────────────────────────────
$router->get('/manufacturer',       [ManufacturerController::class, 'index']);
$router->get('/manufacturer/list',  [ManufacturerController::class, 'list']);
$router->post('/manufacturer',      [ManufacturerController::class, 'store']);
$router->put('/manufacturer/{id}',  [ManufacturerController::class, 'update']);
$router->get('/manufacturer/migrate-on-delete-options', [ManufacturerController::class, 'migrateOnDeleteOptions']);
$router->delete('/manufacturer/{id}', [ManufacturerController::class, 'destroy']);
