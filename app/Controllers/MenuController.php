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
        $menus = Menu::all('id ASC');
        $this->render('admin/menus/index', [
            'title'      => 'Gestion des menus',
            'menus'      => $menus,
            'csrf_token' => $this->generateCsrf(),
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
        $pages = Page::all('sort_order ASC');

        $this->render('admin/menus/edit', [
            'title'      => 'Édition du menu : ' . $menu['name'],
            'menu'       => $menu,
            'items'      => $items,
            'pages'      => $pages,
            'csrf_token' => $this->generateCsrf(),
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
