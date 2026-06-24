<?php
namespace App\Models;

use App\Services\Database;

class Post extends Model {
    protected static string $table = 'blog_posts';

    public static function findBySlug(string $slug): ?array {
        return Database::fetch("SELECT * FROM blog_posts WHERE slug = :s LIMIT 1", ['s' => $slug]);
    }

    public static function getPublished(int $limit = 10, int $offset = 0): array {
        return Database::fetchAll(
            "SELECT * FROM blog_posts WHERE status = 'published' ORDER BY published_at DESC LIMIT :lim OFFSET :off",
            ['lim' => $limit, 'off' => $offset]
        );
    }

    public static function countPublished(): int {
        $row = Database::fetch("SELECT COUNT(*) as total FROM blog_posts WHERE status = 'published'");
        return (int)($row['total'] ?? 0);
    }

    public static function getFeatured(int $limit = 3): array {
        return Database::fetchAll(
            "SELECT * FROM blog_posts WHERE status = 'published' AND is_featured = 1 ORDER BY published_at DESC LIMIT :lim",
            ['lim' => $limit]
        );
    }

    public static function getByCategory(string $category, int $limit = 10): array {
        return Database::fetchAll(
            "SELECT * FROM blog_posts WHERE status = 'published' AND category = :cat ORDER BY published_at DESC LIMIT :lim",
            ['cat' => $category, 'lim' => $limit]
        );
    }

    public static function create(array $data): string {
        $sql = "INSERT INTO blog_posts (title, slug, excerpt, content, featured_image, category, author, status, is_featured, meta_title, meta_description, tags, published_at)
                VALUES (:title,:slug,:excerpt,:content,:featured_image,:category,:author,:status,:is_featured,:meta_title,:meta_description,:tags,:published_at)";
        return Database::insert($sql, [
            'title'            => $data['title'],
            'slug'             => self::slugify($data['slug'] ?: $data['title']),
            'excerpt'          => $data['excerpt'] ?? null,
            'content'          => $data['content'] ?? null,
            'featured_image'   => $data['featured_image'] ?? null,
            'category'         => $data['category'] ?? null,
            'author'           => $data['author'] ?? 'Équipe Digitalium',
            'status'           => $data['status'] ?? 'draft',
            'is_featured'      => (int)($data['is_featured'] ?? 0),
            'meta_title'       => $data['meta_title'] ?? $data['title'],
            'meta_description' => $data['meta_description'] ?? null,
            'tags'             => $data['tags'] ?? null,
            'published_at'     => ($data['status'] === 'published') ? date('Y-m-d H:i:s') : null,
        ]);
    }

    public static function update(int $id, array $data): bool {
        $publishedAt = '';
        $existing = self::find($id);
        if ($data['status'] === 'published' && empty($existing['published_at'])) {
            $publishedAt = ", published_at = NOW()";
        }
        $sql = "UPDATE blog_posts SET
                title = :title, slug = :slug, excerpt = :excerpt, content = :content,
                featured_image = :featured_image, category = :category, author = :author,
                status = :status, is_featured = :is_featured, meta_title = :meta_title,
                meta_description = :meta_description, tags = :tags{$publishedAt}
                WHERE id = :id";
        $stmt = Database::query($sql, [
            'title'            => $data['title'],
            'slug'             => self::slugify($data['slug'] ?: $data['title']),
            'excerpt'          => $data['excerpt'] ?? null,
            'content'          => $data['content'] ?? null,
            'featured_image'   => $data['featured_image'] ?? null,
            'category'         => $data['category'] ?? null,
            'author'           => $data['author'] ?? 'Équipe Digitalium',
            'status'           => $data['status'] ?? 'draft',
            'is_featured'      => (int)($data['is_featured'] ?? 0),
            'meta_title'       => $data['meta_title'] ?? $data['title'],
            'meta_description' => $data['meta_description'] ?? null,
            'tags'             => $data['tags'] ?? null,
            'id'               => $id,
        ]);
        return $stmt->rowCount() >= 0;
    }

    public static function slugify(string $text): string {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        return strtolower($text) ?: 'post';
    }
}
