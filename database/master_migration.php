<?php
/**
 * MASTER MIGRATION — Digitalium Group CMS
 * Idempotent: safe to run multiple times.
 * Run once via CLI: php database/master_migration.php
 * Or via admin: will be triggered automatically.
 */

define('SECURE_ACCESS', true);
define('ROOT_PATH', dirname(__DIR__));
require ROOT_PATH . '/config/config.php';
require ROOT_PATH . '/app/Services/Database.php';

use App\Services\Database;

$pdo = Database::getConnection();
$done = [];
$errors = [];

// ─── Helper: safe ALTER ADD COLUMN ─────────────────────────────────────────
function addColumnIfNotExists(\PDO $pdo, string $table, string $column, string $definition): void {
    global $done, $errors;
    $row = $pdo->query("SHOW COLUMNS FROM `{$table}` LIKE '{$column}'")->fetch();
    if (!$row) {
        $pdo->exec("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");
        $done[] = "ALTER {$table}: added {$column}";
    } else {
        $done[] = "ALTER {$table}: {$column} already exists (skip)";
    }
}

// ─── 1. blog_posts + blog_categories (re-run idempotent) ───────────────────
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `blog_categories` (
        `id`          INT AUTO_INCREMENT PRIMARY KEY,
        `name`        VARCHAR(100) NOT NULL,
        `slug`        VARCHAR(100) NOT NULL UNIQUE,
        `description` TEXT NULL,
        `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $done[] = "blog_categories: OK";
} catch (\PDOException $e) { $errors[] = "blog_categories: " . $e->getMessage(); }

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `blog_posts` (
        `id`               INT AUTO_INCREMENT PRIMARY KEY,
        `title`            VARCHAR(255) NOT NULL,
        `slug`             VARCHAR(255) NOT NULL UNIQUE,
        `excerpt`          TEXT NULL,
        `content`          LONGTEXT NULL,
        `featured_image`   VARCHAR(255) NULL,
        `category`         VARCHAR(100) NULL,
        `category_id`      INT NULL,
        `author`           VARCHAR(100) DEFAULT 'Équipe Digitalium',
        `status`           ENUM('draft','published') DEFAULT 'draft',
        `is_featured`      TINYINT DEFAULT 0,
        `meta_title`       VARCHAR(255) NULL,
        `meta_description` TEXT NULL,
        `tags`             VARCHAR(500) NULL,
        `published_at`     TIMESTAMP NULL,
        `created_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $done[] = "blog_posts: OK";
} catch (\PDOException $e) { $errors[] = "blog_posts: " . $e->getMessage(); }

// ─── 2. hero_features + hero_articles on pages ─────────────────────────────
try { addColumnIfNotExists($pdo, 'pages', 'hero_features', 'TEXT NULL'); } catch (\PDOException $e) { $errors[] = $e->getMessage(); }
try { addColumnIfNotExists($pdo, 'pages', 'hero_articles', 'TEXT NULL AFTER `hero_features`'); } catch (\PDOException $e) { $errors[] = $e->getMessage(); }

// ─── 3. blog_tags ──────────────────────────────────────────────────────────
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `blog_tags` (
        `id`         INT AUTO_INCREMENT PRIMARY KEY,
        `name`       VARCHAR(100) NOT NULL,
        `slug`       VARCHAR(100) NOT NULL UNIQUE,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $done[] = "blog_tags: OK";
} catch (\PDOException $e) { $errors[] = "blog_tags: " . $e->getMessage(); }

// ─── 4. blog_post_tags ─────────────────────────────────────────────────────
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `blog_post_tags` (
        `post_id` INT NOT NULL,
        `tag_id`  INT NOT NULL,
        PRIMARY KEY (`post_id`, `tag_id`),
        FOREIGN KEY (`post_id`) REFERENCES `blog_posts`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`tag_id`)  REFERENCES `blog_tags`(`id`)  ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $done[] = "blog_post_tags: OK";
} catch (\PDOException $e) { $errors[] = "blog_post_tags: " . $e->getMessage(); }

