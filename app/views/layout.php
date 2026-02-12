<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toy Collection V2</title>
    
    <link rel="stylesheet" href="/toy-collection-v2/assets/css/app.css">
</head>
<body class="d-flex flex-column min-vh-100 bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/">🧸 ToyCollection</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/">Manufacturers</a>
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
                    <?php echo $content; ?>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-white border-top py-3 mt-auto text-center text-muted">
        <div class="container">
            <small>&copy; <?php echo date('Y'); ?> Toy Collection V2.</small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>