<?php
/**
 * Database Migration - Alter pages and settings for full customizability
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
use App\Models\Setting;
use App\Models\Block;

try {
    $pdo = Database::getConnection();
    echo "=== RUNNING ALTER PAGES & SETTINGS MIGRATIONS ===\n";

    // 1. Add sort_order and in_navigation to pages table
    $checkSort = $pdo->query("SHOW COLUMNS FROM `pages` LIKE 'sort_order'");
    if ($checkSort->rowCount() === 0) {
        $pdo->exec("ALTER TABLE `pages` ADD COLUMN `sort_order` INT DEFAULT 0");
        echo "Added column 'sort_order' to pages table.\n";
    }

    $checkNav = $pdo->query("SHOW COLUMNS FROM `pages` LIKE 'in_navigation'");
    if ($checkNav->rowCount() === 0) {
        $pdo->exec("ALTER TABLE `pages` ADD COLUMN `in_navigation` TINYINT DEFAULT 1");
        echo "Added column 'in_navigation' to pages table.\n";
    }

    // Set initial sort orders for dynamic pages
    Database::query("UPDATE pages SET sort_order = 1, in_navigation = 1 WHERE slug = 'home'");
    Database::query("UPDATE pages SET sort_order = 2, in_navigation = 1 WHERE slug = 'about'");
    Database::query("UPDATE pages SET sort_order = 3, in_navigation = 1 WHERE slug = 'service'");
    Database::query("UPDATE pages SET sort_order = 4, in_navigation = 1 WHERE slug = 'realisations'");
    Database::query("UPDATE pages SET sort_order = 5, in_navigation = 1 WHERE slug = 'portfolio'");
    Database::query("UPDATE pages SET sort_order = 6, in_navigation = 1 WHERE slug = 'etudes-de-cas'");
    Database::query("UPDATE pages SET sort_order = 7, in_navigation = 1 WHERE slug = 'blog'");
    Database::query("UPDATE pages SET sort_order = 8, in_navigation = 1 WHERE slug = 'contact'");
    Database::query("UPDATE pages SET sort_order = 9, in_navigation = 1 WHERE slug = 'ia-automatisation'");
    echo "Initialized page sort orders and navigation flags.\n";

    // 2. Insert/Overwrite settings
    $settings = [
        'site_favicon' => '/assets/images/hero_3d.png', // Temporary premium favicon icon path
        'site_logo_mobile' => '',
        'site_logo_text' => 'Digitalium',
        'site_logo_subtext' => 'Group',
        'site_whatsapp' => '0101782919',
        'footer_copyright' => '© ' . date('Y') . ' Digitalium Group. Tous droits réservés.',
        'footer_legal_text' => 'Mentions Légales',
        'footer_legal_url' => '/mentions-legales',
        'header_cta_text' => 'Discuter de mon projet',
        'header_cta_link' => '/contact'
    ];

    foreach ($settings as $k => $v) {
        $checkSet = Database::fetch("SELECT id FROM settings WHERE setting_key = :k", ['k' => $k]);
        if (!$checkSet) {
            Setting::setVal($k, $v);
            echo "Seeded setting key: $k\n";
        } else {
            // Only update WhatsApp to match official requested number
            if ($k === 'site_whatsapp') {
                Setting::setVal($k, $v);
                echo "Updated WhatsApp setting key: $k\n";
            }
        }
    }

    // Replace the obsolete contact phone in settings to match official WhatsApp
    Setting::setVal('contact_phone', '0101782919');
    echo "Updated contact_phone to official 0101782919.\n";

    // 3. Inject new fields (images, links, etc.) into existing repeatable groups
    echo "Injecting image & link block fields to repeatable cards...\n";
    $sections = Database::fetchAll("SELECT id, type FROM sections");
    foreach ($sections as $sec) {
        $secId = (int)$sec['id'];
        $type = $sec['type'];
        
        // Find existing groups for this section
        $groups = Database::fetchAll("SELECT DISTINCT group_id FROM blocks WHERE section_id = :sid AND group_id IS NOT NULL", ['sid' => $secId]);
        if (empty($groups)) continue;

        foreach ($groups as $g) {
            $groupId = (int)$g['group_id'];

            switch ($type) {
                case 'services_grid':
                    $checkImg = Database::fetch("SELECT id FROM blocks WHERE section_id = :sid AND group_id = :gid AND block_key = 'svc_image'", ['sid' => $secId, 'gid' => $groupId]);
                    if (!$checkImg) {
                        Block::setVal($secId, 'svc_image', 'image', '', $groupId, 4);
                        Block::setVal($secId, 'svc_link', 'link', '/contact', $groupId, 5);
                        echo "Injected svc_image / svc_link in services_grid section #$secId group #$groupId\n";
                    }
                    break;
                    
                case 'blog_grid':
                    $checkImg = Database::fetch("SELECT id FROM blocks WHERE section_id = :sid AND group_id = :gid AND block_key = 'post_image'", ['sid' => $secId, 'gid' => $groupId]);
                    if (!$checkImg) {
                        Block::setVal($secId, 'post_image', 'image', '', $groupId, 5);
                        Block::setVal($secId, 'post_link', 'link', '/blog', $groupId, 6);
                        echo "Injected post_image / post_link in blog_grid section #$secId group #$groupId\n";
                    }
                    break;
                    
                case 'team_roles':
                case 'team':
                    $checkImg = Database::fetch("SELECT id FROM blocks WHERE section_id = :sid AND group_id = :gid AND block_key = 'role_image'", ['sid' => $secId, 'gid' => $groupId]);
                    if (!$checkImg) {
                        Block::setVal($secId, 'role_image', 'image', '', $groupId, 3);
                        Block::setVal($secId, 'role_link', 'link', '#', $groupId, 4);
                        echo "Injected role_image in team section #$secId group #$groupId\n";
                    }
                    break;
                    
                case 'process':
                case 'process_strip':
                    $checkImg = Database::fetch("SELECT id FROM blocks WHERE section_id = :sid AND group_id = :gid AND block_key = 'proc_image'", ['sid' => $secId, 'gid' => $groupId]);
                    if (!$checkImg) {
                        Block::setVal($secId, 'proc_image', 'image', '', $groupId, 4);
                        Block::setVal($secId, 'proc_link', 'link', '#', $groupId, 5);
                        echo "Injected proc_image in process section #$secId group #$groupId\n";
                    }
                    break;
                    
                case 'features':
                    $checkImg = Database::fetch("SELECT id FROM blocks WHERE section_id = :sid AND group_id = :gid AND block_key = 'card_image'", ['sid' => $secId, 'gid' => $groupId]);
                    if (!$checkImg) {
                        Block::setVal($secId, 'card_image', 'image', '', $groupId, 3);
                        Block::setVal($secId, 'card_link', 'link', '#', $groupId, 4);
                        echo "Injected card_image in features section #$secId group #$groupId\n";
                    }
                    break;
            }
        }
    }

    // Clear CMS Cache
    \App\Services\Cache::clear();
    echo "=== DATABASE ALTERATIONS COMPLETED SUCCESSFULLY ===\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
