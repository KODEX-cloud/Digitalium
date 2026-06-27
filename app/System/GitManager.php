<?php
namespace App\System;

/**
 * GitManager — Opérations Git pour le DSM.
 *
 * Compatible : HTTP (admin), CLI (bin/dsm_cli.php), Webhook, GitHub Actions.
 * Toutes les commandes exec() ciblent le ROOT_PATH.
 */
class GitManager {

    // ─── Informations du dépôt ───────────────────────────────────────────────
    public static function getInfo(): array {
        $t    = DSMResult::timer();
        $root = ROOT_PATH;

        $branch  = self::exec('git branch --show-current', $root);
        $commit  = self::exec('git rev-parse --short HEAD', $root);
        $message = self::exec('git log -1 --pretty=format:"%s"', $root);
        $author  = self::exec('git log -1 --pretty=format:"%an"', $root);
        $date    = self::exec('git log -1 --pretty=format:"%ai"', $root);
        $remote  = self::exec('git remote get-url origin', $root);
        $tag     = self::exec('git describe --tags --abbrev=0 2>NUL', $root);
        $status  = self::exec('git status --porcelain', $root);
        $ahead   = self::exec('git rev-list --count @{u}..HEAD 2>NUL', $root);
        $behind  = self::exec('git rev-list --count HEAD..@{u} 2>NUL', $root);
        $count   = self::exec('git rev-list --count HEAD', $root);

        $isDirty = !empty(trim($status));

        return [
            'branch'     => trim($branch ?: 'main'),
            'commit'     => trim($commit ?: 'unknown'),
            'message'    => trim($message ?: ''),
            'author'     => trim($author ?: ''),
            'date'       => trim(substr($date, 0, 10) ?: date('Y-m-d')),
            'remote'     => trim($remote ?: ''),
            'tag'        => trim($tag ?: ''),
            'dirty'      => $isDirty,
            'status'     => $isDirty ? 'dirty' : 'clean',
            'ahead'      => (int) trim($ahead ?: '0'),
            'behind'     => (int) trim($behind ?: '0'),
            'commits'    => (int) trim($count ?: '0'),
            'duration_ms'=> DSMResult::elapsed($t),
        ];
    }

    // ─── Health check Git ────────────────────────────────────────────────────
    public static function healthCheck(): array {
        $t    = DSMResult::timer();
        $info = self::getInfo();

        $errors   = [];
        $warnings = [];

        if ($info['commit'] === 'unknown') {
            $errors[] = 'Dépôt Git non initialisé ou inaccessible';
        }
        if ($info['dirty']) {
            $warnings[] = 'Changements non commités détectés';
        }
        if ($info['behind'] > 0) {
            $warnings[] = "{$info['behind']} commit(s) en retard sur le remote";
        }
        if ($info['ahead'] > 0) {
            $warnings[] = "{$info['ahead']} commit(s) locaux non pushés";
        }

        if (!empty($errors)) {
            return DSMResult::error('Git', implode(', ', $errors), $errors, $info, DSMResult::elapsed($t));
        }
        if (!empty($warnings)) {
            return DSMResult::warning('Git', implode(', ', $warnings), $info, $warnings, DSMResult::elapsed($t));
        }

        return DSMResult::ok('Git', "Branch: {$info['branch']} — Commit: {$info['commit']} — {$info['commits']} commits", $info, DSMResult::elapsed($t));
    }

