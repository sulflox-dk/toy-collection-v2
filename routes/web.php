<?php

use App\Kernel\Http\Router;
use App\Modules\Meta\Controllers\UniverseController;
use App\Modules\Meta\Controllers\ManufacturerController;
use App\Modules\Meta\Controllers\ToyLineController;
use App\Modules\Meta\Controllers\EntertainmentSourceController;
use App\Modules\Meta\Controllers\ProductTypeController;
use App\Modules\Meta\Controllers\SubjectController;
use App\Modules\Meta\Controllers\AcquisitionStatusController;
use App\Modules\Meta\Controllers\PackagingTypeController;
use App\Modules\Meta\Controllers\ConditionGradeController;
use App\Modules\Meta\Controllers\GraderTierController;
use App\Modules\Meta\Controllers\GradingCompanyController;

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

// ── Meta: Acquisition Status ──────────────────────────────────────
$router->get('/acquisition-status',        [AcquisitionStatusController::class, 'index']);
$router->get('/acquisition-status/list',   [AcquisitionStatusController::class, 'list']);
$router->post('/acquisition-status',       [AcquisitionStatusController::class, 'store']);
$router->put('/acquisition-status/{id}',   [AcquisitionStatusController::class, 'update']);
$router->delete('/acquisition-status/{id}',[AcquisitionStatusController::class, 'destroy']);
$router->get('/acquisition-status/migrate-on-delete-options', [AcquisitionStatusController::class, 'migrateOnDeleteOptions']);

// ── Meta: Packaging type ──────────────────────────────────────
$router->get('/packaging-type',        [PackagingTypeController::class, 'index']);
$router->get('/packaging-type/list',   [PackagingTypeController::class, 'list']);
$router->post('/packaging-type',       [PackagingTypeController::class, 'store']);
$router->put('/packaging-type/{id}',   [PackagingTypeController::class, 'update']);
$router->delete('/packaging-type/{id}',[PackagingTypeController::class, 'destroy']);
$router->get('/packaging-type/migrate-on-delete-options', [PackagingTypeController::class, 'migrateOnDeleteOptions']);

$router->get('/condition-grade',        [ConditionGradeController::class, 'index']);
$router->get('/condition-grade/list',   [ConditionGradeController::class, 'list']);
$router->post('/condition-grade',       [ConditionGradeController::class, 'store']);
$router->put('/condition-grade/{id}',   [ConditionGradeController::class, 'update']);
$router->delete('/condition-grade/{id}',[ConditionGradeController::class, 'destroy']);
$router->get('/condition-grade/migrate-on-delete-options', [ConditionGradeController::class, 'migrateOnDeleteOptions']);

$router->get('/grader-tier',        [GraderTierController::class, 'index']);
$router->get('/grader-tier/list',   [GraderTierController::class, 'list']);
$router->post('/grader-tier',       [GraderTierController::class, 'store']);
$router->put('/grader-tier/{id}',   [GraderTierController::class, 'update']);
$router->delete('/grader-tier/{id}',[GraderTierController::class, 'destroy']);
$router->get('/grader-tier/migrate-on-delete-options', [GraderTierController::class, 'migrateOnDeleteOptions']);

$router->get('/grading-company',        [GradingCompanyController::class, 'index']);
$router->get('/grading-company/list',   [GradingCompanyController::class, 'list']);
$router->post('/grading-company',       [GradingCompanyController::class, 'store']);
$router->put('/grading-company/{id}',   [GradingCompanyController::class, 'update']);
$router->delete('/grading-company/{id}',[GradingCompanyController::class, 'destroy']);
$router->get('/grading-company/migrate-on-delete-options', [GradingCompanyController::class, 'migrateOnDeleteOptions']);