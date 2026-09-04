<?php
namespace App\Helpers;

/**
 * Métadonnées des champs de blocs — source de vérité UNIQUE.
 *
 * Avant ce helper, trois endroits déduisaient le type d'un champ chacun de leur
 * côté (la vue d'édition, le contrôleur, le script de seed) avec des règles
 * divergentes. Conséquence concrète : `image_ratio` et `image_max_width`
 * s'affichaient comme des sélecteurs de média dans l'admin, où l'on ne pouvait
 * donc pas saisir « 1300 / 400 ». Tout passe désormais par ici.
 *
 * Trois informations par clé :
 *   type()    — comment afficher le champ (text, textarea, image, link, select)
 *   label()   — un intitulé lisible, pas la clé technique
 *   help()    — une phrase expliquant à quoi sert le champ
 *   choices() — la liste des valeurs autorisées quand elles sont fermées
 */
class BlockFieldHelper {

    /**
     * Champs à choix fermé : rendus en liste déroulante, jamais en texte libre.
     * Format : clé => [valeur => libellé affiché].
     */
    private const CHOICES = [
        /* `layout` n'a pas les mêmes valeurs selon la section : proposer les
           cinq indistinctement afficherait des options sans effet. La liste
           est donc indexée par type de section. */
        'layout' => [
            'hero_media_cards' => [
                'split'   => 'Texte à gauche, visuel à droite',
                'banner'  => 'Texte en haut, visuel large dessous',
                'overlay' => 'Texte par-dessus le visuel',
            ],
            'problems_solutions' => [
                'stack' => 'Lecture verticale (constat puis réponse)',
                'row'   => 'Lecture horizontale (côte à côte)',
            ],
        ],
        'decor' => [
            '1' => 'Afficher les décors',
            '0' => 'Masquer les décors',
        ],
        'columns' => [
            '1' => '1 colonne',
            '2' => '2 colonnes',
            '3' => '3 colonnes',
            '4' => '4 colonnes',
        ],
        'show_filters' => [
            '1' => 'Afficher la barre de filtres',
            '0' => 'Masquer la barre de filtres',
        ],
        'show_form' => [
            '1' => 'Afficher le formulaire de contact',
            '0' => 'Masquer le formulaire (garder coordonnées et boutons)',
        ],
        'show_search' => [
            '1' => 'Afficher le champ de recherche',
            '0' => 'Masquer le champ de recherche',
        ],
        'fallback_latest' => [
            '1' => "Afficher le plus récent si aucun article n'est mis à la une",
            '0' => "N'afficher qu'un article explicitement mis à la une",
        ],
    ];

