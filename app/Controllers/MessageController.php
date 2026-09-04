<?php
namespace App\Controllers;

use App\Services\Auth;
use App\Models\Message;

/**
 * Demandes entrantes — écran d'administration.
 *
 * L'écran servait à lire des messages de contact ; il gère désormais un
 * pipeline commercial : filtres, recherche, changement de statut, notes
 * internes, historique, pièces jointes et export.
 *
 * Les cinq méthodes historiques (`index`, `show`, `markRead`, `archive`,
 * `delete`) gardent leurs routes et leur comportement : rien de ce qui existait
 * n'a été déplacé.
 */
class MessageController extends Controller {

    protected function middlewareAuth(): void {
        if (!Auth::check()) {
            $this->redirect('/admin/login', 'error', 'Veuillez vous connecter.');
        }
    }

    public function index(): void {
        $this->middlewareAuth();

        // `filter` est l'ancien nom du paramètre de statut : les liens déjà en
        // circulation (favoris, historique du navigateur) continuent de marcher.
        $statut = trim((string)($_GET['statut'] ?? $_GET['filter'] ?? ''));
        if ($statut === 'all' || !isset(Message::STATUTS[$statut])) { $statut = ''; }

        $filtres = [
            'statut'  => $statut,
            'secteur' => trim((string)($_GET['secteur'] ?? '')),
            'besoin'  => trim((string)($_GET['besoin'] ?? '')),
            'q'       => trim((string)($_GET['q'] ?? '')),
        ];

        $this->render('admin/messages/index', [
            'title'      => 'Demandes reçues',
            'messages'   => Message::rechercher($filtres, 300),
            'filtres'    => $filtres,
            'filter'     => $statut !== '' ? $statut : 'all',   // compatibilité de la vue
            'secteurs'   => Message::valeursDistinctes('secteur'),
            'besoins'    => Message::valeursDistinctes('besoin'),
            'stats'      => Message::statistiques(),
            'newCount'   => Message::countNew(),
            'csrf_token' => $this->generateCsrf(),
        ], 'admin/layout');
    }

    public function show(array $params): void {
        $this->middlewareAuth();
        $id  = (int)($params['id'] ?? 0);
        $msg = Message::find($id);
        if (!$msg) {
            $this->redirect('/admin/messages', 'error', 'Demande introuvable.');
        }

        // Ouvrir une demande neuve la fait passer « à qualifier » : elle a été
        // vue. Les autres statuts sont des décisions, jamais touchées ici.
        if (($msg['statut'] ?? '') === 'nouveau') {
            Message::changerStatut($id, 'a_qualifier', $this->nomUtilisateur());
            $msg = Message::find($id) ?: $msg;
        }

        $this->render('admin/messages/show', [
            'title'       => 'Demande de ' . ($msg['nom'] ?? ''),
            'message'     => $msg,
            'historique'  => Message::historique($id),
            'newCount'    => Message::countNew(),
            'csrf_token'  => $this->generateCsrf(),
        ], 'admin/layout');
    }

    public function markRead(array $params): void {
        $this->middlewareAuth();
        $this->validateCsrf();
        Message::changerStatut((int)($params['id'] ?? 0), 'a_qualifier', $this->nomUtilisateur());
        $this->redirect('/admin/messages', 'success', 'Demande marquée à qualifier.');
    }

    public function archive(array $params): void {
        $this->middlewareAuth();
        $this->validateCsrf();
        Message::changerStatut((int)($params['id'] ?? 0), 'archive', $this->nomUtilisateur());
        $this->redirect('/admin/messages', 'success', 'Demande archivée.');
    }

    public function delete(array $params): void {
        $this->middlewareAuth();
        $this->validateCsrf();
        $id  = (int)($params['id'] ?? 0);
        $msg = Message::find($id);

        // Le fichier joint part avec la demande : le laisser sur le disque
        // reviendrait à conserver un document sans plus rien pour le retrouver.
        if ($msg && !empty($msg['piece_jointe'])) {
            $chemin = $this->cheminPieceJointe((string)$msg['piece_jointe']);
            if ($chemin !== null && is_file($chemin)) { @unlink($chemin); }
        }

        if (Message::delete($id)) {
            $this->redirect('/admin/messages', 'success', 'Demande supprimée.');
        }
        $this->redirect('/admin/messages', 'error', 'Suppression impossible.');
    }

