<?php
namespace App\Models;

use App\Services\Database;

class User extends Model {
    protected static string $table = 'users';

    /**
     * Find a user by their username.
     */
    public static function findByUsername(string $username): ?array {
        $sql = "SELECT * FROM " . static::$table . " WHERE username = :username LIMIT 1";
        return Database::fetch($sql, ['username' => $username]);
    }

    /**
     * Update a user's password.
     */
    public static function updatePassword(int $userId, string $hash): bool {
        $sql = "UPDATE " . static::$table . " SET password = :password WHERE id = :id";
        $stmt = Database::query($sql, [
            'password' => $hash,
            'id' => $userId
        ]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Create a new administrator user.
     */
    public static function createAdmin(string $username, string $email, string $password): string {
        $hash = password_hash($password, PASSWORD_ARGON2ID);
        $sql = "INSERT INTO " . static::$table . " (username, email, password) VALUES (:username, :email, :password)";
        return Database::insert($sql, [
            'username' => $username,
            'email' => $email,
            'password' => $hash
        ]);
    }
}
