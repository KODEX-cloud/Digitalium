<?php
namespace App\Controllers;

use App\Services\Auth;
use App\Models\Page;
use App\Models\Section;
use App\Models\Block;
use App\Models\Setting;
use App\Models\Message;
use App\Helpers\Validator;
use App\Helpers\Sanitizer;

class HomeController extends Controller {
    /**
     * Render the main homepage.
     */
    public function index(): void {
        $this->renderPage(['slug' => 'home']);
    }

    /**
     * Render a dynamic page based on slug.
     * Fault-tolerant: each data-loading step is isolated — a single failure
     * never crashes the whole page.
     */
    public function renderPage(array $params): void {
        $slug = trim($params['slug'] ?? 'home');

        try {
            $this->doRenderPage($slug);
        } catch (\Throwable $e) {
            // Log the full exception; show a friendly 500 page
            $logPath = ROOT_PATH . '/storage/logs/errors.log';
            $entry = date('Y-m-d H:i:s')
                . ' [CONTROLLER] HomeController::renderPage slug=' . $slug
                . ' — ' . get_class($e) . ': ' . $e->getMessage()
                . ' in ' . $e->getFile() . ':' . $e->getLine() . "\n";
            @file_put_contents($logPath, $entry, FILE_APPEND | LOCK_EX);

            if (ENVIRONMENT === 'development') {
                throw $e;
            }

            http_response_code(500);
            $view500 = ROOT_PATH . '/app/Views/errors/500.php';
            if (file_exists($view500)) { include $view500; }
            else { echo '<h1>Service temporairement indisponible</h1>'; }
            exit;
        }
    }

    /**
     * Internal render logic — separated so the public method can wrap it cleanly.
     */
    /**
     * Rend une page rattachée à un parent : /solutions/software-platforms.
     *
     * Le couple (parent, enfant) est résolu en base plutôt que déduit du nom :
     * une page n'est accessible sous un parent que si elle y est réellement
     * rattachée. Une URL imbriquée qui ne correspond à rien renvoie 404, ce qui
     * vaut mieux que de servir silencieusement autre chose.
     */
    public function renderChild(array $params): void {
        $parent = trim($params['parent'] ?? '');
        $child  = trim($params['child'] ?? '');

        try {
            $page = Page::findChild($parent, $child);
            if (!$page) {
                $this->render404();
                return;
            }
            $this->doRenderPage($page['slug'], true);
        } catch (\Throwable $e) {
            $logPath = ROOT_PATH . '/storage/logs/errors.log';
            @file_put_contents($logPath, date('Y-m-d H:i:s')
                . " [CONTROLLER] HomeController::renderChild {$parent}/{$child} — "
                . get_class($e) . ': ' . $e->getMessage()
                . ' in ' . $e->getFile() . ':' . $e->getLine() . "\n", FILE_APPEND | LOCK_EX);

            if (ENVIRONMENT === 'development') { throw $e; }
            http_response_code(500);
            echo 'Une erreur est survenue.';
        }
    }

