<?php
namespace App\Controllers;

use App\Services\Auth;
use App\Models\Page;
use App\Models\Section;
use App\Models\Block;
use App\Models\Setting;
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

        // 3. Process the entry (write to log file)
        $storageDir = ROOT_PATH . '/storage/logs';
        if (!file_exists($storageDir)) {
            mkdir($storageDir, 0755, true);
        }

        $logFile = $storageDir . '/contacts.log';
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = sprintf(
            "[%s] Nom: %s | Email: %s | Sujet: %s | Message: %s\n",
            $timestamp,
            $data['name'],
            $data['email'],
            $data['subject'] ?? 'Sans sujet',
            str_replace(["\r", "\n"], " ", $data['message'])
        );
        file_put_contents($logFile, $logEntry, FILE_APPEND);

        // 4. Return success response
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
        echo "<!DOCTYPE html><html lang='fr'><head><meta charset='UTF-8'><title>Page non trouvée - 404</title>";
        echo "<style>body{background:#0b0f19;color:#9ca3af;font-family:sans-serif;display:flex;justify-content:center;align-items:center;height:100vh;flex-direction:column;margin:0;} h1{color:#f87171;font-size:4rem;margin:0;font-family:sans-serif;} p{font-size:1.2rem;margin-top:10px;} a{color:#6366f1;text-decoration:none;margin-top:20px;padding:10px 20px;border:1px solid #6366f1;border-radius:6px;transition:background 0.2s;} a:hover{background:#6366f1;color:white;}</style></head>";
        echo "<body><h1>404</h1><p>Désolé, la page demandée n'existe pas ou est indisponible.</p><a href='/'>Retour à l'accueil</a></body></html>";
        exit;
    }
}
