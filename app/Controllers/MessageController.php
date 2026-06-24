<?php
namespace App\Controllers;

use App\Services\Auth;
use App\Models\Message;

class MessageController extends Controller {

    protected function middlewareAuth(): void {
        if (!Auth::check()) {
            $this->redirect('/admin/login', 'error', 'Veuillez vous connecter.');
        }
    }

    public function index(): void {
        $this->middlewareAuth();
        $filter   = $_GET['filter'] ?? 'all';
        $validFilters = ['all', 'nouveau', 'lu', 'archivé'];
        if (!in_array($filter, $validFilters)) $filter = 'all';

        if ($filter === 'all') {
            $messages = Message::all('id DESC');
        } else {
            $messages = \App\Services\Database::fetchAll(
                "SELECT * FROM contact_messages WHERE statut = :s ORDER BY id DESC",
                ['s' => $filter]
            );
        }

        $this->render('admin/messages/index', [
            'title'    => 'Messages reçus',
            'messages' => $messages,
            'filter'   => $filter,
            'newCount' => Message::countNew(),
            'csrf_token' => $this->generateCsrf(),
        ], 'admin/layout');
    }

    public function show(array $params): void {
        $this->middlewareAuth();
        $id  = (int)($params['id'] ?? 0);
        $msg = Message::find($id);
        if (!$msg) {
            $this->redirect('/admin/messages', 'error', 'Message introuvable.');
        }

        if ($msg['statut'] === 'nouveau') {
            Message::markRead($id);
        }

        $this->render('admin/messages/show', [
            'title'      => 'Message de ' . htmlspecialchars($msg['nom']),
            'message'    => $msg,
            'newCount'   => Message::countNew(),
            'csrf_token' => $this->generateCsrf(),
        ], 'admin/layout');
    }

    public function markRead(array $params): void {
        $this->middlewareAuth();
        $this->validateCsrf();
        $id = (int)($params['id'] ?? 0);
        Message::markRead($id);
        $this->redirect('/admin/messages', 'success', 'Message marqué comme lu.');
    }

    public function archive(array $params): void {
        $this->middlewareAuth();
        $this->validateCsrf();
        $id = (int)($params['id'] ?? 0);
        Message::archive($id);
        $this->redirect('/admin/messages', 'success', 'Message archivé.');
    }

    public function delete(array $params): void {
        $this->middlewareAuth();
        $this->validateCsrf();
        $id = (int)($params['id'] ?? 0);
        if (Message::delete($id)) {
            $this->redirect('/admin/messages', 'success', 'Message supprimé.');
        } else {
            $this->redirect('/admin/messages', 'error', 'Suppression impossible.');
        }
    }
}
