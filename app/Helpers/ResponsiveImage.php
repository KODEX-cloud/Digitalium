<?php
namespace App\Helpers;

/**
 * Images responsives — balise <img> complète et variantes WebP.
 *
 * Constat (2026-11-05) : chaque gabarit écrivait sa balise à la main, sans
 * dimensions ni variantes. Les heroes de 2400 px partaient tels quels sur
 * mobile, et l'accueil chargeait un PNG de 1,5 Mo.
 *
 * Ce helper :
 *   - écrit `width`/`height` (dimensions réelles du fichier) : le navigateur
 *     réserve la place avant le chargement. La taille AFFICHÉE ne change pas :
 *     tous les emplacements fixent déjà leur boîte et `object-fit: cover`
 *     dans le CSS existant (vérifié par mesure avant/après) ;
 *   - propose `srcset` en WebP (480 à 2560 px) quand les variantes existent ;
 *   - calcule `sizes` à partir de la boîte d'affichage ET du ratio de l'image :
 *     en `cover`, une image panoramique dans une boîte haute s'affiche bien
 *     plus large que la boîte — sans cette correction le navigateur prendrait
 *     une variante trop petite et l'image serait floue ;
 *   - met `loading="lazy"` partout sauf sur le visuel prioritaire (hero).
 *
 * Les variantes sont créées au téléversement (MediaManager) et à chaque
 * déploiement (database/images_v1.php). L'AFFICHAGE ne génère jamais rien :
 * une variante absente est simplement omise du srcset. Les originaux ne sont
 * jamais modifiés ni supprimés.
 */
class ResponsiveImage {

    /** Largeurs des variantes, en pixels. */
    public const WIDTHS = [480, 768, 1080, 1440, 1920, 2560];

    /** Dossier des variantes (URL relative à /public). */
    public const DIR = '/assets/uploads/_rs';

    private const QUALITY    = 80;
    private const MAX_PIXELS = 40000000;   // au-delà, GD risque de saturer la mémoire
    private const TYPES      = ['jpg', 'jpeg', 'png', 'webp'];

    private static array $dimsCache = [];

    /**
     * Fichier local d'une valeur d'image telle que stockée en base
     * (« /assets/… », « assets/… » ou « /public/assets/… »). Null si la valeur
     * est externe, hors de /assets, ou si le fichier n'existe pas.
     */
    public static function localFile(string $src): ?string {
        $src = trim($src);
        if ($src === '' || preg_match('#^([a-z][a-z0-9+.-]*:|//)#i', $src)) { return null; }
        $path = '/' . ltrim((string)parse_url($src, PHP_URL_PATH), '/');
        if (str_starts_with($path, '/public/')) { $path = substr($path, 7); }
        if (!str_starts_with($path, '/assets/') || str_contains($path, '..')) { return null; }
        $file = PUBLIC_PATH . $path;
        return is_file($file) ? $file : null;
    }

    /** Chemin relatif à /public (« /assets/… »), ou null. */
    private static function relPath(string $src): ?string {
        $file = self::localFile($src);
        return $file ? substr(str_replace('\\', '/', $file), strlen(str_replace('\\', '/', PUBLIC_PATH))) : null;
    }

    /** [largeur, hauteur] du fichier, ou null. */
    public static function dimensions(string $src): ?array {
        $file = self::localFile($src);
        if (!$file) { return null; }
        if (!array_key_exists($file, self::$dimsCache)) {
            $info = @getimagesize($file);
            self::$dimsCache[$file] = ($info && $info[0] > 0 && $info[1] > 0) ? [(int)$info[0], (int)$info[1]] : null;
        }
        return self::$dimsCache[$file];
    }

    /** Extension en minuscules si le format est redimensionnable, sinon null. */
    private static function resizableExt(string $file): ?string {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        return in_array($ext, self::TYPES, true) ? $ext : null;
    }

