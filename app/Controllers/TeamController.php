<?php
namespace App\Controllers;

use App\Services\Auth;
use App\Helpers\Validator;
use App\Models\TeamMember;
use App\Models\Media;

/**
 * Équipe — administration des collaborateurs.
 *
 * Module distinct des groupes de blocs de la section `team` : un groupe de
 * blocs n'a pas d'indicateur de publication, or le cahier des charges demande
 * de pouvoir publier et dépublier chaque collaborateur individuellement. Voir
 * TeamMember pour le détail du raisonnement.
 *
 * Il n'y a PAS de page publique par collaborateur : la section /a-propos les
 * présente en grille, et le lien sortant prévu est LinkedIn.
 */
class TeamController extends Controller {

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

        $membres = TeamMember::getAll();

        $stats = ['total' => count($membres), 'publies' => 0];
        foreach ($membres as $m) {
            if (($m['status'] ?? '') === 'published') { $stats['publies']++; }
        }

        $this->render('admin/team/index', [
            'title'        => 'Équipe — Collaborateurs',
            'membres'      => $membres,
            'stats'        => $stats,
            'departements' => TeamMember::DEPARTEMENTS,
            'csrf_token'   => $this->generateCsrf(),
            'currentUser'  => Auth::user(),
        ], 'admin/layout');
    }

    // ─── Création ───────────────────────────────────────────────────────────
    public function createForm(): void {
        $this->middlewareAuth();
        $this->render('admin/team/create', [
            'title'        => 'Nouveau collaborateur',
            'membre'       => [],
            'departements' => TeamMember::DEPARTEMENTS,
            'mediaList'    => Media::all('id DESC'),
            'csrf_token'   => $this->generateCsrf(),
            'currentUser'  => Auth::user(),
        ], 'admin/layout');
    }

    public function createSubmit(): void {
        $this->middlewareAuth();
        $this->validateCsrf();

        $v = new Validator();
        if (!$v->validate($_POST, ['name' => 'required'])) {
            $e = $v->getErrors();
            $this->redirect('/admin/team/create', 'error', reset($e));
        }

        try {
            $id = TeamMember::add($this->collecter());
            \App\Services\Cache::clear();
            $this->redirect("/admin/team/edit/{$id}", 'success', 'Collaborateur créé.');
        } catch (\Throwable $e) {
            $this->redirect('/admin/team/create', 'error', 'Erreur : ' . $e->getMessage());
        }
    }

    // ─── Modification ───────────────────────────────────────────────────────
    public function editForm(array $params): void {
        $this->middlewareAuth();

        $membre = TeamMember::find((int)($params['id'] ?? 0));
        if (!$membre) {
            $this->redirect('/admin/team', 'error', 'Collaborateur introuvable.');
        }

        $this->render('admin/team/edit', [
            'title'        => 'Modifier : ' . $membre['name'],
            'membre'       => $membre,
            'departements' => TeamMember::DEPARTEMENTS,
            'mediaList'    => Media::all('id DESC'),
            'csrf_token'   => $this->generateCsrf(),
            'currentUser'  => Auth::user(),
        ], 'admin/layout');
    }

    public function editSubmit(array $params): void {
        $this->middlewareAuth();
        $this->validateCsrf();

        $id = (int)($params['id'] ?? 0);
        if (!TeamMember::find($id)) {
            $this->redirect('/admin/team', 'error', 'Collaborateur introuvable.');
        }

        $v = new Validator();
        if (!$v->validate($_POST, ['name' => 'required'])) {
            $e = $v->getErrors();
            $this->redirect("/admin/team/edit/{$id}", 'error', reset($e));
        }

        try {
            TeamMember::updateMember($id, $this->collecter());
            \App\Services\Cache::clear();
            $this->redirect("/admin/team/edit/{$id}", 'success', 'Collaborateur enregistré.');
        } catch (\Throwable $e) {
            $this->redirect("/admin/team/edit/{$id}", 'error', 'Erreur : ' . $e->getMessage());
        }
    }

    // ─── Suppression ────────────────────────────────────────────────────────
    public function delete(array $params): void {
        $this->middlewareAuth();
        $this->validateCsrf();

        if (TeamMember::delete((int)($params['id'] ?? 0))) {
            \App\Services\Cache::clear();
            $this->redirect('/admin/team', 'success', 'Collaborateur supprimé.');
        }
        $this->redirect('/admin/team', 'error', 'Suppression impossible.');
    }

    /**
     * Traduit le formulaire en champs du modèle.
     *
     * Une seule source pour la création ET la modification : quand les deux
     * vues déclaraient chacune leur liste, un champ ajouté d'un côté n'était
     * éditable que là (leçon du formulaire des Réalisations).
     */
    private function collecter(): array {
        $texte = ['name', 'role', 'department', 'bio', 'photo', 'linkedin', 'email'];

        $data = [];
        foreach ($texte as $champ) {
            $data[$champ] = trim((string)($_POST[$champ] ?? ''));
        }

        $data['sort_order'] = (int)($_POST['sort_order'] ?? 0);
        $data['status']     = ($_POST['status'] ?? '') === 'published' ? 'published' : 'draft';

        return $data;
    }
}