    // ─── Commit + Push ───────────────────────────────────────────────────────
    public static function commitAndPush(string $message = '', array $files = []): array {
        $t    = DSMResult::timer();
        $root = ROOT_PATH;

        if (empty($message)) {
            $message = 'DSM Auto-deploy [' . date('Y-m-d H:i:s') . ']';
        }

        // Ajouter uniquement les fichiers sûrs (pas de .env, pas de binaires)
        $safeFiles = empty($files) ? self::getSafeFilesToAdd($root) : $files;

        if (empty($safeFiles)) {
            $statusOut = self::exec('git status --porcelain', $root);
            if (empty(trim($statusOut))) {
                return DSMResult::ok('Git:Commit', 'Aucun changement à commiter', [], DSMResult::elapsed($t));
            }
        }

        // Stage les fichiers
        if (!empty($safeFiles)) {
            foreach ($safeFiles as $f) {
                self::exec("git add " . escapeshellarg($f), $root);
            }
        } else {
            // Staged existants
        }

        // Vérifier s'il y a des staged changes
        $staged = self::exec('git diff --cached --name-only', $root);
        if (empty(trim($staged))) {
            return DSMResult::ok('Git:Commit', 'Aucun fichier staged — commit ignoré', [], DSMResult::elapsed($t));
        }

        // Commit
        $escapedMsg = str_replace('"', '\\"', $message);
        $commitOut  = self::exec("git commit -m \"{$escapedMsg}\n\nCo-Authored-By: DSM <dsm@digitaliumgroup.com>\"", $root);
        if (str_contains($commitOut, 'nothing to commit') || str_contains($commitOut, 'nothing added')) {
            return DSMResult::ok('Git:Commit', 'Rien à commiter', [], DSMResult::elapsed($t));
        }

        // Push
        $pushOut = self::exec('git push origin main', $root);
        $success = !str_contains(strtolower($pushOut), 'error') && !str_contains(strtolower($pushOut), 'fatal');

        if (!$success) {
            return DSMResult::warning('Git:Push', 'Commit OK — push échoué: ' . substr($pushOut, 0, 200), ['commit' => $commitOut, 'push' => $pushOut], [], DSMResult::elapsed($t));
        }

        $hash = trim(self::exec('git rev-parse --short HEAD', $root));
        return DSMResult::ok('Git:Commit+Push', "Commit {$hash} pushé — {$message}", [
            'hash'   => $hash,
            'branch' => 'main',
        ], DSMResult::elapsed($t));
    }

    // ─── Tag automatique ────────────────────────────────────────────────────
    public static function createTag(string $tag, string $message = ''): array {
        $t    = DSMResult::timer();
        $root = ROOT_PATH;

        if (empty($message)) {
            $message = "DSM Production Deploy — {$tag}";
        }

        // Vérifier si le tag existe déjà
        $existing = self::exec("git tag -l {$tag}", $root);
        if (!empty(trim($existing))) {
            return DSMResult::warning('Git:Tag', "Tag {$tag} existe déjà", ['tag' => $tag], [], DSMResult::elapsed($t));
        }

        $escapedMsg = str_replace('"', '\\"', $message);
        $out        = self::exec("git tag -a {$tag} -m \"{$escapedMsg}\"", $root);
        $pushOut    = self::exec("git push origin {$tag}", $root);

        return DSMResult::ok('Git:Tag', "Tag {$tag} créé et pushé", ['tag' => $tag, 'push' => $pushOut], DSMResult::elapsed($t));
    }

    // ─── Mise à jour CHANGELOG ───────────────────────────────────────────────
    public static function updateChangelog(string $version, string $description): array {
        $t        = DSMResult::timer();
        $path     = ROOT_PATH . '/CHANGELOG.md';
        $date     = date('Y-m-d');
        $entry    = "\n## [{$version}] — {$date}\n\n### DSM Auto-Deploy\n- {$description}\n- Pipeline orchestré par DeployPipeline\n";

        if (!file_exists($path)) {
            $content = "# CHANGELOG\n" . $entry;
        } else {
            $existing = file_get_contents($path);
            // Insérer après la première ligne de titre
            $content = preg_replace('/^(# CHANGELOG[^\n]*\n)/m', "$1{$entry}", $existing, 1);
            if ($content === $existing) {
                $content = $entry . $existing;
            }
        }

        $written = file_put_contents($path, $content);
        if ($written === false) {
            return DSMResult::error('Git:Changelog', 'Impossible d\'écrire dans CHANGELOG.md', [], [], DSMResult::elapsed($t));
        }

        return DSMResult::ok('Git:Changelog', "CHANGELOG mis à jour — version {$version}", ['path' => $path], DSMResult::elapsed($t));
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────
    private static function getSafeFilesToAdd(string $root): array {
        $safe = [
            'PROJECT_STATE.md',
            'CHANGELOG.md',
            'RELEASE_NOTES.md',
            'storage/reports',
        ];

        $result = [];
        foreach ($safe as $f) {
            $full = $root . '/' . $f;
            if (file_exists($full) || is_dir($full)) {
                $result[] = $full;
            }
        }
        return $result;
    }

    private static function exec(string $cmd, string $cwd): string {
        $output = [];
        $code   = 0;
        $oldDir = getcwd();

        if (is_dir($cwd)) {
            chdir($cwd);
        }

        exec($cmd . ' 2>&1', $output, $code);

        if (is_dir($oldDir)) {
            chdir($oldDir);
        }

        return implode("\n", $output);
    }
}