    /** Changement de statut depuis la fiche. */
    public function changeStatus(array $params): void {
        $this->middlewareAuth();
        $this->validateCsrf();
        $id     = (int)($params['id'] ?? 0);
        $statut = trim((string)($_POST['statut'] ?? ''));

        if (!isset(Message::STATUTS[$statut])) {
            $this->redirect('/admin/messages/' . $id, 'error', 'Statut inconnu.');
        }
        $change = Message::changerStatut($id, $statut, $this->nomUtilisateur());
        $this->redirect(
            '/admin/messages/' . $id,
            $change ? 'success' : 'error',
            $change ? 'Statut mis à jour : ' . Message::libelleStatut($statut)
                    : 'La demande était déjà dans ce statut.'
        );
    }

    /** Ajout d'une note interne, conservée dans l'historique. */
    public function addNote(array $params): void {
        $this->middlewareAuth();
        $this->validateCsrf();
        $id   = (int)($params['id'] ?? 0);
        $note = trim((string)($_POST['note'] ?? ''));

        if ($note === '') {
            $this->redirect('/admin/messages/' . $id, 'error', 'La note est vide.');
        }
        Message::ajouterNote($id, mb_substr($note, 0, 2000), $this->nomUtilisateur());
        $this->redirect('/admin/messages/' . $id, 'success', 'Note ajoutée.');
    }

    /**
     * Téléchargement d'une pièce jointe.
     *
     * Le fichier vit hors de la racine web : il n'a pas d'URL publique et ne
     * peut être obtenu que par ici, après authentification.
     */
    public function download(array $params): void {
        $this->middlewareAuth();
        $id  = (int)($params['id'] ?? 0);
        $msg = Message::find($id);

        if (!$msg || empty($msg['piece_jointe'])) {
            $this->redirect('/admin/messages', 'error', 'Aucune pièce jointe.');
        }
        $chemin = $this->cheminPieceJointe((string)$msg['piece_jointe']);
        if ($chemin === null || !is_file($chemin)) {
            $this->redirect('/admin/messages/' . $id, 'error', 'Fichier introuvable sur le serveur.');
        }

        $nom = (string)($msg['piece_jointe_nom'] ?? 'document');
        $nom = preg_replace('/[^\w .\-]+/u', '_', $nom) ?: 'document';

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $nom . '"');
        header('Content-Length: ' . filesize($chemin));
        header('X-Content-Type-Options: nosniff');
        readfile($chemin);
        exit;
    }

    /** Export CSV des demandes, en respectant les filtres affichés. */
    public function export(): void {
        $this->middlewareAuth();

        $statut = trim((string)($_GET['statut'] ?? ''));
        if (!isset(Message::STATUTS[$statut])) { $statut = ''; }
        $lignes = Message::rechercher([
            'statut'  => $statut,
            'secteur' => trim((string)($_GET['secteur'] ?? '')),
            'besoin'  => trim((string)($_GET['besoin'] ?? '')),
            'q'       => trim((string)($_GET['q'] ?? '')),
        ], 1000);

        $colonnes = ['id', 'created_at', 'statut', 'nom', 'entreprise', 'secteur', 'pays',
                     'email', 'telephone', 'besoin', 'objectif', 'urgence', 'budget',
                     'source', 'message'];

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="demandes-' . date('Y-m-d') . '.csv"');

        $out = fopen('php://output', 'w');
        // Nomenclature d'octets : sans elle, Excel lit les accents de travers.
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, $colonnes, ';');
        foreach ($lignes as $l) {
            $ligne = [];
            foreach ($colonnes as $c) {
                $v = (string)($l[$c] ?? '');
                if ($c === 'statut') { $v = Message::libelleStatut($v); }
                $ligne[] = str_replace(["\r\n", "\n", "\r"], ' ', $v);
            }
            fputcsv($out, $ligne, ';');
        }
        fclose($out);
        exit;
    }

    // ── Interne ─────────────────────────────────────────────────────────────

    private function nomUtilisateur(): string {
        $u = Auth::user();
        return (string)($u['username'] ?? 'admin');
    }

    /**
     * Chemin disque d'une pièce jointe, ou null si le nom est suspect.
     *
     * Le nom vient de la base, mais il est traité comme une entrée hostile :
     * un `..` ou un séparateur permettrait de faire lire n'importe quel fichier
     * du serveur par cette route.
     */
    private function cheminPieceJointe(string $nom): ?string {
        if ($nom === '' || !preg_match('/^[A-Za-z0-9._-]+$/', $nom) || str_contains($nom, '..')) {
            return null;
        }
        return ROOT_PATH . '/storage/uploads/leads/' . $nom;
    }
}
