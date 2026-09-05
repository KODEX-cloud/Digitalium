<?php
namespace App\Controllers;

use App\Services\Auth;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;

class MenuController extends Controller {

    protected function middlewareAuth(): void {
        if (!Auth::check()) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->json(['error' => 'Non autorisé'], 401);
            }
            $this->redirect('/admin/login', 'error', 'Veuillez vous connecter.');
        }
    }

    public function index(): void {
        $this->middlewareAuth();

        // Le nombre de liens et le fait d'être « branché sur le site » sont
        // calculés ici : une vue ne doit pas interroger la base (convention du
        // projet), et sans ces deux informations on cherche longtemps pourquoi
        // un menu « ne s'affiche pas ».
        $menus = Menu::all('id ASC');
        foreach ($menus as &$m) {
            $m['nb_liens']   = MenuItem::compter((int)$m['id']);
            $m['est_cable']  = Menu::estCable((string)($m['location'] ?? ''));
            $m['emplacement_libelle'] = Menu::libelleEmplacement((string)($m['location'] ?? ''));
        }
        unset($m);

        $this->render('admin/menus/index', [
            'title'        => 'Gestion des menus',
            'menus'        => $menus,
            'emplacements' => Menu::EMPLACEMENTS,
            'csrf_token'   => $this->generateCsrf(),
            'currentUser'  => Auth::user(),
        ], 'admin/layout');
    }

    public function create(): void {
        $this->middlewareAuth();
        $this->validateCsrf();

        $name     = trim($_POST['name'] ?? '');
        $location = trim($_POST['location'] ?? 'primary');

        if (empty($name)) {
            $this->redirect('/admin/menus', 'error', 'Le nom du menu est obligatoire.');
        }

        $id = Menu::create($name, $location);
        \App\Services\Cache::clear();
        $this->redirect("/admin/menus/edit/{$id}", 'success', "Menu « {$name} » créé. Ajoutez maintenant vos liens.");
    }

    public function editForm(array $params): void {
        $this->middlewareAuth();
        $id   = (int)($params['id'] ?? 0);
        $menu = Menu::find($id);
        if (!$menu) {
            $this->redirect('/admin/menus', 'error', 'Menu introuvable.');
        }

        $items = MenuItem::getByMenu($id);
        $pages = array_values(array_filter(
            Page::all('sort_order ASC'),
            static fn($p) => ($p['status'] ?? '') === 'published'
        ));

        $this->render('admin/menus/edit', [
            'title'        => 'Édition du menu : ' . $menu['name'],
            'menu'         => $menu,
            'items'        => $items,
            'pages'        => $pages,
            'emplacements' => Menu::EMPLACEMENTS,
            'estCable'     => Menu::estCable((string)($menu['location'] ?? '')),
            'csrf_token'   => $this->generateCsrf(),
            'currentUser'  => Auth::user(),
        ], 'admin/layout');
    }

    public function saveItems(array $params): void {
        $this->middlewareAuth();
        $this->validateCsrf();

        $menuId = (int)($params['id'] ?? 0);
        $menu   = Menu::find($menuId);
        if (!$menu) {
            $this->json(['error' => 'Menu introuvable.'], 404);
        }

        $itemsRaw = $_POST['items'] ?? [];
        $items    = is_array($itemsRaw) ? array_values($itemsRaw) : [];

        try {
            MenuItem::saveForMenu($menuId, $items);
            \App\Services\Cache::clear();
            $this->json(['success' => true, 'message' => 'Menu enregistré avec succès.']);
        } catch (\Throwable $e) {
            $this->json(['error' => 'Erreur : ' . $e->getMessage()], 500);
        }
    }

    public function updateMenu(array $params): void {
        $this->middlewareAuth();
        $this->validateCsrf();

        $id       = (int)($params['id'] ?? 0);
        $menu     = Menu::find($id);
        if (!$menu) {
            $this->redirect('/admin/menus', 'error', 'Menu introuvable.');
        }

        $name     = trim($_POST['name'] ?? '');
        $location = trim($_POST['location'] ?? 'primary');

        if (empty($name)) {
            $this->redirect("/admin/menus/edit/{$id}", 'error', 'Le nom est obligatoire.');
        }

        Menu::update($id, $name, $location);
        \App\Services\Cache::clear();
        $this->redirect("/admin/menus/edit/{$id}", 'success', 'Menu mis à jour.');
    }

    public function delete(array $params): void {
        $this->middlewareAuth();
        $this->validateCsrf();

        $id = (int)($params['id'] ?? 0);
        if (Menu::delete($id)) {
            \App\Services\Cache::clear();
            $this->redirect('/admin/menus', 'success', 'Menu supprimé.');
        } else {
            $this->redirect('/admin/menus', 'error', 'Suppression impossible.');
        }
    }
}
