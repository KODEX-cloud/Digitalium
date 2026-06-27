<?php
namespace App\System;

class AssetManager {
    private static array $requiredAssets = [
        'css/index.css' => 'Feuille de styles principale',
    ];

    public static function check(): array {
        $t      = DSMResult::timer();
        $errors = [];
        $found  = [];
        $stats  = [];

        foreach (self::$requiredAssets as $rel => $label) {
            $path = PUBLIC_PATH . '/assets/' . $rel;
            if (!file_exists($path)) {
                $errors[] = "Asset manquant: {$rel}";
            } else {
                $size    = filesize($path);
                $found[] = $rel;
                $stats[] = ['file' => $rel, 'size_bytes' => $size, 'size' => self::formatBytes($size)];
            }
        }

        // Count all assets
        $allCss  = glob(PUBLIC_PATH . '/assets/css/*.css')  ?: [];
        $allJs   = glob(PUBLIC_PATH . '/assets/js/*.js')    ?: [];
        $allImg  = glob(PUBLIC_PATH . '/assets/images/*')   ?: [];

        $totalSize = 0;
        foreach (array_merge($allCss, $allJs, $allImg) as $f) {
            if (is_file($f)) $totalSize += filesize($f);
        }

        $data = [
            'css_files'  => count($allCss),
            'js_files'   => count($allJs),
            'img_files'  => count($allImg),
            'total_size' => self::formatBytes($totalSize),
            'assets'     => $stats,
        ];

        if (!empty($errors)) {
            return DSMResult::error('Assets', count($errors) . " asset(s) manquant(s)", $errors, $data, DSMResult::elapsed($t));
        }

        $msg = count($allCss) . " CSS, " . count($allJs) . " JS, " . count($allImg) . " images (" . self::formatBytes($totalSize) . ")";
        return DSMResult::ok('Assets', $msg, $data, DSMResult::elapsed($t));
    }

    private static function formatBytes(int $bytes): string {
        if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024)    return round($bytes / 1024, 2)    . ' KB';
        return $bytes . ' B';
    }
}
