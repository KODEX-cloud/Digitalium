<?php
namespace App\Models;

use App\Services\Database;

class Section extends Model {
    protected static string $table = 'sections';

    /**
     * Get all sections for a page ordered by sort_order.
     */
    public static function getByPage(int $pageId): array {
        $sql = "SELECT * FROM " . static::$table . " WHERE page_id = :page_id ORDER BY sort_order ASC";
        return Database::fetchAll($sql, ['page_id' => $pageId]);
    }

    /**
     * Get all active sections for a page ordered by sort_order.
     */
    public static function getActiveByPage(int $pageId): array {
        $sql = "SELECT * FROM " . static::$table . " WHERE page_id = :page_id AND status = 'active' ORDER BY sort_order ASC";
        return Database::fetchAll($sql, ['page_id' => $pageId]);
    }

    /**
     * Add a new section to a page.
     */
    public static function addSection(int $pageId, string $name, string $type, int $sortOrder = 0): string {
        $sql = "INSERT INTO " . static::$table . " (page_id, name, type, sort_order) 
                VALUES (:page_id, :name, :type, :sort_order)";
        return Database::insert($sql, [
            'page_id' => $pageId,
            'name' => $name,
            'type' => $type,
            'sort_order' => $sortOrder
        ]);
    }

    /**
     * Reorder sections on a page.
     */
    public static function reorderSections(array $orderedIds): bool {
        Database::beginTransaction();
        try {
            foreach ($orderedIds as $index => $id) {
                $sql = "UPDATE " . static::$table . " SET sort_order = :sort_order WHERE id = :id";
                Database::query($sql, [
                    'sort_order' => $index,
                    'id' => (int)$id
                ]);
            }
            Database::commit();
            return true;
        } catch (\Exception $e) {
            Database::rollBack();
            return false;
        }
    }
}
