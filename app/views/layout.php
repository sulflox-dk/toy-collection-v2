<?php
use App\Kernel\Core\Config;

$appName = $e(Config::get('app.name', 'Toy Collection'));
$baseUrl = rtrim(Config::get('app.url', ''), '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $e($title) ?> — <?= $appName ?></title>

    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/app.css">
</head>
<body class="d-flex flex-column min-vh-100 bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?= $baseUrl ?>/">🧸 <?= $appName ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $baseUrl ?>/">Manufacturers</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">My Collection</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">About</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="flex-grow-1 py-4">
        <div class="container">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <?= $content ?>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-white border-top py-3 mt-auto text-center text-muted">
        <div class="container">
            <small>&copy; <?= date('Y') ?> <?= $appName ?>.</small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