    /**
     * Intitulés et aides. Les clés absentes retombent sur un intitulé dérivé du
     * nom technique — jamais d'erreur, seulement moins d'explication.
     */
    private const FIELDS = [
        // ── Communs à la plupart des sections ──
        'tag'            => ['Pastille de section', "Petit texte en majuscules affiché au-dessus du titre. Laisser vide pour le masquer."],
        'title'          => ['Titre', "Titre principal de la section."],
        'subtitle'       => ['Sous-titre', "Phrase d'introduction sous le titre."],
        'text'           => ['Chapô', "Paragraphe d'introduction."],

        // ── Hero ──
        'badge'          => ['Pastille du hero', "Petit texte en majuscules au-dessus du titre."],
        'title_accent'   => ['Suite du titre (accent)', "Affichée sous le titre, en graisse légère et en couleur d'accent."],
        'cta1_text'      => ['Bouton principal — libellé', "Laisser vide pour masquer le bouton."],
        'cta1_url'       => ['Bouton principal — lien', "Chemin interne (/contact) ou ancre (#secteurs)."],
        'cta1_icon'      => ['Bouton principal — icône', "Nom d'icône Lucide, par exemple arrow-down ou send."],
        'cta2_text'      => ['Bouton secondaire — libellé', "Laisser vide pour masquer le bouton."],
        'cta2_url'       => ['Bouton secondaire — lien', "Chemin interne ou ancre."],
        'cta2_icon'      => ['Bouton secondaire — icône', "Nom d'icône Lucide."],
        'image'          => ['Visuel', "Choisi dans la Bibliothèque Média."],
        'image_alt'      => ['Texte alternatif du visuel', "Décrit l'image pour l'accessibilité et le référencement."],
        'decor'          => ['Décors du fond', "Cercle, courbe et trame de points derrière le hero."],
        'layout'         => ['Disposition', "Change la mise en page de la section sans toucher au contenu."],
        'image_max_width'=> ['Largeur du visuel', "En pixels sans unité (exemple : 1300), ou le mot « full » pour un visuel bord à bord sur toute la largeur de l'écran."],

        // ── Diapositives du hero en mode overlay ──
        'slide_image'    => ['Diapositive — visuel', "Choisi dans la Bibliothèque Média."],
        'slide_alt'      => ['Diapositive — texte alternatif', "Décrit l'image pour l'accessibilité."],
        'slide_badge'    => ['Diapositive — pastille', "Petit texte en majuscules au-dessus du titre."],
        'slide_title'    => ['Diapositive — titre', "Vider ce champ ET le visuel supprime la diapositive."],
        'slide_accent'   => ['Diapositive — suite du titre', "Affichée en graisse légère sous le titre."],
        'slide_text'     => ['Diapositive — texte', "Paragraphe sous le titre."],
        'image_ratio'    => ['Proportions du visuel', "Format « largeur / hauteur ». Exemple : 1300 / 400."],
        'image_ratio_mobile' => ['Proportions sur mobile', "Sous 760px. Un format très allongé donnerait une bande trop fine. Exemple : 16 / 9."],
        'image_radius'   => ['Arrondi des angles du visuel', "En pixels, sans unité. 0 pour des angles droits."],
        'overlay_opacity'=> ['Intensité du voile', "De 0 à 100. Plus la valeur est haute, plus la photo est assombrie et le texte lisible."],
        'overlay_min_height' => ['Hauteur minimale du visuel', "En pixels, sans unité. Exemple : 420."],

        // ── Problèmes / solutions ──
        'problem_label'  => ['Intitulé de la colonne « constat »', "Exemple : Situation."],
        'solution_label' => ['Intitulé de la colonne « réponse »', "Exemple : Réponse."],
        'columns'        => ['Nombre de colonnes', "Répartition des éléments. Passe automatiquement en une colonne sur mobile."],
        'ps_icon'        => ['Icône de la réponse', "Nom d'icône Lucide."],
        'ps_problem'     => ['Constat', "La situation rencontrée, formulée du point de vue du client."],
        'ps_solution'    => ['Réponse', "L'intitulé de la réponse apportée."],
        'ps_detail'      => ['Détail de la réponse', "Une ou deux phrases d'explication."],

        // ── Secteurs ──
        'sec_num'        => ['Numéro', "Affiché en filigrane sur la carte. Exemple : 01."],
        'sec_icon'       => ['Icône', "Nom d'icône Lucide. Ignorée si une image est choisie."],
        'sec_image'      => ['Image', "Remplace l'icône si elle est renseignée."],
        'sec_title'      => ['Nom du secteur', "Vider ce champ masque la carte."],
        'sec_desc'       => ['Description', "Deux à trois lignes."],
        'sec_needs'      => ['Besoins couverts', "Séparés par une barre verticale. Exemple : Gestion | Reporting | Sécurité."],
        'sec_link'       => ['Lien', "Chemin de destination."],
        'sec_link_text'  => ['Libellé du lien', "Exemple : Explorer."],

        // ── Réalisations (grille filtrable) ──
        'filter_all'     => ['Libellé du filtre « tout »', "Exemple : Tous."],
        'cta_text'       => ['Libellé du bouton d\'une carte', "Laisser vide pour ne pas afficher de bouton sur les cartes."],
        'show_filters'   => ['Barre de filtres', "Les filtres n'apparaissent que si au moins deux catégories sont réellement utilisées."],
        'empty_text'     => ['Message quand la liste est vide', "Affiché tant qu'aucun élément publié ne correspond. Laisser vide pour n'afficher aucun message."],
        'cat_value'      => ['Catégorie — valeur exacte', "Doit correspondre au mot saisi dans le champ Catégorie d'une réalisation."],
        'cat_label'      => ['Catégorie — libellé affiché', "Facultatif : par défaut, la valeur exacte est affichée."],

        // ── Formulaire de demande (lead_form) ──
        'step1_title'    => ['Étape 1 — titre', "Exemple : Votre besoin."],
        'step2_title'    => ['Étape 2 — titre', "Exemple : Votre organisation."],
        'step3_title'    => ['Étape 3 — titre', "Exemple : Le projet."],
        'step4_title'    => ['Étape 4 — titre', "Exemple : Validation."],
        'submit_text'    => ['Bouton d\'envoi', "Exemple : Envoyer ma demande."],
        'back_text'      => ['Bouton « précédent »', "Exemple : Retour."],
        'next_text'      => ['Bouton « suivant »', "Exemple : Continuer."],
        'success_title'  => ['Confirmation — titre', "Affiché après un envoi réussi."],
        'success_text'   => ['Confirmation — message', "Ce que le visiteur lit une fois sa demande envoyée."],
        'error_title'    => ['Erreur — titre', "Affiché en tête de la liste des champs à corriger."],
        'privacy_note'   => ['Mention sur les données', "Affichée avant le bouton d'envoi."],
        'file_note'      => ['Aide sous le champ fichier', "Exemple : PDF, Word ou image, 5 Mo maximum."],
        'besoin_label'   => ['Choix de besoin', "Une ligne = un bouton à l'étape 1. Vider ce champ retire le choix."],
        'besoin_icon'    => ['Choix de besoin — icône', "Nom d'icône Lucide. Facultatif."],
        'secteur_label'  => ['Secteur proposé', "Une ligne = une entrée de la liste « Secteur d'activité »."],
        'urgence_label'  => ['Niveau d\'urgence proposé', "Une ligne = une entrée de la liste « Urgence »."],
        'budget_label'   => ['Fourchette de budget proposée', "Une ligne = une entrée de la liste « Budget »."],

        // ── Aiguillage « Je veux… » ──
        'intro_label'    => ['Sur-titre de la liste', "Petit texte en majuscules au-dessus des besoins. Exemple : Je veux."],
        'need_icon'      => ['Icône', "Nom d'icône Lucide."],
        'need_text'      => ['Le besoin', "Formulé du point de vue du client. Vider ce champ masque la ligne."],
        'need_solution'  => ['La réponse', "La famille de solutions concernée. Affichée en petit sous le besoin."],
        'need_link'      => ['Destination', "Chemin interne, par exemple /solutions/ia-automatisation."],

        // ── Insights — article à la une ──
        'badge_label'    => ['Pastille sur le visuel', "Petit texte posé sur l'image, par exemple « À la une ». Vider pour le masquer."],
        'read_suffix'    => ['Unité de durée de lecture', "Affichée après le nombre de minutes. Exemple : min de lecture."],
        'fallback_latest'=> ['Si aucun article n\'est mis à la une', "La durée de lecture est calculée sur le texte de l'article quand elle n'est pas saisie."],

        // ── Insights — grille d'articles ──
        'show_search'    => ['Champ de recherche', "La recherche porte sur le titre, l'extrait, la catégorie et les tags."],
        'search_label'   => ['Intitulé de la recherche', "Affiché au-dessus du champ."],
        'search_placeholder' => ['Texte indicatif du champ de recherche', "Affiché dans le champ tant qu'il est vide."],
        'search_button'  => ['Bouton de recherche — libellé', ""],
        'per_page'       => ['Articles par page', "Entre 1 et 48. Défaut : 9."],
        'read_label'     => ['Lien de carte — libellé', "Affiché en bas de chaque article. Exemple : Lire."],
        'count_label'    => ['Compteur de résultats', "{n} est remplacé par le nombre trouvé. Exemple : {n} résultat(s)."],
        'reset_text'     => ['Lien de réinitialisation', "Ramène à la liste complète après un filtre ou une recherche."],

        // ── Insights — contenus stratégiques ──
        'read_text'      => ['Bouton par défaut — consultation', "Utilisé quand le contenu n'a pas de fichier téléchargeable."],
        'download_text'  => ['Bouton par défaut — téléchargement', "Utilisé quand un fichier est rattaché au contenu."],
        'type_value'     => ['Type — valeur enregistrée', "Doit correspondre exactement au type choisi sur l'article. Exemple : guide."],
        'type_label'     => ['Type — libellé affiché', "Ce que lit le visiteur. Exemple : Guide pratique."],
        'type_icon'      => ['Type — icône', "Nom d'icône Lucide."],

        // ── Newsletter ──
        'placeholder'    => ['Texte indicatif du champ email', "Affiché dans le champ tant qu'il est vide."],
        'button_text'    => ['Bouton — libellé', ""],
        'note'           => ['Mention sous le formulaire', "Fréquence d'envoi, désabonnement, usage de l'adresse."],

        // ── Aperçu de réalisations ──
        'limit'          => ['Nombre de réalisations affichées', "Laisser vide pour toutes les afficher. Exemple : 3 pour un aperçu."],
        'more_text'      => ['Bouton sous la grille — libellé', "Laisser vide pour masquer le bouton."],
        'more_url'       => ['Bouton sous la grille — lien', "Par défaut /realisations."],

        // ── Expertises ──
        'show_form'      => ['Formulaire de contact', "À masquer quand la page porte déjà un formulaire de demande : deux formulaires côte à côte brouillent le parcours."],
        'cap_icon'       => ['Icône', "Nom d'icône Lucide."],
        'cap_title'      => ['Intitulé', "Vider ce champ masque la carte."],
        'cap_desc'       => ['Description', "Une à deux lignes."],

        // ── Étapes ──
        'proc_num'       => ['Numéro d\'étape', "Exemple : 01."],
        'proc_icon'      => ['Icône', "Nom d'icône Lucide."],
        'proc_title'     => ['Titre de l\'étape', ""],
        'proc_desc'      => ['Description de l\'étape', ""],

        // ── Cartes flottantes du hero ──
        'card_icon'      => ['Icône', "Nom d'icône Lucide."],
        'card_label'     => ['Sur-titre', "Affiché en majuscules."],
        'card_badge'     => ['Pastille', "Petit libellé, par exemple « Actif »."],
        'card_value'     => ['Valeur', "Grand chiffre mis en avant."],
        'card_unit'      => ['Unité', "Affichée après la valeur."],
        'card_title'     => ['Titre', ""],
        'card_meta'      => ['Ligne secondaire', ""],
        'card_progress'  => ['Barre de progression', "De 0 à 100. Laisser vide pour ne pas l'afficher."],
        'card_avatar'    => ['Vignette ronde', ""],
        'card_top'       => ['Position verticale', "En pourcentage de la hauteur du visuel."],
        'card_left'      => ['Position horizontale', "En pourcentage de la largeur du visuel."],
    ];