    /**
     * Chemin (URL) de la variante de largeur $w. Le suffixe dépend du chemin
     * ET du poids de l'original : remplacer le fichier sous le même nom
     * produit de nouvelles variantes au lieu de servir les anciennes.
     */
    private static function variantUrl(string $rel, string $file, int $w): string {
        $name = preg_replace('/[^a-z0-9-]+/', '-', strtolower(pathinfo($rel, PATHINFO_FILENAME)));
        $hash = substr(md5($rel . '|' . (int)@filesize($file)), 0, 8);
        return self::DIR . '/' . trim(substr($name, 0, 80), '-') . '-' . $hash . '-' . $w . '.webp';
    }

    /**
     * Largeurs à produire pour un original : les paliers plus petits que lui,
     * plus sa pleine largeur en WebP si l'original n'est pas déjà en WebP.
     */
    private static function plannedWidths(string $ext, int $naturalW): array {
        $widths = array_values(array_filter(self::WIDTHS, fn($w) => $w < $naturalW));
        if ($ext !== 'webp') { $widths[] = $naturalW; }
        return $widths;
    }

    /** Variantes EXISTANTES : [largeur => url]. Jamais de génération ici. */
    public static function variants(string $src): array {
        $file = self::localFile($src);
        $dims = self::dimensions($src);
        if (!$file || !$dims || !($ext = self::resizableExt($file))) { return []; }
        $rel = self::relPath($src);
        $out = [];
        foreach (self::plannedWidths($ext, $dims[0]) as $w) {
            $url = self::variantUrl($rel, $file, $w);
            if (is_file(PUBLIC_PATH . $url)) { $out[$w] = $url; }
        }
        return $out;
    }

