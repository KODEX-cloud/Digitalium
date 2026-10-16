<?php
/**
 * Réalisations de démonstration -> cas types anonymes
 *
 * `seed_demo_content.php` a semé six réalisations dont le client commençait par
 * « Démo — » (Assurance Horizon, Polyclinique Les Palmiers…) : noms de clients,
 * années et deux résultats chiffrés INVENTÉS. Décision utilisateur : retirer le
 * mot « Démo » du site en gardant ces pages, mais sans qu'elles affirment quoi
 * que ce soit de faux. Elles deviennent des cas types :
 *
 *   client       nom inventé       -> type d'organisation (« Compagnie d'assurance »)
 *   year         2023 à 2025       -> vide : une date laisse croire à une livraison réelle
 *   impact       deux lignes chiffrées retirées (« divisé par deux »,
 *                « d'une semaine à une nuit ») ; le reste est qualitatif
 *   testimonial_* vidés par sécurité (aucun n'avait été semé)
 *
 * ── Garde-fous ──────────────────────────────────────────────────────────────
 *  - Seules les lignes dont le client commence ENCORE par « Démo » sont
 *    touchées : une fiche déjà réécrite en admin n'est jamais modifiée.
 *  - Opération unique, drapeau `demo_projects_anonymized_v1`.
 *  - Lignes d'origine sauvegardées en JSON dans `demo_projects_backup_v1`.
 */

define('SECURE_ACCESS', true);
define('ROOT_PATH', dirname(__DIR__));
require ROOT_PATH . '/config/config.php';
require ROOT_PATH . '/app/Services/Database.php';

spl_autoload_register(function ($class) {
    $sep = chr(92);
    $prefix = 'App' . $sep;
    $baseDir = ROOT_PATH . '/app/';
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) { return; }
    $file = $baseDir . str_replace($sep, DIRECTORY_SEPARATOR, substr($class, strlen($prefix))) . '.php';
    if (file_exists($file)) { require_once $file; }
});

use App\Models\Setting;
use App\Services\Database;

echo "=== RÉALISATIONS DE DÉMONSTRATION -> CAS TYPES ===\n";

const DRAPEAU = 'demo_projects_anonymized_v1';

try {
    if (Setting::getVal(DRAPEAU)) {
        echo "  Drapeau " . DRAPEAU . " déjà posé — rien à faire.\n=== TERMINÉ ===\n";
        exit(0);
    }
} catch (\Throwable $e) {
    echo "  ATTENTION drapeau illisible : " . $e->getMessage() . " — abandon.\n=== TERMINÉ ===\n";
    exit(0);
}

/** Type d'organisation par slug — le secteur s'affiche déjà à côté. */
$types = [
    'plateforme-gestion-sinistres'       => "Compagnie d'assurance",
    'dossier-patient-rendez-vous'        => 'Clinique privée',
    'suivi-flotte-tournees-livraison'    => 'Entreprise de logistique',
    'portail-scolarite-paiements'        => "Établissement d'enseignement supérieur",
    'tableau-bord-pilotage-financier'    => 'Groupe immobilier multi-entités',
    'automatisation-traitement-demandes' => 'Fondation',
];
/** Lignes de résultat chiffrées : inventées, donc retirées. */
$chiffrees = [
    'Délai moyen de traitement divisé par deux',
    "Consolidation ramenée d'une semaine à une nuit",
];

try {
    $colonnes = array_column(Database::fetchAll("SHOW COLUMNS FROM `projects`"), 'Field');
    $aTemoignage = in_array('testimonial_quote', $colonnes, true);

    $demos = Database::fetchAll(
        "SELECT * FROM projects WHERE client LIKE :p ORDER BY id",
        ['p' => 'Démo%']
    );
    if (empty($demos)) {
        echo "  Aucune réalisation au client « Démo… » — rien à faire.\n";
        Setting::setVal(DRAPEAU, '1');
        echo "=== TERMINÉ ===\n";
        exit(0);
    }

    Setting::setVal('demo_projects_backup_v1', json_encode($demos, JSON_UNESCAPED_UNICODE));
    echo "  " . count($demos) . " fiche(s) sauvegardée(s) dans demo_projects_backup_v1.\n";

    Database::beginTransaction();
    foreach ($demos as $p) {
        $slug = (string)$p['slug'];
        // Slug inconnu (fiche renommée) : on retire au moins le préfixe.
        $client = $types[$slug] ?? trim((string)preg_replace('/^D[ée]mo\s*[—–-]\s*/u', '', (string)$p['client']));

        $lignes = preg_split('/\r\n|\r|\n/', (string)($p['impact'] ?? '')) ?: [];
        $impact = implode("\n", array_values(array_filter(
            $lignes,
            static fn(string $l): bool => trim($l) !== '' && !in_array(trim($l), $chiffrees, true)
        )));

        $sql = "UPDATE projects SET client = :c, year = NULL, impact = :i"
             . ($aTemoignage ? ", testimonial_quote = NULL, testimonial_author = NULL, testimonial_role = NULL" : '')
             . " WHERE id = :id";
        Database::query($sql, ['c' => $client, 'i' => $impact, 'id' => (int)$p['id']]);

        $retirees = count(array_filter($lignes, fn($l) => trim($l) !== '')) - count(array_filter(explode("\n", $impact), fn($l) => trim($l) !== ''));
        echo "  [$slug] « {$p['client']} » -> « $client » · année retirée"
           . ($retirees ? " · $retirees résultat(s) chiffré(s) retiré(s)" : '') . "\n";
    }
    Database::commit();

    Setting::setVal(DRAPEAU, '1');
    echo "  Drapeau " . DRAPEAU . " posé — opération non rejouable.\n";
    try { \App\Services\Cache::clear(); echo "  Cache vidé.\n"; } catch (\Throwable $e) {}
} catch (\Throwable $e) {
    try { Database::rollBack(); } catch (\Throwable $e2) {}
    echo "  ERREUR : " . $e->getMessage() . "\n";
    echo "  Aucune fiche modifiée, drapeau NON posé — rejoué au prochain déploiement.\n";
}

echo "=== TERMINÉ ===\n";
