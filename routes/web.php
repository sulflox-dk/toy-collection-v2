<?php

use App\Kernel\Http\Router;
use App\Kernel\Http\Request;
use App\Modules\Meta\Models\Manufacturer;

/** @var Router $router */

$e = fn(string $text) => htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

// 1. Home Page
$router->get('/', function(Request $request) use ($e) {
    echo "<h1>🏭 Manufacturers</h1>";

    if (empty(Manufacturer::all())) {
        Manufacturer::create(['name' => 'Kenner', 'slug' => 'kenner']);
        Manufacturer::create(['name' => 'Hasbro', 'slug' => 'hasbro']);
        echo "<p><em>Added default data... refresh!</em></p>";
    }

    echo "<ul>";
    foreach (Manufacturer::all() as $m) {
        $name = $e($m['name']);
        echo "<li><a href='manufacturer/{$m['id']}'>{$name}</a></li>";
    }
    echo "</ul>";
});

// 2. Manufacturer Detail
$router->get('/manufacturer/{id}', function(Request $request, $id) use ($e) {
    $m = Manufacturer::find((int)$id);

    if (!$m) {
        echo "<h1>Not found</h1>";
        return;
    }

    $name = $e($m['name']);
    $slug = $e($m['slug']);
    echo "<h1>{$name}</h1>";
    echo "<p>Slug: {$slug}</p>";
    echo "<p><a href='../'>&larr; Back</a></p>";
});
