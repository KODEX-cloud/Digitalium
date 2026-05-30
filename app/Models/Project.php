<?php
namespace App\Models;

use App\Services\Database;

class Project extends Model {
    protected static string $table = 'projects';

    /**
     * Add a new project to the portfolio database.
     */
    public static function add(array $data): string {
        $sql = "INSERT INTO " . static::$table . " (title, category, logo, main_image, gallery, context, impact, technologies, external_link, sort_order, is_featured) 
                VALUES (:title, :category, :logo, :main_image, :gallery, :context, :impact, :technologies, :external_link, :sort_order, :is_featured)";
        return Database::insert($sql, [
            'title'         => $data['title'],
            'category'      => $data['category'],
            'logo'          => $data['logo'] ?? null,
            'main_image'    => $data['main_image'],
            'gallery'       => $data['gallery'] ?? null,
            'context'       => $data['context'] ?? null,
            'impact'        => $data['impact'] ?? null,
            'technologies'  => $data['technologies'] ?? null,
            'external_link' => $data['external_link'] ?? null,
            'sort_order'    => (int)($data['sort_order'] ?? 0),
            'is_featured'   => (int)($data['is_featured'] ?? 0)
        ]);
    }

    /**
     * Update an existing project in the database.
     */
    public static function updateProject(int $id, array $data): bool {
        $sql = "UPDATE " . static::$table . " SET 
                title = :title,
                category = :category,
                logo = :logo,
                main_image = :main_image,
                gallery = :gallery,
                context = :context,
                impact = :impact,
                technologies = :technologies,
                external_link = :external_link,
                sort_order = :sort_order,
                is_featured = :is_featured
                WHERE id = :id";
        
        Database::query($sql, [
            'title'         => $data['title'],
            'category'      => $data['category'],
            'logo'          => $data['logo'] ?? null,
            'main_image'    => $data['main_image'],
            'gallery'       => $data['gallery'] ?? null,
            'context'       => $data['context'] ?? null,
            'impact'        => $data['impact'] ?? null,
            'technologies'  => $data['technologies'] ?? null,
            'external_link' => $data['external_link'] ?? null,
            'sort_order'    => (int)($data['sort_order'] ?? 0),
            'is_featured'   => (int)($data['is_featured'] ?? 0),
            'id'            => $id
        ]);
        return true;
    }

    /**
     * Delete a project.
     */
    public static function delete(int $id): bool {
        $sql = "DELETE FROM " . static::$table . " WHERE id = :id";
        Database::query($sql, ['id' => $id]);
        return true;
    }

    /**
     * Fetch featured projects.
     */
    public static function getFeatured(): array {
        return Database::fetchAll("SELECT * FROM " . static::$table . " WHERE is_featured = 1 ORDER BY sort_order ASC, id DESC");
    }
}
