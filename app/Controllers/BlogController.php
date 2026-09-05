<?php
namespace App\Controllers;

use App\Services\Auth;
use App\Helpers\Validator;
use App\Models\Post;
use App\Models\Category;
use App\Models\Media;
use App\Models\Tag;
use App\Models\Comment;
use App\Models\Subscriber;
use App\Models\Setting;

/**
 * Module Insights — articles, catégories, tags, commentaires et abonnés.
 *
 * ── Une seule adresse par contenu ───────────────────────────────────────────
 * Le centre de ressources vit à /insights. /blog et /blog/{slug} ne servent
 * plus de page : ils redirigent en 301. Servir le même article aux deux
 * adresses aurait divisé son référencement entre deux URLs concurrentes — la
 * dette DT-05, déjà corrigée sur les sous-pages Solutions.
 *
 * La liste /insights est une page CMS ordinaire, rendue par ses sections
 * (insights_featured, insights_grid, insights_resources, newsletter). Il n'y a
 * donc pas de second moteur de listing à maintenir, et l'administration peut
 * réordonner ou désactiver chaque bloc depuis /admin/pages.
 */
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
            'title'       => 'Articles Insights',
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
            'title'         => 'Nouvel article',
            'categories'    => $categories,
            'mediaList'     => $mediaList,
            'resourceTypes' => self::typesRessource(),
            'csrf_token'    => $this->generateCsrf(),
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
        $id = Post::create(self::champsFormulaire($_POST, $slug, $rawTags));

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
            'title'         => 'Modifier : ' . $post['title'],
            'post'          => $post,
            'categories'    => $categories,
            'mediaList'     => $mediaList,
            'postTags'      => $postTags,
            'resourceTypes' => self::typesRessource(),
            'readingTime'   => Post::dureeLecture($post),
            'csrf_token'    => $this->generateCsrf(),
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
        Post::update($id, self::champsFormulaire($_POST, $slug, $rawTags));

        Tag::syncForPost($id, $rawTags ? array_map('trim', explode(',', $rawTags)) : []);

        \App\Services\Cache::clear();
        $this->redirect("/admin/blog/edit/{$id}", 'success', 'Article enregistré.');
    }

    /**
     * Traduit le formulaire d'édition en champs du modèle.
     *
     * Le modèle ignore de lui-même les colonnes absentes de la base : tant que
     * la migration n'a pas tourné, les champs neufs sont simplement écartés.
     */
    private static function champsFormulaire(array $p, string $slug, string $rawTags): array {
        return [
            'title'            => trim($p['title'] ?? ''),
            'slug'             => $slug,
            'excerpt'          => trim($p['excerpt'] ?? ''),
            'content'          => $p['content'] ?? '',
            'featured_image'   => trim($p['featured_image'] ?? ''),
            'category'         => trim($p['category'] ?? ''),
            'author'           => trim($p['author'] ?? ''),
            'status'           => ($p['status'] ?? '') === 'published' ? 'published' : 'draft',
            'is_featured'      => (int)($p['is_featured'] ?? 0),
            'meta_title'       => trim($p['meta_title'] ?? ''),
            'meta_description' => trim($p['meta_description'] ?? ''),
            'tags'             => $rawTags,
            'published_at'     => trim($p['published_at'] ?? ''),
            'reading_time'     => (int)($p['reading_time'] ?? 0),
            'sort_order'       => (int)($p['sort_order'] ?? 0),
            'og_image'         => trim($p['og_image'] ?? ''),
            'resource_type'    => self::typeRessourceValide($p['resource_type'] ?? ''),
            'resource_file'    => trim($p['resource_file'] ?? ''),
            'resource_cta'     => trim($p['resource_cta'] ?? ''),
        ];
    }

    /** Familles de contenus stratégiques proposées dans l'éditeur. */
    public static function typesRessource(): array {
        return [
            'guide'       => 'Guide pratique',
            'rapport'     => 'Étude / rapport',
            'checklist'   => 'Checklist',
            'livre-blanc' => 'Livre blanc',
            'cas-usage'   => 'Cas d’usage',
            'comparatif'  => 'Comparatif',
        ];
    }

    private static function typeRessourceValide(string $valeur): string {
        $valeur = trim($valeur);
        return isset(self::typesRessource()[$valeur]) ? $valeur : '';
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
            'title'      => 'Catégories Insights',
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
        if (Category::findBySlug($slug)) {
            $this->redirect('/admin/blog/categories', 'error', "La catégorie « {$name} » existe déjà.");
        }
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

    // ─── ADMIN: Tags ────────────────────────────────────────────────────────
    public function tags(): void {
        $this->middlewareAuth();
        $this->render('admin/blog/tags', [
            'title'      => 'Tags Insights',
            'tags'       => Tag::getAllWithCount(),
            'csrf_token' => $this->generateCsrf(),
        ], 'admin/layout');
    }

    public function renameTag(array $params): void {
        $this->middlewareAuth();
        $this->validateCsrf();

        $id  = (int)($params['id'] ?? 0);
        $nom = trim($_POST['name'] ?? '');
        if ($nom === '') {
            $this->redirect('/admin/blog/tags', 'error', 'Le nom est obligatoire.');
        }
        if (!Tag::renommer($id, $nom)) {
            $this->redirect('/admin/blog/tags', 'error', "Un tag « {$nom} » existe déjà.");
        }
        \App\Services\Cache::clear();
        $this->redirect('/admin/blog/tags', 'success', 'Tag renommé.');
    }

    public function deleteTag(array $params): void {
        $this->middlewareAuth();
        $this->validateCsrf();

        // Les rattachements partent avec le tag : blog_post_tags porte une clé
        // étrangère ON DELETE CASCADE. Les articles, eux, ne bougent pas.
        if (Tag::delete((int)($params['id'] ?? 0))) {
            \App\Services\Cache::clear();
            $this->redirect('/admin/blog/tags', 'success', 'Tag supprimé.');
        }
        $this->redirect('/admin/blog/tags', 'error', 'Suppression impossible.');
    }

    // ─── ADMIN: Comments moderation ────────────────────────────────────────
    public function commentsIndex(): void {
        $this->middlewareAuth();
        try {
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
        } catch (\Throwable $e) {
            $entry = date('Y-m-d H:i:s') . ' [COMMENTS-ERROR] ' . get_class($e) . ': ' . $e->getMessage()
                . ' in ' . $e->getFile() . ':' . $e->getLine() . "\n";
            @file_put_contents(ROOT_PATH . '/storage/logs/errors.log', $entry, FILE_APPEND | LOCK_EX);
            $this->redirect('/admin/blog', 'error', 'Erreur de chargement des commentaires — journalisé.');
        }
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

    // ─── ADMIN: Abonnés newsletter ──────────────────────────────────────────
    public function subscribers(): void {
        $this->middlewareAuth();
        $statut = (string)($_GET['statut'] ?? '');
        $q      = (string)($_GET['q'] ?? '');
        $this->render('admin/newsletter/index', [
            'title'       => 'Abonnés à la newsletter',
            'abonnes'     => Subscriber::rechercher($statut, $q),
            'stats'       => Subscriber::statistiques(),
            'filtres'     => ['statut' => $statut, 'q' => $q],
            'csrf_token'  => $this->generateCsrf(),
            'currentUser' => Auth::user(),
        ], 'admin/layout');
    }

    public function subscriberStatus(array $params): void {
        $this->middlewareAuth();
        $this->validateCsrf();
        $ok = Subscriber::changerStatut((int)($params['id'] ?? 0), (string)($_POST['statut'] ?? ''));
        $this->redirect('/admin/newsletter', $ok ? 'success' : 'error',
            $ok ? 'Statut mis à jour.' : 'Statut inconnu.');
    }

    public function deleteSubscriber(array $params): void {
        $this->middlewareAuth();
        $this->validateCsrf();
        Subscriber::delete((int)($params['id'] ?? 0));
        $this->redirect('/admin/newsletter', 'success', 'Abonné supprimé.');
    }

    /** Export CSV des abonnés — reprend exactement les filtres affichés. */
    public function exportSubscribers(): void {
        $this->middlewareAuth();

        $lignes = Subscriber::rechercher((string)($_GET['statut'] ?? ''), (string)($_GET['q'] ?? ''), 2000);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="abonnes-newsletter-' . date('Y-m-d') . '.csv"');
        header('X-Content-Type-Options: nosniff');

        $out = fopen('php://output', 'w');
        // Marque d'ordre des octets : sans elle, Excel lit les accents de travers.
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['Email', 'Statut', 'Source', 'Adresse IP', 'Inscrit le'], ';');
        foreach ($lignes as $l) {
            fputcsv($out, [
                $l['email'] ?? '',
                Subscriber::STATUTS[$l['status'] ?? ''] ?? ($l['status'] ?? ''),
                $l['source'] ?? '',
                $l['ip_address'] ?? '',
                !empty($l['created_at']) ? date('d/m/Y H:i', strtotime($l['created_at'])) : '',
            ], ';');
        }
        fclose($out);
        exit;
    }

    // ─── FRONTEND: Inscription newsletter ───────────────────────────────────

    /**
     * Enregistre un abonné.
     *
     * Le jeton CSRF est déjà vérifié globalement par le Router sur tous les POST ;
     * la vérification est refaite ici pour que la méthode reste sûre même si elle
     * est un jour appelée par un autre chemin.
     */
    public function subscribe(): void {
        $this->validateCsrf();

        $retour = static function (string $type, string $message): void {
            if (session_status() === PHP_SESSION_NONE) { session_start(); }
            $_SESSION['newsletter_retour'] = ['type' => $type, 'message' => $message];
        };

        $destination = $this->retourSur();

        // Piège à robots : on renvoie la confirmation habituelle sans rien
        // enregistrer. Un automate qui reçoit une erreur réessaie autrement.
        if (trim((string)($_POST['website'] ?? '')) !== '') {
            $this->redirection($destination . '?newsletter=ok#newsletter');
        }

        $email = trim((string)($_POST['email'] ?? ''));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) {
            $retour('erreur', 'Cette adresse email ne semble pas valide.');
            $this->redirection($destination . '#newsletter');
        }

        $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');
        $plafond = (int)(Setting::getVal('newsletter_rate_limit') ?: 5);
        if ($plafond > 0 && Subscriber::envoisRecents($ip, 60) >= $plafond) {
            $retour('erreur', 'Trop d’inscriptions depuis cette connexion. Réessayez dans une heure.');
            $this->redirection($destination . '#newsletter');
        }

        try {
            $resultat = Subscriber::inscrire($email, (string)($_POST['source'] ?? ''), $ip);
        } catch (\Throwable $e) {
            @file_put_contents(
                ROOT_PATH . '/storage/logs/errors.log',
                date('Y-m-d H:i:s') . ' [NEWSLETTER] ' . get_class($e) . ': ' . $e->getMessage() . "\n",
                FILE_APPEND | LOCK_EX
            );
            $retour('erreur', 'Inscription momentanément indisponible. Réessayez plus tard.');
            $this->redirection($destination . '#newsletter');
        }

        // Une adresse déjà inscrite reçoit la même confirmation : répondre
        // « vous êtes déjà abonné » révélerait qui figure dans la liste.
        $retour('succes', $resultat === 'existant'
            ? 'Votre adresse est bien enregistrée.'
            : 'Merci — votre inscription est enregistrée.');
        $this->redirection($destination . '?newsletter=ok#newsletter');
    }

    /**
     * Page d'où vient la soumission, pour y ramener le visiteur.
     * Seul un chemin interne est accepté : un Referer pointant ailleurs
     * transformerait le formulaire en tremplin de redirection.
     */
    private function retourSur(): string {
        $ref = (string)($_SERVER['HTTP_REFERER'] ?? '');
        if ($ref !== '') {
            $chemin = (string)parse_url($ref, PHP_URL_PATH);
            $hote   = (string)parse_url($ref, PHP_URL_HOST);
            if ($chemin !== '' && ($hote === '' || $hote === ($_SERVER['HTTP_HOST'] ?? ''))) {
                return $chemin;
            }
        }
        return url('/insights');
    }

    private function redirection(string $url): void {
        header('Location: ' . $url, true, 303);
        exit;
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

    // ─── FRONTEND: Anciennes adresses /blog ─────────────────────────────────

    /** /blog → /insights, définitivement. */
    public function legacyIndex(): void {
        $requete = (string)($_SERVER['QUERY_STRING'] ?? '');
        header('Location: ' . url('/insights') . ($requete !== '' ? '?' . $requete : ''), true, 301);
        exit;
    }

    /** /blog/{slug} → /insights/{slug}, définitivement. */
    public function legacyPost(array $params): void {
        $slug = trim($params['slug'] ?? '');
        header('Location: ' . url('/insights' . ($slug !== '' ? '/' . rawurlencode($slug) : '')), true, 301);
        exit;
    }

    // ─── FRONTEND: Article ──────────────────────────────────────────────────
    public function frontendPost(array $params): void {
        $slug = trim($params['slug'] ?? '');
        $post = Post::findBySlug($slug);

        $settings  = \App\Models\Setting::getAll();
        $menuPages = array_filter(\App\Models\Page::all('sort_order ASC'), fn($p) => $p['status'] === 'published');

        if (!$post || $post['status'] !== 'published') {
            http_response_code(404);
            $this->render('frontend/404', [
                'page'        => ['title' => '404', 'meta_title' => 'Article introuvable', 'meta_description' => '', 'hero_status' => 0],
                'settings'    => $settings,
                'menuPages'   => $menuPages,
                'currentSlug' => 'insights',
            ], 'frontend/layout');
            return;
        }

        $pageHote = \App\Models\Page::findBySlug('insights')
                 ?? \App\Models\Page::findBySlug('blog')
                 ?? ['title' => $post['title'], 'slug' => 'insights', 'id' => 0];

        $this->render('frontend/blog_post', [
            // L'adresse canonique est celle de l'article, pas celle de la liste :
            // sans cela, tous les articles se déclaraient être /blog.
            'page'        => array_merge($pageHote, [
                'title'            => $post['title'],
                'meta_title'       => $post['meta_title'] ?: $post['title'],
                'meta_description' => $post['meta_description'] ?: $post['excerpt'],
                'canonical_path'   => '/insights/' . $post['slug'],
                'og_type'          => 'article',
                'og_image'         => trim((string)($post['og_image'] ?? '')) ?: trim((string)($post['featured_image'] ?? '')),
                'hero_status'      => 0,
                'parent_slug'      => '',
            ]),
            'post'        => $post,
            'related'     => Post::similaires((int)$post['id'], (string)($post['category'] ?? ''), 3),
            'postTags'    => Tag::getForPost((int)$post['id']),
            'comments'    => Comment::getForPost((int)$post['id'], true),
            'settings'    => $settings,
            'menuPages'   => $menuPages,
            'currentSlug' => 'insights',
            'csrf_token'  => \App\Services\CSRF::getToken(),
        ], 'frontend/layout');
    }
}