    /**
     * Crée les variantes manquantes d'une image. Renvoie le nombre de
     * fichiers créés. Ne lève jamais d'exception : une image illisible est
     * ignorée, l'affichage retombe alors sur l'original.
     */
    public static function generate(string $src): int {
        try {
            $file = self::localFile($src);
            $dims = self::dimensions($src);
            if (!$file || !$dims || !($ext = self::resizableExt($file))) { return 0; }
            if ($dims[0] * $dims[1] > self::MAX_PIXELS || !function_exists('imagewebp')) { return 0; }
            $rel = self::relPath($src);
            $todo = [];
            foreach (self::plannedWidths($ext, $dims[0]) as $w) {
                $url = self::variantUrl($rel, $file, $w);
                if (!is_file(PUBLIC_PATH . $url)) { $todo[$w] = PUBLIC_PATH . $url; }
            }
            if (!$todo) { return 0; }

            $img = match ($ext) {
                'jpg', 'jpeg' => @imagecreatefromjpeg($file),
                'png'         => @imagecreatefrompng($file),
                'webp'        => @imagecreatefromwebp($file),
            };
            if (!$img) { return 0; }
            if (!imageistruecolor($img)) { imagepalettetotruecolor($img); }

            $dir = PUBLIC_PATH . self::DIR;
            if (!is_dir($dir)) { @mkdir($dir, 0755, true); }

            $created = 0;
            foreach ($todo as $w => $dest) {
                $h = max(1, (int)round($dims[1] * $w / $dims[0]));
                $res = ($w === $dims[0]) ? $img : self::resize($img, $dims[0], $dims[1], $w, $h);
                if (!$res) { continue; }
                imagealphablending($res, false);
                imagesavealpha($res, true);
                // Écriture atomique : un visiteur ne reçoit jamais un fichier à moitié écrit.
                $tmp = $dest . '.tmp';
                if (@imagewebp($res, $tmp, self::QUALITY) && @rename($tmp, $dest)) { $created++; }
                @unlink($tmp);
                if ($res !== $img) { imagedestroy($res); }
            }
            imagedestroy($img);
            return $created;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Redimensionnement par imagecopyresampled, présent dans toutes les
     * versions de GD. imagescale() renvoie false sur l'hébergement de
     * production (constaté au déploiement du 2026-11-05 : seules les
     * variantes pleine taille, non redimensionnées, avaient été créées).
     */
    private static function resize($src, int $sw, int $sh, int $dw, int $dh) {
        $dst = imagecreatetruecolor($dw, $dh);
        if (!$dst) { return null; }
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagefill($dst, 0, 0, imagecolorallocatealpha($dst, 0, 0, 0, 127));
        if (!imagecopyresampled($dst, $src, 0, 0, 0, 0, $dw, $dh, $sw, $sh)) {
            imagedestroy($dst);
            return null;
        }
        return $dst;
    }

    /**
     * Attribut `sizes` d'une image en `object-fit: cover`.
     *
     * $rules : liste de [media query|null, largeur de la boîte, unité, ratio
     * hauteur/largeur de la boîte]. La largeur réellement occupée par l'image
     * est max(boîte, hauteur de boîte × ratio de l'image).
     */
    public static function sizes(?array $dims, array $rules): string {
        $ratio = $dims ? $dims[0] / $dims[1] : 0;
        $parts = [];
        foreach ($rules as [$media, $value, $unit, $boxAspect]) {
            $factor = $ratio > 0 ? max(1.0, $boxAspect * $ratio) : 1.0;
            $parts[] = ($media ? $media . ' ' : '') . (int)ceil($value * $factor) . $unit;
        }
        return implode(', ', $parts);
    }

    /** Valeur de srcset (variantes existantes + original WebP), ou chaîne vide. */
    private static function srcset(string $src, ?array $dims): string {
        $variants = self::variants($src);
        if (!$variants) { return ''; }
        $set = [];
        foreach ($variants as $w => $u) { $set[] = url($u) . ' ' . $w . 'w'; }
        // Un original WebP sert lui-même de plus grande variante.
        $file = self::localFile($src);
        if ($dims && $file && self::resizableExt($file) === 'webp' && !isset($variants[$dims[0]])) {
            $set[] = url($src) . ' ' . $dims[0] . 'w';
        }
        return implode(', ', $set);
    }

    /**
     * Balise <source> d'un <picture> (visuel alternatif pour une media query,
     * par exemple une version verticale pour mobile).
     */
    public static function source(string $src, string $media, array $rules): string {
        $e = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
        $dims = self::dimensions($src);
        $set = self::srcset($src, $dims);
        $html = '<source media="' . $e($media) . '" srcset="' . $e($set !== '' ? $set : url($src)) . '"';
        if ($set !== '') { $html .= ' sizes="' . $e(self::sizes($dims, $rules)) . '"'; }
        if ($dims) { $html .= ' width="' . $dims[0] . '" height="' . $dims[1] . '"'; }
        return $html . '>';
    }

    /**
     * Balise <img> complète.
     *
     * Options : alt (string), rules (voir sizes()) ou sizes (string),
     * priority (bool : visuel au-dessus de la ligne de flottaison),
     * class, style, attrs (attributs supplémentaires nom => valeur).
     */
    public static function img(string $src, array $o = []): string {
        $e = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
        $dims = self::dimensions($src);
        $a = ['src' => url($src)];

        $set = self::srcset($src, $dims);
        if ($set !== '') {
            $a['srcset'] = $set;
            $a['sizes']  = $o['sizes'] ?? (isset($o['rules']) ? self::sizes($dims, $o['rules']) : '100vw');
        }
        if ($dims) { $a['width'] = $dims[0]; $a['height'] = $dims[1]; }
        $a['alt'] = (string)($o['alt'] ?? '');
        if (!empty($o['class'])) { $a['class'] = $o['class']; }
        if (!empty($o['style'])) { $a['style'] = $o['style']; }
        if (!empty($o['priority'])) {
            $a['fetchpriority'] = 'high';
        } else {
            $a['loading'] = 'lazy';
        }
        $a['decoding'] = 'async';
        foreach (($o['attrs'] ?? []) as $k => $v) { $a[$k] = $v; }

        $html = '<img';
        foreach ($a as $k => $v) { $html .= ' ' . $k . '="' . $e($v) . '"'; }
        return $html . '>';
    }
}
