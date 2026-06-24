<?php
namespace App\Models;

use App\Services\Database;

class Menu extends Model {
    protected static string $table = 'menus';

    public static function findBySlug(string $slug): ?array {
        return Database::fetch("SELECT * FROM menus WHERE slug = :s LIMIT 1", ['s' => $slug]);
    }

    public static function findByLocation(string $location): ?array {
        return Database::fetch("SELECT * FROM menus WHERE location = :l LIMIT 1", ['l' => $location]);
    }

    public static function create(string $name, string $location = 'primary'): string {
        $slug = self::slugify($name);
        $existing = self::findBySlug($slug);
        if ($existing) $slug .= '-' . time();
        return Database::insert(
            "INSERT INTO menus (name, slug, location) VALUES (:name, :slug, :location)",
            ['name' => $name, 'slug' => $slug, 'location' => $location]
        );
    }

    public static function update(int $id, string $name, string $location): bool {
        Database::query(
            "UPDATE menus SET name = :name, location = :location WHERE id = :id",
            ['name' => $name, 'location' => $location, 'id' => $id]
        );
        return true;
    }

    public static function slugify(string $text): string {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        return strtolower(trim($text, '-')) ?: 'menu';
    }
}
