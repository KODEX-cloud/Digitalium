<?php
/**
 * Database update script for Dribbble Redesign
 * Overrides seeded blocks to bind our newly generated abstract 3D images.
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
use App\Models\Block;
use App\Models\Section;

try {
    $pdo = Database::getConnection();
    echo "=== SEEDING ASSETS ET IMAGES GENERES ===\n";

    // 1. Seed Hero Background Image
    $heroSec = Database::fetch("SELECT id FROM sections WHERE type = 'hero' LIMIT 1");
    if ($heroSec) {
        $secId = (int)$heroSec['id'];
        Block::setVal($secId, 'bg_image', 'image', '/assets/images/hero_3d.png');
        echo "Hero image (/assets/images/hero_3d.png) enregistrée en base de données.\n";
    }

    // 2. Register seeded files in the `media` table so they display in the Media Library
    $imagesToRegister = [
        'hero_3d.png' => '/assets/images/hero_3d.png',
        'services_3d.png' => '/assets/images/services_3d.png',
        'about_3d.png' => '/assets/images/about_3d.png'
    ];

    foreach ($imagesToRegister as $name => $path) {
        $fullPath = PUBLIC_PATH . $path;
        if (file_exists($fullPath)) {
            // Check if already registered
            $exists = Database::fetch("SELECT id FROM media WHERE filepath = :path LIMIT 1", ['path' => $path]);
            if (!$exists) {
                Database::insert(
                    "INSERT INTO media (filename, filepath, original_name, file_size, mime_type) 
                     VALUES (:filename, :filepath, :original_name, :file_size, :mime_type)",
                    [
                        'filename' => $name,
                        'filepath' => $path,
                        'original_name' => $name,
                        'file_size' => filesize($fullPath),
                        'mime_type' => 'image/png'
                    ]
                );
                echo "Image '{$name}' enregistrée dans la bibliothèque média (base de données).\n";
            }
        }
    }

    // 3. Clear cache to reflect updates instantly
    \App\Services\Cache::clear();
    echo "Cache vidé.\n";
    echo "=== ASSETS SYNCHRONISÉS ET ENREGISTRÉS ! ===\n";

} catch (Exception $e) {
    echo "Erreur lors de la synchronisation des images : " . $e->getMessage() . "\n";
    exit(1);
}
