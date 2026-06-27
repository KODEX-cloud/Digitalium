<?php
namespace App\Controllers;

use App\Services\Auth;
use App\Helpers\Validator;
use App\Models\Post;
use App\Models\Category;
use App\Models\Media;
use App\Models\Tag;
use App\Models\Comment;

class BlogController extends Controller {

    protected function middlewareAuth(): void {
        if (!Auth::check()) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->json(['error' => 'Non autorisé'], 401);
            }
            $this->redirect('/admin/login', 'error', 'Veuillez vous connecter.');
        }
    }

    // ─── ADMIN: List posts ──────────────────────────────────────────────────
    public function index(): void {
        $this->middlewareAuth();
        $posts = Post::all('id DESC');
        $this->render('admin/blog/index', [
            'title'       => 'Articles du blog',
            'posts'       => $posts,
            'csrf_token'  => $this->generateCsrf(),
            'currentUser' => Auth::user(),
        ], 'admin/layout');
    }

    // ─── ADMIN: Create form ─────────────────────────────────────────────────
    public function createForm(): void {
        $this->middlewareAuth();
        $categories = Category::all('name ASC');
        $mediaList  = Media::all('id DESC');
        $this->render('admin/blog/create', [
            'title'      => 'Nouvel article',
            'categories' => $categories,
            'mediaList'  => $mediaList,
            'csrf_token' => $this->generateCsrf(),
        ], 'admin/layout');
    }

    // ─── ADMIN: Create submit ───────────────────────────────────────────────
    public function createSubmit(): void {
        $this->middlewareAuth();
        $this->validateCsrf();

        $v = new Validator();
        if (!$v->validate($_POST, ['title' => 'required'])) {
            $e = $v->getErrors(); $this->redirect('/admin/blog/create', 'error', reset($e));
        }

        $slug = Post::slugify($_POST['slug'] ?? $_POST['title']);
        if (Post::findBySlug($slug)) {
            $slug .= '-' . time();
        }

        $rawTags = trim($_POST['tags'] ?? '');
        $id = Post::create([
            'title'            => trim($_POST['title']),
            'slug'             => $slug,
            'excerpt'          => trim($_POST['excerpt'] ?? ''),
            'content'          => $_POST['content'] ?? '',
            'featured_image'   => trim($_POST['featured_image'] ?? ''),
            'category'         => trim($_POST['category'] ?? ''),
            'author'           => trim($_POST['author'] ?? 'Équipe Digitalium'),
            'status'           => $_POST['status'] === 'published' ? 'published' : 'draft',
            'is_featured'      => (int)($_POST['is_featured'] ?? 0),
            'meta_title'       => trim($_POST['meta_title'] ?? ''),
            'meta_description' => trim($_POST['meta_description'] ?? ''),
            'tags'             => $rawTags,
        ]);

        if ($rawTags) {
            Tag::syncForPost((int)$id, array_map('trim', explode(',', $rawTags)));
        }

        \App\Services\Cache::clear();
        $this->redirect("/admin/blog/edit/{$id}", 'success', 'Article créé avec succès !');
    }

    // ─── ADMIN: Edit form ───────────────────────────────────────────────────
    public function editForm(array $params): void {
        $this->middlewareAuth();
        $id   = (int)($params['id'] ?? 0);
        $post = Post::find($id);
        if (!$post) {
            $this->redirect('/admin/blog', 'error', 'Article introuvable.');
        }
        $categories = Category::all('name ASC');
        $mediaList  = Media::all('id DESC');
        $postTags   = Tag::getForPost($id);
        $this->render('admin/blog/edit', [
            'title'      => 'Modifier : ' . $post['title'],
            'post'       => $post,
            'categories' => $categories,
            'mediaList'  => $mediaList,
            'postTags'   => $postTags,
            'csrf_token' => $this->generateCsrf(),
        ], 'admin/layout');
    }

    // ─── ADMIN: Edit submit ─────────────────────────────────────────────────
    public function editSubmit(array $params): void {
        $this->middlewareAuth();
        $this->validateCsrf();

        $id   = (int)($params['id'] ?? 0);
        $post = Post::find($id);
        if (!$post) {
            $this->redirect('/admin/blog', 'error', 'Article introuvable.');
        }

        $v = new Validator();
        if (!$v->validate($_POST, ['title' => 'required'])) {
            $e = $v->getErrors(); $this->redirect("/admin/blog/edit/{$id}", 'error', reset($e));
        }

        $slug = Post::slugify($_POST['slug'] ?? $_POST['title']);
        $existing = Post::findBySlug($slug);
        if ($existing && (int)$existing['id'] !== $id) {
            $slug .= '-' . time();
        }

        $rawTags = trim($_POST['tags'] ?? '');
        Post::update($id, [
            'title'            => trim($_POST['title']),
            'slug'             => $slug,
            'excerpt'          => trim($_POST['excerpt'] ?? ''),
            'content'          => $_POST['content'] ?? '',
            'featured_image'   => trim($_POST['featured_image'] ?? ''),
            'category'         => trim($_POST['category'] ?? ''),
            'author'           => trim($_POST['author'] ?? 'Équipe Digitalium'),
            'status'           => $_POST['status'] === 'published' ? 'published' : 'draft',
            'is_featured'      => (int)($_POST['is_featured'] ?? 0),
            'meta_title'       => trim($_POST['meta_title'] ?? ''),
            'meta_description' => trim($_POST['meta_description'] ?? ''),
            'tags'             => $rawTags,
        ]);

        Tag::syncForPost($id, $rawTags ? array_map('trim', explode(',', $rawTags)) : []);

        \App\Services\Cache::clear();
        $this->redirect("/admin/blog/edit/{$id}", 'success', 'Article enregistré.');
    }

    // ─── ADMIN: Delete ──────────────────────────────────────────────────────
    public function delete(array $params): void {
        $this->middlewareAuth();
        $this->validateCsrf();

        $id = (int)($params['id'] ?? 0);
        if (Post::delete($id)) {
            \App\Services\Cache::clear();
            $this->redirect('/admin/blog', 'success', 'Article supprimé.');
        } else {
            $this->redirect('/admin/blog', 'error', 'Suppression impossible.');
        }
    }

    // ─── ADMIN: Categories ──────────────────────────────────────────────────
    public function categories(): void {
        $this->middlewareAuth();
        $categories = Category::getAllWithCount();
        $this->render('admin/blog/categories', [
            'title'      => 'Catégories du blog',
            'categories' => $categories,
            'csrf_token' => $this->generateCsrf(),
        ], 'admin/layout');
    }

    public function createCategory(): void {
        $this->middlewareAuth();
        $this->validateCsrf();

        $name = trim($_POST['name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        if (empty($name)) {
            $this->redirect('/admin/blog/categories', 'error', 'Le nom est obligatoire.');
        }
        $slug = Category::slugify($name);
        Category::create($name, $slug, $desc);
        \App\Services\Cache::clear();
        $this->redirect('/admin/blog/categories', 'success', "Catégorie « {$name} » créée.");
    }

    public function deleteCategory(array $params): void {
        $this->middlewareAuth();
        $this->validateCsrf();

        $id = (int)($params['id'] ?? 0);
        if (Category::delete($id)) {
            \App\Services\Cache::clear();
            $this->redirect('/admin/blog/categories', 'success', 'Catégorie supprimée.');
        } else {
            $this->redirect('/admin/blog/categories', 'error', 'Suppression impossible.');
        }
    }

    // ─── ADMIN: Comments moderation ────────────────────────────────────────
    public function commentsIndex(): void {
        $this->middlewareAuth();
        $status       = $_GET['status'] ?? '';
        $comments     = Comment::getAll($status);
        $pendingCount = Comment::countPending();
        $this->render('admin/blog/comments', [
            'title'        => 'Modération des commentaires',
            'comments'     => $comments,
            'pendingCount' => $pendingCount,
            'filterStatus' => $status,
            'csrf_token'   => $this->generateCsrf(),
            'currentUser'  => Auth::user(),
        ], 'admin/layout');
    }

    public function approveComment(array $params): void {
        $this->middlewareAuth();
        $this->validateCsrf();
        Comment::approve((int)($params['id'] ?? 0));
        $ref = $_SERVER['HTTP_REFERER'] ?? '/admin/blog/comments';
        $this->redirect($ref, 'success', 'Commentaire approuvé.');
    }

    public function rejectComment(array $params): void {
        $this->middlewareAuth();
        $this->validateCsrf();
        Comment::reject((int)($params['id'] ?? 0));
        $ref = $_SERVER['HTTP_REFERER'] ?? '/admin/blog/comments';
        $this->redirect($ref, 'success', 'Commentaire rejeté.');
    }

    public function deleteComment(array $params): void {
        $this->middlewareAuth();
        $this->validateCsrf();
        Comment::delete((int)($params['id'] ?? 0));
        $ref = $_SERVER['HTTP_REFERER'] ?? '/admin/blog/comments';
        $this->redirect($ref, 'success', 'Commentaire supprimé.');
    }

    // ─── FRONTEND: Submit comment ───────────────────────────────────────────
    public function submitComment(): void {
        header('Content-Type: application/json');

        // Honeypot anti-spam
        if (!empty($_POST['website'])) {
            echo json_encode(['success' => true, 'message' => 'Commentaire transmis.']);
            exit;
        }

        $postId = (int)($_POST['post_id'] ?? 0);
        $name   = trim($_POST['author_name'] ?? '');
        $email  = trim($_POST['author_email'] ?? '');
        $body   = trim($_POST['content'] ?? '');

        if (!$postId || strlen($name) < 2 || strlen($body) < 5) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Veuillez remplir tous les champs obligatoires.']);
            exit;
        }

        $post = Post::find($postId);
        if (!$post || $post['status'] !== 'published') {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Article introuvable.']);
            exit;
        }

        try {
            Comment::create([
                'post_id'      => $postId,
                'author_name'  => $name,
                'author_email' => $email,
                'content'      => $body,
            ]);
            echo json_encode(['success' => true, 'message' => 'Merci ! Votre commentaire est en attente de modération.']);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur serveur. Réessayez plus tard.']);
        }
        exit;
    }

    // ─── FRONTEND: Blog listing ─────────────────────────────────────────────
    public function frontendIndex(): void {
        $page_num = max(1, (int)($_GET['page'] ?? 1));
        $perPage  = 9;
        $offset   = ($page_num - 1) * $perPage;
        $total    = Post::countPublished();
        $posts    = Post::getPublished($perPage, $offset);
        $featured = Post::getFeatured(1);
        $categories = Category::getAllWithCount();
        $settings = \App\Models\Setting::getAll();
        $menuPages = array_filter(\App\Models\Page::all('sort_order ASC'), fn($p) => $p['status'] === 'published');
        $blogPage = \App\Models\Page::findBySlug('blog') ?? ['title' => 'Blog', 'meta_title' => 'Blog', 'meta_description' => '', 'slug' => 'blog', 'id' => 0];

        $this->render('frontend/blog_index', [
            'page'        => $blogPage,
            'posts'       => $posts,
            'featured'    => $featured[0] ?? null,
            'categories'  => $categories,
            'settings'    => $settings,
            'menuPages'   => $menuPages,
            'currentSlug' => 'blog',
            'total'       => $total,
            'perPage'     => $perPage,
            'page_num'    => $page_num,
            'totalPages'  => ceil($total / $perPage),
        ], 'frontend/layout');
    }

    // ─── FRONTEND: Single post ──────────────────────────────────────────────
    public function frontendPost(array $params): void {
        $slug = trim($params['slug'] ?? '');
        $post = Post::findBySlug($slug);
        if (!$post || $post['status'] !== 'published') {
            http_response_code(404);
            $settings  = \App\Models\Setting::getAll();
            $menuPages = array_filter(\App\Models\Page::all('sort_order ASC'), fn($p) => $p['status'] === 'published');
            $this->render('frontend/404', [
                'page'      => ['title' => '404', 'meta_title' => 'Article introuvable', 'meta_description' => '', 'hero_status' => 0],
                'settings'  => $settings,
                'menuPages' => $menuPages,
                'currentSlug' => 'blog',
            ], 'frontend/layout');
            return;
        }

        $related = \App\Services\Database::fetchAll(
            "SELECT * FROM blog_posts WHERE status = 'published' AND category = :cat AND id != :id ORDER BY published_at DESC LIMIT 3",
            ['cat' => $post['category'] ?? '', 'id' => $post['id']]
        );

        $settings  = \App\Models\Setting::getAll();
        $menuPages = array_filter(\App\Models\Page::all('sort_order ASC'), fn($p) => $p['status'] === 'published');
        $blogPage  = \App\Models\Page::findBySlug('blog') ?? ['title' => $post['title'], 'meta_title' => $post['meta_title'] ?? $post['title'], 'meta_description' => $post['excerpt'] ?? '', 'slug' => 'blog', 'id' => 0, 'hero_status' => 0];

        $postTags = Tag::getForPost((int)$post['id']);
        $comments = Comment::getForPost((int)$post['id'], true);

        $this->render('frontend/blog_post', [
            'page'        => array_merge($blogPage, [
                'meta_title'       => $post['meta_title'] ?: $post['title'],
                'meta_description' => $post['meta_description'] ?: $post['excerpt'],
                'hero_status'      => 0,
            ]),
            'post'        => $post,
            'related'     => $related,
            'postTags'    => $postTags,
            'comments'    => $comments,
            'settings'    => $settings,
            'menuPages'   => $menuPages,
            'currentSlug' => 'blog',
            'csrf_token'  => \App\Services\CSRF::getToken(),
        ], 'frontend/layout');
    }
}
