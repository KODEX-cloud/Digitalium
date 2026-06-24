<?php
namespace App\Models;

use App\Services\Database;

class Category extends Model {
    protected static string $table = 'blog_categories';

    public static function findBySlug(string $slug): ?array {
        return Database::fetch("SELECT * FROM blog_categories WHERE slug = :s LIMIT 1", ['s' => $slug]);
    }

    public static function getAllWithCount(): array {
        return Database::fetchAll(
            "SELECT c.*, COUNT(p.id) as post_count
             FROM blog_categories c
             LEFT JOIN blog_posts p ON p.category = c.name AND p.status = 'published'
             GROUP BY c.id ORDER BY c.name ASC"
        );
    }

    public static function create(string $name, string $slug, string $description = ''): string {
        return Database::insert(
            "INSERT INTO blog_categories (name, slug, description) VALUES (:name, :slug, :desc)",
            ['name' => $name, 'slug' => $slug, 'desc' => $description]
        );
    }

    public static function slugify(string $text): string {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        return strtolower(trim($text, '-')) ?: 'categorie';
    }
}
