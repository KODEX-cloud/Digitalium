<?php
namespace App\Models;

use App\Services\Database;

class HeroSlide extends Model {
    protected static string $table = 'hero_slides';

    /**
     * Ensure the hero_slides table exists in the database.
     */
    public static function checkTableExists(): void {
        try {
            Database::getConnection()->query("SELECT 1 FROM `hero_slides` LIMIT 1");
        } catch (\Throwable $e) {
            $sql = "CREATE TABLE IF NOT EXISTS `hero_slides` (
              `id` INT AUTO_INCREMENT PRIMARY KEY,
              `page_id` INT NOT NULL,
              `title` VARCHAR(255) NOT NULL,
              `subtitle` TEXT NULL,
              `badge` VARCHAR(255) NULL,
              `image` VARCHAR(255) NULL,
              `cta_text` VARCHAR(255) NULL,
              `cta_url` VARCHAR(255) NULL,
              `sort_order` INT DEFAULT 0,
              `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            Database::getConnection()->exec($sql);
        }
    }

    /**
     * Get all slides for a specific page.
     */
    public static function getByPage(int $pageId): array {
        self::checkTableExists();
        $sql = "SELECT * FROM " . static::$table . " WHERE page_id = :page_id ORDER BY sort_order ASC, id ASC";
        return Database::fetchAll($sql, ['page_id' => $pageId]);
    }

    /**
     * Find a slide, ensuring the table exists.
     */
    public static function find(int $id): ?array {
        self::checkTableExists();
        return parent::find($id);
    }

    /**
     * Delete a slide, ensuring the table exists.
     */
    public static function delete(int $id): bool {
        self::checkTableExists();
        return parent::delete($id);
    }

    /**
     * Add a new slide.
     */
    public static function add(array $data): string {
        self::checkTableExists();
        $sql = "INSERT INTO " . static::$table . " (page_id, title, subtitle, badge, image, cta_text, cta_url, sort_order) 
                VALUES (:page_id, :title, :subtitle, :badge, :image, :cta_text, :cta_url, :sort_order)";
        return Database::insert($sql, [
            'page_id'    => (int)$data['page_id'],
            'title'      => $data['title'],
            'subtitle'   => $data['subtitle'] ?? null,
            'badge'      => $data['badge'] ?? null,
            'image'      => $data['image'] ?? null,
            'cta_text'   => $data['cta_text'] ?? null,
            'cta_url'    => $data['cta_url'] ?? null,
            'sort_order' => (int)($data['sort_order'] ?? 0)
        ]);
    }

    /**
     * Update an existing slide.
     */
    public static function updateSlide(int $id, array $data): bool {
        self::checkTableExists();
        $sql = "UPDATE " . static::$table . " SET 
                title = :title,
                subtitle = :subtitle,
                badge = :badge,
                image = :image,
                cta_text = :cta_text,
                cta_url = :cta_url,
                sort_order = :sort_order
                WHERE id = :id";
        
        Database::query($sql, [
            'title'      => $data['title'],
            'subtitle'   => $data['subtitle'] ?? null,
            'badge'      => $data['badge'] ?? null,
            'image'      => $data['image'] ?? null,
            'cta_text'   => $data['cta_text'] ?? null,
            'cta_url'    => $data['cta_url'] ?? null,
            'sort_order' => (int)($data['sort_order'] ?? 0),
            'id'         => $id
        ]);
        return true;
    }
}