    private function doRenderPage(string $slug, bool $viaParent = false): void {
        $cacheKey   = 'page_' . $slug;
        $cachedData = \App\Services\Cache::get($cacheKey, 3600);

        if ($cachedData !== null) {
            $page          = $cachedData['page'];
            $sections      = $cachedData['sections'];
            $sectionBlocks = $cachedData['sectionBlocks'];
            $settings      = $cachedData['settings'];
            $activeMenuPages = $cachedData['menuPages'];
        } else {
            $page = Page::findBySlug($slug);
            if (!$page) {
                $this->render404();
                return;
            }

            // Each block loads independently — a failure returns [] instead of crashing
            $sections      = Section::getActiveByPage($page['id']) ?: [];
            $sectionBlocks = [];
            foreach ($sections as $sec) {
                try {
                    $sectionBlocks[$sec['id']] = Block::getStructuredContent($sec['id']);
                } catch (\Throwable $e) {
                    $sectionBlocks[$sec['id']] = ['single' => [], 'groups' => []];
                }
            }

            $settings        = Setting::getAll() ?: [];
            $pagesMenu       = Page::all('sort_order ASC, id ASC') ?: [];
            $activeMenuPages = array_filter($pagesMenu, fn($p) => ($p['status'] ?? '') === 'published');

            \App\Services\Cache::set($cacheKey, [
                'page'     => $page,
                'sections' => $sections,
                'sectionBlocks' => $sectionBlocks,
                'settings' => $settings,
                'menuPages' => $activeMenuPages,
            ]);
        }

        // Une page rattachée à un parent n'a qu'une seule URL : l'imbriquée.
        // Sans cette redirection, /software-platforms et
        // /solutions/software-platforms serviraient le même contenu — le doublon
        // d'URL relevé en DT-05. Le test se fait ici, après le cache, donc sans
        // requête supplémentaire.
        $parentSlug = trim((string)($page['parent_slug'] ?? ''));
        if ($parentSlug !== '' && !$viaParent) {
            header('Location: ' . url('/' . $parentSlug . '/' . $page['slug']), true, 301);
            return;
        }

        if (($page['status'] ?? '') === 'draft' && !Auth::check()) {
            $this->render404();
            return;
        }

        $this->render('frontend/page', [
            'page'          => $page,
            'sections'      => $sections,
            'sectionBlocks' => $sectionBlocks,
            'settings'      => $settings,
            'menuPages'     => $activeMenuPages,
            'currentSlug'   => $slug,
            'csrf_token'    => \App\Services\CSRF::getToken(),
        ], 'frontend/layout');
    }