// ─── 5. blog_comments ──────────────────────────────────────────────────────
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `blog_comments` (
        `id`           INT AUTO_INCREMENT PRIMARY KEY,
        `post_id`      INT NOT NULL,
        `author_name`  VARCHAR(100) NOT NULL,
        `author_email` VARCHAR(255) NOT NULL,
        `content`      TEXT NOT NULL,
        `status`       ENUM('pending','approved','spam') DEFAULT 'pending',
        `ip_address`   VARCHAR(45) NULL,
        `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`post_id`) REFERENCES `blog_posts`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $done[] = "blog_comments: OK";
} catch (\PDOException $e) { $errors[] = "blog_comments: " . $e->getMessage(); }

// ─── 6. contact_messages ───────────────────────────────────────────────────
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `contact_messages` (
        `id`         INT AUTO_INCREMENT PRIMARY KEY,
        `nom`        VARCHAR(150) NOT NULL,
        `email`      VARCHAR(255) NOT NULL,
        `telephone`  VARCHAR(30) NULL,
        `sujet`      VARCHAR(255) NULL,
        `message`    TEXT NOT NULL,
        `ip_address` VARCHAR(45) NULL,
        `statut`     ENUM('nouveau','lu','archivé') DEFAULT 'nouveau',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $done[] = "contact_messages: OK";
} catch (\PDOException $e) { $errors[] = "contact_messages: " . $e->getMessage(); }

// ─── 7. menus ──────────────────────────────────────────────────────────────
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `menus` (
        `id`         INT AUTO_INCREMENT PRIMARY KEY,
        `name`       VARCHAR(100) NOT NULL,
        `slug`       VARCHAR(100) NOT NULL UNIQUE,
        `location`   VARCHAR(50) DEFAULT 'primary',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $done[] = "menus: OK";
} catch (\PDOException $e) { $errors[] = "menus: " . $e->getMessage(); }

// ─── 8. menu_items ─────────────────────────────────────────────────────────
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `menu_items` (
        `id`         INT AUTO_INCREMENT PRIMARY KEY,
        `menu_id`    INT NOT NULL,
        `parent_id`  INT NULL,
        `page_id`    INT NULL,
        `label`      VARCHAR(150) NOT NULL,
        `url`        VARCHAR(500) NULL,
        `target`     VARCHAR(20) DEFAULT '_self',
        `icon`       VARCHAR(50) NULL,
        `sort_order` INT DEFAULT 0,
        `is_active`  TINYINT DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`menu_id`)   REFERENCES `menus`(`id`)      ON DELETE CASCADE,
        FOREIGN KEY (`parent_id`) REFERENCES `menu_items`(`id`) ON DELETE SET NULL,
        FOREIGN KEY (`page_id`)   REFERENCES `pages`(`id`)      ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $done[] = "menu_items: OK";
} catch (\PDOException $e) { $errors[] = "menu_items: " . $e->getMessage(); }

// ─── 9. ALTER projects: add slug, client, project_date, description ─────────
try { addColumnIfNotExists($pdo, 'projects', 'slug',         "VARCHAR(255) NULL AFTER `title`"); } catch (\PDOException $e) { $errors[] = $e->getMessage(); }
try { addColumnIfNotExists($pdo, 'projects', 'client',       "VARCHAR(150) NULL AFTER `category`"); } catch (\PDOException $e) { $errors[] = $e->getMessage(); }
try { addColumnIfNotExists($pdo, 'projects', 'project_date', "DATE NULL AFTER `client`"); } catch (\PDOException $e) { $errors[] = $e->getMessage(); }
try { addColumnIfNotExists($pdo, 'projects', 'description',  "TEXT NULL AFTER `project_date`"); } catch (\PDOException $e) { $errors[] = $e->getMessage(); }
try { addColumnIfNotExists($pdo, 'pages', 'hero_title_size', "VARCHAR(50) NULL DEFAULT 'large' AFTER `hero_overlay_opacity`"); } catch (\PDOException $e) { $errors[] = $e->getMessage(); }

// ─── 10. Seed default data ─────────────────────────────────────────────────

// Seed blog categories if empty
try {
    $count = $pdo->query("SELECT COUNT(*) FROM blog_categories")->fetchColumn();
    if ((int)$count === 0) {
        $pdo->exec("INSERT INTO blog_categories (name, slug, description) VALUES
            ('Technologie', 'technologie', 'Dernières tendances technologiques'),
            ('Design', 'design', 'UI/UX, Design Systems et expérience utilisateur'),
            ('Développement', 'developpement', 'Tutoriels et best practices'),
            ('Actualités', 'actualites', 'Actualités de Digitalium Group')
        ");
        $done[] = "blog_categories: seeded 4 defaults";
    }
} catch (\PDOException $e) { $errors[] = "blog_categories seed: " . $e->getMessage(); }

// Seed sample blog post if empty
try {
    $count = $pdo->query("SELECT COUNT(*) FROM blog_posts")->fetchColumn();
    if ((int)$count === 0) {
        $pdo->exec("INSERT INTO blog_posts (title, slug, excerpt, content, category, author, status, is_featured, meta_title, published_at) VALUES
            (
                'Bienvenue sur le blog Digitalium',
                'bienvenue-sur-le-blog-digitalium',
                'Découvrez les insights, actualités et ressources de l\'équipe Digitalium Group.',
                '<p>Bienvenue sur le blog de <strong>Digitalium Group</strong> !</p><p>Nous partageons ici nos expertises en matière de développement logiciel, design système, et stratégie digitale.</p>',
                'Actualités',
                'Équipe Digitalium',
                'published',
                1,
                'Blog Digitalium — Insights & Actualités',
                NOW()
            )
        ");
        $done[] = "blog_posts: seeded 1 sample";
    }
} catch (\PDOException $e) { $errors[] = "blog_posts seed: " . $e->getMessage(); }

// Seed default primary menu from existing published pages
try {
    $menuCount = $pdo->query("SELECT COUNT(*) FROM menus")->fetchColumn();
    if ((int)$menuCount === 0) {
        $pdo->exec("INSERT INTO menus (name, slug, location) VALUES ('Navigation Principale', 'principal', 'primary')");
        $menuId = $pdo->lastInsertId();

        // Import published pages with in_navigation=1
        $pages = $pdo->query("SELECT id, title, slug FROM pages WHERE status='published' AND in_navigation=1 ORDER BY sort_order ASC, id ASC")->fetchAll(\PDO::FETCH_ASSOC);
        $sort = 0;
        foreach ($pages as $p) {
            $url = $p['slug'] === 'home' ? '/' : '/' . $p['slug'];
            $stmt = $pdo->prepare("INSERT INTO menu_items (menu_id, label, url, page_id, sort_order, is_active) VALUES (?, ?, ?, ?, ?, 1)");
            $stmt->execute([$menuId, $p['title'], $url, $p['id'], $sort++]);
        }
        $done[] = "menus: seeded default primary menu with " . count($pages) . " items";
    }
} catch (\PDOException $e) { $errors[] = "menus seed: " . $e->getMessage(); }

// Generate slugs for existing projects that don't have one
try {
    $projects = $pdo->query("SELECT id, title FROM projects WHERE slug IS NULL OR slug = ''")->fetchAll(\PDO::FETCH_ASSOC);
    foreach ($projects as $p) {
        $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', iconv('utf-8', 'us-ascii//TRANSLIT', $p['title'])), '-'));
        if (empty($slug)) $slug = 'projet-' . $p['id'];
        // Ensure unique
        $existing = $pdo->prepare("SELECT id FROM projects WHERE slug = ? AND id != ?")->execute([$slug, $p['id']]);
        $pdo->prepare("UPDATE projects SET slug = ? WHERE id = ?")->execute([$slug, $p['id']]);
    }
    if (count($projects) > 0) $done[] = "projects: generated slugs for " . count($projects) . " records";
} catch (\PDOException $e) { $errors[] = "projects slug gen: " . $e->getMessage(); }

// ─── 11. Hero data — home page (idempotent) ────────────────────────────────
try {
    $pdo->exec("
        UPDATE pages
        SET hero_image          = '/assets/images/digitalium-hero-team.png',
            hero_variant        = 'hero_ambient_glow',
            hero_title_size     = 'xlarge',
            hero_overlay_opacity = 0.78
        WHERE slug = 'home'
    ");
    $done[] = "pages: hero_image + hero_variant + hero_title_size home → xlarge / hero_ambient_glow";
} catch (\PDOException $e) { $errors[] = "pages hero_data: " . $e->getMessage(); }

// ─── 12. Theme Builder — seed default design tokens (skip if already set) ────
try {
    $themeDefaults = [
        'theme_primary'                => '#1d6363',
        'theme_secondary'              => '#346a6d',
        'theme_accent'                 => '#004d3f',
        'theme_text_main'              => '#272727',
        'theme_text_sub'               => '#3b3f4a',
        'theme_text_muted'             => '#4d4f63',
        'theme_bg_base'                => '#fbfbfb',
        'theme_bg_alt'                 => '#e8f3e9',
        'theme_bg_card'                => '#ffffff',
        'theme_radius_pill'            => '8',
        'theme_radius_card'            => '22',
        'theme_radius_btn'             => '12',
        'theme_radius_md'              => '14',
        'theme_radius_sm'              => '10',
        'theme_space_section'          => '92',
        'theme_font_h1'                => '4.2',
        'theme_font_h2'                => '2.8',
        'theme_font_h3'                => '1.08',
        'theme_font_body'              => '1',
        'theme_font_weight_heading'    => '800',
        'theme_font_weight_body'       => '400',
        'theme_line_height_body'       => '1.78',
        'theme_letter_spacing_heading' => '-0.032',
        'theme_shadow_card'            => '0 1px 3px rgba(0,0,0,0.04), 0 8px 32px rgba(0,0,0,0.06)',
        'theme_shadow_btn'             => '0 4px 18px rgba(13,148,136,0.28)',
        'theme_hero_min_height'        => '100',
        'theme_hero_overlay_opacity'   => '0.78',
        'theme_hero_overlay_color'     => '#0a0f1e',
        'theme_btn_primary_bg'         => '#272727',
        'theme_btn_primary_text'       => '#ffffff',
        'theme_badge_bg'               => '#e0f1df',
        'theme_badge_text'             => '#004d3f',
        'theme_footer_bg'              => '#1d6363',
        'theme_surface_dark'           => '#12202c',
    ];
    $seeded = 0;
    foreach ($themeDefaults as $k => $v) {
        $chk = $pdo->prepare("SELECT id FROM settings WHERE setting_key = ?");
        $chk->execute([$k]);
        $row = $chk->fetch();
        if (!$row) {
            $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)")->execute([$k, $v]);
            $seeded++;
        }
    }
    $done[] = "settings: theme_* — $seeded nouvelles clés seedées (" . count($themeDefaults) . " total)";
} catch (\PDOException $e) { $errors[] = "theme settings seed: " . $e->getMessage(); }

// ─── 13. Admin account — upsert (toujours force le hash à jour) ─────────────
try {
    // Hash généré en local PHP 8.3 — password = Digitalium2026!
    $hash = '$argon2id$v=19$m=65536,t=4,p=1$dmNDZTBvRjNiNzFMc2dGQw$lVToUfCLuxyE9GRJmSP9+5jstxx729qn3W/xgW5L1g4';

    $existing = $pdo->prepare("SELECT id FROM users WHERE username = 'admin'");
    $existing->execute();
    $row = $existing->fetch(\PDO::FETCH_ASSOC);

    if (!$row) {
        $pdo->prepare("INSERT INTO users (username, password, email, created_at, updated_at) VALUES ('admin', ?, 'admin@digitaliumgroup.com', NOW(), NOW())")
            ->execute([$hash]);
        $done[] = "users: admin créé — mot de passe = Digitalium2026!";
    } else {
        $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE username = 'admin'")
            ->execute([$hash]);
        $done[] = "users: mot de passe admin réinitialisé → Digitalium2026!";
    }
} catch (\PDOException $e) { $errors[] = "users admin upsert: " . $e->getMessage(); }

// ─── Output ─────────────────────────────────────────────────────────────────
$isCli = PHP_SAPI === 'cli';

if (!$isCli) {
    echo "<pre style='font-family:monospace;padding:20px;background:#0b0f19;color:#e2e8f0;border-radius:8px;max-width:900px;margin:20px auto;'>";
    echo "<strong style='color:#4ade80;font-size:1.1em'>✅ MASTER MIGRATION — Digitalium CMS</strong>\n";
    echo "<strong style='color:#94a3b8'>Date: " . date('Y-m-d H:i:s') . "</strong>\n\n";
    foreach ($done as $d) echo "  <span style='color:#4ade80'>✓</span> $d\n";
    if ($errors) {
        echo "\n<strong style='color:#f87171'>⚠ Erreurs :</strong>\n";
        foreach ($errors as $err) echo "  <span style='color:#f87171'>✗</span> $err\n";
    }
    echo "</pre>";
} else {
    echo "=== MASTER MIGRATION — Digitalium CMS ===\n";
    foreach ($done as $d) echo "  ✓ $d\n";
    if ($errors) {
        echo "\n⚠ ERREURS:\n";
        foreach ($errors as $err) echo "  ✗ $err\n";
    }
    echo "\nDone.\n";
}
