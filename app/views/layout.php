<?php
use App\Kernel\Core\Config;

$appName = $e(Config::get('app.name', 'Toy Collection'));
$baseUrl = rtrim(Config::get('app.url', ''), '/') . '/';
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= $e($csrfToken) ?>">
    <title><?= $title ? $e($title) . ' | ' : '' ?><?= $appName ?></title>

    <link href="<?= $baseUrl ?>assets/css/app.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer">
  </head>
  <body class="bg-light">

  <div class="app-layout">
    <nav class="sidebar d-flex flex-column flex-shrink-0 p-3 text-white bg-dark" id="sidebarMenu">
        <div class="d-flex align-items-center justify-content-between mb-0 w-100">
            <a href="<?= $baseUrl ?>" class="d-flex align-items-center text-white text-decoration-none text-truncate">
                <span class="fs-4 fw-bold"><?= $appName ?></span>
            </a>
            
            <button class="btn btn-link text-white-50 p-0 ms-2 me-3" id="btn-toggle-all" title="Expand/Collapse All">
                <i class="fa-solid fa-angles-right"></i>
            </button>
        </div>
        <hr>


        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
                <a href="<?= $baseUrl ?>" class="nav-link text-white" aria-current="page">
                    <i class="fa-solid fa-gauge-high"></i>
                    Dashboard
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link btn-toggle-nav collapsed" data-bs-toggle="collapse" href="#menu-collection" role="button" aria-expanded="false">
                    <i class="fa-solid fa-boxes-stacked"></i> My Collection
                </a>
                <div class="collapse" id="menu-collection">
                    <ul class="sidebar-submenu btn-toggle-nav-list align-items-center rounded">
                        <li><a href="#" class="nav-link">Collection</a></li>
                        <li><a href="#" class="nav-link">Storage</a></li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link btn-toggle-nav collapsed" data-bs-toggle="collapse" href="#menu-catalog" role="button" aria-expanded="false">
                    <i class="fa-solid fa-book-open"></i> Catalog
                </a>
                <div class="collapse" id="menu-catalog">
                    <ul class="sidebar-submenu btn-toggle-nav-list align-items-center rounded">
                        <li><a href="#" class="nav-link">Master Toys</a></li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link btn-toggle-nav collapsed" data-bs-toggle="collapse" href="#menu-meta" role="button" aria-expanded="false">
                    <i class="fa-solid fa-tags"></i> Meta Data
                </a>
                <div class="collapse" id="menu-meta">
                    <ul class="sidebar-submenu btn-toggle-nav-list align-items-center rounded">
                        <li><a href="<?= $baseUrl ?>manufacturer" class="nav-link">Manufacturers</a></li>
                        <li><a href="<?= $baseUrl ?>toy-line" class="nav-link">Toy Lines</a></li>
                        <li><a href="<?= $baseUrl ?>product-type" class="nav-link">Product Types</a></li>
                        <li class="py-2"><hr class="dropdown-divider bg-primary" style="height:3px;"></li>
                        <li><a href="<?= $baseUrl ?>universe" class="nav-link">Universes</a></li>                        
                        <li><a href="<?= $baseUrl ?>entertainment-source" class="nav-link">Entertainment Sources</a></li>
                        <li><a href="<?= $baseUrl ?>subject" class="nav-link">Subjects</a></li>
                        <li class="py-2"><hr class="dropdown-divider bg-primary" style="height:3px;"></li>
                        <li><a href="<?= $baseUrl ?>packaging-type" class="nav-link">Packaging Types</a></li>
                        <li><a href="<?= $baseUrl ?>acquisition-status" class="nav-link">Acquisition Statuses</a></li>
                    </ul>
                </div>
            </li>

             <li class="nav-item">
                <a class="nav-link btn-toggle-nav collapsed" data-bs-toggle="collapse" href="#menu-media" role="button" aria-expanded="false">
                    <i class="fa-solid fa-images"></i> Media
                </a>
                <div class="collapse" id="menu-media">
                    <ul class="sidebar-submenu btn-toggle-nav-list align-items-center rounded">
                        <li><a href="#" class="nav-link">Media Library</a></li>
                        <li><a href="#" class="nav-link">Media Tags</a></li>
                    </ul>
                </div>
            </li>
        </ul>
        <hr>
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-solid fa-gear me-2"></i>
                <strong>Settings</strong>
            </a>
            <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
                <li><a class="dropdown-item" href="#">Profile</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#">Sign out</a></li>
            </ul>
        </div>
    </nav>

    <div class="main-content-wrapper">
        
        <header class="navbar navbar-dark bg-dark d-xl-none sticky-top mb-4">
            <div class="container-fluid">
                <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar" aria-controls="offcanvasSidebar">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <span class="navbar-brand m-0"><?= $appName ?></span>
            </div>
        </header>

        <main class="container-fluid ms-0 py-3 px-4 flex-grow-1" style="max-width: 1280px;">
            <?= $content ?>
        </main>

        <footer class="bg-dark text-white text-center py-3 mt-auto">
            <div class="container">
                <small>&copy; <?= date('Y') ?> <?= $appName ?>. May the Force be with you.</small>
            </div>
        </footer>
    </div>
