<?php

use App\Kernel\Http\Router;
use App\Modules\Auth\Controllers\LoginController;
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
use App\Modules\Media\Controllers\MediaFileController;
use App\Modules\Auth\Controllers\UserController;
use App\Modules\Media\Controllers\MediaTagController;
use \App\Modules\Catalog\Controllers\CatalogToyController;
Use \App\Modules\Collection\Controllers\CollectionStorageUnitController;


/** @var Router $router */

// ── Auth (Guest Routes) ─────────────────────────────────
$router->guest('/login');
$router->get('/login',  [LoginController::class, 'showLoginForm']);
$router->post('/login', [LoginController::class, 'login']);
$router->post('/logout', [LoginController::class, 'logout']);

// ── Admin: User Management ──────────────────────────────
$router->admin('/user');
$router->get('/user',           [UserController::class, 'index']);
$router->get('/user/list',      [UserController::class, 'list']);
$router->post('/user',          [UserController::class, 'store']);
$router->put('/user/{id}',      [UserController::class, 'update']);
$router->delete('/user/{id}',   [UserController::class, 'destroy']);

// ── Dashboard ───────────────────────────────────────────
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

$router->get('/media-file',        [MediaFileController::class, 'index']);
$router->get('/media-file/list',   [MediaFileController::class, 'list']);
$router->post('/media-file',       [MediaFileController::class, 'store']); // Handles Uploads globally
$router->put('/media-file/{id}',   [MediaFileController::class, 'update']);
$router->post('/media-file/{id}',  [MediaFileController::class, 'update']);
$router->delete('/media-file/{id}',[MediaFileController::class, 'destroy']);
$router->get('/media-file/migrate-on-delete-options', [MediaFileController::class, 'migrateOnDeleteOptions']);

$router->get('/media-file/search-json', [MediaFileController::class, 'searchJson']);
$router->post('/media-file/link',       [MediaFileController::class, 'link']);
$router->post('/media-file/unlink', [MediaFileController::class, 'unlink']);
$router->get('/media-file/thumbnails',  [MediaFileController::class, 'getThumbnails']);

$router->get('/media-tag',        [MediaTagController::class, 'index']);
$router->get('/media-tag/list',   [MediaTagController::class, 'list']);
$router->post('/media-tag',       [MediaTagController::class, 'store']);
$router->put('/media-tag/{id}',   [MediaTagController::class, 'update']);
$router->delete('/media-tag/{id}',[MediaTagController::class, 'destroy']);
$router->get('/media-tag/migrate-on-delete-options', [MediaTagController::class, 'migrateOnDeleteOptions']);

$router->get('/catalog-toy', [CatalogToyController::class, 'index']);
$router->get('/catalog-toy/list', [CatalogToyController::class, 'list']);
$router->get('/catalog-toy/create-step-1', [CatalogToyController::class, 'createStep1']);
$router->get('/catalog-toy/create-step-2', [CatalogToyController::class, 'createStep2']);
$router->post('/catalog-toy/store', [CatalogToyController::class, 'store']);
$router->get('/catalog-toy/create-step-3', [CatalogToyController::class, 'createStep3']);
$router->delete('/catalog-toy/{id}', [CatalogToyController::class, 'destroy']);

$router->get('/storage-unit', [CollectionStorageUnitController::class, 'index']);
$router->get('/storage-unit/list', [CollectionStorageUnitController::class, 'list']);
$router->post('/storage-unit', [CollectionStorageUnitController::class, 'store']);
$router->put('/storage-unit/{id}', [CollectionStorageUnitController::class, 'update']);
$router->delete('/storage-unit/{id}', [CollectionStorageUnitController::class, 'destroy']);
$router->get('/storage-unit/migrate-on-delete-options', [CollectionStorageUnitController::class, 'migrateOnDeleteOptions']);