    /**
     * Handle public contact form submission via AJAX/POST.
     */
    public function contactSubmit(): void {
        header('Content-Type: application/json');

        // 1. Sanitize incoming inputs
        $data = Sanitizer::clean($_POST);

        // 2. Validate inputs
        $validator = new Validator();
        $rules = [
            'name' => 'required|min:2|max:100',
            'email' => 'required|email',
            'message' => 'required|min:10|max:2000'
        ];

        if (!$validator->validate($data, $rules)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'errors' => $validator->getErrors()
            ]);
            exit;
        }

        // 3. Anti-spam: honeypot field check
        if (!empty($_POST['website'])) {
            echo json_encode(['success' => true, 'message' => 'Message transmis.']);
            exit;
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        // 4. Save to database
        try {
            Message::create([
                'nom'       => $data['name'],
                'email'     => $data['email'],
                'telephone' => $data['phone'] ?? null,
                'sujet'     => $data['subject'] ?? null,
                'message'   => $data['message'],
                'ip_address' => $ip,
            ]);
        } catch (\Throwable $e) {
            // Fallback: log only if DB write fails
            $storageDir = ROOT_PATH . '/storage/logs';
            if (!is_dir($storageDir)) mkdir($storageDir, 0755, true);
            file_put_contents($storageDir . '/contacts.log',
                '[' . date('Y-m-d H:i:s') . '] DB ERROR: ' . $e->getMessage() . "\n",
                FILE_APPEND
            );
        }

        // 5. Send notification email (best effort)
        $settings = Setting::getAll();
        $toEmail  = $settings['contact_email'] ?? null;
        if ($toEmail && filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            $subject  = '[Digitalium] Nouveau message de ' . $data['name'];
            $body  = "Nouveau message reçu depuis le site.\n\n";
            $body .= "Nom     : " . $data['name'] . "\n";
            $body .= "Email   : " . $data['email'] . "\n";
            $body .= "Téléphone : " . ($data['phone'] ?? 'Non renseigné') . "\n";
            $body .= "Sujet   : " . ($data['subject'] ?? 'Sans sujet') . "\n\n";
            $body .= "Message :\n" . $data['message'] . "\n\n";
            $body .= "---\nIP : $ip · Date : " . date('d/m/Y H:i:s');
            $headers  = "From: no-reply@digitaliumgroup.com\r\n";
            $headers .= "Reply-To: " . $data['email'] . "\r\n";
            $headers .= "X-Mailer: PHP/" . PHP_VERSION;
            @mail($toEmail, $subject, $body, $headers);
        }

        // 6. Return success response
        echo json_encode([
            'success' => true,
            'message' => 'Votre message a bien été transmis. Un architecte conseil de notre équipe prendra contact avec vous sous 24 heures.'
        ]);
        exit;
    }

    /**
     * Réception d'une demande de projet — section `lead_form` de /contact.
     *
     * Envoi de formulaire classique, pas d'appel JSON : le formulaire doit
     * fonctionner sans JavaScript, y compris avec une pièce jointe. En cas de
     * refus, erreurs et valeurs saisies repartent en session et le visiteur
     * retrouve son texte.
     *
     * Le jeton CSRF est déjà vérifié pour tous les POST par le routeur
     * (Router.php:38) ; on ne le revalide pas ici.
     *
     * Défenses, dans l'ordre où elles se déclenchent :
     *   1. pot de miel  — un robot remplit le champ caché `website`
     *   2. plafond par IP — au-delà de N demandes par heure, on refuse
     *   3. validation serveur — indépendante de celle du navigateur
     *   4. pièce jointe — taille, extension ET type réel du contenu
     */
    public function leadSubmit(): void {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        $retour = url('/contact');

        // 1. Pot de miel. On répond « c'est envoyé » sans rien enregistrer :
        //    signaler le rejet apprendrait au robot à contourner le piège.
        if (trim((string)($_POST['website'] ?? '')) !== '') {
            $this->redirigerVers($retour . '?demande=ok#demande');
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? '';

        // 2. Plafond par adresse IP. Le seuil est un réglage, pas une constante
        //    de code : une campagne ou un salon peut légitimement faire monter
        //    le rythme depuis un même réseau.
        $settings = Setting::getAll() ?: [];
        $plafond  = (int)($settings['lead_rate_limit'] ?? 5);
        if ($plafond > 0 && Message::envoisRecents($ip, 60) >= $plafond) {
            $_SESSION['lead_errors'] = [
                'global' => "Trop de demandes envoyées depuis cette connexion. Réessayez dans une heure, ou écrivez-nous directement par email.",
            ];
            $this->redirigerVers($retour . '#demande');
        }

        // 3. Validation serveur, indépendante du navigateur.
        $in = Sanitizer::clean($_POST);
        $t  = static fn(string $k, int $max): string => mb_substr(trim((string)($in[$k] ?? '')), 0, $max);

        $champs = [
            'nom'        => $t('nom', 150),
            'entreprise' => $t('entreprise', 150),
            'secteur'    => $t('secteur', 100),
            'pays'       => $t('pays', 100),
            'email'      => $t('email', 255),
            'telephone'  => $t('telephone', 30),
            'besoin'     => $t('besoin', 100),
            'message'    => $t('message', 4000),
            'objectif'   => $t('objectif', 255),
            'urgence'    => $t('urgence', 50),
            'budget'     => $t('budget', 80),
        ];

        $erreurs = [];
        if (mb_strlen($champs['nom']) < 2) {
            $erreurs['nom'] = 'Indiquez votre nom (2 caractères minimum).';
        }
        if (!filter_var($champs['email'], FILTER_VALIDATE_EMAIL)) {
            $erreurs['email'] = 'Cette adresse email ne semble pas valide.';
        }
        if (mb_strlen($champs['message']) < 10) {
            $erreurs['message'] = 'Décrivez votre besoin en quelques mots (10 caractères minimum).';
        }

        // 4. Pièce jointe : facultative, mais contrôlée si elle est là.
        $piece = null; $pieceNom = null;
        if (!empty($_FILES['document']['name']) && (int)($_FILES['document']['error'] ?? 4) !== UPLOAD_ERR_NO_FILE) {
            $resultat = $this->enregistrerPieceJointe($_FILES['document']);
            if (isset($resultat['erreur'])) {
                $erreurs['document'] = $resultat['erreur'];
            } else {
                $piece    = $resultat['fichier'];
                $pieceNom = $resultat['nom_origine'];
            }
        }

        if ($erreurs) {
            $_SESSION['lead_errors'] = $erreurs;
            $_SESSION['lead_old']    = $champs;
            $this->redirigerVers($retour . '#demande');
        }

        // 5. Enregistrement de la demande commerciale.
        try {
            $id = Message::createLead($champs + [
                'sujet'            => $champs['besoin'] !== '' ? $champs['besoin'] : 'Demande de projet',
                'ip_address'       => $ip,
                'piece_jointe'     => $piece,
                'piece_jointe_nom' => $pieceNom,
                'source'           => 'Formulaire /contact',
            ]);
        } catch (\Throwable $e) {
            // La base a refusé : on ne perd pas la demande pour autant.
            @file_put_contents(
                ROOT_PATH . '/storage/logs/contacts.log',
                date('Y-m-d H:i:s') . ' [LEAD-ECHEC] ' . $e->getMessage()
                    . ' | ' . json_encode($champs, JSON_UNESCAPED_UNICODE) . "\n",
                FILE_APPEND | LOCK_EX
            );
            $_SESSION['lead_errors'] = [
                'global' => "Votre demande n'a pas pu être enregistrée. Écrivez-nous directement par email, nous la traiterons.",
            ];
            $_SESSION['lead_old'] = $champs;
            $this->redirigerVers($retour . '#demande');
        }

        // 6. Notification par email — au mieux, jamais bloquante.
        $toEmail = $settings['contact_email'] ?? null;
        if ($toEmail && filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            $corps = "Nouvelle demande de projet reçue depuis le site.\n\n";
            foreach ([
                'Nom' => 'nom', 'Entreprise' => 'entreprise', 'Secteur' => 'secteur',
                'Pays' => 'pays', 'Email' => 'email', 'Téléphone' => 'telephone',
                'Besoin' => 'besoin', 'Objectif' => 'objectif', 'Urgence' => 'urgence',
                'Budget' => 'budget',
            ] as $libelle => $cle) {
                $corps .= str_pad($libelle, 12) . ': ' . ($champs[$cle] !== '' ? $champs[$cle] : '—') . "\n";
            }
            $corps .= "\nDescription :\n" . $champs['message'] . "\n\n";
            if ($pieceNom) { $corps .= "Pièce jointe : " . $pieceNom . "\n"; }
            $corps .= "---\nDemande #$id · IP : $ip · " . date('d/m/Y H:i:s') . "\n";
            $corps .= "À traiter dans l'admin : " . url('/admin/messages/' . $id) . "\n";

            $entetes  = "From: no-reply@digitaliumgroup.com\r\n";
            $entetes .= "Reply-To: " . $champs['email'] . "\r\n";
            $entetes .= "Content-Type: text/plain; charset=UTF-8\r\n";
            @mail($toEmail, '[Digitalium] Demande de projet — ' . $champs['nom'], $corps, $entetes);
        }

        $this->redirigerVers($retour . '?demande=ok#demande');
    }

    /**
     * Contrôle et range une pièce jointe hors de la racine web.
     *
     * Le fichier va dans `storage/uploads/leads/`, que le .htaccess refuse de
     * servir : un document déposé par un inconnu ne doit jamais être atteignable
     * par son URL, encore moins exécutable. Il se télécharge depuis l'admin,
     * après authentification.
     *
     * @return array{fichier:string,nom_origine:string}|array{erreur:string}
     */
    private function enregistrerPieceJointe(array $f): array {
        $codes = [
            UPLOAD_ERR_INI_SIZE  => "Le fichier dépasse la taille autorisée par le serveur.",
            UPLOAD_ERR_FORM_SIZE => "Le fichier est trop volumineux.",
            UPLOAD_ERR_PARTIAL   => "Le fichier n'a été que partiellement transféré.",
            UPLOAD_ERR_NO_TMP_DIR=> "Le serveur n'a pas pu recevoir le fichier.",
            UPLOAD_ERR_CANT_WRITE=> "Le serveur n'a pas pu enregistrer le fichier.",
            UPLOAD_ERR_EXTENSION => "Le transfert du fichier a été interrompu.",
        ];
        $err = (int)($f['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($err !== UPLOAD_ERR_OK) {
            return ['erreur' => $codes[$err] ?? "Le fichier n'a pas pu être transféré."];
        }
        if (!is_uploaded_file($f['tmp_name'] ?? '')) {
            return ['erreur' => "Fichier invalide."];
        }

        $verdict = $this->pieceJointeRecevable(
            (string)($f['name'] ?? ''), (string)$f['tmp_name'], (int)($f['size'] ?? 0)
        );
        if (isset($verdict['erreur'])) { return $verdict; }
        $ext = $verdict['extension'];

        $dossier = ROOT_PATH . '/storage/uploads/leads';
        if (!is_dir($dossier) && !@mkdir($dossier, 0755, true) && !is_dir($dossier)) {
            return ['erreur' => "Le serveur n'a pas pu enregistrer le fichier."];
        }

        // Nom tiré au sort : le nom d'origine est conservé en base pour
        // l'affichage, jamais utilisé comme chemin sur le disque.
        $nomDisque = date('Ymd') . '-' . bin2hex(random_bytes(8)) . '.' . $ext;
        if (!@move_uploaded_file($f['tmp_name'], $dossier . '/' . $nomDisque)) {
            return ['erreur' => "Le serveur n'a pas pu enregistrer le fichier."];
        }
        @chmod($dossier . '/' . $nomDisque, 0644);

        return [
            'fichier'     => $nomDisque,
            'nom_origine' => mb_substr((string)($f['name'] ?? 'document'), 0, 200),
        ];
    }

    /**
     * Recevabilité d'un fichier : taille, extension et type réel du contenu.
     *
     * Isolée de `enregistrerPieceJointe` pour être vérifiable hors requête HTTP.
     * Cette séparation n'est pas cosmétique : tant que ces contrôles vivaient
     * derrière `is_uploaded_file()`, aucun test ne pouvait les atteindre — ils
     * étaient réputés bons sans l'avoir jamais été.
     *
     * @return array{extension:string}|array{erreur:string}
     */
    private function pieceJointeRecevable(string $nom, string $chemin, int $taille): array {
        $maxOctets = 5 * 1024 * 1024;
        if ($taille > $maxOctets) {
            return ['erreur' => "Le fichier dépasse 5 Mo. Envoyez-le par email si nécessaire."];
        }

        /* Extension ET type réel doivent concorder : se fier au seul nom du
           fichier laisserait passer un script renommé en .pdf. */
        $autorises = [
            'pdf'  => ['application/pdf'],
            'doc'  => ['application/msword'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'],
            'odt'  => ['application/vnd.oasis.opendocument.text', 'application/zip'],
            'txt'  => ['text/plain'],
            'png'  => ['image/png'],
            'jpg'  => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'webp' => ['image/webp'],
        ];
        // pathinfo ne retient que ce qui suit le DERNIER point : « doc.pdf.php »
        // donne donc bien « php », et se fait refuser.
        $ext = strtolower(pathinfo($nom, PATHINFO_EXTENSION));
        if ($ext === '' || !isset($autorises[$ext])) {
            return ['erreur' => "Format non accepté. Formats possibles : PDF, Word, ODT, TXT, PNG, JPG, WEBP."];
        }
        if (function_exists('finfo_open') && is_file($chemin)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime  = (string)finfo_file($finfo, $chemin);
            finfo_close($finfo);
            if (!in_array($mime, $autorises[$ext], true)) {
                return ['erreur' => "Le contenu du fichier ne correspond pas à son extension."];
            }
        } elseif (is_file($chemin) && !$this->signatureConcorde($ext, $chemin)) {
            // L'extension `fileinfo` n'est pas garantie sur tous les hébergeurs.
            // Sans repli, son absence désactivait SILENCIEUSEMENT tout le contrôle
            // de type : un script renommé en .pdf passait. On lit alors les
            // premiers octets, qui suffisent à démentir une extension mensongère.
            return ['erreur' => "Le contenu du fichier ne correspond pas à son extension."];
        }
        return ['extension' => $ext];
    }

    /**
     * Les premiers octets du fichier correspondent-ils à l'extension annoncée ?
     *
     * Repli utilisé quand `fileinfo` est absent. Volontairement strict sur les
     * formats binaires, tolérant sur le texte : un .txt n'a pas de signature, et
     * un cahier des charges peut légitimement contenir des extraits de code.
     */
    private function signatureConcorde(string $ext, string $chemin): bool {
        $tete = (string)@file_get_contents($chemin, false, null, 0, 16);
        if ($tete === '') { return false; }

        switch ($ext) {
            case 'pdf':  return str_starts_with($tete, '%PDF');
            case 'png':  return str_starts_with($tete, "\x89PNG\r\n\x1a\n");
            case 'jpg':
            case 'jpeg': return str_starts_with($tete, "\xFF\xD8\xFF");
            case 'webp': return str_starts_with($tete, 'RIFF') && substr($tete, 8, 4) === 'WEBP';
            case 'doc':  return str_starts_with($tete, "\xD0\xCF\x11\xE0");   // OLE2
            case 'docx':
            case 'odt':  return str_starts_with($tete, "PK\x03\x04");         // archive ZIP
            case 'txt':
                // Pas de signature : on refuse seulement ce qui est manifestement
                // exécutable ou binaire.
                $debut = (string)@file_get_contents($chemin, false, null, 0, 1024);
                if (preg_match('/<\?(php|=)/i', $debut)) { return false; }
                return !preg_match('/[\x00-\x08\x0E-\x1F]/', $debut);
        }
        return false;
    }

    /** Redirection puis arrêt — évite d'oublier le `exit` après un `header`. */
    private function redirigerVers(string $url): void {
        if (!headers_sent()) { header('Location: ' . $url, true, 303); }
        exit;
    }

    /**
     * Generate dynamic sitemap.xml for search engines.
     */
    public function sitemap(): void {
        header("Content-Type: application/xml; charset=utf-8");
        
        $pages = Page::all("id ASC");
        $publishedPages = array_filter($pages, function($p) {
            return $p['status'] === 'published';
        });

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($publishedPages as $p) {
            $slug = $p['slug'];

            // Une page rattachée à un parent n'est servie qu'à son URL imbriquée :
            // l'URL courte y redirige en 301. Déclarer la courte reviendrait à
            // remplir le sitemap de redirections.
            $parent = trim((string)($p['parent_slug'] ?? ''));
            $path = $slug === 'home'
                ? ''
                : '/' . ($parent !== '' ? $parent . '/' : '') . $slug;

            $loc = "https://digitaliumgroup.com" . $path;
            $lastmod = date('Y-m-d', strtotime($p['updated_at'] ?? $p['created_at']));
            $changefreq = ($slug === 'home') ? 'daily' : 'weekly';
            // Une sous-page compte un peu moins que la page qui la porte.
            $priority = ($slug === 'home') ? '1.0' : ($parent !== '' ? '0.6' : '0.8');

            echo '  <url>' . "\n";
            echo '    <loc>' . htmlspecialchars($loc) . '</loc>' . "\n";
            echo '    <lastmod>' . $lastmod . '</lastmod>' . "\n";
            echo '    <changefreq>' . $changefreq . '</changefreq>' . "\n";
            echo '    <priority>' . $priority . '</priority>' . "\n";
            echo '  </url>' . "\n";
        }

        echo '</urlset>' . "\n";
        exit;
    }

    /**
     * Simple utility to trigger a 404.
     */
    private function render404(): void {
        http_response_code(404);
        if (!headers_sent()) {
            header('HTTP/1.1 404 Not Found', true, 404);
        }
        $settings  = Setting::getAll();
        $menuPages = array_filter(Page::all('sort_order ASC'), fn($p) => $p['status'] === 'published');
        $this->render('frontend/404', [
            'page'        => [
                'title'            => '404 — Page introuvable',
                'meta_title'       => 'Page introuvable — Digitalium Group',
                'meta_description' => '',
                'slug'             => '',
                'hero_status'      => 0,
            ],
            'settings'    => $settings,
            'menuPages'   => $menuPages,
            'currentSlug' => '',
        ], 'frontend/layout');
        exit;
    }
}