</div>
    <div class="offcanvas offcanvas-start text-bg-dark" tabindex="-1" id="offcanvasSidebar" aria-labelledby="offcanvasSidebarLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasSidebarLabel"><?= $appName ?></h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0">
            </div>
    </div>


    <div class="modal fade" id="appModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Loading...</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-5">
                <div class="spinner-border" role="status"></div>
            </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="core-confirm-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="core-confirm-title">Confirm Action</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-1" id="core-confirm-message"></p>
                    <p class="text-muted small mb-0 d-none" id="core-confirm-warning">
                        <i class="fa-solid fa-circle-info me-1"></i> 
                        If this item is linked to other records, you will be prompted to reassign them.
                    </p>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-link me-auto" data-bs-dismiss="modal" id="core-confirm-cancel-btn">Cancel</button>
                    <button type="button" class="btn btn-primary" id="core-confirm-ok-btn">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="core-migration-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-sm border-0">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa-solid fa-code-merge me-2 text-primary"></i> Migration Required
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-4">
                <p class="mb-4 text-dark" id="core-migration-message"></p>
                <div>
                    <label class="form-label text-muted small fw-bold">Reassign items to:</label>
                    <select class="form-select" id="core-migration-select">
                        </select>
                    <div class="invalid-feedback">Please select a destination.</div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-link me-auto" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="core-migration-btn">Migrate and Delete</button>
            </div>
        </div>
    </div>
</div>

    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3 mt-5" style="z-index: 2000"></div>

    <script>const SITE_URL = "<?= $e($baseUrl) ?>";</script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>

    <script src="<?= $e($baseUrl) ?>assets/js/core/api-client.js?v=<?= \App\Kernel\Core\Config::get('app.version', '1.0.0') ?>"></script>
    <script src="<?= $e($baseUrl) ?>assets/js/core/ui-helper.js?v=<?= \App\Kernel\Core\Config::get('app.version', '1.0.0') ?>"></script>
    <script src="<?= $e($baseUrl) ?>assets/js/core/validation.js?v=<?= \App\Kernel\Core\Config::get('app.version', '1.0.0') ?>"></script>
    <script src="<?= $e($baseUrl) ?>assets/js/core/entity-manager.js?v=<?= \App\Kernel\Core\Config::get('app.version', '1.0.0') ?>"></script>
    <script src="<?= $e($baseUrl) ?>assets/js/core/layout.js?v=<?= \App\Kernel\Core\Config::get('app.version', '1.0.0') ?>"></script>

    <?php if (!empty($scripts) && is_array($scripts)): ?>
        <?php foreach ($scripts as $script): ?>
            <script src="<?= $e($baseUrl . $script) ?>?v=<?= \App\Kernel\Core\Config::get('app.version', '1.0.0') ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
  </body>
</html>
