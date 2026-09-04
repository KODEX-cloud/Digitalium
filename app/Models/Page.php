<?php
namespace App\Models;

use App\Services\Database;

class Page extends Model {
    protected static string $table = 'pages';

    /**
     * Find page by slug.
     */
    public static function findBySlug(string $slug): ?array {
        $sql = "SELECT * FROM " . static::$table . " WHERE slug = :slug LIMIT 1";
        return Database::fetch($sql, ['slug' => $slug]);
    }

    /**
     * Retrouve une page rattachée à un parent, par exemple
     * /solutions/software-platforms.
     *
     * Le rattachement est une donnée (`pages.parent_slug`), pas une convention
     * de nommage : une page enfant garde un slug court et lisible, et le couple
     * parent + slug identifie l'URL. Voir HomeController::doRenderPage, qui
     * redirige en 301 toute tentative d'accès à l'URL non imbriquée — une page
     * enfant n'a qu'une seule adresse, jamais deux (leçon DT-05).
     */
    public static function findChild(string $parentSlug, string $childSlug): ?array {
        if ($parentSlug === '' || $childSlug === '') { return null; }
        $sql = "SELECT * FROM " . static::$table . "
                WHERE slug = :child AND parent_slug = :parent LIMIT 1";
        return Database::fetch($sql, ['child' => $childSlug, 'parent' => $parentSlug]);
    }

    /** Pages rattachées à un parent, dans l'ordre d'affichage. */
    public static function childrenOf(string $parentSlug): array {
        if ($parentSlug === '') { return []; }
        return Database::fetchAll(
            "SELECT * FROM " . static::$table . "
             WHERE parent_slug = :parent AND status = 'published'
             ORDER BY sort_order ASC, id ASC",
            ['parent' => $parentSlug]
        ) ?: [];
    }

    /**
     * Create a new page.
     */
    public static function createPage(string $title, string $slug, string $metaTitle = '', string $metaDescription = '', string $status = 'draft'): string {
        $sql = "INSERT INTO " . static::$table . " (title, slug, meta_title, meta_description, status) 
                VALUES (:title, :slug, :meta_title, :meta_description, :status)";
        return Database::insert($sql, [
            'title' => $title,
            'slug' => self::slugify($slug),
            'meta_title' => $metaTitle ?: $title,
            'meta_description' => $metaDescription,
            'status' => $status
        ]);
    }

