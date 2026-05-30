<?php
/**
 * Migration: Add advanced Header & Hero layout columns to pages table.
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
    echo "=== RUNNING SQL MIGRATION (ADVANCED HEADER & HERO BUILDER) ===\n";

    $columnsToAdd = [
        'header_bg_mode' => "VARCHAR(50) DEFAULT 'glass'",
        'header_opacity' => "FLOAT DEFAULT 0.65",
        'header_blur' => "INT DEFAULT 20",
        'header_shadow' => "VARCHAR(50) DEFAULT 'moyen'",
        'header_contrast_mode' => "VARCHAR(50) DEFAULT 'default'",
        'logo_light' => "VARCHAR(255) NULL",
        'logo_dark' => "VARCHAR(255) NULL",
        'logo_size' => "INT DEFAULT 38",
        'hero_layout_mode' => "VARCHAR(50) DEFAULT 'moyen'",
        'hero_text_position' => "VARCHAR(50) DEFAULT 'centre'",
        'hero_text_alignment' => "VARCHAR(50) DEFAULT 'center'",
        'hero_text_width' => "VARCHAR(50) DEFAULT '100%'",
        'hero_overlay_opacity' => "FLOAT DEFAULT 0.45",
        'hero_shadow_strength' => "VARCHAR(50) DEFAULT 'moyen'",
        'hero_image_mobile' => "VARCHAR(255) NULL",
        'responsive_settings' => "TEXT NULL"
    ];

    foreach ($columnsToAdd as $col => $type) {
        $check = $pdo->query("SHOW COLUMNS FROM `pages` LIKE '$col'");
        if ($check->rowCount() === 0) {
            $pdo->exec("ALTER TABLE `pages` ADD COLUMN `$col` $type");
            echo "Added column: $col\n";
        } else {
            echo "Column $col already exists.\n";
        }
    }

    echo "Migration completed successfully.\n";
} catch (\Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
