<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $e($title) ?></title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #1a1a1a;
            background: #f5f5f5;
        }
        .container { max-width: 960px; margin: 0 auto; padding: 2rem 1rem; }
        h1 { margin-bottom: 1rem; }
        a { color: #2563eb; text-decoration: none; }
        a:hover { text-decoration: underline; }
        ul { list-style: none; }
        ul li { padding: 0.4rem 0; }
        .back { display: inline-block; margin-bottom: 1rem; }
        .badge {
            display: inline-block;
            background: #e5e7eb;
            color: #374151;
            font-size: 0.8rem;
            padding: 0.15rem 0.5rem;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <?= $content ?>
    </div>
</body>
</html>
