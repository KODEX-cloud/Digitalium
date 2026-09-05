<?php
namespace App\Models;

use App\Services\Database;

/**
 * Collaborateurs de Digitalium Group.
 *
 * ── Pourquoi une table dédiée et non des groupes de blocs ───────────────────
 * La section `team` existante est pilotée par des groupes répétables. Un groupe
 * porte bien `group_id` et `sort_order` — création, modification et ordre sont
 * donc couverts — mais AUCUN indicateur de publication : retirer un membre du
 * site obligerait à supprimer ses données. Le cahier des charges demande
 * explicitement « publié / dépublié », d'où cette table, calquée sur
 * `lab_products` : même séparation entre ce qui est saisi et ce qui est visible.
 *
 * ── Aucun collaborateur n'est semé par le code ──────────────────────────────
 * Le cahier des charges interdit d'inventer un membre d'équipe. La table part
 * donc VIDE, et la section bascule d'elle-même sur les pôles d'expertise tant
 * qu'aucun collaborateur réel n'a été saisi. Ce repli n'est pas un mode
 * dégradé : c'est l'état normal du site tant que l'équipe n'est pas publiée.
 */
class TeamMember extends Model {
    protected static string $table = 'team_members';

    /**
     * Pôles d'expertise — la même liste que le repli de la section, pour qu'un
     * membre saisi se range naturellement sous le pôle déjà affiché.
     */
    public const DEPARTEMENTS = [
        'engineering'    => 'Engineering',
        'ai_data'        => 'AI & Data',
        'infrastructure' => 'Infrastructure',
        'design'         => 'Design',
        'business'       => 'Business',
        'support'        => 'Support',
    ];

    /** Champs acceptés en écriture. */
    private const CHAMPS = [
        'name', 'role', 'department', 'bio', 'photo', 'linkedin', 'email',
        'sort_order', 'status',
    ];

    private static ?array $colonnes = null;

    /**
     * Colonnes réellement présentes. L'écriture s'y adapte : tant que la
     * migration n'a pas tourné, l'enregistrement reste possible et ignore les
     * champs absents au lieu d'échouer sur un SQL invalide.
     */
    private static function colonnesExistantes(): array {
        if (self::$colonnes === null) {
            try {
                $rows = Database::fetchAll("SHOW COLUMNS FROM " . static::$table);
                self::$colonnes = $rows ? array_column($rows, 'Field') : [];
            } catch (\Throwable $e) {
                self::$colonnes = [];
            }
        }
        return self::$colonnes;
    }

    public static function libelleDepartement(?string $dep): string {
        return self::DEPARTEMENTS[(string)$dep] ?? '';
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  LECTURE PUBLIQUE
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Collaborateurs visibles en ligne.
     *
     * L'échec de lecture est ATTRAPÉ et rendu comme « aucun membre » : si la
     * migration n'a pas encore tourné, la page doit afficher son repli, pas une
     * erreur fatale. Une page institutionnelle cassée coûte plus cher qu'une
     * section qui montre les pôles d'expertise.
     *
     * @param int $limit 0 = tous
     */
    public static function getPublic(int $limit = 0): array {
        // Borne plafonnée puis injectée en clair : PDO tourne sans émulation
        // des requêtes préparées, où MySQL refuse un paramètre lié dans LIMIT.
        $limite = ($limit > 0) ? ' LIMIT ' . max(1, min(200, $limit)) : '';
        try {
            return Database::fetchAll(
                "SELECT * FROM " . static::$table . " WHERE status = 'published'"
                . " ORDER BY sort_order ASC, id ASC" . $limite
            );
        } catch (\Throwable $e) {
            return [];
        }
    }

    /** Tous les collaborateurs, publiés ou non — pour l'administration. */
    public static function getAll(): array {
        try {
            return Database::fetchAll(
                "SELECT * FROM " . static::$table . " ORDER BY sort_order ASC, id ASC"
            );
        } catch (\Throwable $e) {
            return [];
        }
    }

    public static function compterPublies(): int {
        try {
            $r = Database::fetch(
                "SELECT COUNT(*) AS n FROM " . static::$table . " WHERE status = 'published'"
            );
            return (int)($r['n'] ?? 0);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  ÉCRITURE
    // ─────────────────────────────────────────────────────────────────────────

    private static function normaliser(array $d): array {
        $d['name']       = trim((string)($d['name'] ?? ''));
        $d['role']       = trim((string)($d['role'] ?? ''));
        $d['department'] = isset(self::DEPARTEMENTS[$d['department'] ?? '']) ? $d['department'] : '';
        $d['status']     = ($d['status'] ?? '') === 'published' ? 'published' : 'draft';
        $d['sort_order'] = max(0, min(9999, (int)($d['sort_order'] ?? 0)));

        // Une adresse LinkedIn saisie sans schéma produirait un lien relatif
        // pointant vers le site lui-même.
        $lien = trim((string)($d['linkedin'] ?? ''));
        if ($lien !== '' && !preg_match('~^https?://~i', $lien)) {
            $lien = 'https://' . ltrim($lien, '/');
        }
        $d['linkedin'] = $lien;

        $mail = trim((string)($d['email'] ?? ''));
        $d['email'] = ($mail !== '' && filter_var($mail, FILTER_VALIDATE_EMAIL)) ? $mail : '';

        unset($d['id']);
        return $d;
    }

    private static function retenir(array $d): array {
        $colonnes = self::colonnesExistantes();
        $out = [];
        foreach (self::CHAMPS as $champ) {
            if (!in_array($champ, $colonnes, true) || !array_key_exists($champ, $d)) { continue; }
            $out[$champ] = $d[$champ];
        }
        return $out;
    }

    public static function add(array $data): string {
        $valeurs = self::retenir(self::normaliser($data));
        if (!$valeurs) { return '0'; }
        $cols = array_keys($valeurs);
        return Database::insert(
            "INSERT INTO " . static::$table . " (`" . implode('`, `', $cols) . "`)"
            . " VALUES (:" . implode(', :', $cols) . ")",
            $valeurs
        );
    }

    public static function updateMember(int $id, array $data): bool {
        if (!self::find($id)) { return false; }
        $valeurs = self::retenir(self::normaliser($data));
        if (!$valeurs) { return false; }

        $sets = [];
        foreach (array_keys($valeurs) as $col) { $sets[] = "`$col` = :$col"; }
        $valeurs['id'] = $id;

        Database::query(
            "UPDATE " . static::$table . " SET " . implode(', ', $sets) . " WHERE id = :id",
            $valeurs
        );
        return true;
    }
}
