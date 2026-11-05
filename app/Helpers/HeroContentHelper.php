<?php
namespace App\Helpers;

/**
 * Contenu éditorial des heroes `hero_media_cards` — règles UNIQUES.
 *
 * Trois responsabilités, partagées par l'admin, le contrôleur et le gabarit :
 *   CONTENT_KEYS   — les champs que l'administrateur rédige (le reste de la
 *                    section, ratio, opacité, largeur…, relève de la mise en page) ;
 *   validate()     — contrôle serveur avant enregistrement : longueur, lien,
 *                    type et poids de l'image ;
 *   withDefaults() — complète un champ laissé vide avec la valeur d'origine,
 *                    pour qu'un oubli en admin ne casse jamais le bandeau.
 *
 * Les valeurs d'origine ne sont PAS écrites ici (règle zéro hardcode) : elles
 * sont relevées en base par database/hero_content_v1.php et rangées dans le
 * paramètre `hero_defaults_v1` (JSON indexé par identifiant de section).
 */
class HeroContentHelper {

    /** Paramètre `settings` qui porte les valeurs d'origine. */
    public const DEFAULTS_SETTING = 'hero_defaults_v1';

    /**
     * Champs rédactionnels, dans l'ordre d'affichage de l'admin.
     * Longueur maximale en caractères (0 = pas de contrôle de longueur, le
     * champ image est contrôlé autrement).
     */
    public const CONTENT_KEYS = [
        'badge'        => 60,
        'title'        => 150,
        'title_accent' => 150,
        'text'         => 500,
        'image'        => 0,
        'image_alt'    => 180,
        'cta1_text'    => 40,
        'cta1_url'     => 255,
        'cta2_text'    => 40,
        'cta2_url'     => 255,
    ];

    /** Formats d'image acceptés pour le visuel du hero : extension => types MIME. */
    private const IMAGE_TYPES = [
        'jpg'  => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png'  => ['image/png'],
        'webp' => ['image/webp'],
    ];

    /** Poids maximal du visuel : un hero chargé en priorité doit rester léger. */
    public const IMAGE_MAX_BYTES = 5 * 1024 * 1024;

    /**
     * Contrôle les champs rédactionnels soumis. Les clés de mise en page ne
     * sont pas concernées. Renvoie la liste des erreurs, vide si tout est valide.
     *
     * @param array<string,string> $values clé => valeur soumise
     * @return string[]
     */
    public static function validate(array $values): array {
        $errors = [];
        foreach (self::CONTENT_KEYS as $key => $max) {
            if (!array_key_exists($key, $values)) { continue; }
            $value = trim((string)$values[$key]);
            if ($value === '') { continue; }   // vide = valeur d'origine au rendu
            $label = BlockFieldHelper::label($key, 'hero_media_cards');

            if ($max > 0 && mb_strlen($value, 'UTF-8') > $max) {
                $errors[] = "« $label » : $max caractères au maximum (" . mb_strlen($value, 'UTF-8') . " saisis).";
                continue;
            }
            if (str_ends_with($key, '_url') && !self::isValidLink($value)) {
                $errors[] = "« $label » : lien invalide. Attendu : chemin interne (/contact), ancre (#section) ou adresse https://…";
                continue;
            }
            if ($key === 'image') {
                $imageError = self::imageError($value);
                if ($imageError !== null) { $errors[] = "« $label » : $imageError"; }
            }
        }
        return $errors;
    }

    /**
     * Lien de bouton accepté : chemin interne, ancre, http(s), mailto ou tel.
     * Refuse tout autre schéma (javascript:, data:…) et les espaces.
     */
    public static function isValidLink(string $url): bool {
        if (preg_match('/\s/', $url)) { return false; }
        if ($url[0] === '#') { return (bool)preg_match('/^#[A-Za-z][\w\-]*$/', $url); }
        if ($url[0] === '/') { return !str_starts_with($url, '//'); }
        if (preg_match('/^(mailto|tel):[^\s]+$/i', $url)) { return true; }
        if (preg_match('/^https?:\/\//i', $url)) { return filter_var($url, FILTER_VALIDATE_URL) !== false; }
        return false;
    }

