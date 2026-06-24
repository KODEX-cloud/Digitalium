<?php
namespace App\Models;

use App\Services\Database;

class Project extends Model {
    protected static string $table = 'projects';

    /**
     * Ensure the projects table exists in the database.
     */
    public static function checkTableExists(): void {
        try {
            Database::getConnection()->query("SELECT 1 FROM `projects` LIMIT 1");
        } catch (\Throwable $e) {
            $sql = "CREATE TABLE IF NOT EXISTS `projects` (
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
            Database::getConnection()->exec($sql);
        }
    }

    /**
     * Get all records from the table, ensuring the table exists.
     */
    public static function all(string $orderBy = 'id ASC'): array {
        self::checkTableExists();
        return parent::all($orderBy);
    }

    /**
     * Find a record, ensuring the table exists.
     */
    public static function find(int $id): ?array {
        self::checkTableExists();
        return parent::find($id);
    }

    /**
     * Delete a record, ensuring the table exists.
     */
    public static function delete(int $id): bool {
        self::checkTableExists();
        return parent::delete($id);
    }

    /**
     * Add a new project to the portfolio database.
     */
    public static function add(array $data): string {
        self::checkTableExists();
        $slug = !empty($data['slug']) ? $data['slug'] : self::slugify($data['title']);
        $sql = "INSERT INTO " . static::$table . " (title, slug, category, client, project_date, description, logo, main_image, gallery, context, impact, technologies, external_link, sort_order, is_featured)
                VALUES (:title, :slug, :category, :client, :project_date, :description, :logo, :main_image, :gallery, :context, :impact, :technologies, :external_link, :sort_order, :is_featured)";
        return Database::insert($sql, [
            'title'        => $data['title'],
            'slug'         => $slug,
            'category'     => $data['category'],
            'client'       => $data['client'] ?? null,
            'project_date' => $data['project_date'] ?: null,
            'description'  => $data['description'] ?? null,
            'logo'         => $data['logo'] ?? null,
            'main_image'   => $data['main_image'],
            'gallery'      => $data['gallery'] ?? null,
            'context'      => $data['context'] ?? null,
            'impact'       => $data['impact'] ?? null,
            'technologies' => $data['technologies'] ?? null,
            'external_link'=> $data['external_link'] ?? null,
            'sort_order'   => (int)($data['sort_order'] ?? 0),
            'is_featured'  => (int)($data['is_featured'] ?? 0)
        ]);
    }

    /**
     * Update an existing project in the database.
     */
    public static function updateProject(int $id, array $data): bool {
        self::checkTableExists();
        $slug = !empty($data['slug']) ? $data['slug'] : self::slugify($data['title']);
        $sql = "UPDATE " . static::$table . " SET
                title = :title, slug = :slug, category = :category, client = :client,
                project_date = :project_date, description = :description,
                logo = :logo, main_image = :main_image, gallery = :gallery,
                context = :context, impact = :impact, technologies = :technologies,
                external_link = :external_link, sort_order = :sort_order, is_featured = :is_featured
                WHERE id = :id";
        Database::query($sql, [
            'title'        => $data['title'],
            'slug'         => $slug,
            'category'     => $data['category'],
            'client'       => $data['client'] ?? null,
            'project_date' => $data['project_date'] ?: null,
            'description'  => $data['description'] ?? null,
            'logo'         => $data['logo'] ?? null,
            'main_image'   => $data['main_image'],
            'gallery'      => $data['gallery'] ?? null,
            'context'      => $data['context'] ?? null,
            'impact'       => $data['impact'] ?? null,
            'technologies' => $data['technologies'] ?? null,
            'external_link'=> $data['external_link'] ?? null,
            'sort_order'   => (int)($data['sort_order'] ?? 0),
            'is_featured'  => (int)($data['is_featured'] ?? 0),
            'id'           => $id
        ]);
        return true;
    }

    /**
     * Find project by slug.
     */
    public static function findBySlug(string $slug): ?array {
        self::checkTableExists();
        return Database::fetch("SELECT * FROM " . static::$table . " WHERE slug = :slug LIMIT 1", ['slug' => $slug]);
    }

    /**
     * Get published/featured projects for frontend.
     */
    public static function getFeatured(): array {
        self::checkTableExists();
        return Database::fetchAll("SELECT * FROM " . static::$table . " WHERE is_featured = 1 ORDER BY sort_order ASC, id DESC");
    }

    /**
     * Get all projects ordered for frontend display.
     */
    public static function getPublic(string $category = ''): array {
        self::checkTableExists();
        if ($category) {
            return Database::fetchAll(
                "SELECT * FROM " . static::$table . " WHERE category = :cat ORDER BY sort_order ASC, id DESC",
                ['cat' => $category]
            );
        }
        return Database::fetchAll("SELECT * FROM " . static::$table . " ORDER BY sort_order ASC, id DESC");
    }

    /**
     * Get distinct categories for filter UI.
     */
    public static function getCategories(): array {
        self::checkTableExists();
        $rows = Database::fetchAll("SELECT DISTINCT category FROM " . static::$table . " WHERE category IS NOT NULL AND category != '' ORDER BY category ASC");
        return array_column($rows, 'category');
    }

    /**
     * Slugify helper.
     */
    public static function slugify(string $text): string {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        return strtolower($text) ?: 'projet';
    }
}
