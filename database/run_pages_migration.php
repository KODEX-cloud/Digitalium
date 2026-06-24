<?php
/**
 * Safe database migration runner.
 * Syncs the 'pages' table columns safely without duplicate column errors.
 */

define('SECURE_ACCESS', true);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Services/Database.php';

echo "<h1>Syncing Pages Table Schema...</h1>";

try {
    $pdo = App\Services\Database::getConnection();

    $columnsToAdd = [
        'sort_order' => "INT DEFAULT 0",
        'in_navigation' => "TINYINT DEFAULT 1",
        'hero_title' => "VARCHAR(255) NULL",
        'hero_subtitle' => "TEXT NULL",
        'hero_image' => "VARCHAR(255) NULL",
        'hero_cta1_text' => "VARCHAR(100) NULL",
        'hero_cta1_url' => "VARCHAR(255) NULL",
        'hero_cta2_text' => "VARCHAR(100) NULL",
        'hero_cta2_url' => "VARCHAR(255) NULL",
        'hero_bg_color' => "VARCHAR(100) NULL",
        'hero_effect' => "VARCHAR(50) DEFAULT 'particles'",
        'hero_variant' => "VARCHAR(50) DEFAULT 'hero_split_large_image'",
        'hero_image_layout' => "VARCHAR(50) DEFAULT 'right'",
        'hero_image_size' => "VARCHAR(50) DEFAULT 'large'",
        'hero_badge' => "VARCHAR(255) NULL",
        'hero_status' => "TINYINT DEFAULT 1",
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

    echo "<ul>";
    foreach ($columnsToAdd as $col => $type) {
        $check = $pdo->query("SHOW COLUMNS FROM `pages` LIKE '$col'");
        if ($check->rowCount() === 0) {
            $pdo->exec("ALTER TABLE `pages` ADD COLUMN `$col` $type");
            echo "<li style='color:green;'>Added column: <strong>$col</strong></li>";
        } else {
            echo "<li style='color:orange;'>Column already exists: <strong>$col</strong></li>";
        }
    }
    echo "</ul>";

    echo "<h2 style='color:green;'>Synchronization completed successfully!</h2>";

} catch (Throwable $e) {
    echo "<h2 style='color:red;'>Synchronization failed!</h2>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>File: " . htmlspecialchars($e->getFile()) . " (Line: " . $e->getLine() . ")</p>";
}
