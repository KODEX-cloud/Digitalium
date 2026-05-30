<?php
namespace App\Models;

use App\Services\Database;
use PDO;

abstract class Model {
    protected static string $table;

    /**
     * Get the active PDO connection.
     */
    protected static function db(): PDO {
        return Database::getConnection();
    }

    /**
     * Find a record by primary key (id).
     */
    public static function find(int $id): ?array {
        $sql = "SELECT * FROM " . static::$table . " WHERE id = :id LIMIT 1";
        return Database::fetch($sql, ['id' => $id]);
    }

    /**
     * Get all records from the table.
     */
    public static function all(string $orderBy = 'id ASC'): array {
        $sql = "SELECT * FROM " . static::$table . " ORDER BY " . $orderBy;
        return Database::fetchAll($sql);
    }

    /**
     * Delete a record by primary key.
     */
    public static function delete(int $id): bool {
        $sql = "DELETE FROM " . static::$table . " WHERE id = :id";
        $stmt = Database::query($sql, ['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Count all rows in this table.
     */
    public static function count(): int {
        $sql = "SELECT COUNT(*) as total FROM " . static::$table;
        $row = Database::fetch($sql);
        return (int)($row['total'] ?? 0);
    }
}
