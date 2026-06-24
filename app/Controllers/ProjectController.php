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

        $data = [
            'title'        => trim($_POST['title']),
            'slug'         => trim($_POST['slug'] ?? ''),
            'category'     => trim($_POST['category']),
            'client'       => trim($_POST['client'] ?? ''),
            'project_date' => trim($_POST['project_date'] ?? ''),
            'description'  => trim($_POST['description'] ?? ''),
            'logo'         => trim($_POST['logo'] ?? ''),
            'main_image'   => trim($_POST['main_image']),
            'gallery'      => trim($_POST['gallery'] ?? ''),
            'context'      => trim($_POST['context'] ?? ''),
            'impact'       => trim($_POST['impact'] ?? ''),
            'technologies' => trim($_POST['technologies'] ?? ''),
            'external_link'=> trim($_POST['external_link'] ?? ''),
            'sort_order'   => (int)($_POST['sort_order'] ?? 0),
            'is_featured'  => isset($_POST['is_featured']) ? 1 : 0
        ];

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

        $data = [
            'title'        => trim($_POST['title']),
            'slug'         => trim($_POST['slug'] ?? ''),
            'category'     => trim($_POST['category']),
            'client'       => trim($_POST['client'] ?? ''),
            'project_date' => trim($_POST['project_date'] ?? ''),
            'description'  => trim($_POST['description'] ?? ''),
            'logo'         => trim($_POST['logo'] ?? ''),
            'main_image'   => trim($_POST['main_image']),
            'gallery'      => trim($_POST['gallery'] ?? ''),
            'context'      => trim($_POST['context'] ?? ''),
            'impact'       => trim($_POST['impact'] ?? ''),
            'technologies' => trim($_POST['technologies'] ?? ''),
            'external_link'=> trim($_POST['external_link'] ?? ''),
            'sort_order'   => (int)($_POST['sort_order'] ?? 0),
            'is_featured'  => isset($_POST['is_featured']) ? 1 : 0
        ];

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
        $category   = trim($_GET['categorie'] ?? '');
        $projects   = Project::getPublic($category);
        $categories = Project::getCategories();
        $settings   = \App\Models\Setting::getAll();
        $menuPages  = array_filter(\App\Models\Page::all('sort_order ASC'), fn($p) => $p['status'] === 'published');

        $portfolioPage = \App\Models\Page::findBySlug('realisations') ?? [
            'title' => 'Réalisations', 'meta_title' => 'Nos Réalisations — Digitalium Group',
            'meta_description' => 'Découvrez nos projets digitaux sur mesure.',
            'slug' => 'realisations', 'hero_status' => 0,
        ];

        $this->render('frontend/portfolio_index', [
            'page'        => $portfolioPage,
            'projects'    => $projects,
            'categories'  => $categories,
            'activeCategory' => $category,
            'settings'    => $settings,
            'menuPages'   => $menuPages,
            'currentSlug' => 'realisations',
        ], 'frontend/layout');
    }

    /**
     * Frontend: single project detail.
     */
    public function publicShow(array $params): void {
        $slug    = trim($params['slug'] ?? '');
        $project = Project::findBySlug($slug);

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
                'meta_title'       => $project['title'] . ' — Digitalium Group',
                'meta_description' => $project['description'] ?? $project['context'] ?? '',
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