    /** Préfixes retirés pour dériver un intitulé lisible d'une clé inconnue. */
    private const PREFIXES = ['card_', 'item_', 'member_', 'client_', 'faq_', 'post_', 'sec_', 'cap_', 'proc_', 'ps_', 'svc_', 'need_'];

    /**
     * Type de champ à afficher. Ordre des règles significatif : les réglages
     * dérivés d'une image (image_ratio, image_radius…) sont des valeurs
     * saisies, et doivent être testés AVANT la règle « image ».
     */
    public static function type(string $key, string $sectionType = ''): string {
        if (self::choices($key, $sectionType)) { return 'select'; }

        // Libellés courts : cta1_text, sec_link_text, more_text…
        // `slide_text` est un paragraphe, pas un libellé de bouton : il échappe
        // à la règle générale sur les clés en « _text ».
        // Paragraphes malgré leur suffixe « _text » : ce sont des messages
        // adressés au visiteur, pas des libellés de bouton.
        if (in_array($key, ['slide_text', 'success_text', 'empty_text'], true)) { return 'textarea'; }
        if (str_ends_with($key, '_text')) { return 'text'; }

        foreach (['_ratio', '_max_width', '_width', '_height', '_alt', '_position', '_radius', '_opacity'] as $suffix) {
            if (str_contains($key, $suffix)) { return 'text'; }
        }

        if (str_contains($key, 'image') || str_contains($key, 'avatar') || str_contains($key, 'logo')) { return 'image'; }
        if (str_contains($key, 'url') || str_contains($key, 'link')) { return 'link'; }

        $long = ['text', 'subtitle', 'content', 'description', 'contact_address',
                 'ps_problem', 'ps_solution', 'privacy_note', 'note'];
        if (in_array($key, $long, true)) { return 'textarea'; }
        foreach (['desc', 'quote', 'points', 'needs', 'detail', 'answer', 'summary'] as $needle) {
            if (str_contains($key, $needle)) { return 'textarea'; }
        }
        return 'text';
    }

    /** Intitulé lisible ; à défaut, dérivé du nom technique. */
    public static function label(string $key): string {
        if (isset(self::FIELDS[$key])) { return self::FIELDS[$key][0]; }
        $clean = str_replace(self::PREFIXES, '', $key);
        return ucfirst(str_replace('_', ' ', $clean));
    }

    /** Phrase d'aide, ou chaîne vide s'il n'y en a pas. */
    public static function help(string $key): string {
        return self::FIELDS[$key][1] ?? '';
    }

    /**
     * Valeurs autorisées pour un champ fermé, sinon tableau vide.
     *
     * Une entrée peut être soit une liste simple valeur => libellé, soit une
     * liste indexée par type de section. Dans ce second cas, un type inconnu
     * ne renvoie rien : le champ reste en saisie libre plutôt que d'exposer
     * des options qui n'auraient aucun effet.
     */
    public static function choices(string $key, string $sectionType = ''): array {
        $entry = self::CHOICES[$key] ?? null;
        if ($entry === null) { return []; }
        $first = reset($entry);
        if (is_array($first)) {
            return $entry[$sectionType] ?? [];
        }
        return $entry;
    }
}
