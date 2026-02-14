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
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
      <div class="container">
        <a class="navbar-brand" href="<?= $baseUrl ?>">
            <i class="fa-solid fa-jedi"></i> <?= $appName ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarMain">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item">
              <a class="nav-link" href="<?= $baseUrl ?>">Dashboard</a>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                  My Collection
              </a>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#">Collection</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#">Storage</a></li>
              </ul>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Catalog</a>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#">Master Toys</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#">Product Types</a></li>
                <li><a class="dropdown-item" href="#">Toy Lines</a></li>
                <li><a class="dropdown-item" href="<?= $baseUrl ?>/manufacturer">Manufacturers</a></li>
              </ul>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                  Data
              </a>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#">Universes</a></li>
                <li><a class="dropdown-item" href="#">Subjects</a></li>
                <li><a class="dropdown-item" href="#">Entertainment Sources</a></li>
              </ul>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                  Media
              </a>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#">Media Library</a></li>
                <li><a class="dropdown-item" href="#">Media Tags</a></li>
              </ul>
            </li>
          </ul>
          <ul class="navbar-nav ms-auto">
            <li class="nav-item">
              <a class="nav-link" href="#"><i class="fa-solid fa-gear"></i> Settings</a>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <div class="container min-vh-100">
        <?= $content ?>
    </div>

    <footer class="bg-dark text-white text-center py-3 mt-5">
        <div class="container">
            <small>&copy; <?= date('Y') ?> <?= $appName ?>. May the Force be with you.</small>
        </div>
    </footer>

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

    <?php if (!empty($scripts) && is_array($scripts)): ?>
        <?php foreach ($scripts as $script): ?>
            <script src="<?= $e($baseUrl . $script) ?>?v=<?= \App\Kernel\Core\Config::get('app.version', '1.0.0') ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
  </body>
</html>
