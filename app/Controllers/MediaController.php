<?php
namespace App\Controllers;

use App\Services\Auth;
use App\Services\MediaManager;
use App\Models\Media;

class MediaController extends Controller {
    /**
     * Enforce authentication.
     */
    protected function middlewareAuth(): void {
        if (!Auth::check()) {
            if (isset($_SERVER['CONTENT_TYPE']) && str_contains($_SERVER['CONTENT_TYPE'], 'application/json')) {
                $this->json(['error' => 'Non autorisé'], 401);
            }
            $this->redirect('/admin/login', 'error', 'Veuillez vous connecter.');
        }
    }

    /**
     * Show Media Library workspace.
     */
    public function index(): void {
        $this->middlewareAuth();

        $query = trim($_GET['search'] ?? '');
        if (!empty($query)) {
            $mediaList = Media::search($query);
        } else {
            $mediaList = Media::all('id DESC');
        }

        $this->render('admin/media/index', [
            'title' => 'Bibliothèque Média',
            'mediaList' => $mediaList,
            'search' => $query,
            'csrf_token' => $this->generateCsrf()
        ], 'admin/layout');
    }

    /**
     * Upload an image/file securely.
     */
    public function upload(): void {
        $this->middlewareAuth();
        $this->validateCsrf();

        if (empty($_FILES['file'])) {
            if (isset($_POST['ajax']) || isset($_GET['ajax'])) {
                $this->json(['error' => 'Aucun fichier fourni.'], 400);
            }
            $this->redirect('/admin/media', 'error', 'Aucun fichier fourni.');
        }

        try {
            $uploaded = MediaManager::upload($_FILES['file']);
            $mediaId = Media::add($uploaded);
            
            $uploaded['id'] = $mediaId;

            if (isset($_POST['ajax']) || isset($_GET['ajax'])) {
                $this->json([
                    'success' => true,
                    'message' => 'Fichier téléversé avec succès !',
                    'file' => $uploaded
                ]);
            }

            $this->redirect('/admin/media', 'success', 'Fichier téléversé avec succès !');
        } catch (\Exception $e) {
            if (isset($_POST['ajax']) || isset($_GET['ajax'])) {
                $this->json(['error' => $e->getMessage()], 400);
            }
            $this->redirect('/admin/media', 'error', $e->getMessage());
        }
    }

    /**
     * Delete a media item.
     */
    public function delete(): void {
        $this->middlewareAuth();
        $this->validateCsrf();

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->json(['error' => 'Identifiant média invalide.'], 400);
        }

        $media = Media::find($id);
        if (!$media) {
            $this->json(['error' => 'Fichier introuvable.'], 404);
        }

        if (Media::deleteMedia($id)) {
            $this->json([
                'success' => true,
                'message' => 'Le fichier média a été supprimé définitivement.'
            ]);
        } else {
            $this->json(['error' => 'Impossible de supprimer le fichier.'], 500);
        }
    }
}
