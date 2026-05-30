<?php
/**
 * Database Migration & Data Seeder
 * Adds unified hero columns to pages, creates projects (Réalisations) table,
 * migrates existing hero blocks data, and seeds the 6 projects from the poster.
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
    echo "=== RUNNING SQL MIGRATIONS & SEEDER ===\n";

    // 1. Alter 'pages' table to add hero_* columns if they don't exist
    echo "Altering 'pages' table with unified hero fields...\n";
    $columnsToAdd = [
        'hero_title' => 'VARCHAR(255) NULL',
        'hero_subtitle' => 'TEXT NULL',
        'hero_image' => 'VARCHAR(255) NULL',
        'hero_cta1_text' => 'VARCHAR(100) NULL',
        'hero_cta1_url' => 'VARCHAR(255) NULL',
        'hero_cta2_text' => 'VARCHAR(100) NULL',
        'hero_cta2_url' => 'VARCHAR(255) NULL',
        'hero_bg_color' => 'VARCHAR(100) NULL',
        'hero_effect' => "VARCHAR(50) DEFAULT 'particles'"
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

    // 2. Create 'projects' table
    echo "Creating 'projects' table...\n";
    $sqlProjects = "CREATE TABLE IF NOT EXISTS `projects` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `title` VARCHAR(255) NOT NULL,
      `category` VARCHAR(100) NOT NULL,
      `logo` VARCHAR(255) NULL,
      `main_image` VARCHAR(255) NOT NULL,
      `gallery` TEXT NULL,
      `context` TEXT NULL,
      `impact` TEXT NULL,
      `technologies` VARCHAR(255) NULL,
      `external_link` VARCHAR(255) NULL,
      `sort_order` INT DEFAULT 0,
      `is_featured` TINYINT DEFAULT 0,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    $pdo->exec($sqlProjects);
    echo "'projects' table created successfully.\n";

    // 3. Migrate existing section heroes to the new page-level columns to preserve data
    echo "Migrating section hero blocks data to page-level columns...\n";
    $pages = Database::fetchAll("SELECT * FROM pages");
    foreach ($pages as $p) {
        $pageId = (int)$p['id'];
        $slug = $p['slug'];
        
        // Find if this page has a section of type 'hero', 'about_hero', 'services_hero', 'blog_hero', 'contact_hero'
        $sec = Database::fetch("SELECT id FROM sections WHERE page_id = :pid AND type IN ('hero', 'about_hero', 'services_hero', 'blog_hero', 'contact_hero') LIMIT 1", ['pid' => $pageId]);
        if ($sec) {
            $secId = (int)$sec['id'];
            // Fetch blocks for this section
            $blocksRaw = Database::fetchAll("SELECT block_key, value FROM blocks WHERE section_id = :sid AND group_id IS NULL", ['sid' => $secId]);
            $blocks = [];
            foreach ($blocksRaw as $b) {
                $blocks[$b['block_key']] = $b['value'];
            }
            
            // Map blocks to new page columns
            $heroTitle = $blocks['title'] ?? ($slug === 'home' ? 'Des solutions<br>technologiques<br><span class="hi">innovantes</span>' : $p['title']);
            $heroSub = $blocks['subtitle'] ?? '';
            $heroImg = $blocks['bg_image'] ?? '';
            $cta1Text = $blocks['cta_text'] ?? '';
            $cta1Url = $blocks['cta_url'] ?? '';
            
            // Set defaults based on slugs
            $heroBg = 'linear-gradient(135deg, #f8fafc 0%, #f1f5f9 50%, #e2e8f0 100%)';
            $heroEff = 'particles';
            if ($slug === 'home') {
                $heroImg = '/assets/images/hero_3d.png';
                $cta1Text = 'Découvrez nos services';
                $cta1Url = '#services';
            } elseif ($slug === 'service') {
                $heroImg = '/assets/images/services_3d.png';
            } elseif ($slug === 'about') {
                $heroImg = '/assets/images/about_3d.png';
            }
            
            // Update page
            Database::query("UPDATE pages SET 
                hero_title = :title,
                hero_subtitle = :sub,
                hero_image = :img,
                hero_cta1_text = :cta1_t,
                hero_cta1_url = :cta1_u,
                hero_bg_color = :bg,
                hero_effect = :eff
                WHERE id = :pid",
                [
                    'title' => $heroTitle,
                    'sub' => $heroSub,
                    'img' => $heroImg,
                    'cta1_t' => $cta1Text,
                    'cta1_u' => $cta1Url,
                    'bg' => $heroBg,
                    'eff' => $heroEff,
                    'pid' => $pageId
                ]
            );
            
            // Drop old section and its blocks to avoid duplicate display
            Database::query("DELETE FROM sections WHERE id = :sid", ['sid' => $secId]);
            echo "Migrated hero data for page: {$slug} and deleted redundant section.\n";
        } else {
            // Set basic defaults if no hero section existed
            Database::query("UPDATE pages SET 
                hero_title = :title,
                hero_subtitle = :sub,
                hero_bg_color = 'linear-gradient(135deg, #f8fafc 0%, #f1f5f9 50%, #e2e8f0 100%)',
                hero_effect = 'particles'
                WHERE id = :pid AND hero_title IS NULL",
                [
                    'title' => $p['title'],
                    'sub' => $p['meta_description'] ?? '',
                    'pid' => $pageId
                ]
            );
        }
    }

    // 4. Seed the 6 projects from the poster
    echo "Seeding the 6 premium projects from the poster...\n";
    $projects = [
        [
            'title' => 'Assalé Président',
            'category' => 'Politique',
            'logo' => '/assets/images/hero_3d.png', // Default premium brand logo
            'main_image' => '/assets/images/hero_3d.png',
            'context' => 'Plateforme de communication politique pour renforcer la visibilité et l\'engagement des électeurs.',
            'impact' => 'Canal direct de communication fluide avec les citoyens.',
            'technologies' => 'HTML, CSS, JavaScript, PHP',
            'external_link' => 'https://assale.digitaliumgroup.com',
            'sort_order' => 1,
            'is_featured' => 1
        ],
        [
            'title' => 'Mairie de Tiassalé',
            'category' => 'Institutionnel',
            'logo' => '/assets/images/about_3d.png',
            'main_image' => '/assets/images/about_3d.png',
            'context' => 'Site institutionnel pour améliorer la communication municipale et l\'accès rapide aux services administratifs.',
            'impact' => 'Accès grandement facilité aux services municipaux et démarches en ligne.',
            'technologies' => 'HTML, CSS, WordPress, MySQL',
            'external_link' => 'https://tiassale.digitaliumgroup.com',
            'sort_order' => 2,
            'is_featured' => 1
        ],
        [
            'title' => 'Cabinet Dentaire Blessing',
            'category' => 'Médical',
            'logo' => '/assets/images/services_3d.png',
            'main_image' => '/assets/images/services_3d.png',
            'context' => 'Site web professionnel haut de gamme pour promouvoir un cabinet dentaire moderne et attirer de nouveaux patients.',
            'impact' => 'Augmentation notable de la visibilité locale et des prises de rendez-vous en ligne.',
            'technologies' => 'HTML, CSS, PHP, Native JS',
            'external_link' => 'https://blessing.digitaliumgroup.com',
            'sort_order' => 3,
            'is_featured' => 1
        ],
        [
            'title' => 'ONG 2SC',
            'category' => 'Humanitaire',
            'logo' => '/assets/images/hero_3d.png',
            'main_image' => '/assets/images/hero_3d.png',
            'context' => 'Plateforme internationale de visibilité et de collecte de dons sécurisés pour une ONG panafricaine.',
            'impact' => 'Visibilité internationale accrue pour l\'organisation et transparence des dons.',
            'technologies' => 'HTML, CSS, Bootstrap, PHP',
            'external_link' => 'https://ong2sc.digitaliumgroup.com',
            'sort_order' => 4,
            'is_featured' => 1
        ],
        [
            'title' => 'Elephant Déchaîné',
            'category' => 'Média Digital',
            'logo' => '/assets/images/about_3d.png',
            'main_image' => '/assets/images/about_3d.png',
            'context' => 'Portail d\'actualités et de presse satirique digitale avec passerelle d\'abonnements payants sécurisés.',
            'impact' => 'Monétisation des articles en ligne et fidélisation des abonnés.',
            'technologies' => 'HTML, CSS, TailWind, Laravel, MySQL',
            'external_link' => 'https://elephant.digitaliumgroup.com',
            'sort_order' => 5,
            'is_featured' => 1
        ],
        [
            'title' => 'Ivoire Kita & KenCity Shop',
            'category' => 'E-Commerce',
            'logo' => '/assets/images/services_3d.png',
            'main_image' => '/assets/images/services_3d.png',
            'context' => 'Boutiques de commerce électronique performantes conçues pour promouvoir le textile Kita traditionnel.',
            'impact' => 'Expansion importante des ventes nationales et internationales.',
            'technologies' => 'HTML, CSS, WooCommerce, Stripe',
            'external_link' => 'https://kita.digitaliumgroup.com',
            'sort_order' => 6,
            'is_featured' => 1
        ]
    ];

    // Truncate projects first to avoid duplicate seeds
    $pdo->exec("TRUNCATE TABLE `projects`");
    
    $insertQuery = "INSERT INTO projects (title, category, logo, main_image, context, impact, technologies, external_link, sort_order, is_featured)
                    VALUES (:title, :cat, :logo, :img, :context, :impact, :tech, :link, :ord, :feat)";
    
    foreach ($projects as $proj) {
        $pdo->prepare($insertQuery)->execute([
            'title' => $proj['title'],
            'cat' => $proj['category'],
            'logo' => $proj['logo'],
            'img' => $proj['main_image'],
            'context' => $proj['context'],
            'impact' => $proj['impact'],
            'tech' => $proj['technologies'],
            'link' => $proj['external_link'],
            'ord' => $proj['sort_order'],
            'feat' => $proj['is_featured']
        ]);
        echo "Seeded project: {$proj['title']}\n";
    }

    // 5. Register these new media paths in media database table so they represent correctly
    // The main illustrations paths /assets/images/hero_3d.png etc are already in `media` table.

    // 6. Clear CMS Cache
    \App\Services\Cache::clear();
    echo "Cache cleared successfully.\n";
    echo "=== MIGRATION & SEEDING COMPLETED SUCCESSFULLY ===\n";

} catch (Exception $e) {
    echo "Error running migration: " . $e->getMessage() . "\n";
    exit(1);
}
