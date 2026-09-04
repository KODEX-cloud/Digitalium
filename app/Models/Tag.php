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

    /** Tags avec le nombre d'articles PUBLIÉS qui les portent. */
    public static function getAllWithCount(): array {
        return Database::fetchAll(
            "SELECT t.*, COUNT(p.id) AS post_count
             FROM blog_tags t
             LEFT JOIN blog_post_tags pt ON pt.tag_id = t.id
             LEFT JOIN blog_posts p ON p.id = pt.post_id AND p.status = 'published'
             GROUP BY t.id ORDER BY post_count DESC, t.name ASC"
        );
    }

    /**
     * Renomme un tag sans casser ses rattachements.
     * Refuse si le nouveau nom entre en collision avec un tag existant : la
     * fusion de deux tags est une autre opération, qui doit rester explicite.
     */
    public static function renommer(int $id, string $nom): bool {
        $nom = trim($nom);
        if ($nom === '') { return false; }
        $slug = self::slugify($nom);
        $doublon = Database::fetch(
            "SELECT id FROM blog_tags WHERE slug = :s AND id <> :id LIMIT 1",
            ['s' => $slug, 'id' => $id]
        );
        if ($doublon) { return false; }
        Database::query(
            "UPDATE blog_tags SET name = :n, slug = :s WHERE id = :id",
            ['n' => $nom, 's' => $slug, 'id' => $id]
        );
        return true;
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
