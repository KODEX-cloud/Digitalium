<?php
/**
 * Create Case Studies page in CMS
 */

define('SECURE_ACCESS', true);
require_once __DIR__ . '/../config/config.php';

// Autoloader
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
use App\Models\Page;
use App\Models\Section;

try {
    $pdo = Database::getConnection();
    echo "=== CREATING CASE STUDIES PAGE ===\n";

    $check = Database::fetch("SELECT id FROM pages WHERE slug = 'etudes-de-cas'");
    if (!$check) {
        $pageId = Page::createPage(
            'Études de Cas',
            'etudes-de-cas',
            'Études de Cas | Analyses & Impacts Rituels',
            'Découvrez nos études de cas détaillées montrant le contexte, les technologies et l\'impact de nos solutions.',
            'published'
        );
        
        // Update Hero fields
        Database::query("UPDATE pages SET 
            hero_title = 'Études de &lt;span class=\"hi\"&gt;Cas&lt;/span&gt;',
            hero_subtitle = 'Analyses d\'impacts de nos intégrations logicielles et automatisations IA.',
            hero_image = '/assets/images/about_3d.png',
            hero_cta1_text = 'Lancer un projet',
            hero_cta1_url = '/contact',
            hero_bg_color = 'linear-gradient(135deg, #f8fafc 0%, #f1f5f9 50%, #e2e8f0 100%)',
            hero_effect = 'particles'
            WHERE id = :id", ['id' => $pageId]);

        // Add portfolio section
        Section::addSection($pageId, 'Grille des Études de Cas', 'portfolio', 0);
        echo "Created page: Études de Cas (/etudes-de-cas)\n";
    } else {
        echo "Page Études de Cas already exists.\n";
    }

    // Clear Cache
    \App\Services\Cache::clear();
    echo "Cache cleared. Case studies page is active!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