    /**
     * Le visuel doit être un fichier présent sous public/assets (Bibliothèque
     * Média), d'un format accepté et d'un poids raisonnable. Null si valide.
     */
    private static function imageError(string $path): ?string {
        $relative = '/' . ltrim(parse_url($path, PHP_URL_PATH) ?? '', '/');
        if (!str_starts_with($relative, '/assets/') || str_contains($relative, '..')) {
            return "choisissez une image de la Bibliothèque Média.";
        }
        $ext = strtolower(pathinfo($relative, PATHINFO_EXTENSION));
        if (!isset(self::IMAGE_TYPES[$ext])) {
            return "format non accepté (JPG, PNG ou WebP).";
        }
        $file = PUBLIC_PATH . $relative;
        if (!is_file($file)) {
            return "fichier introuvable sur le serveur.";
        }
        if (filesize($file) > self::IMAGE_MAX_BYTES) {
            return "image trop lourde (" . round(filesize($file) / 1048576, 1) . " Mo, 5 Mo au maximum).";
        }
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime  = $finfo ? finfo_file($finfo, $file) : false;
            if ($finfo) { finfo_close($finfo); }
            if ($mime !== false && !in_array($mime, self::IMAGE_TYPES[$ext], true)) {
                return "le contenu du fichier ne correspond pas à une image $ext.";
            }
        }
        return null;
    }

    /**
     * Complète les champs vides avec les valeurs d'origine de la section.
     *
     * Règles d'appariement, pour ne jamais produire un rendu incohérent :
     *   - titre vide : début ET fin du H1 reprennent l'origine (la fin seule,
     *     vide, est un choix légitime : un H1 sans partie en couleur) ;
     *   - texte alternatif vide : celui d'origine si le visuel est celui
     *     d'origine, sinon le H1 — jamais la description d'une autre image.
     *
     * @param array<string,mixed> $single   blocs simples de la section
     * @param array<string,mixed> $settings paramètres globaux (Setting::getAll)
     */
    public static function withDefaults(int $sectionId, array $single, array $settings): array {
        $all = json_decode((string)($settings[self::DEFAULTS_SETTING] ?? ''), true);
        $defaults = is_array($all) ? ($all[(string)$sectionId] ?? null) : null;
        if (!is_array($defaults)) { return $single; }

        $isEmpty = static fn(string $k): bool => trim((string)($single[$k] ?? '')) === '';

        if ($isEmpty('title')) {
            $single['title']        = (string)($defaults['title'] ?? '');
            $single['title_accent'] = (string)($defaults['title_accent'] ?? '');
        }
        foreach (['badge', 'text', 'cta1_text', 'cta1_url', 'cta2_text', 'cta2_url'] as $k) {
            if ($isEmpty($k) && isset($defaults[$k])) { $single[$k] = (string)$defaults[$k]; }
        }
        if ($isEmpty('image') && isset($defaults['image'])) {
            $single['image'] = (string)$defaults['image'];
        }
        if ($isEmpty('image_alt')) {
            $sameImage = trim((string)$single['image']) === trim((string)($defaults['image'] ?? ''));
            $single['image_alt'] = $sameImage && isset($defaults['image_alt'])
                ? (string)$defaults['image_alt']
                : trim(trim((string)($single['title'] ?? '')) . ' ' . trim((string)($single['title_accent'] ?? '')));
        }
        return $single;
    }

    /** Valeurs d'origine d'une section, ou tableau vide. Sert aux aides de l'admin. */
    public static function defaultsFor(int $sectionId, array $settings): array {
        $all = json_decode((string)($settings[self::DEFAULTS_SETTING] ?? ''), true);
        $d = is_array($all) ? ($all[(string)$sectionId] ?? []) : [];
        return is_array($d) ? $d : [];
    }
}
