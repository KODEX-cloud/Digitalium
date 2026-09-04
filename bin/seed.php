<?php

// ─── Sécurité : CLI uniquement ────────────────────────────────────────────────
// Le dossier bin/ était servi par Apache : /bin/read_logs.php et /bin/deploy.php
// répondaient en HTTP. Le .htaccess les bloque désormais, mais un .htaccess perdu
// ou ignoré ne doit pas suffire à rendre ces scripts exécutables depuis le Web.
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('Access denied — CLI only');
}
/**
 * Database Seeder Script
 * Run this from command line or via browser to build your CMS.
 */

// Define access constant
define('SECURE_ACCESS', true);

// 1. Load Configuration
require_once __DIR__ . '/../config/config.php';

// 2. Simple Autoloader
spl_autoload_register(function ($class) {
    $classPath = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    $file = APP_PATH . DIRECTORY_SEPARATOR . $classPath . '.php';
    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    return false;
});

use Core\Database;
use Models\User;
use Models\Page;
use Models\Section;
use Models\Block;
use Models\Setting;

echo "=== INITIALISATION DE LA BASE DE DONNÉES DIGITALIUM ===\n";

try {
    // Fail-safe: Connect to MySQL server without selecting DB to create it if it doesn't exist yet
    $dsnTemp = sprintf("mysql:host=%s;port=%s;charset=%s", DB_HOST, DB_PORT, DB_CHARSET);
    $pdoTemp = new \PDO($dsnTemp, DB_USER, DB_PASS, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
    $pdoTemp->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    echo "Base de données '" . DB_NAME . "' créée ou existante.\n";

    $pdo = Database::getConnection();

    // 1. Import schema from database.sql
    echo "Importation du schéma SQL...\n";
    $sqlFile = ROOT_PATH . '/config/database.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("Schéma SQL manquant à l'emplacement : " . $sqlFile);
    }
    
    $sqlContent = file_get_contents($sqlFile);
    $pdo->exec($sqlContent);
    echo "Schéma SQL importé avec succès.\n";

    // 2. Check if admin already exists
    $admin = User::findByUsername('admin');
    if (!$admin) {
        echo "Création de l'utilisateur administrateur par défaut (admin / password123)...\n";
        User::createAdmin('admin', 'admin@digitaliumgroup.com', 'password123');
        echo "Administrateur créé.\n";
    } else {
        echo "Administrateur existant. Saut de l'étape.\n";
    }

    // 3. Seed global settings
    echo "Génération des réglages système...\n";
    Setting::setVal('site_name', 'DIGITALIUM');
    Setting::setVal('footer_pitch', 'Nous concevons des produits technologiques haut de gamme et des solutions digitales pour accélérer la croissance de nos partenaires.');
    Setting::setVal('contact_address', "128 Rue de la Boétie\n75008 Paris, France");
    Setting::setVal('contact_phone', '+33 1 76 54 32 10');
    Setting::setVal('contact_email', 'contact@digitaliumgroup.com');
    Setting::setVal('social_linkedin', 'https://linkedin.com/company/digitalium');
    Setting::setVal('social_twitter', 'https://twitter.com/digitalium');
    Setting::setVal('social_github', 'https://github.com/digitalium');
    echo "Réglages insérés.\n";

    // 4. Seed home page
    $homePage = Page::findBySlug('home');
    if (!$homePage) {
        echo "Création de la page d'accueil principale...\n";
        $pageId = (int)Page::createPage(
            'Accueil', 
            'home', 
            'Digitalium Group | Leader de la Transformation Digitale', 
            'Conception de logiciels sur mesure, d\'applications cloud haute performance et de solutions web innovantes pour grandes entreprises.',
            'published'
        );
        echo "Page d'accueil créée avec ID: {$pageId}.\n";

        // Seed default sections
        echo "Ajout des sections de démonstration à la page d'accueil...\n";
        
        // --- 1. HERO SECTION ---
        $secHeroId = (int)Section::addSection($pageId, 'Bannière Principale', 'hero', 0);
        Block::setVal($secHeroId, 'title', 'wysiwyg', '<h1>Concevons ensemble<br><span style="color: #2563eb;">l\'architecture</span> de votre futur</h1>');
        Block::setVal($secHeroId, 'subtitle', 'textarea', 'Nous sommes un groupe technologique d\'élite. Nous façonnons des logiciels sur mesure stables, performants et hautement sécurisés.');
        Block::setVal($secHeroId, 'cta_text', 'text', 'Démarrer mon projet');
        Block::setVal($secHeroId, 'cta_url', 'link', '#contact');
        Block::setVal($secHeroId, 'bg_image', 'image', '');

        // --- 2. SERVICES SECTION ---
        $secServicesId = (int)Section::addSection($pageId, 'Nos Expertises', 'services', 1);
        Block::setVal($secServicesId, 'title', 'text', 'Nos Services Technologiques');
        Block::setVal($secServicesId, 'subtitle', 'textarea', 'Des ingénieurs chevronnés pour concevoir, optimiser et propulser vos projets à grande échelle.');
        
        // Service 1
        Block::setVal($secServicesId, 'card_title', 'text', 'Ingénierie Logicielle', 1, 0);
        Block::setVal($secServicesId, 'card_icon', 'text', 'cpu', 1, 1);
        Block::setVal($secServicesId, 'card_description', 'textarea', 'Développement de systèmes d\'information robustes en PHP, architectures micro-services et API performantes.', 1, 2);
        // Service 2
        Block::setVal($secServicesId, 'card_title', 'text', 'Applications Cloud & Mobiles', 2, 0);
        Block::setVal($secServicesId, 'card_icon', 'text', 'cloud', 2, 1);
        Block::setVal($secServicesId, 'card_description', 'textarea', 'Conception d\'architectures Serverless scalables sur AWS, Azure et plateformes hybrides sécurisées.', 2, 2);
        // Service 3
        Block::setVal($secServicesId, 'card_title', 'text', 'Audit & Conseil DevOps', 3, 0);
        Block::setVal($secServicesId, 'card_icon', 'text', 'shield-check', 3, 1);
        Block::setVal($secServicesId, 'card_description', 'textarea', 'Optimisation de vos bases de données MySQL, pipelines CI/CD sécurisés et audits de vulnérabilités applicatives.', 3, 2);

        // --- 3. PORTFOLIO SECTION ---
        $secPortfolioId = (int)Section::addSection($pageId, 'Réalisations Récentes', 'portfolio', 2);
        Block::setVal($secPortfolioId, 'title', 'text', 'Études de Cas & Projets');
        Block::setVal($secPortfolioId, 'subtitle', 'textarea', 'Découvrez les plateformes logicielles complexes que nous avons déployées en production.');
        
        // Project 1
        Block::setVal($secPortfolioId, 'item_title', 'text', 'Plateforme Logistique Internationale', 1, 0);
        Block::setVal($secPortfolioId, 'item_category', 'text', 'Ingénierie', 1, 1);
        Block::setVal($secPortfolioId, 'item_image', 'image', '', 1, 2);
        Block::setVal($secPortfolioId, 'item_url', 'link', '#', 1, 3);
        // Project 2
        Block::setVal($secPortfolioId, 'item_title', 'text', 'Application Fintech Sécurisée', 2, 0);
        Block::setVal($secPortfolioId, 'item_category', 'text', 'Mobile', 2, 1);
        Block::setVal($secPortfolioId, 'item_image', 'image', '', 2, 2);
        Block::setVal($secPortfolioId, 'item_url', 'link', '#', 2, 3);

        // --- 4. TESTIMONIALS SECTION ---
        $secTestimonialsId = (int)Section::addSection($pageId, 'Témoignages Clients', 'testimonials', 3);
        Block::setVal($secTestimonialsId, 'title', 'text', 'Avis de Nos Partenaires');
        Block::setVal($secTestimonialsId, 'subtitle', 'textarea', 'Découvrez les retours d\'expérience des directeurs techniques et fondateurs qui nous font confiance.');
        
        // Quote 1
        Block::setVal($secTestimonialsId, 'client_name', 'text', 'Marc Lemaire', 1, 0);
        Block::setVal($secTestimonialsId, 'client_company', 'text', 'CTO - HexaSolutions', 1, 1);
        Block::setVal($secTestimonialsId, 'client_avatar', 'image', '', 1, 2);
        Block::setVal($secTestimonialsId, 'client_quote', 'textarea', 'Le professionnalisme de Digitalium a dépassé nos espérances. Une architecture PHP impeccable et un code extrêmement propre.', 1, 3);
        Block::setVal($secTestimonialsId, 'client_rating', 'number', '5', 1, 4);

        // --- 5. FAQ SECTION ---
        $secFaqId = (int)Section::addSection($pageId, 'Questions Fréquentes', 'faq', 4);
        Block::setVal($secFaqId, 'title', 'text', 'Questions Fréquentes');
        Block::setVal($secFaqId, 'subtitle', 'textarea', 'Voici les réponses aux questions les plus courantes posées par nos partenaires.');
        
        // FAQ 1
        Block::setVal($secFaqId, 'faq_question', 'text', 'Comment est structuré le suivi de projet ?', 1, 0);
        Block::setVal($secFaqId, 'faq_answer', 'textarea', 'Nous fonctionnons selon les méthodologies agiles (Scrum) avec des livraisons de versions fonctionnelles toutes les deux semaines (sprints) et un canal Slack/Teams dédié.', 1, 1);
        // FAQ 2
        Block::setVal($secFaqId, 'faq_question', 'text', 'Le site est-il optimisé pour le référencement Google ?', 2, 0);
        Block::setVal($secFaqId, 'faq_answer', 'textarea', 'Absolument. Chaque page, section et image est conçue pour optimiser le Core Web Vitals : balisage sémantique rigoureux HTML5, chargement asynchrone des médias et métadonnées SEO modifiables à chaud.', 2, 1);

        // --- 6. CONTACT SECTION ---
        $secContactId = (int)Section::addSection($pageId, 'Formulaire de Contact', 'contact', 5);
        Block::setVal($secContactId, 'title', 'text', 'Parlez-nous de Votre Projet');
        Block::setVal($secContactId, 'subtitle', 'textarea', 'Prêt à concevoir un système performant ? Remplissez ce formulaire et notre architecte principal vous contactera.');
        Block::setVal($secContactId, 'contact_email', 'text', 'contact@digitaliumgroup.com');
        Block::setVal($secContactId, 'contact_phone', 'text', '+33 1 76 54 32 10');
        Block::setVal($secContactId, 'contact_address', 'textarea', "128 Rue de la Boétie\n75008 Paris, France");
        Block::setVal($secContactId, 'cta_label', 'text', 'Planifier un entretien technique');

        echo "Sections de démonstration insérées avec succès.\n";
    } else {
        echo "Page d'accueil existante. Saut de la génération de sections.\n";
    }

    echo "=== SEEDING EFFECTUÉ AVEC SUCCÈS ! ===\n";
    echo "Vous pouvez vous connecter à l'administration via: /admin/login\n";
    echo "Identifiants: admin / password123\n";

} catch (Exception $e) {
    echo "ERREUR LORS DU SEEDING : " . $e->getMessage() . "\n";
    exit(1);
}
