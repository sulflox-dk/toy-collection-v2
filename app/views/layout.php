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
    <title><?= $title ? $e($title) . ' | ' : '' ?><?= $appName ?></title>

    <link href="<?= $baseUrl ?>assets/css/app.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                <li><a class="dropdown-item" href="<?= $baseUrl ?>">Manufacturers</a></li>
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

    <div class="toast-container position-fixed top-0 end-0 p-3 mt-5" style="z-index: 2000">
        <div id="liveToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="toastBody">
                    Action successful!
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <script>const SITE_URL = "<?= $e($baseUrl) ?>";</script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script src="<?= $e($baseUrl) ?>assets/js/core/api-client.js?v=<?= time() ?>"></script>
    <script src="<?= $e($baseUrl) ?>assets/js/core/ui-helper.js?v=<?= time() ?>"></script>
    <script src="<?= $e($baseUrl) ?>assets/js/core/validation.js?v=<?= time() ?>"></script>
    
    <script src="<?= $e($baseUrl) ?>assets/js/core/entity-manager.js?v=<?= time() ?>"></script>

    <?php if (!empty($scripts) && is_array($scripts)): ?>
        <?php foreach ($scripts as $script): ?>
            <script src="<?= $e($baseUrl . $script) ?>?v=<?= time() ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
  </body>
</html>
