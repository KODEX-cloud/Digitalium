<?php
/**
 * Contrôle statique des vues — classes non résolvables.
 *
 * ── Pourquoi ce contrôle existe ─────────────────────────────────────────────
 * Une vue est chargée par `require` depuis Controller::render(). Elle n'a pas
 * de déclaration de namespace : elle s'exécute donc dans le namespace GLOBAL.
 * Un nom de classe court n'y est résolu que si LA VUE ELLE-MÊME l'importe par
 * un `use` ; le `use` du contrôleur ne la suit pas.
 *
 * /admin/menus/edit/{id} a répondu 500 en production pour cette raison exacte.
 * Le contrôle de syntaxe du pipeline ne voit pas cette faute : elle n'existe
 * qu'à l'exécution, et seulement sur le chemin de code concerné (ici, un menu
 * comportant au moins un lien).
 *
 * ── Pourquoi le tokenizer, et pas une expression régulière ──────────────────
 * Une première version balayait le texte et signalait les mentions présentes
 * dans les COMMENTAIRES et le HTML. Le tokenizer de PHP distingue un appel réel
 * d'un mot écrit dans une phrase, et distingue un nom court (T_STRING) d'un nom
 * qualifié (T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED).
 *
 * Usage : php bin/check_views.php
 * Sortie : 0 si tout va bien, 1 si au moins une vue est fautive.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Ce script est réservé à la ligne de commande.\n");
}

$racine = dirname(__DIR__);
$vues   = $racine . '/app/Views';
$bs     = chr(92);

if (!is_dir($vues)) {
    echo "  Dossier des vues introuvable — contrôle ignoré.\n";
    exit(0);
}

/* Classes définies par le projet. Une classe du cœur de PHP (DateTime, PDO…)
   se résout sans problème dans le namespace global : hors sujet ici. */
$connues = [];
foreach (['Models', 'Helpers', 'Services', 'Controllers', 'System'] as $dossier) {
    foreach (glob($racine . "/app/$dossier/*.php") as $f) {
        $connues[basename($f, '.php')] = true;
    }
}

/** Jetons significatifs seulement : ni espaces, ni commentaires. */
function jetonsUtiles(string $src): array {
    $utiles = [];
    foreach (token_get_all($src) as $jeton) {
        if (is_array($jeton) && in_array($jeton[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
            continue;
        }
        $utiles[] = $jeton;
    }
    return $utiles;
}

$fautes = 0;
$fichiers = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($vues));

foreach ($fichiers as $fichier) {
    if ($fichier->getExtension() !== 'php') { continue; }

    $jetons = jetonsUtiles(file_get_contents($fichier->getPathname()));

    // 1. Classes importées par la vue elle-même.
    //    `function () use ($x)` porte le même mot-clé : on l'écarte en exigeant
    //    qu'un nom suive, et non une parenthèse.
    $importees = [];
    foreach ($jetons as $i => $jeton) {
        if (!is_array($jeton) || $jeton[0] !== T_USE) { continue; }
        $suivant = $jetons[$i + 1] ?? null;
        if (!is_array($suivant)) { continue; }
        if (!in_array($suivant[0], [T_STRING, T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED], true)) { continue; }
        $nom = ltrim($suivant[1], $bs);
        $pos = strrpos($nom, $bs);
        $importees[$pos === false ? $nom : substr($nom, $pos + 1)] = true;
    }

    // 2. Appels statiques sur un nom COURT (T_STRING suivi de « :: »).
    foreach ($jetons as $i => $jeton) {
        if (!is_array($jeton) || $jeton[0] !== T_STRING) { continue; }

        $suivant = $jetons[$i + 1] ?? null;
        $estDoubleDeuxPoints = is_array($suivant)
            ? $suivant[0] === T_DOUBLE_COLON
            : $suivant === '::';
        if (!$estDoubleDeuxPoints) { continue; }

        $nom = $jeton[1];
        if (isset($importees[$nom])) { continue; }
        if (!isset($connues[$nom]))  { continue; }

        $chemin = str_replace([$racine, $bs], ['', '/'], $fichier->getPathname());
        $chemin = ltrim($chemin, '/');
        echo "  ERREUR $chemin:{$jeton[2]} — « $nom:: » ne sera pas résolu (namespace global).\n";
        echo "         Qualifier pleinement, ou importer la classe dans la vue.\n";
        $fautes++;
    }
}

if ($fautes === 0) {
    echo "  OK — aucune vue ne référence une classe qu'elle ne peut pas résoudre.\n";
    exit(0);
}

echo "\n  $fautes appel(s) non résolvable(s). Chacun produira une erreur fatale à l'affichage.\n";
exit(1);
