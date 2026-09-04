<?php
namespace App\Controllers;

use App\Services\Auth;
use App\Helpers\Validator;
use App\Models\LabProduct;
use App\Models\Media;

/**
 * Digitalium Labs — administration des produits propriétaires.
 *
 * Le module est volontairement séparé des Réalisations : un produit Labs
 * appartient à Digitalium et suit un cycle de vie, une réalisation appartient à
 * un client et est terminée. Voir LabProduct pour le détail du raisonnement.
 *
 * Il n'y a PAS de page publique par produit : le cahier des charges prévoit un
 * lien externe par produit, pas une fiche. Le slug sert d'ancre stable dans la
 * grille (#produit-mon-produit), pour qu'un produit puisse être pointé
 * directement depuis un autre contenu.
 */
class LabController extends Controller {

    protected function middlewareAuth(): void {
        if (!Auth::check()) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->json(['error' => 'Non autorisé'], 401);
            }
            $this->redirect('/admin/login', 'error', 'Veuillez vous connecter.');
        }
    }

    // ─── Liste ──────────────────────────────────────────────────────────────
    public function index(): void {
        $this->middlewareAuth();

        $produits = LabProduct::all('sort_order ASC, id DESC');

        $stats = ['total' => count($produits), 'publies' => 0, 'avant' => 0];
        foreach ($produits as $p) {
            if (($p['status'] ?? '') === 'published') { $stats['publies']++; }
            if ((int)($p['is_featured'] ?? 0) === 1)  { $stats['avant']++; }
        }

        $this->render('admin/labs/index', [
            'title'       => 'Digitalium Labs — Produits',
            'produits'    => $produits,
            'stats'       => $stats,
            'csrf_token'  => $this->generateCsrf(),
            'currentUser' => Auth::user(),
        ], 'admin/layout');
    }

    // ─── Création ───────────────────────────────────────────────────────────
    public function createForm(): void {
        $this->middlewareAuth();
        $this->render('admin/labs/create', [
            'title'       => 'Nouveau produit Labs',
            'produit'     => [],
            'mediaList'   => Media::all('id DESC'),
            'csrf_token'  => $this->generateCsrf(),
            'currentUser' => Auth::user(),
        ], 'admin/layout');
    }

    public function createSubmit(): void {
        $this->middlewareAuth();
        $this->validateCsrf();

        $v = new Validator();
        if (!$v->validate($_POST, ['name' => 'required'])) {
            $e = $v->getErrors();
            $this->redirect('/admin/labs/create', 'error', reset($e));
        }

        try {
            $id = LabProduct::add($this->collecter());
            \App\Services\Cache::clear();
            $this->redirect("/admin/labs/edit/{$id}", 'success', 'Produit créé.');
        } catch (\Throwable $e) {
            $this->redirect('/admin/labs/create', 'error', 'Erreur : ' . $e->getMessage());
        }
    }

    // ─── Modification ───────────────────────────────────────────────────────
    public function editForm(array $params): void {
        $this->middlewareAuth();

        $produit = LabProduct::find((int)($params['id'] ?? 0));
        if (!$produit) {
            $this->redirect('/admin/labs', 'error', 'Produit introuvable.');
        }

        $this->render('admin/labs/edit', [
            'title'       => 'Modifier : ' . $produit['name'],
            'produit'     => $produit,
            'mediaList'   => Media::all('id DESC'),
            'csrf_token'  => $this->generateCsrf(),
            'currentUser' => Auth::user(),
        ], 'admin/layout');
    }

    public function editSubmit(array $params): void {
        $this->middlewareAuth();
        $this->validateCsrf();

        $id = (int)($params['id'] ?? 0);
        if (!LabProduct::find($id)) {
            $this->redirect('/admin/labs', 'error', 'Produit introuvable.');
        }

        $v = new Validator();
        if (!$v->validate($_POST, ['name' => 'required'])) {
            $e = $v->getErrors();
            $this->redirect("/admin/labs/edit/{$id}", 'error', reset($e));
        }

        try {
            LabProduct::updateProduct($id, $this->collecter());
            \App\Services\Cache::clear();
            $this->redirect("/admin/labs/edit/{$id}", 'success', 'Produit enregistré.');
        } catch (\Throwable $e) {
            $this->redirect("/admin/labs/edit/{$id}", 'error', 'Erreur : ' . $e->getMessage());
        }
    }

    // ─── Suppression ────────────────────────────────────────────────────────
    public function delete(array $params): void {
        $this->middlewareAuth();
        $this->validateCsrf();

        if (LabProduct::delete((int)($params['id'] ?? 0))) {
            \App\Services\Cache::clear();
            $this->redirect('/admin/labs', 'success', 'Produit supprimé.');
        }
        $this->redirect('/admin/labs', 'error', 'Suppression impossible.');
    }

    /**
     * Traduit le formulaire en champs du modèle.
     *
     * Une seule source pour la création ET la modification : quand les deux
     * vues déclaraient chacune leur liste, un champ ajouté d'un côté n'était
     * éditable que là (leçon du formulaire des Réalisations).
     */
    private function collecter(): array {
        $texte = [
            'name', 'slug', 'tagline', 'description', 'sector', 'stage',
            'logo', 'main_image', 'technologies', 'external_link', 'availability',
            'meta_title', 'meta_description',
        ];

        $data = [];
        foreach ($texte as $champ) {
            $data[$champ] = trim((string)($_POST[$champ] ?? ''));
        }

        $data['sort_order']  = (int)($_POST['sort_order'] ?? 0);
        $data['is_featured'] = isset($_POST['is_featured']) ? 1 : 0;
        $data['status']      = ($_POST['status'] ?? '') === 'published' ? 'published' : 'draft';

        return $data;
    }
}
