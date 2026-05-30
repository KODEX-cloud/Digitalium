<?php
/**
 * Migration: Add hero_variant column to pages table.
 */

define('SECURE_ACCESS', true);
require_once __DIR__ . '/../config/config.php';

// PSR-4 Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = dirname(__DIR__) . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

use App\Services\Database;

try {
    $pdo = Database::getConnection();
    echo "=== RUNNING SQL MIGRATION (HERO VARIANT) ===\n";

    $col = 'hero_variant';
    $check = $pdo->query("SHOW COLUMNS FROM `pages` LIKE '$col'");
    if ($check->rowCount() === 0) {
        $pdo->exec("ALTER TABLE `pages` ADD COLUMN `$col` VARCHAR(50) DEFAULT 'hero_split_large_image'");
        echo "Added column: $col\n";
    } else {
        echo "Column $col already exists.\n";
    }

    echo "Migration completed successfully.\n";
} catch (\Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
