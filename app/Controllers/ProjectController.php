<?php
namespace App\Controllers;

use App\Services\Auth;
use App\Helpers\Validator;
use App\Models\Project;
use App\Models\Media;

class ProjectController extends Controller {
    /**
     * Helper to enforce auth.
     */
    protected function middlewareAuth(): void {
        if (!Auth::check()) {
            if (isset($_GET['ajax']) || $_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->json(['error' => 'Non autorisé'], 401);
            }
            $this->redirect('/admin/login', 'error', 'Veuillez vous connecter pour gérer les réalisations.');
        }
    }

    /**
     * List all projects.
     */
    public function index(): void {
        $this->middlewareAuth();
        $projects = Project::all('sort_order ASC, id DESC');

        $this->render('admin/projects/index', [
            'title' => 'Gestion des Réalisations',
            'projects' => $projects,
            'csrf_token' => $this->generateCsrf(),
            'currentUser' => Auth::user()
        ], 'admin/layout');
    }

    /**
     * Project creation form.
     */
    public function createForm(): void {
        $this->middlewareAuth();
        $mediaList = Media::all('id DESC');

        $this->render('admin/projects/create', [
            'title' => 'Ajouter une Réalisation',
            'mediaList' => $mediaList,
            'csrf_token' => $this->generateCsrf(),
            'currentUser' => Auth::user()
        ], 'admin/layout');
    }

    /**
     * Rassemble les champs du formulaire d'une réalisation.
     *
     * Une seule méthode pour la création ET la modification : les deux
     * actions écrivaient auparavant la même liste, et un champ ajouté d'un
     * seul côté aurait été perdu à l'enregistrement.
     */
    private function collectPostData(): array {
        $text = [
            'title', 'slug', 'category', 'sector', 'client', 'year', 'project_date',
            'description', 'logo', 'main_image', 'gallery', 'context', 'objectives',
            'solution', 'technologies', 'features', 'impact',
            'testimonial_quote', 'testimonial_author', 'testimonial_role',
            'external_link', 'meta_title', 'meta_description',
        ];

        $data = [];
        foreach ($text as $field) {
            $data[$field] = trim((string)($_POST[$field] ?? ''));
        }

        $data['status']      = ($_POST['status'] ?? '') === 'published' ? 'published' : 'draft';
        $data['sort_order']  = (int)($_POST['sort_order'] ?? 0);
        $data['is_featured'] = isset($_POST['is_featured']) ? 1 : 0;

        return $data;
    }

    /**
     * Handle project creation submission.
     */
    public function createSubmit(): void {
        $this->middlewareAuth();
        $this->validateCsrf();

        $validator = new Validator();
        $rules = [
            'title' => 'required',
            'category' => 'required',
            'main_image' => 'required'
        ];

        if (!$validator->validate($_POST, $rules)) {
            $errors = $validator->getErrors();
            $this->redirect('/admin/projects/create', 'error', reset($errors));
        }

        $data = $this->collectPostData();

        try {
            Project::add($data);
            \App\Services\Cache::clear();
            $this->redirect('/admin/projects', 'success', 'La réalisation a été ajoutée avec succès !');
        } catch (\Exception $e) {
            $this->redirect('/admin/projects/create', 'error', 'Erreur : ' . $e->getMessage());
        }
    }

    /**
     * Project editing form.
     */
    public function editForm(array $params): void {
        $this->middlewareAuth();
        
        $id = (int)($params['id'] ?? 0);
        $project = Project::find($id);

        if (!$project) {
            $this->redirect('/admin/projects', 'error', 'Réalisation introuvable.');
        }

        $mediaList = Media::all('id DESC');

        $this->render('admin/projects/edit', [
            'title' => 'Modifier la Réalisation : ' . $project['title'],
            'project' => $project,
            'mediaList' => $mediaList,
            'csrf_token' => $this->generateCsrf(),
            'currentUser' => Auth::user()
        ], 'admin/layout');
    }

    /**
     * Handle project editing submission.
     */
    public function editSubmit(array $params): void {
        $this->middlewareAuth();
        $this->validateCsrf();

        $id = (int)($params['id'] ?? 0);
        $project = Project::find($id);

        if (!$project) {
            $this->redirect('/admin/projects', 'error', 'Réalisation introuvable.');
        }

        $validator = new Validator();
        $rules = [
            'title' => 'required',
            'category' => 'required',
            'main_image' => 'required'
        ];

        if (!$validator->validate($_POST, $rules)) {
            $errors = $validator->getErrors();
            $this->redirect("/admin/projects/edit/{$id}", 'error', reset($errors));
        }

        $data = $this->collectPostData();

        try {
            Project::updateProject($id, $data);
            \App\Services\Cache::clear();
            $this->redirect('/admin/projects', 'success', 'La réalisation a été mise à jour !');
        } catch (\Exception $e) {
            $this->redirect("/admin/projects/edit/{$id}", 'error', 'Erreur : ' . $e->getMessage());
        }
    }

