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
    <title>Sign In | <?= $appName ?></title>
    <link href="<?= $baseUrl ?>assets/css/app.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            border: none;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
    </style>
</head>
<body>

<div class="card login-card">
    <div class="card-body p-5">
        <div class="text-center mb-4">
            <i class="fa-solid fa-cube fa-2x text-primary mb-2"></i>
            <h4 class="fw-bold mb-1"><?= $appName ?></h4>
            <p class="text-muted small">Sign in to your account</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2 small">
                <i class="fa-solid fa-circle-exclamation me-1"></i>
                <?= $e($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= $baseUrl ?>login">
            <?= $csrfField() ?>

            <div class="mb-3">
                <label class="form-label small fw-semibold" for="email">Email address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                    <input type="email" class="form-control" id="email" name="email"
                           placeholder="you@example.com" required autofocus>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label small fw-semibold" for="password">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password"
                           placeholder="Your password" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 fw-semibold">
                <i class="fa-solid fa-right-to-bracket me-1"></i> Sign In
            </button>
        </form>
    </div>
</div>

</body>
</html>
