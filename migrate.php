<?php
// 1. SIKKERHED: Kun adgang fra terminalen
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die('⛔ This script can only be run from the command line.');
}

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) require $file;
});

use App\Kernel\Database\Database;

echo "\n🚀 Toy Tracker V2 Migration Tool\n";
echo "================================\n";

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    echo "✅ Database connected.\n";
} catch (Exception $e) {
    die("❌ Error: " . $e->getMessage() . "\n");
}

// 2. Opret migrations-tabellen hvis den mangler
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `migrations` (
        `id` int AUTO_INCREMENT PRIMARY KEY,
        `migration` varchar(255),
        `created_at` timestamp DEFAULT CURRENT_TIMESTAMP
    )");
} catch (Exception $e) {
    die("❌ Could not create migrations table: " . $e->getMessage() . "\n");
}

// Hent allerede kørte migrations
$stmt = $pdo->query("SELECT migration FROM migrations");
$executedMigrations = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Find filer
$files = glob(__DIR__ . '/database/migrations/*.php');
sort($files);

$newMigrations = 0;

foreach ($files as $file) {
    $filename = basename($file);
    
    // 3. Tjek om den allerede er kørt
    if (in_array($filename, $executedMigrations)) {
        continue; // Skip den
    }

    echo "Running: $filename... ";
    
    try {
        $migration = require $file;
        
        // Vi fjerner transaction herfra, da DDL (Create Table) alligevel laver implicit commit i MySQL

        if (is_array($migration) && isset($migration['up'])) {
            $sql = $migration['up'];
        } elseif (is_string($migration)) {
            $sql = $migration;
        } else {
            echo "⏭️ Skipped (Invalid format)\n";
            continue;
        }

        // Kør SQL
        $pdo->exec($sql);

        // Gem at vi har kørt den
        $stmt = $pdo->prepare("INSERT INTO migrations (migration) VALUES (?)");
        $stmt->execute([$filename]);

        echo "✅ Done\n";
        $newMigrations++;
        
    } catch (Exception $e) {
        echo "❌ Failed!\n";
        echo "   Error: " . $e->getMessage() . "\n";
        exit(1);
    }
}

if ($newMigrations === 0) {
    echo "✨ Nothing to migrate. Database is up to date.\n";
} else {
    echo "\n🎉 Migration completed successfully!\n";
}