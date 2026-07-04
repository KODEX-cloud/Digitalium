<?php
namespace App\System;

use App\Services\Database;

/**
 * SyncProductionManager — Synchronisation schéma DB production → local.
 *
 * Utilisable via :
 *   - CLI   : php database/sync_production.php
 *   - HTTP  : POST /admin/api/system/sync-db (SyncProductionController)
 *   - DSM   : DeployPipeline step 'sync_db'
 *
 * INCIDENT ROOT CAUSE (2026-07-04) :
 *   menus.location manquant → SQL: SELECT * FROM menus WHERE location = :l
 *   Modèle : App\Models\Menu::findByLocation() — Menu.php:14
 *   Layout : app/Views/frontend/layout.php:313
 *   Cause  : master_migration.php utilisait CREATE TABLE IF NOT EXISTS
 *            → si menus existait sans location, IF NOT EXISTS skipppait
 *            → colonne jamais ajoutée sur Hostinger
 *   Fix    : addColumnIfNotExists('menus','location',...) idempotent
 */
class SyncProductionManager {

    private static array $results = [];

    // ─── Schéma attendu (source de vérité = base locale) ─────────────────────

    /**
     * Liste complète des tables et leurs colonnes obligatoires.
     * Format : table => [ colonne => [definition, after_column|null] ]
     */
    private static function expectedColumns(): array {
        return [
            'pages' => [
                'hero_title'           => ["VARCHAR(255) NULL",                            'meta_description'],
                'hero_subtitle'        => ["TEXT NULL",                                    'hero_title'],
                'hero_image'           => ["VARCHAR(255) NULL",                            'hero_subtitle'],
                'hero_cta1_text'       => ["VARCHAR(100) NULL",                            'hero_image'],
                'hero_cta1_url'        => ["VARCHAR(255) NULL",                            'hero_cta1_text'],
                'hero_cta2_text'       => ["VARCHAR(100) NULL",                            'hero_cta1_url'],
                'hero_cta2_url'        => ["VARCHAR(255) NULL",                            'hero_cta2_text'],
                'hero_bg_color'        => ["VARCHAR(100) NULL",                            'hero_cta2_url'],
                'hero_effect'          => ["VARCHAR(50) DEFAULT 'particles'",              'hero_bg_color'],
                'sort_order'           => ["INT DEFAULT 0",                                'hero_effect'],
                'in_navigation'        => ["TINYINT DEFAULT 1",                            'sort_order'],
                'hero_variant'         => ["VARCHAR(50) DEFAULT 'hero_split_large_image'", 'in_navigation'],
                'hero_image_layout'    => ["VARCHAR(50) DEFAULT 'right'",                  'hero_variant'],
                'hero_image_size'      => ["VARCHAR(50) DEFAULT 'large'",                  'hero_image_layout'],
                'hero_badge'           => ["VARCHAR(255) NULL",                            'hero_image_size'],
                'hero_status'          => ["TINYINT DEFAULT 1",                            'hero_badge'],
                'header_bg_mode'       => ["VARCHAR(50) DEFAULT 'glass'",                  'hero_status'],
                'header_opacity'       => ["FLOAT DEFAULT 0.65",                           'header_bg_mode'],
                'header_blur'          => ["INT DEFAULT 20",                               'header_opacity'],
                'header_shadow'        => ["VARCHAR(50) DEFAULT 'moyen'",                  'header_blur'],
                'header_contrast_mode' => ["VARCHAR(50) DEFAULT 'default'",                'header_shadow'],
                'logo_light'           => ["VARCHAR(255) NULL",                            'header_contrast_mode'],
                'logo_dark'            => ["VARCHAR(255) NULL",                            'logo_light'],
                'logo_size'            => ["INT DEFAULT 38",                               'logo_dark'],
                'hero_layout_mode'     => ["VARCHAR(50) DEFAULT 'moyen'",                  'logo_size'],
                'hero_text_position'   => ["VARCHAR(50) DEFAULT 'centre'",                 'hero_layout_mode'],
                'hero_text_alignment'  => ["VARCHAR(50) DEFAULT 'center'",                 'hero_text_position'],
                'hero_text_width'      => ["VARCHAR(50) DEFAULT '100%'",                   'hero_text_alignment'],
                'hero_overlay_opacity' => ["FLOAT DEFAULT 0.45",                           'hero_text_width'],
                'hero_shadow_strength' => ["VARCHAR(50) DEFAULT 'moyen'",                  'hero_overlay_opacity'],
                'hero_image_mobile'    => ["VARCHAR(255) NULL",                            'hero_shadow_strength'],
                'responsive_settings'  => ["TEXT NULL",                                    'hero_image_mobile'],
                'hero_features'        => ["TEXT NULL",                                    'responsive_settings'],
                'hero_articles'        => ["TEXT NULL",                                    'hero_features'],
            ],
            'menus' => [
                // COLONNE CRITIQUE — cause de l'incident production 2026-07-04
                'location' => ["VARCHAR(50) DEFAULT 'primary'", 'slug'],
            ],
            'menu_items' => [
                'parent_id'  => ["INT NULL",                   'menu_id'],
                'page_id'    => ["INT NULL",                   'parent_id'],
                'url'        => ["VARCHAR(500) NULL",          'label'],
                'target'     => ["VARCHAR(20) DEFAULT '_self'", 'url'],
                'icon'       => ["VARCHAR(50) NULL",           'target'],
                'sort_order' => ["INT DEFAULT 0",              'icon'],
                'is_active'  => ["TINYINT DEFAULT 1",          'sort_order'],
            ],
            'projects' => [
                'slug'         => ["VARCHAR(255) NULL", 'title'],
                'client'       => ["VARCHAR(150) NULL", 'category'],
                'project_date' => ["DATE NULL",          'client'],
                'description'  => ["TEXT NULL",          'project_date'],
                'logo'         => ["VARCHAR(255) NULL",  'description'],
                'gallery'      => ["TEXT NULL",          'main_image'],
                'context'      => ["TEXT NULL",          'gallery'],
                'impact'       => ["TEXT NULL",          'context'],
                'technologies' => ["VARCHAR(255) NULL",  'impact'],
                'external_link'=> ["VARCHAR(255) NULL",  'technologies'],
                'sort_order'   => ["INT DEFAULT 0",      'external_link'],
                'is_featured'  => ["TINYINT DEFAULT 0",  'sort_order'],
            ],
            'blog_posts' => [
                'excerpt'          => ["TEXT NULL",        'slug'],
                'content'          => ["LONGTEXT NULL",    'excerpt'],
                'featured_image'   => ["VARCHAR(255) NULL", 'content'],
                'category'         => ["VARCHAR(100) NULL", 'featured_image'],
                'category_id'      => ["INT NULL",          'category'],
                'author'           => ["VARCHAR(100) DEFAULT 'Équipe Digitalium'", 'category_id'],
                'is_featured'      => ["TINYINT DEFAULT 0", 'status'],
                'meta_title'       => ["VARCHAR(255) NULL", 'is_featured'],
                'meta_description' => ["TEXT NULL",         'meta_title'],
                'tags'             => ["VARCHAR(500) NULL", 'meta_description'],
                'published_at'     => ["TIMESTAMP NULL",    'tags'],
                'updated_at'       => ["TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP", 'created_at'],
            ],
        ];
    }