    /**
     * Update page metadata.
     */
    public static function updatePage(int $id, array $data): bool {
        $logPath = ROOT_PATH . '/storage/logs/app.log';
        $timestamp = date('Y-m-d H:i:s');
        error_log("[{$timestamp}] [Page::updatePage START] ID: {$id}\n", 3, $logPath);

        $sql = "UPDATE " . static::$table . " SET 
                title = :title, 
                slug = :slug, 
                meta_title = :meta_title, 
                meta_description = :meta_description, 
                status = :status,
                sort_order = :sort_order,
                in_navigation = :in_navigation,
                hero_title = :hero_title,
                hero_subtitle = :hero_subtitle,
                hero_image = :hero_image,
                hero_cta1_text = :hero_cta1_text,
                hero_cta1_url = :hero_cta1_url,
                hero_cta2_text = :hero_cta2_text,
                hero_cta2_url = :hero_cta2_url,
                hero_bg_color = :hero_bg_color,
                hero_effect = :hero_effect,
                hero_variant = :hero_variant,
                hero_image_layout = :hero_image_layout,
                hero_image_size = :hero_image_size,
                hero_badge = :hero_badge,
                hero_status = :hero_status,
                header_bg_mode = :header_bg_mode,
                header_opacity = :header_opacity,
                header_blur = :header_blur,
                header_shadow = :header_shadow,
                header_contrast_mode = :header_contrast_mode,
                logo_light = :logo_light,
                logo_dark = :logo_dark,
                logo_size = :logo_size,
                hero_layout_mode = :hero_layout_mode,
                hero_text_position = :hero_text_position,
                hero_text_alignment = :hero_text_alignment,
                hero_text_width = :hero_text_width,
                hero_overlay_opacity = :hero_overlay_opacity,
                hero_shadow_strength = :hero_shadow_strength,
                hero_image_mobile = :hero_image_mobile,
                responsive_settings = :responsive_settings,
                hero_features = :hero_features,
                hero_articles = :hero_articles,
                hero_title_size = :hero_title_size
                WHERE id = :id";
        
        try {
            $stmt = Database::query($sql, [
                'title' => $data['title'],
                'slug' => self::slugify($data['slug']),
                'meta_title' => $data['meta_title'] ?? $data['title'],
                'meta_description' => $data['meta_description'] ?? null,
                'status' => $data['status'] ?? 'draft',
                'sort_order' => $data['sort_order'] ?? 0,
                'in_navigation' => $data['in_navigation'] ?? 1,
                'hero_title' => $data['hero_title'] ?? null,
                'hero_subtitle' => $data['hero_subtitle'] ?? null,
                'hero_image' => $data['hero_image'] ?? null,
                'hero_cta1_text' => $data['hero_cta1_text'] ?? null,
                'hero_cta1_url' => $data['hero_cta1_url'] ?? null,
                'hero_cta2_text' => $data['hero_cta2_text'] ?? null,
                'hero_cta2_url' => $data['hero_cta2_url'] ?? null,
                'hero_bg_color' => $data['hero_bg_color'] ?? null,
                'hero_effect' => $data['hero_effect'] ?? 'particles',
                'hero_variant' => $data['hero_variant'] ?? 'hero_split_large_image',
                'hero_image_layout' => $data['hero_image_layout'] ?? 'right',
                'hero_image_size' => $data['hero_image_size'] ?? 'large',
                'hero_badge' => $data['hero_badge'] ?? null,
                'hero_status' => $data['hero_status'] ?? 1,
                'header_bg_mode' => $data['header_bg_mode'] ?? 'glass',
                'header_opacity' => $data['header_opacity'] ?? 0.65,
                'header_blur' => $data['header_blur'] ?? 20,
                'header_shadow' => $data['header_shadow'] ?? 'moyen',
                'header_contrast_mode' => $data['header_contrast_mode'] ?? 'default',
                'logo_light' => $data['logo_light'] ?? null,
                'logo_dark' => $data['logo_dark'] ?? null,
                'logo_size' => $data['logo_size'] ?? 38,
                'hero_layout_mode' => $data['hero_layout_mode'] ?? 'moyen',
                'hero_text_position' => $data['hero_text_position'] ?? 'centre',
                'hero_text_alignment' => $data['hero_text_alignment'] ?? 'center',
                'hero_text_width' => $data['hero_text_width'] ?? '100%',
                'hero_overlay_opacity' => $data['hero_overlay_opacity'] ?? 0.45,
                'hero_shadow_strength' => $data['hero_shadow_strength'] ?? 'moyen',
                'hero_image_mobile' => $data['hero_image_mobile'] ?? null,
                'responsive_settings' => $data['responsive_settings'] ?? null,
                'hero_features' => $data['hero_features'] ?? null,
                'hero_articles' => $data['hero_articles'] ?? null,
                'hero_title_size' => $data['hero_title_size'] ?? 'large',
                'id' => $id
            ]);
            error_log("[{$timestamp}] [Page::updatePage SUCCESS] Affected rows: " . $stmt->rowCount() . "\n", 3, $logPath);
            return true;
        } catch (\Throwable $e) {
            error_log("[{$timestamp}] [Page::updatePage ERROR] " . $e->getMessage() . "\n", 3, $logPath);
            throw $e;
        }
    }

    /**
     * Helper to slugify a string.
     */
    public static function slugify(string $text): string {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);
        return empty($text) ? 'n-a' : $text;
    }
}
