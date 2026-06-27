<?php
namespace App\Models;

use App\Services\Database;

class Comment extends Model {
    protected static string $table = 'blog_comments';

    public static function getForPost(int $postId, bool $approvedOnly = true): array {
        $sql = "SELECT * FROM blog_comments WHERE post_id = :pid";
        if ($approvedOnly) $sql .= " AND status = 'approved'";
        $sql .= " ORDER BY created_at ASC";
        return Database::fetchAll($sql, ['pid' => $postId]);
    }

    public static function countPending(): int {
        $row = Database::fetch("SELECT COUNT(*) as total FROM blog_comments WHERE status = 'pending'");
        return (int)($row['total'] ?? 0);
    }

    public static function approve(int $id): void {
        Database::query("UPDATE blog_comments SET status = 'approved' WHERE id = :id", ['id' => $id]);
    }

    public static function reject(int $id): void {
        Database::query("UPDATE blog_comments SET status = 'rejected' WHERE id = :id", ['id' => $id]);
    }

    public static function create(array $data): string {
        return Database::insert(
            "INSERT INTO blog_comments (post_id, author_name, author_email, content, status)
             VALUES (:post_id, :author_name, :author_email, :content, 'pending')",
            [
                'post_id'      => $data['post_id'],
                'author_name'  => $data['author_name'],
                'author_email' => $data['author_email'] ?? null,
                'content'      => $data['content'],
            ]
        );
    }

    public static function delete(int $id): void {
        Database::query("DELETE FROM blog_comments WHERE id = :id", ['id' => $id]);
    }

    public static function getAll(string $status = ''): array {
        if ($status) {
            return Database::fetchAll(
                "SELECT c.*, p.title as post_title FROM blog_comments c
                 LEFT JOIN blog_posts p ON p.id = c.post_id
                 WHERE c.status = :s ORDER BY c.created_at DESC",
                ['s' => $status]
            );
        }
        return Database::fetchAll(
            "SELECT c.*, p.title as post_title FROM blog_comments c
             LEFT JOIN blog_posts p ON p.id = c.post_id
             ORDER BY c.created_at DESC"
        );
    }
}
