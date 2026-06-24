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
     */
    public function renderPage(array $params): void {
        $slug = trim($params['slug'] ?? 'home');
        
        $cacheKey = 'page_' . $slug;
        $cachedData = \App\Services\Cache::get($cacheKey, 3600);

        if ($cachedData !== null) {
            $page = $cachedData['page'];
            $sections = $cachedData['sections'];
            $sectionBlocks = $cachedData['sectionBlocks'];
            $settings = $cachedData['settings'];
            $activeMenuPages = $cachedData['menuPages'];
        } else {
            $page = Page::findBySlug($slug);
            if (!$page) {
                $this->render404();
                return;
            }

            $sections = Section::getActiveByPage($page['id']);
            $sectionBlocks = [];
            foreach ($sections as $sec) {
                $sectionBlocks[$sec['id']] = Block::getStructuredContent($sec['id']);
            }

            $settings = Setting::getAll();
            $pagesMenu = Page::all("sort_order ASC, id ASC");
            $activeMenuPages = array_filter($pagesMenu, function($p) {
                return $p['status'] === 'published';
            });

            // Cache the structured dataset
            \App\Services\Cache::set($cacheKey, [
                'page' => $page,
                'sections' => $sections,
                'sectionBlocks' => $sectionBlocks,
                'settings' => $settings,
                'menuPages' => $activeMenuPages
            ]);
        }

        if ($page['status'] === 'draft' && !Auth::check()) {
            $this->render404();
            return;
        }

        $this->render('frontend/page', [
            'page' => $page,
            'sections' => $sections,
            'sectionBlocks' => $sectionBlocks,
            'settings' => $settings,
            'menuPages' => $activeMenuPages,
            'currentSlug' => $slug,
            'csrf_token' => \App\Services\CSRF::getToken()
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
            $loc = "https://digitaliumgroup.com" . ($slug === 'home' ? '' : '/' . htmlspecialchars($slug));
            $lastmod = date('Y-m-d', strtotime($p['updated_at'] ?? $p['created_at']));
            $changefreq = ($slug === 'home') ? 'daily' : 'weekly';
            $priority = ($slug === 'home') ? '1.0' : '0.8';

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
