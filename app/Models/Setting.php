<?php
namespace App\Models;

use App\Services\Database;

class Setting extends Model {
    protected static string $table = 'settings';

    /**
     * Retrieve all settings in a single flat key-value array.
     */
    public static function getAll(): array {
        $settings = [];
        $raw = Database::fetchAll("SELECT setting_key, setting_value FROM " . static::$table);
        foreach ($raw as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }

    /**
     * Get a setting by key.
     */
    public static function getVal(string $key, mixed $default = null): mixed {
        $sql = "SELECT setting_value FROM " . static::$table . " WHERE setting_key = :key LIMIT 1";
        $row = Database::fetch($sql, ['key' => $key]);
        return $row ? $row['setting_value'] : $default;
    }

    /**
     * Set a setting value (inserts or updates).
     */
    public static function setVal(string $key, ?string $value): bool {
        $sql = "SELECT id FROM " . static::$table . " WHERE setting_key = :key LIMIT 1";
        $row = Database::fetch($sql, ['key' => $key]);

        if ($row) {
            $sql = "UPDATE " . static::$table . " SET setting_value = :value WHERE id = :id";
            Database::query($sql, ['value' => $value, 'id' => $row['id']]);
        } else {
            $sql = "INSERT INTO " . static::$table . " (setting_key, setting_value) VALUES (:key, :value)";
            Database::insert($sql, ['key' => $key, 'value' => $value]);
        }
        return true;
    }
}
