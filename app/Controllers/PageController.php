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
            \App\Services\Cache::clear();
            $this->redirect("/admin/pages/edit/{$pageId}", 'success', "La page a été créée.");
        } catch (\Exception $e) {
            $this->redirect('/admin/pages/create', 'error', "Erreur : " . $e->getMessage());
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

        $this->render('admin/pages/edit', [
            'title' => "Édition : " . $page['title'],
            'page' => $page,
            'sections' => $sections,
            'sectionBlocks' => $sectionBlocks,
            'mediaList' => $mediaList,
            'csrf_token' => $this->generateCsrf()
        ], 'admin/layout');
    }

    /**
     * Handle page metadata update.
     */
    public function editSubmit(array $params): void {
        ini_set('error_log', ROOT_PATH . '/storage/logs/app.log');
        error_log(print_r($_POST,true));

        $this->middlewareAuth();
        $this->validateCsrf();

        $id = (int)($params['id'] ?? 0);
        $page = Page::find($id);

        if (!$page) {
            $this->redirect('/admin/pages', 'error', 'Page introuvable.');
        }

        $validator = new Validator();
        $rules = [
            'title' => 'required',
            'slug' => 'required'
        ];

        if (!$validator->validate($_POST, $rules)) {
            $this->redirect("/admin/pages/edit/{$id}", 'error', reset($validator->getErrors()));
        }

        $slug = Page::slugify($_POST['slug']);
        $existing = Page::findBySlug($slug);
        if ($existing && (int)$existing['id'] !== $id) {
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
            'responsive_settings' => trim($_POST['responsive_settings'] ?? '')
        ];

        if (Page::updatePage($id, $data)) {
            \App\Services\Cache::clear();
            $this->redirect("/admin/pages/edit/{$id}", 'success', 'Enregistré avec succès.');
        } else {
            $this->redirect("/admin/pages/edit/{$id}", 'info', 'Aucune modification apportée.');
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
                Block::setVal($sectionId, 'title', 'wysiwyg', '<h1>Votre transformation digitale commence ici</h1>');
                Block::setVal($sectionId, 'subtitle', 'textarea', 'Nous concevons des solutions logicielles sur mesure.');
                Block::setVal($sectionId, 'cta_text', 'text', 'Commencer mon projet');
                Block::setVal($sectionId, 'cta_url', 'link', '#contact');
                Block::setVal($sectionId, 'bg_image', 'image', '');
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
        }
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
}
