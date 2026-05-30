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
            'title'         => trim($_POST['title']),
            'category'      => trim($_POST['category']),
            'logo'          => trim($_POST['logo'] ?? ''),
            'main_image'    => trim($_POST['main_image']),
            'gallery'       => trim($_POST['gallery'] ?? ''),
            'context'       => trim($_POST['context'] ?? ''),
            'impact'        => trim($_POST['impact'] ?? ''),
            'technologies'  => trim($_POST['technologies'] ?? ''),
            'external_link' => trim($_POST['external_link'] ?? ''),
            'sort_order'    => (int)($_POST['sort_order'] ?? 0),
            'is_featured'   => isset($_POST['is_featured']) ? 1 : 0
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
            'title'         => trim($_POST['title']),
            'category'      => trim($_POST['category']),
            'logo'          => trim($_POST['logo'] ?? ''),
            'main_image'    => trim($_POST['main_image']),
            'gallery'       => trim($_POST['gallery'] ?? ''),
            'context'       => trim($_POST['context'] ?? ''),
            'impact'        => trim($_POST['impact'] ?? ''),
            'technologies'  => trim($_POST['technologies'] ?? ''),
            'external_link' => trim($_POST['external_link'] ?? ''),
            'sort_order'    => (int)($_POST['sort_order'] ?? 0),
            'is_featured'   => isset($_POST['is_featured']) ? 1 : 0
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
