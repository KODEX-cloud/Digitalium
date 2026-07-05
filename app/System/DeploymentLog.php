<?php
namespace App\System;

/**
 * DeploymentLog — Journal persistant des déploiements.
 * Chaque déploiement est un fichier JSON dans storage/deployments/.
 */
class DeploymentLog {

    private const DIR       = ROOT_PATH . '/storage/deployments';
    private const MAX_LOGS  = 50;

    // ─── Enregistrement ──────────────────────────────────────────────────────

    public static function record(array $data): string {
        self::ensureDir();
        $id   = date('Y-m-d_H-i-s') . '_' . substr(md5(uniqid()), 0, 6);
        $path = self::DIR . "/{$id}.json";

        $entry = array_merge([
            'id'          => $id,
            'recorded_at' => date('Y-m-d H:i:s'),
            'php_sapi'    => PHP_SAPI,
        ], $data);

        file_put_contents($path, json_encode($entry, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        self::purgeOld();
        return $id;
    }

    // ─── Lecture ─────────────────────────────────────────────────────────────

    public static function getLatest(): ?array {
        $all = self::getAll(1);
        return $all[0] ?? null;
    }

    public static function getAll(int $limit = 20): array {
        self::ensureDir();
        $files = glob(self::DIR . '/*.json');
        if (!$files) return [];
        rsort($files);
        $results = [];
        foreach (array_slice($files, 0, $limit) as $file) {
            $data = json_decode(file_get_contents($file), true);
            if ($data) $results[] = $data;
        }
        return $results;
    }

    public static function getById(string $id): ?array {
        $path = self::DIR . "/{$id}.json";
        if (!file_exists($path)) return null;
        return json_decode(file_get_contents($path), true);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private static function ensureDir(): void {
        if (!is_dir(self::DIR)) {
            @mkdir(self::DIR, 0755, true);
        }
    }

    private static function purgeOld(): void {
        $files = glob(self::DIR . '/*.json');
        if (!$files || count($files) <= self::MAX_LOGS) return;
        rsort($files);
        foreach (array_slice($files, self::MAX_LOGS) as $old) {
            @unlink($old);
        }
    }
}