    /**
     * Frontend: list all projects.
     */
    public function publicIndex(): void {
        $settings   = \App\Models\Setting::getAll();
        $menuPages  = array_filter(\App\Models\Page::all('sort_order ASC'), fn($p) => $p['status'] === 'published');

        $portfolioPage = \App\Models\Page::findBySlug('realisations') ?? [
            'title' => 'Réalisations', 'meta_title' => 'Nos Réalisations — Digitalium Group',
            'meta_description' => 'Découvrez nos projets digitaux sur mesure.',
            'slug' => 'realisations', 'hero_status' => 0,
        ];

        /**
         * La page est pilotée par le CMS comme n'importe quelle autre : on
         * charge ses sections actives et leurs blocs. Le contenu éditorial
         * (hero, expertises, CTA) devient ainsi administrable, et les
         * réalisations restent servies par le module Réalisations.
         */
        $sections      = [];
        $sectionBlocks = [];
        if (!empty($portfolioPage['id'])) {
            $sections = \App\Models\Section::getActiveByPage((int)$portfolioPage['id']) ?: [];
            foreach ($sections as $sec) {
                try {
                    $sectionBlocks[$sec['id']] = \App\Models\Block::getStructuredContent($sec['id']);
                } catch (\Throwable $e) {
                    // Une section illisible ne doit pas emporter toute la page.
                    $sectionBlocks[$sec['id']] = ['single' => [], 'groups' => []];
                }
            }
        }

        $this->render('frontend/portfolio_index', [
            'page'          => $portfolioPage,
            'sections'      => $sections,
            'sectionBlocks' => $sectionBlocks,
            'settings'      => $settings,
            'menuPages'     => $menuPages,
            'currentSlug'   => 'realisations',
        ], 'frontend/layout');
    }

    /**
     * Frontend: single project detail.
     */
    public function publicShow(array $params): void {
        $slug = trim($params['slug'] ?? '');
        // Un brouillon reste visible pour un administrateur connecté, afin de
        // relire une étude de cas avant de la publier.
        $project = \App\Services\Auth::check()
            ? Project::findBySlug($slug)
            : Project::findPublishedBySlug($slug);

        if (!$project) {
            http_response_code(404);
            $this->render('frontend/404', [
                'page'        => ['title' => '404', 'meta_title' => 'Page introuvable', 'meta_description' => '', 'hero_status' => 0],
                'settings'    => \App\Models\Setting::getAll(),
                'menuPages'   => array_filter(\App\Models\Page::all('sort_order ASC'), fn($p) => $p['status'] === 'published'),
                'currentSlug' => '',
            ], 'frontend/layout');
            return;
        }

        $related   = Project::getPublic($project['category']);
        $related   = array_filter($related, fn($p) => $p['id'] !== $project['id']);
        $related   = array_slice(array_values($related), 0, 3);
        $settings  = \App\Models\Setting::getAll();
        $menuPages = array_filter(\App\Models\Page::all('sort_order ASC'), fn($p) => $p['status'] === 'published');

        $this->render('frontend/portfolio_show', [
            'page'        => [
                'title'            => $project['title'],
                // Le SEO saisi sur la réalisation prime ; sinon on retombe sur
                // le titre et la description courte, jamais sur du texte inventé.
                'meta_title'       => trim((string)($project['meta_title'] ?? '')) ?: $project['title'] . ' — Digitalium Group',
                'meta_description' => trim((string)($project['meta_description'] ?? ''))
                                      ?: trim((string)($project['description'] ?? $project['context'] ?? '')),
                'slug'             => 'realisations/' . $project['slug'],
                'hero_status'      => 0,
            ],
            'project'     => $project,
            'related'     => $related,
            'settings'    => $settings,
            'menuPages'   => $menuPages,
            'currentSlug' => 'realisations',
        ], 'frontend/layout');
    }

    /**
     * Handle project deletion.
     */
    public function delete(array $params): void {
        $this->middlewareAuth();
        $this->validateCsrf();

        $id = (int)($params['id'] ?? 0);
        $project = Project::find($id);

        if (!$project) {
            $this->redirect('/admin/projects', 'error', 'Réalisation introuvable.');
        }

        try {
            Project::delete($id);
            \App\Services\Cache::clear();
            $this->redirect('/admin/projects', 'success', 'La réalisation a été supprimée avec succès.');
        } catch (\Exception $e) {
            $this->redirect('/admin/projects', 'error', 'Erreur : ' . $e->getMessage());
        }
    }
}
