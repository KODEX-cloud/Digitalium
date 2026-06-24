<?php
namespace App\Models;

use App\Services\Database;

class Tag extends Model {
    protected static string $table = 'blog_tags';

    public static function all(string $orderBy = 'name ASC'): array {
        return Database::fetchAll("SELECT * FROM blog_tags ORDER BY name ASC");
    }

    public static function getForPost(int $postId): array {
        return Database::fetchAll(
            "SELECT t.* FROM blog_tags t
             INNER JOIN blog_post_tags pt ON pt.tag_id = t.id
             WHERE pt.post_id = :post_id
             ORDER BY t.name ASC",
            ['post_id' => $postId]
        );
    }

    public static function syncForPost(int $postId, array $tagNames): void {
        Database::query("DELETE FROM blog_post_tags WHERE post_id = :pid", ['pid' => $postId]);
        foreach ($tagNames as $name) {
            $name = trim($name);
            if ($name === '') continue;
            $slug = self::slugify($name);
            $existing = Database::fetch("SELECT id FROM blog_tags WHERE slug = :slug", ['slug' => $slug]);
            if ($existing) {
                $tagId = $existing['id'];
            } else {
                $tagId = Database::insert(
                    "INSERT INTO blog_tags (name, slug) VALUES (:name, :slug)",
                    ['name' => $name, 'slug' => $slug]
                );
            }
            Database::query(
                "INSERT IGNORE INTO blog_post_tags (post_id, tag_id) VALUES (:pid, :tid)",
                ['pid' => $postId, 'tid' => $tagId]
            );
        }
    }

    public static function slugify(string $text): string {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        return strtolower($text) ?: 'tag';
    }
}
