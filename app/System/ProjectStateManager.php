<?php
namespace App\System;

class ProjectStateManager {
    private static string $stateFile = '';

    private static function path(): string {
        if (self::$stateFile === '') {
            self::$stateFile = ROOT_PATH . '/PROJECT_STATE.md';
        }
        return self::$stateFile;
    }

    /**
     * Update PROJECT_STATE.md after a deploy operation.
     */
    public static function update(array $deployResults = [], string $version = 'v1.0.0-enterprise'): array {
        $t    = DSMResult::timer();
        $file = self::path();

        if (!file_exists($file)) {
            return DSMResult::error('ProjectState', 'PROJECT_STATE.md introuvable', [$file], [], DSMResult::elapsed($t));
        }

        $content = file_get_contents($file);
        $git     = trim(@shell_exec('git -C ' . escapeshellarg(ROOT_PATH) . ' rev-parse --short HEAD 2>/dev/null') ?? '');
        $date    = date('Y-m-d');
        $now     = date('Y-m-d H:i:s');

        // Update "Dernière mise à jour" header
        $content = preg_replace(
            '/> Dernière mise à jour : .+/',
            '> Dernière mise à jour : ' . $now . ' — DSM Auto-update',
            $content,
            1
        );

        // Prepend new commit entry to the commits table
        if (!empty($git)) {
            $newRow = "| `{$git}` | DSM deploy automatique | {$date} | ❌ |";
            $content = preg_replace(
                '/(\| Hash \| Description \| Date \| Production \|\n\|---\|---\|---\|---\|\n)/',
                '$1' . $newRow . "\n",
                $content,
                1
            );
        }

        // Append a deploy log section if there are results
        if (!empty($deployResults)) {
            $okCount    = count(array_filter($deployResults, fn($r) => ($r['status'] ?? '') === 'ok'));
            $total      = count($deployResults);
            $score      = $total > 0 ? (int)round(array_sum(array_map(fn($r) => match($r['status'] ?? '') { 'ok' => 10, 'warning' => 5, default => 0 }, $deployResults)) / $total) : 0;

            $section  = "\n\n---\n\n## DERNIER DÉPLOIEMENT DSM\n\n";
            $section .= "**Date :** {$now}  \n";
            $section .= "**Commit :** `{$git}`  \n";
            $section .= "**Résultat :** {$okCount}/{$total} opérations OK — Score: {$score}/10  \n";

            // Remove previous deploy section to keep file clean
            $content = preg_replace('/\n\n---\n\n## DERNIER DÉPLOIEMENT DSM\n.*/s', '', $content);
            $content .= $section;
        }

        file_put_contents($file, $content);

        return DSMResult::ok('ProjectState', "PROJECT_STATE.md mis à jour — commit {$git}",
            ['git' => $git, 'date' => $now],
            DSMResult::elapsed($t)
        );
    }

    /**
     * Read current state as structured data.
     */
    public static function read(): array {
        $file    = self::path();
        $content = file_exists($file) ? file_get_contents($file) : '';

        preg_match('/> Dernière mise à jour : (.+)/', $content, $m);
        $lastUpdate = $m[1] ?? 'inconnue';

        preg_match('/ÉTAT GLOBAL : (.+)/', $content, $m2);
        $status = $m2[1] ?? 'inconnu';

        return [
            'last_update' => $lastUpdate,
            'status'      => $status,
            'file'        => $file,
            'size'        => file_exists($file) ? filesize($file) : 0,
        ];
    }
}
