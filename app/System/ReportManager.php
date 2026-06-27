<?php
namespace App\System;

class ReportManager {
    private static string $reportDir = '';

    private static function dir(): string {
        if (self::$reportDir === '') {
            self::$reportDir = ROOT_PATH . '/storage/reports';
        }
        if (!is_dir(self::$reportDir)) {
            mkdir(self::$reportDir, 0755, true);
        }
        return self::$reportDir;
    }

    /**
     * Generate and save a complete operation report.
     * Returns paths and a summary.
     */
    public static function generate(string $operationName, array $results, array $context = []): array {
        $t        = DSMResult::timer();
        $slug     = strtolower(preg_replace('/[^a-zA-Z0-9]/', '-', $operationName));
        $basename = date('Y-m-d-His') . '-' . $slug;

        $mdFile   = self::dir() . '/' . $basename . '.md';
        $jsonFile = self::dir() . '/' . $basename . '.json';

        $md   = self::buildMarkdown($operationName, $results, $context);
        $json = self::buildJson($operationName, $results, $context);

        file_put_contents($mdFile, $md);
        file_put_contents($jsonFile, $json);

        $okCount      = count(array_filter($results, fn($r) => ($r['status'] ?? '') === 'ok'));
        $warnCount    = count(array_filter($results, fn($r) => ($r['status'] ?? '') === 'warning'));
        $errorCount   = count(array_filter($results, fn($r) => ($r['status'] ?? '') === 'error'));
        $total        = count($results);

        $globalStatus = $errorCount > 0 ? 'error' : ($warnCount > 0 ? 'warning' : 'ok');

        return DSMResult::make(
            $globalStatus,
            'Rapport',
            "Rapport généré: {$basename} — {$okCount} OK, {$warnCount} WARNING, {$errorCount} ERROR / {$total} opérations",
            [
                'markdown_file' => $mdFile,
                'json_file'     => $jsonFile,
                'ok'            => $okCount,
                'warning'       => $warnCount,
                'error'         => $errorCount,
                'total'         => $total,
            ],
            [],
            DSMResult::elapsed($t)
        );
    }

    private static function buildMarkdown(string $name, array $results, array $context): string {
        $date  = date('Y-m-d H:i:s');
        $git   = trim(@shell_exec('git -C ' . escapeshellarg(ROOT_PATH) . ' rev-parse --short HEAD 2>/dev/null') ?? '');
        $score = self::globalScore($results);

        $md  = "# DSM Report — {$name}\n";
        $md .= "> Généré le {$date} | Commit: `{$git}` | Score: {$score}/100\n\n";
        $md .= "---\n\n";

        // Summary table
        $md .= "## Résumé\n\n";
        $md .= "| Opération | Statut | Message | Durée |\n";
        $md .= "|---|---|---|---|\n";

        foreach ($results as $r) {
            $icon   = match($r['status'] ?? 'error') { 'ok' => '✅', 'warning' => '⚠️', default => '❌' };
            $label  = htmlspecialchars($r['label'] ?? '?');
            $msg    = htmlspecialchars(mb_substr($r['message'] ?? '', 0, 80));
            $dur    = round($r['duration_ms'] ?? 0) . 'ms';
            $md    .= "| {$label} | {$icon} {$r['status']} | {$msg} | {$dur} |\n";
        }

        $md .= "\n---\n\n";

        // Details
        $md .= "## Détails\n\n";
        foreach ($results as $r) {
            $icon = match($r['status'] ?? 'error') { 'ok' => '✅', 'warning' => '⚠️', default => '❌' };
            $md  .= "### {$icon} {$r['label']}\n\n";
            $md  .= "**Statut :** `{$r['status']}`  \n";
            $md  .= "**Message :** {$r['message']}  \n";
            $md  .= "**Durée :** {$r['duration_ms']}ms  \n\n";

            if (!empty($r['errors'])) {
                $md .= "**Erreurs :**\n";
                foreach ($r['errors'] as $e) $md .= "- " . htmlspecialchars((string)$e) . "\n";
                $md .= "\n";
            }
        }

        $md .= "\n---\n\n";
        $md .= "*Digitalium System Manager — v1.0.0*\n";

        return $md;
    }

    private static function buildJson(string $name, array $results, array $context): string {
        $git = trim(@shell_exec('git -C ' . escapeshellarg(ROOT_PATH) . ' rev-parse --short HEAD 2>/dev/null') ?? '');
        return json_encode([
            'operation' => $name,
            'timestamp' => date('Y-m-d H:i:s'),
            'git_commit' => $git,
            'score'     => self::globalScore($results),
            'context'   => $context,
            'results'   => $results,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    private static function globalScore(array $results): int {
        $total  = count($results);
        if ($total === 0) return 0;
        $points = array_sum(array_map(fn($r) => match($r['status'] ?? 'error') {
            'ok'      => 10,
            'warning' => 5,
            default   => 0,
        }, $results));
        return (int)round($points / $total);
    }

    /**
     * List the latest reports.
     */
    public static function list(int $limit = 20): array {
        $files = array_merge(
            glob(self::dir() . '/*.md')   ?: [],
            glob(self::dir() . '/*.json') ?: []
        );
        usort($files, fn($a, $b) => filemtime($b) - filemtime($a));
        $files = array_slice($files, 0, $limit);

        return array_map(fn($f) => [
            'file' => basename($f),
            'date' => date('Y-m-d H:i:s', filemtime($f)),
            'size' => filesize($f),
            'type' => pathinfo($f, PATHINFO_EXTENSION),
        ], $files);
    }
}
