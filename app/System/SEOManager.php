<?php
namespace App\System;

use App\Services\Database;

class SEOManager {
    public static function audit(): array {
        $t = DSMResult::timer();
        $checks = [
            self::checkPagesMeta(),
            self::checkSitemap(),
            self::checkCanonical(),
        ];

        return DSMResult::aggregate('Audit SEO', $checks);
    }

    public static function checkPagesMeta(): array {
        $t = DSMResult::timer();

        try {
            $pages  = Database::fetchAll("SELECT id, slug, title, meta_title, meta_description FROM pages WHERE is_active = 1");
            $issues = [];

            foreach ($pages as $p) {
                if (empty($p['meta_title']))       $issues[] = "/{$p['slug']} — meta_title vide";
                if (empty($p['meta_description'])) $issues[] = "/{$p['slug']} — meta_description vide";
            }

            $total    = count($pages);
            $withIssues = intdiv(count($issues), 2); // 2 issues per page max

            if (!empty($issues)) {
                return DSMResult::warning('Pages SEO', "{$withIssues}/{$total} page(s) avec meta incomplète",
                    ['total' => $total, 'issues_count' => count($issues)], $issues, DSMResult::elapsed($t));
            }

            return DSMResult::ok('Pages SEO', "{$total}/{$total} pages avec meta_title et meta_description",
                ['total' => $total], DSMResult::elapsed($t));
        } catch (\Exception $e) {
            return DSMResult::error('Pages SEO', $e->getMessage(), [], [], DSMResult::elapsed($t));
        }
    }

    public static function checkSitemap(): array {
        $t        = DSMResult::timer();
        $sitemap  = PUBLIC_PATH . '/sitemap.xml';

        // The sitemap is generated dynamically — check the route exists
        $routes = file_get_contents(ROOT_PATH . '/routes/web.php');
        if (!str_contains($routes, 'sitemap.xml')) {
            return DSMResult::error('Sitemap', 'Route /sitemap.xml non définie dans routes/web.php', [], [], DSMResult::elapsed($t));
        }

        return DSMResult::ok('Sitemap',
            "/sitemap.xml — Route active, génération dynamique depuis la BDD",
            ['type' => 'dynamic', 'route' => '/sitemap.xml'],
            DSMResult::elapsed($t)
        );
    }

    public static function checkCanonical(): array {
        $t      = DSMResult::timer();
        $layout = APP_PATH . '/Views/frontend/layout.php';

        if (!file_exists($layout)) {
            return DSMResult::error('Canonical', 'layout.php introuvable', [$layout], [], DSMResult::elapsed($t));
        }

        $src = file_get_contents($layout);

        $hasTitle    = str_contains($src, '<title>');
        $hasDesc     = str_contains($src, 'meta_description') || str_contains($src, 'meta name="description"');
        $hasOg       = str_contains($src, 'og:');
        $hasCanon    = str_contains($src, 'canonical');

        $issues = [];
        if (!$hasTitle)   $issues[] = '<title> absent du layout';
        if (!$hasDesc)    $issues[] = 'meta description absent du layout';

        if (!empty($issues)) {
            return DSMResult::warning('Meta Tags', implode(', ', $issues), [], $issues, DSMResult::elapsed($t));
        }

        $features = array_filter(['title' => $hasTitle, 'description' => $hasDesc, 'og' => $hasOg, 'canonical' => $hasCanon]);
        return DSMResult::ok('Meta Tags',
            "title, meta description" . ($hasOg ? ", OG tags" : "") . ($hasCanon ? ", canonical" : ""),
            $features,
            DSMResult::elapsed($t)
        );
    }
}
