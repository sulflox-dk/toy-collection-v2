<?php

use App\Kernel\Http\Router;
use App\Modules\Meta\Controllers\UniverseController;
use App\Modules\Meta\Controllers\ManufacturerController;
use App\Modules\Meta\Controllers\ToyLineController;
use App\Modules\Meta\Controllers\EntertainmentSourceController;
use App\Modules\Meta\Controllers\ProductTypeController;
use App\Modules\Meta\Controllers\SubjectController;

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

// ── Meta: Toy Lines ──────────────────────────────────────
$router->get('/toy-line',       [ToyLineController::class, 'index']);
$router->get('/toy-line/list',  [ToyLineController::class, 'list']);
$router->post('/toy-line',      [ToyLineController::class, 'store']);
$router->put('/toy-line/{id}',  [ToyLineController::class, 'update']);
$router->get('/toy-line/migrate-on-delete-options', [ToyLineController::class, 'migrateOnDeleteOptions']);
$router->delete('/toy-line/{id}', [ToyLineController::class, 'destroy']);

// ── Meta: Entertainment Sources ──────────────────────────────────────
$router->get('/entertainment-source',       [EntertainmentSourceController::class, 'index']);
$router->get('/entertainment-source/list',  [EntertainmentSourceController::class, 'list']);
$router->post('/entertainment-source',      [EntertainmentSourceController::class, 'store']);
$router->put('/entertainment-source/{id}',  [EntertainmentSourceController::class, 'update']);
$router->get('/entertainment-source/migrate-on-delete-options', [EntertainmentSourceController::class, 'migrateOnDeleteOptions']);
$router->delete('/entertainment-source/{id}', [EntertainmentSourceController::class, 'destroy']);

// ── Meta: Product Types ──────────────────────────────────────
$router->get('/product-type',       [ProductTypeController::class, 'index']);
$router->get('/product-type/list',  [ProductTypeController::class, 'list']);
$router->post('/product-type',      [ProductTypeController::class, 'store']);
$router->put('/product-type/{id}',  [ProductTypeController::class, 'update']);
$router->delete('/product-type/{id}', [ProductTypeController::class, 'destroy']);
$router->get('/product-type/migrate-on-delete-options', [ProductTypeController::class, 'migrateOnDeleteOptions']);

// ── Meta: Subjects ──────────────────────────────────────
$router->get('/subject',        [SubjectController::class, 'index']);
$router->get('/subject/list',   [SubjectController::class, 'list']);
$router->post('/subject',       [SubjectController::class, 'store']);
$router->put('/subject/{id}',   [SubjectController::class, 'update']);
$router->delete('/subject/{id}',[SubjectController::class, 'destroy']);
$router->get('/subject/migrate-on-delete-options', [SubjectController::class, 'migrateOnDeleteOptions']);
