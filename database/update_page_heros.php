<?php
/**
 * Database Seeder: Configure premium unique Hero styles for all public pages.
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
use App\Services\Cache;

$pagesConfig = [
    'home' => [
        'hero_variant' => 'hero_ambient_glow',
        'hero_badge' => 'Innovation IA & Performance',
        'hero_overlay_opacity' => 0.0,
        'hero_shadow_strength' => 'none',
        'hero_layout_mode' => 'grand',
    ],
    'about' => [
        'hero_variant' => 'hero_split_asymmetric',
        'hero_badge' => 'Qui Sommes-Nous',
        'hero_overlay_opacity' => 0.0,
        'hero_shadow_strength' => 'leger',
        'hero_layout_mode' => 'moyen',
    ],
    'service' => [
        'hero_variant' => 'hero_grid_features',
        'hero_badge' => 'Nos Domaines d\'Expertise',
        'hero_overlay_opacity' => 0.0,
        'hero_shadow_strength' => 'leger',
        'hero_layout_mode' => 'grand',
    ],
    'blog' => [
        'hero_variant' => 'hero_text_only',
        'hero_badge' => 'Mag Tech & Tendances',
        'hero_overlay_opacity' => 0.0,
        'hero_shadow_strength' => 'none',
        'hero_layout_mode' => 'compact',
    ],
    'contact' => [
        'hero_variant' => 'hero_full_image',
        'hero_badge' => 'Discutons de vos projets',
        'hero_overlay_opacity' => 0.5,
        'hero_shadow_strength' => 'moyen',
        'hero_layout_mode' => 'plein',
    ],
    'realisations' => [
        'hero_variant' => 'hero_floating_card',
        'hero_badge' => 'Portfolio & Études de Cas',
        'hero_overlay_opacity' => 0.0,
        'hero_shadow_strength' => 'leger',
        'hero_layout_mode' => 'moyen',
    ]
];

try {
    foreach ($pagesConfig as $slug => $config) {
        $sql = "UPDATE pages SET 
                hero_variant = :hero_variant,
                hero_badge = :hero_badge,
                hero_overlay_opacity = :hero_overlay_opacity,
                hero_shadow_strength = :hero_shadow_strength,
                hero_layout_mode = :hero_layout_mode
                WHERE slug = :slug";
        
        $params = array_merge($config, ['slug' => $slug]);
        Database::query($sql, $params);
        echo "Successfully updated Hero config for page: '{$slug}'\n";
    }
    
    Cache::clear();
    echo "CMS Cache cleared successfully.\n";
    echo "Database upgrade successfully completed!\n";
} catch (Exception $e) {
    echo "Error running database upgrade seeder: " . $e->getMessage() . "\n";
}