    /**
     * Tables qui doivent exister avec leur DDL complet.
     */
    private static function requiredTables(): array {
        return [
            'users' => "CREATE TABLE IF NOT EXISTS `users` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,`username` VARCHAR(50) NOT NULL UNIQUE,
                `password` VARCHAR(255) NOT NULL,`email` VARCHAR(100) NOT NULL UNIQUE,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            'pages' => "CREATE TABLE IF NOT EXISTS `pages` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,`title` VARCHAR(255) NOT NULL,
                `slug` VARCHAR(255) NOT NULL UNIQUE,`meta_title` VARCHAR(255) NULL,
                `meta_description` TEXT NULL,`status` ENUM('draft','published') DEFAULT 'draft',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            'sections' => "CREATE TABLE IF NOT EXISTS `sections` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,`page_id` INT NOT NULL,
                `name` VARCHAR(100) NOT NULL,`type` VARCHAR(50) NOT NULL,`sort_order` INT DEFAULT 0,
                `status` ENUM('active','inactive') DEFAULT 'active',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            'blocks' => "CREATE TABLE IF NOT EXISTS `blocks` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,`section_id` INT NOT NULL,
                `block_key` VARCHAR(100) NOT NULL,
                `type` ENUM('text','textarea','wysiwyg','image','link','color','number') DEFAULT 'text',
                `value` LONGTEXT NULL,`sort_order` INT DEFAULT 0,`group_id` INT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            'settings' => "CREATE TABLE IF NOT EXISTS `settings` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,`setting_key` VARCHAR(100) NOT NULL UNIQUE,
                `setting_value` TEXT NULL,`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            'media' => "CREATE TABLE IF NOT EXISTS `media` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,`filename` VARCHAR(255) NOT NULL,
                `filepath` VARCHAR(255) NOT NULL,`original_name` VARCHAR(255) NOT NULL,
                `file_size` INT NOT NULL,`mime_type` VARCHAR(100) NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            'hero_slides' => "CREATE TABLE IF NOT EXISTS `hero_slides` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,`page_id` INT NOT NULL,
                `title` VARCHAR(255) NOT NULL,`subtitle` TEXT NULL,`badge` VARCHAR(255) NULL,
                `image` VARCHAR(255) NULL,`cta_text` VARCHAR(255) NULL,`cta_url` VARCHAR(255) NULL,
                `sort_order` INT DEFAULT 0,`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            'projects' => "CREATE TABLE IF NOT EXISTS `projects` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,`title` VARCHAR(255) NOT NULL,
                `slug` VARCHAR(255) NULL,`category` VARCHAR(100) NOT NULL,
                `client` VARCHAR(150) NULL,`project_date` DATE NULL,`description` TEXT NULL,
                `logo` VARCHAR(255) NULL,`main_image` VARCHAR(255) NOT NULL,
                `gallery` TEXT NULL,`context` TEXT NULL,`impact` TEXT NULL,
                `technologies` VARCHAR(255) NULL,`external_link` VARCHAR(255) NULL,
                `sort_order` INT DEFAULT 0,`is_featured` TINYINT DEFAULT 0,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            'blog_categories' => "CREATE TABLE IF NOT EXISTS `blog_categories` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,`name` VARCHAR(100) NOT NULL,
                `slug` VARCHAR(100) NOT NULL UNIQUE,`description` TEXT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            'blog_posts' => "CREATE TABLE IF NOT EXISTS `blog_posts` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,`title` VARCHAR(255) NOT NULL,
                `slug` VARCHAR(255) NOT NULL UNIQUE,`excerpt` TEXT NULL,`content` LONGTEXT NULL,
                `featured_image` VARCHAR(255) NULL,`category` VARCHAR(100) NULL,`category_id` INT NULL,
                `author` VARCHAR(100) DEFAULT 'Équipe Digitalium',
                `status` ENUM('draft','published') DEFAULT 'draft',`is_featured` TINYINT DEFAULT 0,
                `meta_title` VARCHAR(255) NULL,`meta_description` TEXT NULL,`tags` VARCHAR(500) NULL,
                `published_at` TIMESTAMP NULL,`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            'blog_tags' => "CREATE TABLE IF NOT EXISTS `blog_tags` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,`name` VARCHAR(100) NOT NULL,
                `slug` VARCHAR(100) NOT NULL UNIQUE,`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            'blog_post_tags' => "CREATE TABLE IF NOT EXISTS `blog_post_tags` (
                `post_id` INT NOT NULL,`tag_id` INT NOT NULL,PRIMARY KEY (`post_id`,`tag_id`),
                FOREIGN KEY (`post_id`) REFERENCES `blog_posts`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`tag_id`)  REFERENCES `blog_tags`(`id`)  ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            'blog_comments' => "CREATE TABLE IF NOT EXISTS `blog_comments` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,`post_id` INT NOT NULL,
                `author_name` VARCHAR(100) NOT NULL,`author_email` VARCHAR(255) NOT NULL,
                `content` TEXT NOT NULL,`status` ENUM('pending','approved','spam') DEFAULT 'pending',
                `ip_address` VARCHAR(45) NULL,`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`post_id`) REFERENCES `blog_posts`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            'contact_messages' => "CREATE TABLE IF NOT EXISTS `contact_messages` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,`nom` VARCHAR(150) NOT NULL,
                `email` VARCHAR(255) NOT NULL,`telephone` VARCHAR(30) NULL,
                `sujet` VARCHAR(255) NULL,`message` TEXT NOT NULL,`ip_address` VARCHAR(45) NULL,
                `statut` ENUM('nouveau','lu','archivé') DEFAULT 'nouveau',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            'menus' => "CREATE TABLE IF NOT EXISTS `menus` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,`name` VARCHAR(100) NOT NULL,
                `slug` VARCHAR(100) NOT NULL UNIQUE,`location` VARCHAR(50) DEFAULT 'primary',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            'menu_items' => "CREATE TABLE IF NOT EXISTS `menu_items` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,`menu_id` INT NOT NULL,`parent_id` INT NULL,
                `page_id` INT NULL,`label` VARCHAR(150) NOT NULL,`url` VARCHAR(500) NULL,
                `target` VARCHAR(20) DEFAULT '_self',`icon` VARCHAR(50) NULL,
                `sort_order` INT DEFAULT 0,`is_active` TINYINT DEFAULT 1,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`menu_id`)   REFERENCES `menus`(`id`)      ON DELETE CASCADE,
                FOREIGN KEY (`parent_id`) REFERENCES `menu_items`(`id`) ON DELETE SET NULL,
                FOREIGN KEY (`page_id`)   REFERENCES `pages`(`id`)      ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        ];
    }

    // ─── Inspection — état actuel de la DB ───────────────────────────────────

    /**
     * Retourne l'état actuel de la DB : tables présentes, colonnes par table.
     */
    public static function inspect(): array {
        $pdo    = Database::getConnection();
        $tables = $pdo->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
        $schema = [];
        foreach ($tables as $t) {
            $cols = $pdo->query("SHOW COLUMNS FROM `{$t}`")->fetchAll(\PDO::FETCH_ASSOC);
            $schema[$t] = array_column($cols, 'Field');
        }
        return $schema;
    }

    /**
     * Compare la DB actuelle vs le schéma attendu.
     * Retourne les tables et colonnes manquantes.
     */
    public static function diff(): array {
        $current    = self::inspect();
        $currentTbl = array_keys($current);
        $missing    = ['tables' => [], 'columns' => []];

        foreach (array_keys(self::requiredTables()) as $tbl) {
            if (!in_array($tbl, $currentTbl)) {
                $missing['tables'][] = $tbl;
            }
        }

        foreach (self::expectedColumns() as $tbl => $cols) {
            if (!in_array($tbl, $currentTbl)) continue;
            foreach ($cols as $col => [$def, $after]) {
                if (!in_array($col, $current[$tbl] ?? [])) {
                    $missing['columns'][] = ['table' => $tbl, 'column' => $col, 'definition' => $def];
                }
            }
        }

        return $missing;
    }

    // ─── Exécution ───────────────────────────────────────────────────────────

    /**
     * Lance la synchronisation complète.
     *
     * @return array DSMResult-compatible
     */
    public static function run(): array {
        self::$results = [];
        $t             = DSMResult::timer();
        $pdo           = Database::getConnection();
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        // 1. Créer les tables manquantes
        foreach (self::requiredTables() as $tbl => $ddl) {
            self::createTableSafe($pdo, $tbl, $ddl);
        }

        // 2. Ajouter les colonnes manquantes
        foreach (self::expectedColumns() as $tbl => $cols) {
            if (!self::tableExists($pdo, $tbl)) continue;
            foreach ($cols as $col => [$def, $after]) {
                self::addColumnSafe($pdo, $tbl, $col, $def, $after);
            }
        }

        // 3. Seed menus si vide
        self::seedMenus($pdo);

        // 4. Seed blog_categories si vide
        self::seedBlogCategories($pdo);

        // 5. Seed settings minimaux
        self::seedSettings($pdo);

        // 6. Index de performance
        self::ensureIndexes($pdo);

        $ok   = array_filter(self::$results, fn($r) => $r['status'] === 'ok');
        $errs = array_filter(self::$results, fn($r) => $r['status'] === 'error');
        $skip = array_filter(self::$results, fn($r) => $r['status'] === 'skip');

        $status  = count($errs) > 0 ? 'error' : 'ok';
        $message = count($ok) . ' corrections — ' . count($skip) . ' déjà OK — ' . count($errs) . ' erreurs';

        $logEntry = date('Y-m-d H:i:s') . " [SYNC_DB] {$message}\n";
        @file_put_contents(ROOT_PATH . '/storage/logs/errors.log', $logEntry, FILE_APPEND | LOCK_EX);

        return DSMResult::make($status, 'Sync Production DB', $message, [
            'ok'      => count($ok),
            'skipped' => count($skip),
            'errors'  => count($errs),
            'results' => self::$results,
            'incident' => [
                'root_cause' => 'menus.location column missing on production',
                'sql_failed' => 'SELECT * FROM menus WHERE location = :l LIMIT 1',
                'model'      => 'App\\Models\\Menu::findByLocation() — Menu.php:14',
                'called_from'=> 'app/Views/frontend/layout.php:313',
                'why'        => 'CREATE TABLE IF NOT EXISTS skipped because table already existed without location column',
                'fixed_by'   => 'addColumnIfNotExists(menus, location, VARCHAR(50) DEFAULT primary)',
            ],
        ], [], DSMResult::elapsed($t));
    }

    // ─── Helpers privés ───────────────────────────────────────────────────────

    private static function tableExists(\PDO $pdo, string $table): bool {
        return (bool) $pdo->query("SHOW TABLES LIKE '{$table}'")->fetch();
    }

    private static function columnExists(\PDO $pdo, string $table, string $col): bool {
        return (bool) $pdo->query("SHOW COLUMNS FROM `{$table}` LIKE '{$col}'")->fetch();
    }

    private static function createTableSafe(\PDO $pdo, string $table, string $ddl): void {
        if (self::tableExists($pdo, $table)) {
            self::$results[] = ['status' => 'skip', 'action' => "TABLE {$table}", 'detail' => 'existe déjà'];
            return;
        }
        try {
            $pdo->exec($ddl);
            self::$results[] = ['status' => 'ok', 'action' => "TABLE {$table}", 'detail' => 'créée'];
        } catch (\Throwable $e) {
            self::$results[] = ['status' => 'error', 'action' => "TABLE {$table}", 'detail' => $e->getMessage()];
        }
    }

    private static function addColumnSafe(\PDO $pdo, string $table, string $col, string $def, string $after): void {
        if (self::columnExists($pdo, $table, $col)) {
            self::$results[] = ['status' => 'skip', 'action' => "COL {$table}.{$col}", 'detail' => 'existe déjà'];
            return;
        }
        try {
            $afterClause = $after ? " AFTER `{$after}`" : '';
            $pdo->exec("ALTER TABLE `{$table}` ADD COLUMN `{$col}` {$def}{$afterClause}");
            self::$results[] = ['status' => 'ok', 'action' => "COL {$table}.{$col}", 'detail' => "ajoutée ({$def})"];
        } catch (\Throwable $e) {
            self::$results[] = ['status' => 'error', 'action' => "COL {$table}.{$col}", 'detail' => $e->getMessage()];
        }
    }

    private static function seedMenus(\PDO $pdo): void {
        try {
            if (!self::tableExists($pdo, 'menus') || !self::tableExists($pdo, 'menu_items')) return;
            $count = (int) $pdo->query("SELECT COUNT(*) FROM menus")->fetchColumn();
            if ($count > 0) {
                self::$results[] = ['status' => 'skip', 'action' => 'SEED menus', 'detail' => "{$count} menu(s) existant(s)"];
                return;
            }
            $pdo->exec("INSERT INTO menus (name, slug, location) VALUES ('Navigation Principale','principal','primary')");
            $menuId = $pdo->lastInsertId();
            $pages  = self::tableExists($pdo, 'pages')
                ? $pdo->query("SELECT id,title,slug FROM pages WHERE status='published' AND in_navigation=1 ORDER BY sort_order ASC,id ASC")->fetchAll(\PDO::FETCH_ASSOC)
                : [];
            $sort = 0;
            foreach ($pages as $p) {
                $url  = $p['slug'] === 'home' ? '/' : '/' . $p['slug'];
                $stmt = $pdo->prepare("INSERT INTO menu_items (menu_id,label,url,page_id,sort_order,is_active) VALUES (?,?,?,?,?,1)");
                $stmt->execute([$menuId, $p['title'], $url, $p['id'], $sort++]);
            }
            self::$results[] = ['status' => 'ok', 'action' => 'SEED menus', 'detail' => 'Menu primaire créé avec ' . count($pages) . ' items'];
        } catch (\Throwable $e) {
            self::$results[] = ['status' => 'error', 'action' => 'SEED menus', 'detail' => $e->getMessage()];
        }
    }

    private static function seedBlogCategories(\PDO $pdo): void {
        try {
            if (!self::tableExists($pdo, 'blog_categories')) return;
            $count = (int) $pdo->query("SELECT COUNT(*) FROM blog_categories")->fetchColumn();
            if ($count > 0) {
                self::$results[] = ['status' => 'skip', 'action' => 'SEED blog_categories', 'detail' => "{$count} existante(s)"];
                return;
            }
            $pdo->exec("INSERT INTO blog_categories (name,slug) VALUES ('Technologie','technologie'),('Design','design'),('Développement','developpement'),('Actualités','actualites')");
            self::$results[] = ['status' => 'ok', 'action' => 'SEED blog_categories', 'detail' => '4 catégories insérées'];
        } catch (\Throwable $e) {
            self::$results[] = ['status' => 'error', 'action' => 'SEED blog_categories', 'detail' => $e->getMessage()];
        }
    }

    private static function seedSettings(\PDO $pdo): void {
        try {
            if (!self::tableExists($pdo, 'settings')) return;
            $required = [
                'site_name'        => 'Digitalium Group',
                'site_tagline'     => 'Leader de la Transformation Digitale',
                'contact_email'    => '',
                'footer_copyright' => '© ' . date('Y') . ' Digitalium Group. Tous droits réservés.',
            ];
            $stmt    = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE setting_key = ?");
            $ins     = $pdo->prepare("INSERT IGNORE INTO settings (setting_key,setting_value) VALUES (?,?)");
            $seeded  = 0;
            foreach ($required as $k => $v) {
                $stmt->execute([$k]);
                if ((int)$stmt->fetchColumn() === 0) { $ins->execute([$k, $v]); $seeded++; }
            }
            $msg = $seeded > 0 ? "{$seeded} clé(s) insérée(s)" : 'Toutes les clés système présentes';
            self::$results[] = ['status' => $seeded > 0 ? 'ok' : 'skip', 'action' => 'SEED settings', 'detail' => $msg];
        } catch (\Throwable $e) {
            self::$results[] = ['status' => 'error', 'action' => 'SEED settings', 'detail' => $e->getMessage()];
        }
    }

    private static function ensureIndexes(\PDO $pdo): void {
        $indexes = [
            'menus'      => ['idx_menus_loc',  "CREATE INDEX `idx_menus_loc`  ON `menus`      (`location`)"],
            'pages'      => ['idx_pages_slug', "CREATE INDEX `idx_pages_slug` ON `pages`      (`slug`)"],
            'blog_posts' => ['idx_bp_status',  "CREATE INDEX `idx_bp_status`  ON `blog_posts` (`status`)"],
            'projects'   => ['idx_proj_slug',  "CREATE INDEX `idx_proj_slug`  ON `projects`   (`slug`)"],
        ];
        foreach ($indexes as $tbl => [$name, $ddl]) {
            if (!self::tableExists($pdo, $tbl)) continue;
            $exists = $pdo->query("SHOW INDEX FROM `{$tbl}` WHERE Key_name = '{$name}'")->fetch();
            if ($exists) {
                self::$results[] = ['status' => 'skip', 'action' => "IDX {$tbl}.{$name}", 'detail' => 'existe déjà'];
                continue;
            }
            try {
                $pdo->exec($ddl);
                self::$results[] = ['status' => 'ok', 'action' => "IDX {$tbl}.{$name}", 'detail' => 'créé'];
            } catch (\Throwable $e) {
                self::$results[] = ['status' => 'error', 'action' => "IDX {$tbl}.{$name}", 'detail' => $e->getMessage()];
            }
        }
    }
}
