<?php
/**
 * Create Portfolio and Réalisations pages in CMS
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
use App\Models\Page;
use App\Models\Section;

try {
    $pdo = Database::getConnection();
    echo "=== CREATING PORTFOLIO & REALISATIONS PAGES ===\n";

    // 1. Create 'realisations' page
    $checkReal = Database::fetch("SELECT id FROM pages WHERE slug = 'realisations'");
    if (!$checkReal) {
        $pageId = Page::createPage(
            'Réalisations',
            'realisations',
            'Réalisations | Nos projets de transformation digitale',
            'Découvrez mes projets de transformation digitale et solutions de pointe pour des clients variés.',
            'published'
        );
        
        // Update Hero fields
        Database::query("UPDATE pages SET 
            hero_title = 'Nos Réalisations &lt;span class=\"hi\"&gt;Digitales&lt;/span&gt;',
            hero_subtitle = 'Découvrez mes projets de transformation digitale pour des clients variés.',
            hero_image = '/assets/images/services_3d.png',
            hero_cta1_text = 'Parler à un expert',
            hero_cta1_url = '/contact',
            hero_bg_color = 'linear-gradient(135deg, #f8fafc 0%, #f1f5f9 50%, #e2e8f0 100%)',
            hero_effect = 'particles'
            WHERE id = :id", ['id' => $pageId]);

        // Add portfolio section
        Section::addSection($pageId, 'Portfolio de Réalisations', 'portfolio', 0);
        echo "Created page: Réalisations (/realisations)\n";
    } else {
        echo "Page Réalisations already exists.\n";
    }

    // 2. Create 'portfolio' page
    $checkPort = Database::fetch("SELECT id FROM pages WHERE slug = 'portfolio'");
    if (!$checkPort) {
        $pageId = Page::createPage(
            'Portfolio',
            'portfolio',
            'Portfolio | Notre savoir-faire technologique',
            'Un aperçu complet de notre savoir-faire technologique, ingénierie logicielle et impact digital.',
            'published'
        );
        
        // Update Hero fields
        Database::query("UPDATE pages SET 
            hero_title = 'Notre Portfolio &lt;span class=\"hi\"&gt;Premium&lt;/span&gt;',
            hero_subtitle = 'Un aperçu complet de notre savoir-faire technologique, d\'ingénierie logicielle et d\'impact digital.',
            hero_image = '/assets/images/hero_3d.png',
            hero_cta1_text = 'Lancer un projet',
            hero_cta1_url = '/contact',
            hero_bg_color = 'linear-gradient(135deg, #f8fafc 0%, #f1f5f9 50%, #e2e8f0 100%)',
            hero_effect = 'particles'
            WHERE id = :id", ['id' => $pageId]);

        // Add portfolio section
        Section::addSection($pageId, 'Grille Portfolio', 'portfolio', 0);
        echo "Created page: Portfolio (/portfolio)\n";
    } else {
        echo "Page Portfolio already exists.\n";
    }

    // Clear Cache
    \App\Services\Cache::clear();
    echo "Cache cleared. All pages are now fully active!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
