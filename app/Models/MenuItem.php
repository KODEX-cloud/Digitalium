<?php
namespace App\Models;

use App\Services\Database;

class MenuItem extends Model {
    protected static string $table = 'menu_items';

    /**
     * Get all items for a menu, structured as a tree.
     */
    public static function getByMenu(int $menuId): array {
        return Database::fetchAll(
            "SELECT mi.*, p.slug as page_slug
             FROM menu_items mi
             LEFT JOIN pages p ON p.id = mi.page_id
             WHERE mi.menu_id = :menu_id
             ORDER BY mi.parent_id ASC, mi.sort_order ASC, mi.id ASC",
            ['menu_id' => $menuId]
        );
    }

    /**
     * Get tree-structured items for a menu (for frontend rendering).
     */
    public static function getTree(int $menuId): array {
        $flat = self::getByMenu($menuId);
        $tree = [];
        $indexed = [];

        foreach ($flat as $item) {
            $item['children'] = [];
            $indexed[$item['id']] = $item;
        }

        foreach ($indexed as $id => $item) {
            if ($item['parent_id'] && isset($indexed[$item['parent_id']])) {
                $indexed[$item['parent_id']]['children'][] = &$indexed[$id];
            } else {
                $tree[] = &$indexed[$id];
            }
        }

        return $tree;
    }

    /**
     * Get active top-level items for a menu (for simple nav rendering).
     */
    public static function getActiveByMenu(int $menuId): array {
        return Database::fetchAll(
            "SELECT mi.*, p.slug as page_slug
             FROM menu_items mi
             LEFT JOIN pages p ON p.id = mi.page_id
             WHERE mi.menu_id = :menu_id AND mi.is_active = 1
             ORDER BY mi.parent_id ASC, mi.sort_order ASC, mi.id ASC",
            ['menu_id' => $menuId]
        );
    }

    /**
     * Save all items for a menu atomically (replace strategy).
     */
    public static function saveForMenu(int $menuId, array $items): void {
        Database::beginTransaction();
        try {
            Database::query("DELETE FROM menu_items WHERE menu_id = :mid", ['mid' => $menuId]);
            foreach ($items as $idx => $item) {
                $url = $item['url'] ?? '';
                $pageId = !empty($item['page_id']) ? (int)$item['page_id'] : null;
                Database::query(
                    "INSERT INTO menu_items (menu_id, parent_id, page_id, label, url, target, icon, sort_order, is_active)
                     VALUES (:menu_id, :parent_id, :page_id, :label, :url, :target, :icon, :sort_order, :is_active)",
                    [
                        'menu_id'    => $menuId,
                        'parent_id'  => !empty($item['parent_id']) ? (int)$item['parent_id'] : null,
                        'page_id'    => $pageId,
                        'label'      => trim($item['label'] ?? ''),
                        'url'        => $url,
                        'target'     => in_array($item['target'] ?? '_self', ['_self', '_blank']) ? $item['target'] : '_self',
                        'icon'       => trim($item['icon'] ?? ''),
                        'sort_order' => (int)($item['sort_order'] ?? $idx),
                        'is_active'  => (int)($item['is_active'] ?? 1),
                    ]
                );
            }
            Database::commit();
        } catch (\Throwable $e) {
            Database::rollBack();
            throw $e;
        }
    }

    /**
     * Resolve the final URL of a menu item.
     */
    public static function resolveUrl(array $item): string {
        if (!empty($item['url'])) {
            return $item['url'];
        }
        if (!empty($item['page_slug'])) {
            return $item['page_slug'] === 'home' ? '/' : '/' . $item['page_slug'];
        }
        return '#';
    }
}
