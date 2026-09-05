<?php
namespace App\Controllers;

use App\Services\Auth;
use App\Helpers\Validator;
use App\Models\Page;
use App\Models\Section;
use App\Models\Block;
use App\Models\Media;

class PageController extends Controller {
    /**
     * Enforce authentication.
     */
    protected function middlewareAuth(): void {
        if (!Auth::check()) {
            if (isset($_GET['ajax']) || $_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->json(['error' => 'Non autorisé'], 401);
            }
            $this->redirect('/admin/login', 'error', 'Veuillez vous connecter pour gérer les pages.');
        }
    }

    /**
     * List all pages.
     */
    public function index(): void {
        $this->middlewareAuth();
        $pages = Page::all('id DESC');

        $this->render('admin/pages/index', [
            'title' => 'Gestion des pages',
            'pages' => $pages,
            'csrf_token' => $this->generateCsrf()
        ], 'admin/layout');
    }

    /**
     * Page creation form.
     */
    public function createForm(): void {
        $this->middlewareAuth();
        $this->render('admin/pages/create', [
            'title' => 'Créer une nouvelle page',
            'csrf_token' => $this->generateCsrf()
        ], 'admin/layout');
    }

    /**
     * Handle page creation.
     */
    public function createSubmit(): void {
        $this->middlewareAuth();
        $this->validateCsrf();

        $validator = new Validator();
        $rules = [
            'title' => 'required',
            'slug' => 'required'
        ];

        if (!$validator->validate($_POST, $rules)) {
            $errors = $validator->getErrors();
            $this->redirect('/admin/pages/create', 'error', reset($errors));
        }

        $title = trim($_POST['title']);
        $slug = Page::slugify($_POST['slug']);
        $metaTitle = trim($_POST['meta_title'] ?? '') ?: $title;
        $metaDescription = trim($_POST['meta_description'] ?? '');
        $status = $_POST['status'] === 'published' ? 'published' : 'draft';

        if (Page::findBySlug($slug)) {
            $this->redirect('/admin/pages/create', 'error', "L'URL slug '{$slug}' existe déjà.");
        }

        try {
            $pageId = Page::createPage($title, $slug, $metaTitle, $metaDescription, $status);
            $this->proposerDansLaNavigation((int)$pageId);
            \App\Services\Cache::clear();
            $this->redirect("/admin/pages/edit/{$pageId}", 'success', "La page a été créée.");
        } catch (\Exception $e) {
            $this->redirect('/admin/pages/create', 'error', "Erreur : " . $e->getMessage());
        }
    }

    /**
     * Propose la page dans le menu principal — une seule fois.
     *
     * Appelée après la création ET après l'enregistrement d'une page : une
     * seule source de décision pour les deux écrans (Règle #1). Le caractère
     * unique tient au drapeau `pages.nav_seeded` : une fois la page proposée,
     * le menu fait autorité. Retirer le lien ne le fait pas revenir.
     *
     * Isolée : l'échec de cet agrément ne doit jamais empêcher l'enregistrement
     * d'une page. Au pire le lien manque, et il s'ajoute à la main.
     */
    private function proposerDansLaNavigation(int $pageId): void {
        try {
            $page = Page::find($pageId);
            if ($page) { \App\Models\MenuItem::semerPage($page); }
        } catch (\Throwable $e) {
            @file_put_contents(
                ROOT_PATH . '/storage/logs/errors.log',
                date('Y-m-d H:i:s') . " [PageController] navigation non semée pour la page $pageId — "
                . $e->getMessage() . "\n",
                FILE_APPEND | LOCK_EX
            );
        }
    }

    /**
     * Unified block builder workspace.
     */
    public function editForm(array $params): void {
        $this->middlewareAuth();

        $id = (int)($params['id'] ?? 0);
        $page = Page::find($id);

        if (!$page) {
            $this->redirect('/admin/pages', 'error', 'Page introuvable.');
        }

        $sections = Section::getByPage($id);
        
        $sectionBlocks = [];
        foreach ($sections as $sec) {
            $sectionBlocks[$sec['id']] = Block::getStructuredContent($sec['id']);
        }

        $mediaList = Media::all('id DESC');
        $slides = \App\Models\HeroSlide::getByPage($id);

        $this->render('admin/pages/edit', [
            'title' => "Édition : " . $page['title'],
            'page' => $page,
            'sections' => $sections,
            'sectionBlocks' => $sectionBlocks,
            'mediaList' => $mediaList,
            'slides' => $slides,
            'csrf_token' => $this->generateCsrf()
        ], 'admin/layout');
    }

