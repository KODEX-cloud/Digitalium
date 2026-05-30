<?php
namespace App\Models;

use App\Services\Database;

class Block extends Model {
    protected static string $table = 'blocks';

    /**
     * Get all raw blocks for a section.
     */
    public static function getBySection(int $sectionId): array {
        $sql = "SELECT * FROM " . static::$table . " WHERE section_id = :section_id ORDER BY sort_order ASC, id ASC";
        return Database::fetchAll($sql, ['section_id' => $sectionId]);
    }

    /**
     * Get structured block content for a section.
     */
    public static function getStructuredContent(int $sectionId): array {
        $rawBlocks = self::getBySection($sectionId);
        $structured = [
            'single' => [],
            'groups' => []
        ];

        foreach ($rawBlocks as $block) {
            $key = $block['block_key'];
            $value = $block['value'];
            $groupId = $block['group_id'];

            if ($groupId === null) {
                $structured['single'][$key] = $value;
            } else {
                if (!isset($structured['groups'][$groupId])) {
                    $structured['groups'][$groupId] = [
                        '_group_id' => $groupId,
                        '_sort_order' => $block['sort_order']
                    ];
                }
                $structured['groups'][$groupId][$key] = $value;
            }
        }

        usort($structured['groups'], function($a, $b) {
            return $a['_sort_order'] <=> $b['_sort_order'];
        });

        return $structured;
    }

    /**
     * Set/update a single block value.
     */
    public static function setVal(int $sectionId, string $key, string $type, ?string $value, ?int $groupId = null, int $sortOrder = 0): bool {
        if ($groupId === null) {
            $sql = "SELECT id FROM " . static::$table . " WHERE section_id = :section_id AND block_key = :block_key AND group_id IS NULL LIMIT 1";
            $row = Database::fetch($sql, ['section_id' => $sectionId, 'block_key' => $key]);
        } else {
            $sql = "SELECT id FROM " . static::$table . " WHERE section_id = :section_id AND block_key = :block_key AND group_id = :group_id LIMIT 1";
            $row = Database::fetch($sql, ['section_id' => $sectionId, 'block_key' => $key, 'group_id' => $groupId]);
        }

        if ($row) {
            $sql = "UPDATE " . static::$table . " SET value = :value, type = :type, sort_order = :sort_order WHERE id = :id";
            $stmt = Database::query($sql, [
                'value' => $value,
                'type' => $type,
                'sort_order' => $sortOrder,
                'id' => $row['id']
            ]);
            return true;
        } else {
            $sql = "INSERT INTO " . static::$table . " (section_id, block_key, type, value, group_id, sort_order) 
                    VALUES (:section_id, :block_key, :type, :value, :group_id, :sort_order)";
            Database::insert($sql, [
                'section_id' => $sectionId,
                'block_key' => $key,
                'type' => $type,
                'value' => $value,
                'group_id' => $groupId,
                'sort_order' => $sortOrder
            ]);
            return true;
        }
    }

    /**
     * Delete an entire repeatable group by group_id.
     */
    public static function deleteGroup(int $sectionId, int $groupId): bool {
        $sql = "DELETE FROM " . static::$table . " WHERE section_id = :section_id AND group_id = :group_id";
        $stmt = Database::query($sql, [
            'section_id' => $sectionId,
            'group_id' => $groupId
        ]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Get the next available group ID for a section.
     */
    public static function getNextGroupId(int $sectionId): int {
        $sql = "SELECT MAX(group_id) as max_group FROM " . static::$table . " WHERE section_id = :section_id";
        $row = Database::fetch($sql, ['section_id' => $sectionId]);
        return (int)($row['max_group'] ?? 0) + 1;
    }
}
