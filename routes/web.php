<?php

use App\Kernel\Http\Router;
use App\Kernel\Database\Database;

/** @var Router $router */

// 1. Home Page
$router->get('/', function() {
    echo "<h1>🚀 Welcome to Toy Tracker V2!</h1>";
    echo "<p>The router is working perfectly.</p>";
    echo "<ul>
            <li><a href='db-test'>Test Database Connection</a></li>
            <li><a href='non-existent'>Test 404 Page</a></li>
          </ul>";
});

// 2. Database Test Route
$router->get('/db-test', function() {
    try {
        $db = Database::getInstance();
        $pdo = $db->getConnection();
        echo "<h1>✅ Database Connected!</h1>";
        
        // Fetch stats
        $tables = $pdo->query("SHOW TABLES")->fetchAll();
        echo "<p>Found " . count($tables) . " tables in the database.</p>";
        
    } catch (Exception $e) {
        echo "<h1>❌ Database Error</h1>";
        echo $e->getMessage();
    }
});