    /**
     * Handle page metadata update.
     */
    public function editSubmit(array $params): void {
        $logPath = ROOT_PATH . '/storage/logs/app.log';
        $timestamp = date('Y-m-d H:i:s');
        error_log("[{$timestamp}] [PageController::editSubmit START] Params: " . json_encode($params) . "\n", 3, $logPath);

        $this->middlewareAuth();
        error_log("[{$timestamp}] [PageController::editSubmit] Auth middleware passed.\n", 3, $logPath);
        
        $this->validateCsrf();
        error_log("[{$timestamp}] [PageController::editSubmit] CSRF validation passed.\n", 3, $logPath);

        $id = (int)($params['id'] ?? 0);
        $page = Page::find($id);

        if (!$page) {
            error_log("[{$timestamp}] [PageController::editSubmit ERROR] Page ID {$id} not found.\n", 3, $logPath);
            $this->redirect('/admin/pages', 'error', 'Page introuvable.');
        }

        $validator = new Validator();
        $rules = [
            'title' => 'required',
            'slug' => 'required'
        ];

        if (!$validator->validate($_POST, $rules)) {
            $errs = $validator->getErrors();
            $err = reset($errs);
            error_log("[{$timestamp}] [PageController::editSubmit ERROR] Validation failed: {$err}\n", 3, $logPath);
            $this->redirect("/admin/pages/edit/{$id}", 'error', $err);
        }

        $slug = Page::slugify($_POST['slug']);
        $existing = Page::findBySlug($slug);
        if ($existing && (int)$existing['id'] !== $id) {
            error_log("[{$timestamp}] [PageController::editSubmit ERROR] Slug {$slug} is already taken by page ID " . $existing['id'] . ".\n", 3, $logPath);
            $this->redirect("/admin/pages/edit/{$id}", 'error', "L'URL slug '{$slug}' est déjà pris.");
        }

        $data = [
            'title' => trim($_POST['title']),
            'slug' => $slug,
            'meta_title' => trim($_POST['meta_title'] ?? ''),
            'meta_description' => trim($_POST['meta_description'] ?? ''),
            'status' => $_POST['status'] === 'published' ? 'published' : 'draft',
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
            'in_navigation' => (int)($_POST['in_navigation'] ?? 1),
            // Couleur d'accent propre à la page. Validée ici : une valeur non
            // conforme est enregistrée vide plutôt que d'atteindre la vue.
            'accent_color' => preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', trim($_POST['accent_color'] ?? ''))
                ? trim($_POST['accent_color'])
                : '',
            'hero_title' => trim($_POST['hero_title'] ?? ''),
            'hero_subtitle' => trim($_POST['hero_subtitle'] ?? ''),
            'hero_image' => trim($_POST['hero_image'] ?? ''),
            'hero_cta1_text' => trim($_POST['hero_cta1_text'] ?? ''),
            'hero_cta1_url' => trim($_POST['hero_cta1_url'] ?? ''),
            'hero_cta2_text' => trim($_POST['hero_cta2_text'] ?? ''),
            'hero_cta2_url' => trim($_POST['hero_cta2_url'] ?? ''),
            'hero_bg_color' => trim($_POST['hero_bg_color'] ?? ''),
            'hero_effect' => trim($_POST['hero_effect'] ?? 'particles'),
            'hero_variant' => trim($_POST['hero_variant'] ?? 'hero_split_large_image'),
            'hero_image_layout' => trim($_POST['hero_image_layout'] ?? 'right'),
            'hero_image_size' => trim($_POST['hero_image_size'] ?? 'large'),
            'hero_badge' => trim($_POST['hero_badge'] ?? ''),
            'hero_status' => (int)($_POST['hero_status'] ?? 0),
            'header_bg_mode' => trim($_POST['header_bg_mode'] ?? 'glass'),
            'header_opacity' => (float)($_POST['header_opacity'] ?? 0.65),
            'header_blur' => (int)($_POST['header_blur'] ?? 20),
            'header_shadow' => trim($_POST['header_shadow'] ?? 'moyen'),
            'header_contrast_mode' => trim($_POST['header_contrast_mode'] ?? 'default'),
            'logo_light' => trim($_POST['logo_light'] ?? ''),
            'logo_dark' => trim($_POST['logo_dark'] ?? ''),
            'logo_size' => (int)($_POST['logo_size'] ?? 38),
            'hero_layout_mode' => trim($_POST['hero_layout_mode'] ?? 'moyen'),
            'hero_text_position' => trim($_POST['hero_text_position'] ?? 'centre'),
            'hero_text_alignment' => trim($_POST['hero_text_alignment'] ?? 'center'),
            'hero_text_width' => trim($_POST['hero_text_width'] ?? '100%'),
            'hero_overlay_opacity' => (float)($_POST['hero_overlay_opacity'] ?? 0.45),
            'hero_shadow_strength' => trim($_POST['hero_shadow_strength'] ?? 'moyen'),
            'hero_image_mobile' => trim($_POST['hero_image_mobile'] ?? ''),
            'responsive_settings' => trim($_POST['responsive_settings'] ?? ''),
            'hero_features' => trim($_POST['hero_features'] ?? ''),
            'hero_articles' => trim($_POST['hero_articles'] ?? '')
        ];

        error_log("[{$timestamp}] [PageController::editSubmit] Form data prepared. Calling Page::updatePage()...\n", 3, $logPath);

        try {
            if (Page::updatePage($id, $data)) {
                $this->proposerDansLaNavigation($id);
                \App\Services\Cache::clear();
                error_log("[{$timestamp}] [PageController::editSubmit SUCCESS] Redirecting to edit page.\n", 3, $logPath);
                $this->redirect("/admin/pages/edit/{$id}", 'success', 'Enregistré avec succès.');
            } else {
                error_log("[{$timestamp}] [PageController::editSubmit INFO] No columns updated.\n", 3, $logPath);
                $this->redirect("/admin/pages/edit/{$id}", 'info', 'Aucune modification apportée.');
            }
        } catch (\Throwable $e) {
            error_log("[{$timestamp}] [PageController::editSubmit EXCEPTION] " . $e->getMessage() . " | file: " . $e->getFile() . " line: " . $e->getLine() . "\n", 3, $logPath);
            $this->redirect("/admin/pages/edit/{$id}", 'error', "Erreur de sauvegarde : " . $e->getMessage());
        }
    }

