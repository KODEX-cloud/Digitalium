<?php
/**
 * Page /realisations — pilotée par le CMS.
 *
 * La page n'écrit aucun contenu : elle rend les sections actives de la page
 * CMS « realisations » exactement comme les autres pages du site, ce qui rend
 * le hero, les expertises et le CTA administrables (Règle #2).
 *
 * Les réalisations elles-mêmes viennent du module Réalisations, via la section
 * de type `projects_cms`.
 *
 * Repli : tant que la page CMS n'a pas de sections (fenêtre entre le
 * déploiement du code et l'exécution de la migration), on rend malgré tout la
 * grille des réalisations. La page ne peut donc jamais apparaître vide.
 */

if (!empty($sections)) {
    require APP_PATH . '/Views/frontend/section_renderer.php';
} else {
    $single = [];
    $groups = [];
    $sectionId = 0;
    require APP_PATH . '/Views/frontend/sections/projects_cms.php';
}
