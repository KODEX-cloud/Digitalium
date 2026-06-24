<?php
/**
 * Migration: Add hero_features, hero_articles to pages + create blog tables
 * Run once from CLI or via /public/run_blog_migration.php
 */

define('SECURE_ACCESS', true);
define('ROOT_PATH', dirname(__DIR__));
require ROOT_PATH . '/config/config.php';
require ROOT_PATH . '/app/Services/Database.php';

use App\Services\Database;

$pdo = Database::getConnection();
$errors = [];
$done = [];

// 1. Add hero_features to pages
try {
    $pdo->exec("ALTER TABLE `pages` ADD COLUMN `hero_features` TEXT NULL AFTER `responsive_settings`");
    $done[] = "Added hero_features column to pages";
} catch (\PDOException $e) {
    if (str_contains($e->getMessage(), 'Duplicate column')) {
        $done[] = "hero_features already exists (skipped)";
    } else {
        $errors[] = "hero_features: " . $e->getMessage();
    }
}

// 2. Add hero_articles to pages
try {
    $pdo->exec("ALTER TABLE `pages` ADD COLUMN `hero_articles` TEXT NULL AFTER `hero_features`");
    $done[] = "Added hero_articles column to pages";
} catch (\PDOException $e) {
    if (str_contains($e->getMessage(), 'Duplicate column')) {
        $done[] = "hero_articles already exists (skipped)";
    } else {
        $errors[] = "hero_articles: " . $e->getMessage();
    }
}

// 3. Create blog_categories table
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `blog_categories` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `name` VARCHAR(100) NOT NULL,
          `slug` VARCHAR(100) NOT NULL UNIQUE,
          `description` TEXT NULL,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    $done[] = "Created blog_categories table";
} catch (\PDOException $e) {
    $errors[] = "blog_categories: " . $e->getMessage();
}

// 4. Create blog_posts table
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `blog_posts` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `title` VARCHAR(255) NOT NULL,
          `slug` VARCHAR(255) NOT NULL UNIQUE,
          `excerpt` TEXT NULL,
          `content` LONGTEXT NULL,
          `featured_image` VARCHAR(255) NULL,
          `category` VARCHAR(100) NULL,
          `category_id` INT NULL,
          `author` VARCHAR(100) DEFAULT 'Équipe Digitalium',
          `status` ENUM('draft','published') DEFAULT 'draft',
          `is_featured` TINYINT DEFAULT 0,
          `meta_title` VARCHAR(255) NULL,
          `meta_description` TEXT NULL,
          `tags` VARCHAR(500) NULL,
          `published_at` TIMESTAMP NULL,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    $done[] = "Created blog_posts table";
} catch (\PDOException $e) {
    $errors[] = "blog_posts: " . $e->getMessage();
}

// 5. Seed default blog categories
try {
    $count = $pdo->query("SELECT COUNT(*) FROM blog_categories")->fetchColumn();
    if ($count == 0) {
        $pdo->exec("
            INSERT INTO blog_categories (name, slug, description) VALUES
            ('Technologie', 'technologie', 'Articles sur les dernières tendances technologiques'),
            ('Design', 'design', 'UI/UX, Design Systems et expérience utilisateur'),
            ('Développement', 'developpement', 'Tutoriels et best practices de développement'),
            ('Actualités', 'actualites', 'Actualités de Digitalium Group')
        ");
        $done[] = "Seeded default blog categories";
    }
} catch (\PDOException $e) {
    $errors[] = "Seed categories: " . $e->getMessage();
}

// 6. Seed 1 sample blog post
try {
    $count = $pdo->query("SELECT COUNT(*) FROM blog_posts")->fetchColumn();
    if ($count == 0) {
        $pdo->exec("
            INSERT INTO blog_posts (title, slug, excerpt, content, category, author, status, is_featured, meta_title, published_at) VALUES
            (
                'Bienvenue sur le blog Digitalium',
                'bienvenue-sur-le-blog-digitalium',
                'Découvrez les insights, actualités et ressources de l\'équipe Digitalium Group sur la transformation digitale.',
                '<p>Bienvenue sur le blog de <strong>Digitalium Group</strong> !</p><p>Nous partageons ici nos expertises en matière de développement logiciel, design système, et stratégie digitale.</p>',
                'Actualités',
                'Équipe Digitalium',
                'published',
                1,
                'Blog Digitalium — Insights & Actualités',
                NOW()
            )
        ");
        $done[] = "Seeded sample blog post";
    }
} catch (\PDOException $e) {
    $errors[] = "Seed post: " . $e->getMessage();
}

// Output result
echo "<pre style='font-family:monospace;padding:20px;background:#0b0f19;color:#e2e8f0;border-radius:8px;'>";
echo "<strong style='color:#4ade80'>✅ Migration terminée</strong>\n\n";
foreach ($done as $d) echo "  ✓ $d\n";
if ($errors) {
    echo "\n<strong style='color:#f87171'>⚠ Erreurs :</strong>\n";
    foreach ($errors as $err) echo "  ✗ $err\n";
}
echo "</pre>";