    /**
     * Delete page.
     */
    public function deletePage(array $params): void {
        $this->middlewareAuth();
        $this->validateCsrf();

        $id = (int)($params['id'] ?? 0);
        $page = Page::find($id);

        if (!$page) {
            $this->redirect('/admin/pages', 'error', 'Page introuvable.');
        }

        if ($page['slug'] === 'home') {
            $this->redirect('/admin/pages', 'error', 'Impossible de supprimer la page d\'accueil.');
        }

        if (Page::delete($id)) {
            \App\Services\Cache::clear();
            $this->redirect('/admin/pages', 'success', 'La page a été supprimée.');
        } else {
            $this->redirect('/admin/pages', 'error', 'Une erreur est survenue.');
        }
    }

    /**
     * AJAX API: Add section to a page.
     */
    public function addSection(): void {
        $this->middlewareAuth();
        $this->validateCsrf();

        $pageId = (int)($_POST['page_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $type = trim($_POST['type'] ?? '');

        if ($pageId <= 0 || empty($name) || empty($type)) {
            $this->json(['error' => 'Champs requis manquants.'], 400);
        }

        $sections = Section::getByPage($pageId);
        $nextOrder = count($sections) > 0 ? (end($sections)['sort_order'] + 1) : 0;

        $sectionId = Section::addSection($pageId, $name, $type, $nextOrder);

        if ($sectionId) {
            $this->seedDefaultSectionBlocks((int)$sectionId, $type);
            \App\Services\Cache::clear();

            $this->json([
                'success' => true,
                'message' => 'Section ajoutée avec succès !',
                'section_id' => $sectionId
            ]);
        } else {
            $this->json(['error' => 'Échec d\'ajout de la section.'], 500);
        }
    }

    /**
     * AJAX API: Reorder sections.
     */
    public function sortSections(): void {
        $this->middlewareAuth();
        $this->validateCsrf();

        $ids = $_POST['ids'] ?? [];
        if (empty($ids) || !is_array($ids)) {
            $this->json(['error' => 'Ordre de sections non fourni.'], 400);
        }

        if (Section::reorderSections($ids)) {
            \App\Services\Cache::clear();
            $this->json(['success' => true, 'message' => 'Ordre enregistré.']);
        } else {
            $this->json(['error' => 'Échec de tri.'], 500);
        }
    }

    /**
     * AJAX API: Delete section.
     */
    public function deleteSection(): void {
        $this->middlewareAuth();
        $this->validateCsrf();

        $sectionId = (int)($_POST['section_id'] ?? 0);
        if ($sectionId <= 0) {
            $this->json(['error' => 'Section invalide.'], 400);
        }

        if (Section::delete($sectionId)) {
            \App\Services\Cache::clear();
            $this->json(['success' => true, 'message' => 'Section supprimée.']);
        } else {
            $this->json(['error' => 'Impossible de supprimer la section.'], 500);
        }
    }

    /**
     * AJAX API: activer / désactiver une section sans la supprimer.
     *
     * Une section inactive n'est plus rendue sur le site (Section::getActiveByPage
     * filtre sur status = 'active') mais conserve tout son contenu : c'est le
     * moyen sûr de retirer un bloc d'une page, par opposition à la suppression
     * qui détruit les blocs (Règle #4).
     */
    public function toggleSection(): void {
        $this->middlewareAuth();
        $this->validateCsrf();

        $sectionId = (int)($_POST['section_id'] ?? 0);
        if ($sectionId <= 0) {
            $this->json(['error' => 'Section invalide.'], 400);
        }

        $section = \App\Services\Database::fetch(
            "SELECT id, status FROM sections WHERE id = :id",
            ['id' => $sectionId]
        );
        if (!$section) {
            $this->json(['error' => 'Section introuvable.'], 404);
        }

        $next = (($section['status'] ?? 'active') === 'active') ? 'inactive' : 'active';
        \App\Services\Database::query(
            "UPDATE sections SET status = :s WHERE id = :id",
            ['s' => $next, 'id' => $sectionId]
        );
        \App\Services\Cache::clear();

        $this->json([
            'success' => true,
            'status'  => $next,
            'message' => $next === 'active' ? 'Section activée.' : 'Section désactivée (contenu conservé).'
        ]);
    }

    /**
     * AJAX API: Update section blocks.
     */
    public function updateBlocks(): void {
        $this->middlewareAuth();
        $this->validateCsrf();

        $sectionId = (int)($_POST['section_id'] ?? 0);
        $blocks = $_POST['blocks'] ?? [];

        if ($sectionId <= 0 || !is_array($blocks)) {
            $this->json(['error' => 'Champs invalides.'], 400);
        }

        foreach ($blocks as $key => $data) {
            $val = $data['value'] ?? '';
            $type = $data['type'] ?? 'text';
            $groupId = isset($data['group_id']) ? (int)$data['group_id'] : null;
            $sortOrder = isset($data['sort_order']) ? (int)$data['sort_order'] : 0;
            
            Block::setVal($sectionId, $key, $type, $val, $groupId, $sortOrder);
        }
        \App\Services\Cache::clear();

        $this->json(['success' => true, 'message' => 'Modifications de la section enregistrées !']);
    }

    /**
     * AJAX API: Add a repeatable group.
     */
    public function addGroup(): void {
        $this->middlewareAuth();
        $this->validateCsrf();

        $sectionId = (int)($_POST['section_id'] ?? 0);
        $type = trim($_POST['type'] ?? '');

        if ($sectionId <= 0 || empty($type)) {
            $this->json(['error' => 'Données invalides.'], 400);
        }

        $nextGroupId = Block::getNextGroupId($sectionId);

        $this->seedDefaultSectionGroupBlocks($sectionId, $type, $nextGroupId);
        \App\Services\Cache::clear();

        $this->json([
            'success' => true,
            'message' => 'Élément ajouté !',
            'group_id' => $nextGroupId
        ]);
    }

    /**
     * AJAX API: Delete a repeatable group.
     */
    public function deleteGroup(): void {
        $this->middlewareAuth();
        $this->validateCsrf();

        $sectionId = (int)($_POST['section_id'] ?? 0);
        $groupId = (int)($_POST['group_id'] ?? 0);

        if ($sectionId <= 0 || $groupId <= 0) {
            $this->json(['error' => 'Paramètres invalides.'], 400);
        }

        if (Block::deleteGroup($sectionId, $groupId)) {
            \App\Services\Cache::clear();
            $this->json(['success' => true, 'message' => 'Élément supprimé avec succès.']);
        } else {
            $this->json(['error' => 'Aucun élément supprimé.'], 404);
        }
    }

    /**
     * Seeds default blocks for section types.
     */
    private function seedDefaultSectionBlocks(int $sectionId, string $type): void {
        switch ($type) {
            case 'hero':
                Block::setVal($sectionId, 'badge', 'text', 'Innovation');
                Block::setVal($sectionId, 'title', 'wysiwyg', '<h1>Votre transformation digitale commence ici</h1>');
                Block::setVal($sectionId, 'subtitle', 'textarea', 'Nous concevons des solutions logicielles sur mesure.');
                Block::setVal($sectionId, 'cta_text', 'text', 'Commencer mon projet');
                Block::setVal($sectionId, 'cta_url', 'link', '#contact');
                Block::setVal($sectionId, 'cta2_text', 'text', 'En savoir plus');
                Block::setVal($sectionId, 'cta2_url', 'link', '#about');
                Block::setVal($sectionId, 'bg_image', 'image', '');
                Block::setVal($sectionId, 'visual_label', 'text', 'Digital Innovation');
                Block::setVal($sectionId, 'stats_years', 'text', '10+');
                Block::setVal($sectionId, 'stats_clients', 'text', '100+');
                Block::setVal($sectionId, 'stats_satisfaction', 'text', '98%');
                Block::setVal($sectionId, 'stats_label_years', 'text', 'Expérience');
                Block::setVal($sectionId, 'stats_label_clients', 'text', 'Clients');
                Block::setVal($sectionId, 'stats_label_satisfaction', 'text', 'Satisfaction');
                break;

            case 'services':
                Block::setVal($sectionId, 'title', 'text', 'Nos Services Premium');
                Block::setVal($sectionId, 'subtitle', 'textarea', 'Une expertise pointue pour des résultats concrets.');
                $this->seedDefaultSectionGroupBlocks($sectionId, 'services', 1);
                break;

            case 'portfolio':
                Block::setVal($sectionId, 'title', 'text', 'Nos Réalisations');
                Block::setVal($sectionId, 'subtitle', 'textarea', 'Découvrez les projets récents.');
                $this->seedDefaultSectionGroupBlocks($sectionId, 'portfolio', 1);
                break;

            case 'team':
                Block::setVal($sectionId, 'title', 'text', 'Notre Équipe d\'Experts');
                Block::setVal($sectionId, 'subtitle', 'textarea', 'Des passionnés dévoués.');
                $this->seedDefaultSectionGroupBlocks($sectionId, 'team', 1);
                break;

            case 'testimonials':
                Block::setVal($sectionId, 'title', 'text', 'Ce Que Disent Nos Clients');
                Block::setVal($sectionId, 'subtitle', 'textarea', 'La satisfaction de nos partenaires.');
                $this->seedDefaultSectionGroupBlocks($sectionId, 'testimonials', 1);
                break;

            case 'faq':
                Block::setVal($sectionId, 'title', 'text', 'Questions Fréquentes');
                Block::setVal($sectionId, 'subtitle', 'textarea', 'Trouvez des réponses.');
                $this->seedDefaultSectionGroupBlocks($sectionId, 'faq', 1);
                break;

            case 'blog':
                Block::setVal($sectionId, 'title', 'text', 'Actualités & Insights');
                Block::setVal($sectionId, 'subtitle', 'textarea', 'Restez informé.');
                $this->seedDefaultSectionGroupBlocks($sectionId, 'blog', 1);
                break;

            case 'contact':
                Block::setVal($sectionId, 'title', 'text', 'Discutons de Votre Projet');
                Block::setVal($sectionId, 'subtitle', 'textarea', 'Prêt à franchir le pas ?');
                Block::setVal($sectionId, 'contact_email', 'text', 'contact@digitaliumgroup.com');
                Block::setVal($sectionId, 'contact_phone', 'text', '+33 1 23 45 67 89');
                Block::setVal($sectionId, 'contact_address', 'textarea', 'Paris, France');
                Block::setVal($sectionId, 'cta_label', 'text', 'Envoyer');
                break;

            // ── Modèles du design système v2 ────────────────────────────────
            // Chaque type crée ses clés VIDES : l'éditeur de blocs étant
            // générique, l'administrateur voit immédiatement tous les champs
            // disponibles et les remplit lui-même. Aucun texte de démonstration
            // n'est injecté en base (Règle #2 : pas de contenu écrit par le code).
            default:
                $skeletons = self::sectionSkeletons();
                if (!isset($skeletons[$type])) {
                    break;
                }
                [$singles, $groupKeys, $groupCount] = $skeletons[$type];
                foreach ($singles as $i => $key) {
                    Block::setVal($sectionId, $key, self::guessBlockType($key), '', null, $i);
                }
                for ($g = 1; $g <= $groupCount; $g++) {
                    foreach ($groupKeys as $i => $key) {
                        Block::setVal($sectionId, $key, self::guessBlockType($key), '', $g, $g - 1);
                    }
                }
                break;
        }
    }

    /**
     * Champs attendus par chaque modèle du design système v2.
     * Format : type => [clés uniques, clés répétables, nombre de groupes créés].
     *
     * Source de vérité pour l'éditeur : ajouter une section pré-crée ces champs,
     * que l'administrateur retrouve vides et renseigne depuis /admin/pages.
     */
    public static function sectionSkeletons(): array {
        return [
            'hero_media_cards' => [
                ['badge', 'title', 'title_accent', 'text', 'cta1_text', 'cta1_url', 'cta1_icon',
                 'cta2_text', 'cta2_url', 'cta2_icon', 'image', 'image_alt', 'decor',
                 'layout', 'image_max_width', 'image_ratio', 'image_ratio_mobile',
                 'overlay_opacity', 'overlay_min_height', 'image_radius'],
                ['slide_image', 'slide_alt', 'slide_badge', 'slide_title', 'slide_accent', 'slide_text',
                 'card_icon', 'card_label', 'card_badge', 'card_value', 'card_unit',
                 'card_title', 'card_meta', 'card_progress', 'card_avatar', 'card_top', 'card_left'],
                3,
            ],
            'projects_cms' => [
                ['tag', 'title', 'subtitle', 'filter_all', 'cta_text', 'show_filters',
                 'limit', 'more_text', 'more_url',
                 'empty_text', 'empty_cta_text', 'empty_cta_url'],
                ['cat_value', 'cat_label'],
                3,
            ],
            'sectors_grid' => [
                ['tag', 'title', 'subtitle', 'more_text', 'more_url'],
                ['sec_num', 'sec_icon', 'sec_image', 'sec_title', 'sec_desc', 'sec_needs', 'sec_link', 'sec_link_text'],
                3,
            ],
            'problems_solutions' => [
                ['tag', 'title', 'subtitle', 'problem_label', 'solution_label', 'layout', 'columns'],
                ['ps_icon', 'ps_problem', 'ps_solution', 'ps_detail'],
                3,
            ],
            'contact_details' => [
                ['title', 'subtitle', 'show_form', 'coordonnees_title',
                 'cta_label', 'whatsapp_btn_label', 'hours_title', 'hours_desc',
                 'map_office_label', 'social_section_title', 'social_section_subtitle'],
                [],
                0,
            ],
            'lead_form' => [
                ['tag', 'title', 'subtitle',
                 'step1_title', 'step2_title', 'step3_title', 'step4_title',
                 'submit_text', 'back_text', 'next_text',
                 'success_title', 'success_text', 'error_title',
                 'privacy_note', 'file_note'],
                ['besoin_label', 'besoin_icon', 'secteur_label', 'urgence_label', 'budget_label'],
                3,
            ],
            'needs_router' => [
                ['tag', 'title', 'subtitle', 'intro_label'],
                ['need_icon', 'need_text', 'need_solution', 'need_link'],
                3,
            ],
            // `cta_text` / `cta_url` sont facultatifs : vides, aucun bouton
            // n'est rendu, donc les sections déjà en ligne sont inchangées.
            'capabilities_grid' => [
                ['tag', 'title', 'subtitle', 'cta_text', 'cta_url'],
                ['cap_icon', 'cap_title', 'cap_desc'],
                3,
            ],
            'services_grid_v2' => [
                ['tag', 'title', 'subtitle', 'card_link_text'],
                ['svc_icon', 'svc_tag', 'svc_title', 'svc_points', 'svc_link', 'svc_featured'],
                3,
            ],
            'process_timeline' => [
                ['tag', 'title'],
                ['proc_num', 'proc_icon', 'proc_title', 'proc_desc'],
                3,
            ],
            'process_strip' => [
                ['tag', 'title', 'subtitle'],
                ['proc_num', 'proc_icon', 'proc_title', 'proc_desc', 'proc_link'],
                3,
            ],
            'stats_intro' => [
                ['badge', 'title', 'description', 'link_text', 'link_url'],
                ['stat_icon', 'stat_value', 'stat_label', 'stat_desc'],
                4,
            ],
            'about_visual' => [
                ['image', 'badge_years', 'badge_label', 'tag', 'title', 'description',
                 'check_1', 'check_2', 'check_3', 'check_4', 'check_5'],
                [],
                0,
            ],
            'projects_showcase' => [
                ['tag', 'title', 'subtitle', 'result_label', 'more_text', 'more_url'],
                ['proj_image', 'proj_category', 'proj_title', 'proj_desc', 'proj_result', 'proj_link'],
                3,
            ],
            'testimonials_carousel' => [
                ['tag', 'title', 'subtitle'],
                ['client_quote', 'client_name', 'client_role', 'client_avatar'],
                3,
            ],
            'logos_strip' => [
                ['title'],
                ['logo_name', 'logo_icon', 'logo_image', 'logo_link'],
                4,
            ],
            'cta' => [
                ['eyebrow', 'title', 'subtitle', 'cta_text', 'cta_url', 'cta2_text', 'cta2_url'],
                [],
                0,
            ],
            // ── Centre de ressources (/insights) ──
            'insights_featured' => [
                ['tag', 'title', 'subtitle', 'badge_label', 'cta_text', 'read_suffix', 'fallback_latest'],
                [],
                0,
            ],
            'insights_grid' => [
                ['tag', 'title', 'subtitle', 'filter_all', 'show_filters', 'show_search',
                 'search_label', 'search_placeholder', 'search_button', 'per_page',
                 'read_label', 'read_suffix', 'count_label', 'reset_text', 'empty_text'],
                [],
                0,
            ],
            'insights_resources' => [
                ['tag', 'title', 'subtitle', 'read_text', 'download_text', 'empty_text'],
                ['type_value', 'type_label', 'type_icon'],
                3,
            ],
            'newsletter' => [
                ['tag', 'title', 'subtitle', 'placeholder', 'button_text', 'note', 'success_text'],
                [],
                0,
            ],
            // ── Digitalium Labs (/labs) ──
            // Les produits eux-mêmes ne sont pas des blocs : ils viennent du
            // module /admin/labs, pour rester réutilisables sur d'autres pages.
            'lab_products' => [
                ['tag', 'title', 'subtitle', 'show_filters', 'filter_all', 'limit',
                 'featured_only', 'cta_text', 'tech_label', 'availability_label',
                 'empty_text', 'more_text', 'more_url'],
                ['stage_value', 'stage_label'],
                0,
            ],
            'flow_chain' => [
                ['tag', 'title', 'subtitle'],
                ['flow_label', 'flow_note', 'flow_icon', 'flow_accent'],
                3,
            ],
            // `values` existait déjà comme gabarit mais n'était creable depuis
            // aucun écran : sans squelette, une section de ce type restait vide.
            'values' => [
                ['tag', 'title'],
                ['val_icon', 'val_title', 'val_text'],
                3,
            ],
        ];
    }

    /**
     * Déduit le type d'éditeur à afficher pour une clé de bloc, afin que
     * l'éditeur générique propose le bon champ (média, lien, texte long…).
     */
    private static function guessBlockType(string $key): string {
        // Delegue a BlockFieldHelper : une seule regle pour l'editeur,
        // le controleur et les scripts de seed.
        return \App\Helpers\BlockFieldHelper::type($key);
    }

    /**
     * Seeds group templates for repeatable items.
     */
    private function seedDefaultSectionGroupBlocks(int $sectionId, string $type, int $groupId): void {
        switch ($type) {
            case 'services':
                Block::setVal($sectionId, 'card_title', 'text', 'Nom du Service', $groupId, 0);
                Block::setVal($sectionId, 'card_icon', 'text', 'laptop', $groupId, 1);
                Block::setVal($sectionId, 'card_description', 'textarea', 'Description courte expliquant le service.', $groupId, 2);
                break;

            case 'services_grid':
                Block::setVal($sectionId, 'svc_title', 'text', 'Nom du Service', $groupId, 0);
                Block::setVal($sectionId, 'svc_tag', 'text', 'Web', $groupId, 1);
                Block::setVal($sectionId, 'svc_icon', 'text', 'laptop', $groupId, 2);
                Block::setVal($sectionId, 'svc_points', 'textarea', 'UX/UI | E-commerce | Solutions sur mesure', $groupId, 3);
                Block::setVal($sectionId, 'svc_image', 'image', '', $groupId, 4);
                Block::setVal($sectionId, 'svc_link', 'link', '/contact', $groupId, 5);
                break;

            case 'portfolio':
                Block::setVal($sectionId, 'item_title', 'text', 'Titre du projet', $groupId, 0);
                Block::setVal($sectionId, 'item_category', 'text', 'Web & Mobile', $groupId, 1);
                Block::setVal($sectionId, 'item_image', 'image', '', $groupId, 2);
                Block::setVal($sectionId, 'item_url', 'link', '#', $groupId, 3);
                break;

            case 'team':
                Block::setVal($sectionId, 'member_name', 'text', 'Jean Dupont', $groupId, 0);
                Block::setVal($sectionId, 'member_role', 'text', 'Lead Architect', $groupId, 1);
                Block::setVal($sectionId, 'member_avatar', 'image', '', $groupId, 2);
                Block::setVal($sectionId, 'member_linkedin', 'link', 'https://linkedin.com', $groupId, 3);
                break;

            case 'team_roles':
                Block::setVal($sectionId, 'role_title', 'text', 'Intitulé du Poste', $groupId, 0);
                Block::setVal($sectionId, 'role_sub', 'text', 'Département', $groupId, 1);
                Block::setVal($sectionId, 'role_avatar', 'text', 'user', $groupId, 2);
                Block::setVal($sectionId, 'role_image', 'image', '', $groupId, 3);
                Block::setVal($sectionId, 'role_link', 'link', '#', $groupId, 4);
                break;

            case 'testimonials':
                Block::setVal($sectionId, 'client_name', 'text', 'Alice Martin', $groupId, 0);
                Block::setVal($sectionId, 'client_company', 'text', 'CEO - Innova', $groupId, 1);
                Block::setVal($sectionId, 'client_avatar', 'image', '', $groupId, 2);
                Block::setVal($sectionId, 'client_quote', 'textarea', 'Un service d\'une réactivité incroyable.', $groupId, 3);
                Block::setVal($sectionId, 'client_rating', 'number', '5', $groupId, 4);
                break;

            case 'faq':
                Block::setVal($sectionId, 'faq_question', 'text', 'Quelle est la durée moyenne ?', $groupId, 0);
                Block::setVal($sectionId, 'faq_answer', 'textarea', 'La durée dépend de la complexité.', $groupId, 1);
                break;

            case 'blog':
                Block::setVal($sectionId, 'post_title', 'text', 'L\'impact de l\'IA', $groupId, 0);
                Block::setVal($sectionId, 'post_summary', 'textarea', 'Comment les serveurs s\'adaptent.', $groupId, 1);
                Block::setVal($sectionId, 'post_image', 'image', '', $groupId, 2);
                Block::setVal($sectionId, 'post_date', 'text', date('d/m/Y'), $groupId, 3);
                Block::setVal($sectionId, 'post_read_time', 'text', '5 min', $groupId, 4);
                break;

            case 'blog_grid':
                Block::setVal($sectionId, 'post_title', 'text', 'Titre de l\'article', $groupId, 0);
                Block::setVal($sectionId, 'post_category', 'text', 'Intelligence Artificielle', $groupId, 1);
                Block::setVal($sectionId, 'post_date', 'text', date('d M Y'), $groupId, 2);
                Block::setVal($sectionId, 'post_summary', 'textarea', 'Résumé court de l\'article.', $groupId, 3);
                Block::setVal($sectionId, 'post_icon', 'text', 'cpu', $groupId, 4);
                Block::setVal($sectionId, 'post_image', 'image', '', $groupId, 5);
                Block::setVal($sectionId, 'post_link', 'link', '/blog', $groupId, 6);
                break;

            case 'process':
            case 'process_strip':
                Block::setVal($sectionId, 'proc_title', 'text', 'Étape du processus', $groupId, 0);
                Block::setVal($sectionId, 'proc_icon', 'text', 'cpu', $groupId, 1);
                Block::setVal($sectionId, 'proc_desc', 'textarea', 'Description courte expliquant cette étape.', $groupId, 2);
                Block::setVal($sectionId, 'proc_num', 'text', '01', $groupId, 3);
                Block::setVal($sectionId, 'proc_image', 'image', '', $groupId, 4);
                Block::setVal($sectionId, 'proc_link', 'link', '#', $groupId, 5);
                break;

            case 'features':
                Block::setVal($sectionId, 'card_title', 'text', 'Titre de l\'atout', $groupId, 0);
                Block::setVal($sectionId, 'card_icon', 'text', 'laptop', $groupId, 1);
                Block::setVal($sectionId, 'card_description', 'textarea', 'Description de l\'atout.', $groupId, 2);
                Block::setVal($sectionId, 'card_image', 'image', '', $groupId, 3);
                Block::setVal($sectionId, 'card_link', 'link', '#', $groupId, 4);
                break;
        }
    }

    /**
     * AJAX API: Add a new Hero Slide.
     */
    public function addSlide(): void {
        $this->middlewareAuth();
        $this->validateCsrf();

        $pageId = (int)($_POST['page_id'] ?? 0);
        if ($pageId <= 0) {
            $this->json(['error' => 'ID de page invalide.'], 400);
        }

        try {
            $slideId = \App\Models\HeroSlide::add([
                'page_id' => $pageId,
                'title' => 'Nouveau Slide',
                'subtitle' => 'Description du slide.',
                'badge' => 'Premium',
                'image' => '',
                'cta_text' => 'Découvrir',
                'cta_url' => '#',
                'sort_order' => 0
            ]);
            \App\Services\Cache::clear();
            $this->json([
                'success' => true,
                'message' => 'Slide ajouté !',
                'slide_id' => $slideId
            ]);
        } catch (\Exception $e) {
            $this->json(['error' => 'Erreur : ' . $e->getMessage()], 500);
        }
    }

    /**
     * AJAX API: Update all slides for a page.
     */
    public function updateSlides(): void {
        $this->middlewareAuth();
        $this->validateCsrf();

        $slidesData = $_POST['slides'] ?? [];
        try {
            foreach ($slidesData as $slideId => $slide) {
                \App\Models\HeroSlide::updateSlide((int)$slideId, [
                    'title'      => trim($slide['title'] ?? ''),
                    'subtitle'   => trim($slide['subtitle'] ?? ''),
                    'badge'      => trim($slide['badge'] ?? ''),
                    'image'      => trim($slide['image'] ?? ''),
                    'cta_text'   => trim($slide['cta_text'] ?? ''),
                    'cta_url'    => trim($slide['cta_url'] ?? ''),
                    'sort_order' => (int)($slide['sort_order'] ?? 0)
                ]);
            }
            \App\Services\Cache::clear();
            $this->json(['success' => true, 'message' => 'Slides mis à jour avec succès.']);
        } catch (\Exception $e) {
            $this->json(['error' => 'Erreur : ' . $e->getMessage()], 500);
        }
    }

    /**
     * AJAX API: Delete a Hero Slide.
     */
    public function deleteSlide(): void {
        $this->middlewareAuth();
        $this->validateCsrf();

        $slideId = (int)($_POST['slide_id'] ?? 0);
        if ($slideId <= 0) {
            $this->json(['error' => 'ID de slide invalide.'], 400);
        }

        try {
            if (\App\Models\HeroSlide::delete($slideId)) {
                \App\Services\Cache::clear();
                $this->json(['success' => true, 'message' => 'Slide supprimé avec succès.']);
            } else {
                $this->json(['error' => 'Aucun slide supprimé.'], 404);
            }
        } catch (\Exception $e) {
            $this->json(['error' => 'Erreur : ' . $e->getMessage()], 500);
        }
    }
}
