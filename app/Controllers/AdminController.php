<?php
namespace App\Controllers;

use App\Services\Auth;
use App\Services\Session;
use App\Helpers\Validator;
use App\Models\Page;
use App\Models\Section;
use App\Models\Media;
use App\Models\Post;
use App\Models\Project;

class AdminController extends Controller {
    /**
     * Helper to enforce admin authentication.
     */
    protected function middlewareAuth(): void {
        if (!Auth::check()) {
            $this->redirect('/admin/login', 'error', 'Veuillez vous connecter pour accéder à l\'administration.');
        }
    }

    /**
     * Show admin dashboard.
     */
    public function dashboard(): void {
        $this->middlewareAuth();

        $blogCount = 0;
        try { $blogCount = Post::count(); } catch (\Throwable $e) { /* table may not exist yet */ }
        $projectCount = 0;
        try { $projectCount = Project::count(); } catch (\Throwable $e) {}

        $stats = [
            'pages_count'    => Page::count(),
            'sections_count' => Section::count(),
            'media_count'    => Media::count(),
            'blog_count'     => $blogCount,
            'project_count'  => $projectCount,
        ];

        $recentPages = Page::all('id DESC LIMIT 5');
        $recentMedia = Media::all('id DESC LIMIT 6');

        $this->render('admin/dashboard', [
            'title' => 'Tableau de bord',
            'stats' => $stats,
            'recentPages' => $recentPages,
            'recentMedia' => $recentMedia,
            'currentUser' => Auth::user()
        ], 'admin/layout');
    }

    /**
     * Show login form.
     */
    public function loginForm(): void {
        if (Auth::check()) {
            $this->redirect('/admin/dashboard');
        }

        $this->render('admin/login', [
            'title' => 'Connexion - Administration',
            'csrf_token' => $this->generateCsrf()
        ]);
    }

    /**
     * Handle login submission.
     */
    public function loginSubmit(): void {
        if (Auth::check()) {
            $this->redirect('/admin/dashboard');
        }

        $this->validateCsrf();

        $validator = new Validator();
        $rules = [
            'username' => 'required',
            'password' => 'required'
        ];

        if (!$validator->validate($_POST, $rules)) {
            $errors = $validator->getErrors();
            $firstError = reset($errors);
            $this->redirect('/admin/login', 'error', $firstError);
        }

        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        $storageDir = ROOT_PATH . '/storage/logs';
        if (!file_exists($storageDir)) {
            mkdir($storageDir, 0755, true);
        }
        $secLogFile = $storageDir . '/security.log';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $timestamp = date('Y-m-d H:i:s');

        try {
            if (Auth::attempt($username, $password)) {
                file_put_contents($secLogFile, "[$timestamp] [AUTH-SUCCESS] User: $username | IP: $ip\n", FILE_APPEND);
                $this->redirect('/admin/dashboard', 'success', 'Connexion réussie. Bienvenue dans votre tableau de bord !');
            } else {
                file_put_contents($secLogFile, "[$timestamp] [AUTH-FAILURE] Attempted User: $username | IP: $ip\n", FILE_APPEND);
                $this->redirect('/admin/login', 'error', 'Identifiants ou mot de passe incorrects.');
            }
        } catch (\Exception $e) {
            file_put_contents($secLogFile, "[$timestamp] [AUTH-ERROR] Attempted User: $username | IP: $ip | Error: " . $e->getMessage() . "\n", FILE_APPEND);
            $this->redirect('/admin/login', 'error', $e->getMessage());
        }
    }

    /**
     * Show general settings form.
     */
    public function settingsForm(): void {
        $this->middlewareAuth();
        $settings = \App\Models\Setting::getAll();
        $mediaList = Media::all('id DESC');

        $this->render('admin/settings', [
            'title' => 'Configuration Générale',
            'settings' => $settings,
            'mediaList' => $mediaList,
            'csrf_token' => $this->generateCsrf(),
            'currentUser' => Auth::user()
        ], 'admin/layout');
    }

    /**
     * Handle general settings submission.
     */
    public function settingsSubmit(): void {
        $this->middlewareAuth();
        $this->validateCsrf();

        $keys = [
            'site_name',
            'site_logo',
            'footer_pitch',
            'contact_address',
            'contact_phone',
            'contact_email',
            'social_linkedin',
            'social_twitter',
            'social_github',
            'social_facebook',
            'social_instagram',
            'social_youtube',
            'site_favicon',
            'site_logo_mobile',
            'site_logo_light',
            'site_logo_dark',
            'site_logo_text',
            'site_logo_subtext',
            'site_whatsapp',
            'footer_copyright',
            'footer_slogan',
            'footer_cta_text',
            'footer_cta_link',
            'footer_legal_text',
            'footer_legal_url',
            'header_cta_text',
            'header_cta_link'
        ];

        foreach ($keys as $key) {
            $value = trim($_POST[$key] ?? '');
            \App\Models\Setting::setVal($key, $value);
        }

        \App\Services\Cache::clear();
        $this->redirect('/admin/settings', 'success', 'Réglages enregistrés avec succès !');
    }

    /**
     * Theme Builder — show form.
     */
    public function themeForm(): void {
        $this->middlewareAuth();
        $settings = \App\Models\Setting::getAll();

        $this->render('admin/theme', [
            'title'       => 'Theme Builder — Design System',
            'settings'    => $settings,
            'csrf_token'  => $this->generateCsrf(),
            'currentUser' => Auth::user()
        ], 'admin/layout');
    }

    /**
     * Theme Builder — save all design tokens.
     */
    public function themeSubmit(): void {
        $this->middlewareAuth();
        $this->validateCsrf();

        $keys = [
            'theme_primary',
            'theme_secondary',
            'theme_accent',
            'theme_text_main',
            'theme_text_sub',
            'theme_text_muted',
            'theme_bg_base',
            'theme_bg_alt',
            'theme_bg_card',
            'theme_radius_pill',
            'theme_radius_card',
            'theme_radius_btn',
            'theme_radius_md',
            'theme_radius_sm',
            'theme_space_section',
            'theme_font_h1',
            'theme_font_h2',
            'theme_font_h3',
            'theme_font_body',
            'theme_font_weight_heading',
            'theme_font_weight_body',
            'theme_line_height_body',
            'theme_letter_spacing_heading',
            'theme_shadow_card',
            'theme_shadow_btn',
            'theme_hero_min_height',
            'theme_hero_overlay_opacity',
            'theme_hero_overlay_color',
            // Jetons de design v3 (modèle fintech vert profond)
            'theme_btn_primary_bg',
            'theme_btn_primary_text',
            'theme_badge_bg',
            'theme_badge_text',
            'theme_footer_bg',
            'theme_surface_dark',
        ];

        foreach ($keys as $key) {
            $value = trim($_POST[$key] ?? '');
            if ($value !== '') {
                \App\Models\Setting::setVal($key, $value);
            }
        }

        \App\Services\Cache::clear();
        $this->redirect('/admin/settings/theme', 'success', 'Theme Design System enregistré avec succès !');
    }

    /**
     * Handle logout.
     */
    public function logout(): void {
        Auth::logout();
        header("Location: /admin/login");
        exit;
    }
}
