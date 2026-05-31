<?php
/**
 * Root Database Seeder Script
 * Designed to be run safely from the project root directory during production initialization.
 */

define('SECURE_ACCESS', true);

// 1. Load Configuration
require_once __DIR__ . '/config/config.php';

// 2. PSR-4 Compliant Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

use App\Services\Database;
use App\Models\User;
use App\Models\Page;
use App\Models\Section;
use App\Models\Block;
use App\Models\Setting;

header('Content-Type: text/plain; charset=utf-8');

echo "=== INITIALISATION ET SEEDING COMPLET DE LA BASE DE DONNÉES DIGITALIUM ===\n\n";

try {
    // Connect directly to the existing database created via hPanel
    $pdo = Database::getConnection();
    echo "Connexion établie avec succès à la base de données '" . DB_NAME . "'.\n";

    // 1. Import schema from database/database.sql (which drops tables first)
    echo "Réinitialisation des tables et importation du schéma SQL...\n";
    $sqlFile = __DIR__ . '/database/database.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("Schéma SQL manquant : " . $sqlFile);
    }
    
    $sqlContent = file_get_contents($sqlFile);
    $pdo->exec($sqlContent);
    echo "Schéma SQL importé avec succès (tables recréées à blanc).\n";

    // 2. Create admin user
    echo "Création de l'administrateur (admin / password123)...\n";
    User::createAdmin('admin', 'admin@digitaliumgroup.com', 'password123');
    echo "Administrateur créé.\n";

    // 3. Seed global settings
    echo "Génération des réglages généraux du site...\n";
    Setting::setVal('site_name', 'Digitalium Group');
    Setting::setVal('footer_pitch', 'Leader en stratégie digitale, intelligence artificielle et maintenance informatique — votre partenaire de confiance pour la transformation numérique.');
    Setting::setVal('contact_address', "Abidjan, Côte d'Ivoire");
    Setting::setVal('contact_phone', '+225 07 07 82 02 11');
    Setting::setVal('contact_email', 'contact@digitaliumgroup.com');
    Setting::setVal('social_linkedin', 'https://linkedin.com/company/digitalium');
    Setting::setVal('social_twitter', 'https://twitter.com/digitalium');
    Setting::setVal('social_github', 'https://github.com/digitalium');
    echo "Réglages insérés.\n";

    // ----------------------------------------------------
    // PAGE 1 : ACCUEIL (home)
    // ----------------------------------------------------
    echo "Génération de la Page : Accueil...\n";
    $page1Id = (int)Page::createPage(
        'Accueil', 
        'home', 
        'Digitalium Group | Leader de la Transformation Digitale & IA', 
        'Stratégie digitale, intelligence artificielle avant-gardiste et maintenance de parcs informatiques. Découvrez nos services professionnels.',
        'published'
    );

    // Section 1.1 : Hero
    $secHeroId = (int)Section::addSection($page1Id, 'Bannière Principale', 'hero', 0);
    Block::setVal($secHeroId, 'badge', 'text', 'AAIA — Agence d\'Automatisation en IA');
    Block::setVal($secHeroId, 'title', 'wysiwyg', 'Des solutions<br>technologiques<br><span class="hi">innovantes</span>');
    Block::setVal($secHeroId, 'subtitle', 'textarea', 'Plongez dans un monde où la convergence du digital et de la technologie redéfinit les normes et réinvente l\'avenir de votre entreprise.');
    Block::setVal($secHeroId, 'cta_text', 'text', 'Découvrez nos services');
    Block::setVal($secHeroId, 'cta_url', 'link', '#services');
    Block::setVal($secHeroId, 'stats_years', 'text', '10+');
    Block::setVal($secHeroId, 'stats_clients', 'text', '100+');
    Block::setVal($secHeroId, 'stats_satisfaction', 'text', '98%');

    // Section 1.2 : Features Strip
    $secFeatId = (int)Section::addSection($page1Id, 'Bande des Atouts', 'features', 1);
    Block::setVal($secFeatId, 'title', 'text', 'Nos Points Forts');
    // Group 1
    Block::setVal($secFeatId, 'card_title', 'text', 'Stratégie Digitale Révolutionnaire', 1, 0);
    Block::setVal($secFeatId, 'card_icon', 'text', 'laptop', 1, 1);
    Block::setVal($secFeatId, 'card_description', 'textarea', 'Analyse approfondie de votre secteur, définition d\'objectifs clairs et mise en place d\'un plan d\'action efficace.', 1, 2);
    // Group 2
    Block::setVal($secFeatId, 'card_title', 'text', 'Intelligence Artificielle Avant-gardiste', 2, 0);
    Block::setVal($secFeatId, 'card_icon', 'text', 'cpu', 2, 1);
    Block::setVal($secFeatId, 'card_description', 'textarea', 'Exploitez tout le potentiel de l\'IA pour repousser les limites de votre entreprise et automatiser vos processus.', 2, 2);
    // Group 3
    Block::setVal($secFeatId, 'card_title', 'text', 'Maintenance & Régie Informatique', 3, 0);
    Block::setVal($secFeatId, 'card_icon', 'text', 'shield-check', 3, 1);
    Block::setVal($secFeatId, 'card_description', 'textarea', 'Gestion des parcs informatiques, sécurité renforcée, dépannages rapides par des experts certifiés.', 3, 2);

    // Section 1.3 : Who We Are (About)
    $secAboutId = (int)Section::addSection($page1Id, 'Qui Sommes-Nous', 'about', 2);
    Block::setVal($secAboutId, 'tag', 'text', 'Qui sommes-nous');
    Block::setVal($secAboutId, 'title', 'text', 'Votre partenaire de confiance pour la transformation digitale');
    Block::setVal($secAboutId, 'description', 'textarea', 'Digitalium Group aide les entreprises à naviguer le monde digital complexe via une solution tout-en-un : analyse, stratégie, développement web, marketing numérique et maintenance — sans complexité ni intimidation.');
    Block::setVal($secAboutId, 'check_1', 'text', 'Approche personnalisée et stratégies sur mesure');
    Block::setVal($secAboutId, 'check_2', 'text', 'Innovation constante avec les dernières technologies');
    Block::setVal($secAboutId, 'check_3', 'text', 'Transparence totale et communication ouverte');
    Block::setVal($secAboutId, 'check_4', 'text', 'Excellence et service client exceptionnel');
    Block::setVal($secAboutId, 'check_5', 'text', 'Résultats tangibles et mesurables à chaque étape');
    // Values repeatable cards
    Block::setVal($secAboutId, 'val_title', 'text', 'Personnalisé', 1, 0);
    Block::setVal($secAboutId, 'val_icon', 'text', 'briefcase', 1, 1);
    Block::setVal($secAboutId, 'val_text', 'textarea', 'Stratégies adaptées à vos objectifs spécifiques.', 1, 2);
    
    Block::setVal($secAboutId, 'val_title', 'text', 'Innovation', 2, 0);
    Block::setVal($secAboutId, 'val_icon', 'text', 'lightbulb', 2, 1);
    Block::setVal($secAboutId, 'val_text', 'textarea', 'Technologies de pointe pour rester compétitif.', 2, 2);
    
    Block::setVal($secAboutId, 'val_title', 'text', 'Transparence', 3, 0);
    Block::setVal($secAboutId, 'val_icon', 'text', 'eye', 3, 1);
    Block::setVal($secAboutId, 'val_text', 'textarea', 'Communication ouverte et suivi régulier.', 3, 2);
    
    Block::setVal($secAboutId, 'val_title', 'text', 'Excellence', 4, 0);
    Block::setVal($secAboutId, 'val_icon', 'text', 'star', 4, 1);
    Block::setVal($secAboutId, 'val_text', 'textarea', 'Qualité haut de gamme à chaque étape.', 4, 2);

    // Section 1.4 : Process
    $secProcId = (int)Section::addSection($page1Id, 'Notre Processus (6 étapes)', 'process', 3);
    Block::setVal($secProcId, 'tag', 'text', 'Notre Processus');
    Block::setVal($secProcId, 'title', 'text', 'Six étapes vers votre transformation');
    // Steps
    Block::setVal($secProcId, 'title', 'text', 'Analyse Approfondie', 1, 0);
    Block::setVal($secProcId, 'icon', 'text', 'search', 1, 1);
    Block::setVal($secProcId, 'description', 'textarea', 'Évaluation de vos objectifs et de votre présence digitale actuelle.', 1, 2);
    Block::setVal($secProcId, 'num', 'text', '01', 1, 3);
    
    Block::setVal($secProcId, 'title', 'text', 'Stratégie Numérique', 2, 0);
    Block::setVal($secProcId, 'icon', 'text', 'map', 2, 1);
    Block::setVal($secProcId, 'description', 'textarea', 'Plan complet avec objectifs, tactiques et mesures de succès.', 2, 2);
    Block::setVal($secProcId, 'num', 'text', '02', 2, 3);
    
    Block::setVal($secProcId, 'title', 'text', 'Développement Web', 3, 0);
    Block::setVal($secProcId, 'icon', 'text', 'code', 3, 1);
    Block::setVal($secProcId, 'description', 'textarea', 'Sites professionnels co-construits avec une UX exceptionnelle.', 3, 2);
    Block::setVal($secProcId, 'num', 'text', '03', 3, 3);
    
    Block::setVal($secProcId, 'title', 'text', 'Marketing Numérique', 4, 0);
    Block::setVal($secProcId, 'icon', 'text', 'trending-up', 4, 1);
    Block::setVal($secProcId, 'description', 'textarea', 'Campagnes personnalisées pour visibilité, leads et croissance.', 4, 2);
    Block::setVal($secProcId, 'num', 'text', '04', 4, 3);
    
    Block::setVal($secProcId, 'title', 'text', 'Intelligence Artificielle', 5, 0);
    Block::setVal($secProcId, 'icon', 'text', 'cpu', 5, 1);
    Block::setVal($secProcId, 'description', 'textarea', 'Chatbots, personnalisation et analyses prédictives via IA.', 5, 2);
    Block::setVal($secProcId, 'num', 'text', '05', 5, 3);
    
    Block::setVal($secProcId, 'title', 'text', 'Maintenance Informatique', 6, 0);
    Block::setVal($secProcId, 'icon', 'text', 'settings', 6, 1);
    Block::setVal($secProcId, 'description', 'textarea', 'Gestion réseau, sécurité et mises à jour pour performances optimales.', 6, 2);
    Block::setVal($secProcId, 'num', 'text', '06', 6, 3);

    // Section 1.5 : CTA
    $secCtaId = (int)Section::addSection($page1Id, 'Appel à l\'action', 'cta', 4);
    Block::setVal($secCtaId, 'eyebrow', 'text', 'Prêt à démarrer votre transformation ?');
    Block::setVal($secCtaId, 'title', 'text', 'Simplifiez votre marketing numérique avec nos solutions complètes');
    Block::setVal($secCtaId, 'subtitle', 'textarea', 'Rejoignez 100+ clients satisfaits à Abidjan et à l\'international. Notre équipe d\'experts vous accompagne à chaque étape.');
    Block::setVal($secCtaId, 'cta_text', 'text', 'Commencer aujourd\'hui');
    Block::setVal($secCtaId, 'cta_url', 'link', '#contact');

    // ----------------------------------------------------
    // PAGE 2 : À PROPOS (about)
    // ----------------------------------------------------
    echo "Génération de la Page : À Propos...\n";
    $page2Id = (int)Page::createPage(
        'À Propos', 
        'about', 
        'À Propos | Notre mission et nos valeurs d\'excellence', 
        'Découvrez qui nous sommes : une équipe d\'experts unis pour la transformation digitale et l\'intégration de systèmes d\'intelligence artificielle.',
        'published'
    );

    // Section 2.1 : About Hero
    $secAbHeroId = (int)Section::addSection($page2Id, 'Bannière À Propos', 'about_hero', 0);
    Block::setVal($secAbHeroId, 'badge', 'text', 'Leader en stratégie digitale & IA');
    Block::setVal($secAbHeroId, 'title', 'wysiwyg', 'L\'excellence digitale,<br><span class="highlight">au service de votre croissance</span>');
    Block::setVal($secAbHeroId, 'subtitle', 'textarea', 'Une équipe passionnée d\'experts en marketing, développement web, intelligence artificielle et technologies — unis pour transformer votre entreprise à l\'échelle mondiale.');
    Block::setVal($secAbHeroId, 'stats_years', 'text', '10+');
    Block::setVal($secAbHeroId, 'stats_clients', 'text', '200+');
    Block::setVal($secAbHeroId, 'stats_satisfaction', 'text', '98%');

    // Section 2.2 : Mission
    $secMissionId = (int)Section::addSection($page2Id, 'Notre Mission', 'mission', 1);
    Block::setVal($secMissionId, 'tag', 'text', 'Notre Mission');
    Block::setVal($secMissionId, 'title', 'text', 'Votre succès, notre engagement');
    Block::setVal($secMissionId, 'description', 'textarea', 'Nous simplifions le marketing numérique pour aider nos clients à prospérer dans un monde en évolution constante — sans complexité, sans intimidation.<br><br>Vous vous concentrez sur votre cœur de métier. Nous gérons votre présence en ligne, de A à Z.');
    
    // repeatable card 1
    Block::setVal($secMissionId, 'card_title', 'text', 'Partenaire de confiance', 1, 0);
    Block::setVal($secMissionId, 'card_icon', 'text', 'star', 1, 1);
    Block::setVal($secMissionId, 'card_description', 'textarea', 'Conseils stratégiques personnalisés et service exceptionnel à chaque étape de votre transformation.', 1, 2);
    // repeatable card 2
    Block::setVal($secMissionId, 'card_title', 'text', 'Solution clé en main', 2, 0);
    Block::setVal($secMissionId, 'card_icon', 'text', 'check-circle', 2, 1);
    Block::setVal($secMissionId, 'card_description', 'textarea', 'De l\'analyse initiale au déploiement, nous gérons l\'intégralité de votre présence numérique.', 2, 2);
    // repeatable card 3
    Block::setVal($secMissionId, 'card_title', 'text', 'Résultats tangibles', 3, 0);
    Block::setVal($secMissionId, 'card_icon', 'text', 'zap', 3, 1);
    Block::setVal($secMissionId, 'card_description', 'textarea', 'Reconnus leaders du secteur pour des solutions innovantes auprès de clients mondiaux.', 3, 2);

    // Section 2.3 : Values
    $secValId = (int)Section::addSection($page2Id, 'Nos Valeurs', 'values', 2);
    Block::setVal($secValId, 'tag', 'text', 'Nos Valeurs');
    Block::setVal($secValId, 'title', 'text', 'Ce qui nous définit');
    // Group repeatable
    Block::setVal($secValId, 'val_title', 'text', 'Personnalisé', 1, 0);
    Block::setVal($secValId, 'val_icon', 'text', 'briefcase', 1, 1);
    Block::setVal($secValId, 'val_text', 'textarea', 'Stratégies sur mesure adaptées à vos objectifs spécifiques.', 1, 2);
    
    Block::setVal($secValId, 'val_title', 'text', 'Innovation', 2, 0);
    Block::setVal($secValId, 'val_icon', 'text', 'lightbulb', 2, 1);
    Block::setVal($secValId, 'val_text', 'textarea', 'Technologies et approches créatives pour repousser les limites.', 2, 2);
    
    Block::setVal($secValId, 'val_title', 'text', 'Transparence', 3, 0);
    Block::setVal($secValId, 'val_icon', 'text', 'eye', 3, 1);
    Block::setVal($secValId, 'val_text', 'textarea', 'Communication ouverte et mises à jour régulières sur vos résultats.', 3, 2);
    
    Block::setVal($secValId, 'val_title', 'text', 'Excellence', 4, 0);
    Block::setVal($secValId, 'val_icon', 'text', 'star', 4, 1);
    Block::setVal($secValId, 'val_text', 'textarea', 'Solutions haut de gamme et service exceptionnel à chaque étape.', 4, 2);
    
    Block::setVal($secValId, 'val_title', 'text', 'Service Client', 5, 0);
    Block::setVal($secValId, 'val_icon', 'text', 'users', 5, 1);
    Block::setVal($secValId, 'val_text', 'textarea', 'Réactivité, soutien continu et dépassement de vos attentes.', 5, 2);

    // Section 2.4 : Team Grid
    $secTeamGridId = (int)Section::addSection($page2Id, 'Nos Profils d\'Experts', 'team_roles', 3);
    Block::setVal($secTeamGridId, 'tag', 'text', 'Notre Équipe');
    Block::setVal($secTeamGridId, 'title', 'text', 'Des experts à votre service');
    // Team member blocks
    Block::setVal($secTeamGridId, 'role_title', 'text', 'Marketing Digital', 1, 0);
    Block::setVal($secTeamGridId, 'role_sub', 'text', 'Stratégie & Croissance', 1, 1);
    Block::setVal($secTeamGridId, 'role_avatar', 'text', 'trending-up', 1, 2);
    
    Block::setVal($secTeamGridId, 'role_title', 'text', 'Développeurs Web', 2, 0);
    Block::setVal($secTeamGridId, 'role_sub', 'text', 'Design & Technologie', 2, 1);
    Block::setVal($secTeamGridId, 'role_avatar', 'text', 'code', 2, 2);
    
    Block::setVal($secTeamGridId, 'role_title', 'text', 'Spécialistes IA', 3, 0);
    Block::setVal($secTeamGridId, 'role_sub', 'text', 'Automatisation & ML', 3, 1);
    Block::setVal($secTeamGridId, 'role_avatar', 'text', 'cpu', 3, 2);
    
    Block::setVal($secTeamGridId, 'role_title', 'text', 'Experts TI', 4, 0);
    Block::setVal($secTeamGridId, 'role_sub', 'text', 'Infrastructure & Sécurité', 4, 1);
    Block::setVal($secTeamGridId, 'role_avatar', 'text', 'server', 4, 2);

    // Section 2.5 : About CTA
    $secAbCtaId = (int)Section::addSection($page2Id, 'CTA À Propos', 'cta', 4);
    Block::setVal($secAbCtaId, 'eyebrow', 'text', 'Prêt à transformer votre entreprise ?');
    Block::setVal($secAbCtaId, 'title', 'text', 'Commençons ensemble');
    Block::setVal($secAbCtaId, 'subtitle', 'textarea', 'Votre vision mérite une stratégie à la hauteur de vos ambitions. Nos ingénieurs sont prêts.');
    Block::setVal($secAbCtaId, 'cta_text', 'text', 'Nous Contacter');
    Block::setVal($secAbCtaId, 'cta_url', 'link', '/contact');

    // ----------------------------------------------------
    // PAGE 3 : SERVICES (service)
    // ----------------------------------------------------
    echo "Génération de la Page : Services...\n";
    $page3Id = (int)Page::createPage(
        'Services', 
        'service', 
        'Nos Prestations | Des expertises digitales haut de gamme', 
        'Ingénierie logicielle, marketing d\'automatisation IA, réseaux d\'entreprises et production vidéo. Découvrez notre catalogue complet.',
        'published'
    );

    // Section 3.1 : Services Hero
    $secSvHeroId = (int)Section::addSection($page3Id, 'Bannière Services', 'services_hero', 0);
    Block::setVal($secSvHeroId, 'badge', 'text', 'Nos Prestations Digitales');
    Block::setVal($secSvHeroId, 'title', 'wysiwyg', 'Des solutions digitales<br><em>sur mesure</em> pour<br>propulser votre business');
    Block::setVal($secSvHeroId, 'subtitle', 'textarea', 'De la conception web à l\'intelligence artificielle, en passant par le câblage réseau et la création de contenu — une approche globale de la transformation digitale.');
    Block::setVal($secSvHeroId, 'pstat_clients', 'text', '100+');
    Block::setVal($secSvHeroId, 'pstat_years', 'text', '7+');

    // Section 3.2 : Services Grid (6 services)
    $secSvGridId = (int)Section::addSection($page3Id, 'Grille des Services', 'services_grid', 1);
    Block::setVal($secSvGridId, 'tag', 'text', 'Nos Services');
    Block::setVal($secSvGridId, 'title', 'text', 'Nos Réalisations & Expertises');
    Block::setVal($secSvGridId, 'subtitle', 'textarea', 'Découvrez nos prestations de transformation digitale — chaque service conçu pour booster votre présence en ligne.');
    // Card 1
    Block::setVal($secSvGridId, 'svc_title', 'text', 'Web Designer & Développement', 1, 0);
    Block::setVal($secSvGridId, 'svc_tag', 'text', 'Web', 1, 1);
    Block::setVal($secSvGridId, 'svc_icon', 'text', 'layout', 1, 2);
    Block::setVal($secSvGridId, 'svc_points', 'textarea', "UX/UI | E-commerce | Sites vitrines et plateformes sur mesure", 1, 3);
    // Card 2
    Block::setVal($secSvGridId, 'svc_title', 'text', 'Maintenance Informatique', 2, 0);
    Block::setVal($secSvGridId, 'svc_tag', 'text', 'Informatique', 2, 1);
    Block::setVal($secSvGridId, 'svc_icon', 'text', 'server', 2, 2);
    Block::setVal($secSvGridId, 'svc_points', 'textarea', "Gestion de parcs TI | Réseau & Support | Mises à jour & dépannages", 2, 3);
    // Card 3
    Block::setVal($secSvGridId, 'svc_title', 'text', 'Câblage Réseaux', 3, 0);
    Block::setVal($secSvGridId, 'svc_tag', 'text', 'Infrastructure', 3, 1);
    Block::setVal($secSvGridId, 'svc_icon', 'text', 'network', 3, 2);
    Block::setVal($secSvGridId, 'svc_points', 'textarea', "Réseaux sécurisés | Réseau Wi-Fi Pro | Sécurité accrue", 3, 3);
    // Card 4
    Block::setVal($secSvGridId, 'svc_title', 'text', 'Montage Vidéo TikTok & YouTube', 4, 0);
    Block::setVal($secSvGridId, 'svc_tag', 'text', 'Vidéo', 4, 1);
    Block::setVal($secSvGridId, 'svc_icon', 'text', 'video', 4, 2);
    Block::setVal($secSvGridId, 'svc_points', 'textarea', "Montage vertical/horizontal | Effets visuels & audio | Stratégie virale", 4, 3);
    // Card 5
    Block::setVal($secSvGridId, 'svc_title', 'text', 'Création de Contenu', 5, 0);
    Block::setVal($secSvGridId, 'svc_tag', 'text', 'Contenu', 5, 1);
    Block::setVal($secSvGridId, 'svc_icon', 'text', 'edit-3', 5, 2);
    Block::setVal($secSvGridId, 'svc_points', 'textarea', "Copywriting | Articles optimisés SEO | Stratégies éditoriales", 5, 3);
    // Card 6
    Block::setVal($secSvGridId, 'svc_title', 'text', 'Stratégie & Automatisation IA', 6, 0);
    Block::setVal($secSvGridId, 'svc_tag', 'text', 'IA', 6, 1);
    Block::setVal($secSvGridId, 'svc_icon', 'text', 'cpu', 6, 2);
    Block::setVal($secSvGridId, 'svc_points', 'textarea', "Intégration d'agents intelligents | Audits & automatisation | Pipelines IA", 6, 3);

    // Section 3.3 : Process strip 4 steps
    $secSvStripId = (int)Section::addSection($page3Id, 'Comment ça marche', 'process_strip', 2);
    Block::setVal($secSvStripId, 'tag', 'text', 'Comment ça marche');
    Block::setVal($secSvStripId, 'title', 'text', 'Notre processus en 4 étapes');
    // Step 1
    Block::setVal($secSvStripId, 'proc_title', 'text', 'Analyse & Audit', 1, 0);
    Block::setVal($secSvStripId, 'proc_icon', 'text', 'search', 1, 1);
    Block::setVal($secSvStripId, 'proc_desc', 'textarea', 'Évaluation de vos besoins, objectifs et situation digitale actuelle.', 1, 2);
    Block::setVal($secSvStripId, 'proc_num', 'text', '01', 1, 3);
    // Step 2
    Block::setVal($secSvStripId, 'proc_title', 'text', 'Proposition sur mesure', 2, 0);
    Block::setVal($secSvStripId, 'proc_icon', 'text', 'file-text', 2, 1);
    Block::setVal($secSvStripId, 'proc_desc', 'textarea', 'Plan d\'action personnalisé avec budget, délais et livrables clairs.', 2, 2);
    Block::setVal($secSvStripId, 'proc_num', 'text', '02', 2, 3);
    // Step 3
    Block::setVal($secSvStripId, 'proc_title', 'text', 'Développement & Exécution', 3, 0);
    Block::setVal($secSvStripId, 'proc_icon', 'text', 'code', 3, 1);
    Block::setVal($secSvStripId, 'proc_desc', 'textarea', 'Mise en œuvre par nos experts avec suivi régulier et validation.', 3, 2);
    Block::setVal($secSvStripId, 'proc_num', 'text', '03', 3, 3);
    // Step 4
    Block::setVal($secSvStripId, 'proc_title', 'text', 'Livraison & Support', 4, 0);
    Block::setVal($secSvStripId, 'proc_icon', 'text', 'check-circle', 4, 1);
    Block::setVal($secSvStripId, 'proc_desc', 'textarea', 'Déploiement, formation et support continu pour des résultats durables.', 4, 2);
    Block::setVal($secSvStripId, 'proc_num', 'text', '04', 4, 3);

    // Section 3.4 : Testimonials
    $secSvTestiId = (int)Section::addSection($page3Id, 'Témoignages Clients', 'testimonials_grid', 3);
    Block::setVal($secSvTestiId, 'tag', 'text', 'Témoignages');
    Block::setVal($secSvTestiId, 'title', 'text', 'Ce que disent nos clients');
    // Testimonial 1
    Block::setVal($secSvTestiId, 'client_name', 'text', 'Mamadou Koné', 1, 0);
    Block::setVal($secSvTestiId, 'client_company', 'text', 'Directeur Commercial, PME Abidjan', 1, 1);
    Block::setVal($secSvTestiId, 'client_avatar', 'text', 'MK', 1, 2);
    Block::setVal($secSvTestiId, 'client_quote', 'textarea', 'Digitalium Group a transformé notre présence en ligne. Notre site e-commerce génère maintenant 3x plus de leads qu\'avant.', 1, 3);
    Block::setVal($secSvTestiId, 'client_rating', 'text', '5', 1, 4);
    // Testimonial 2
    Block::setVal($secSvTestiId, 'client_name', 'text', 'Adjoua Bamba', 2, 0);
    Block::setVal($secSvTestiId, 'client_company', 'text', 'DSI, Entreprise Industrielle', 2, 1);
    Block::setVal($secSvTestiId, 'client_avatar', 'text', 'AB', 2, 2);
    Block::setVal($secSvTestiId, 'client_quote', 'textarea', 'L\'équipe a géré notre câblage réseau et notre parc informatique avec un professionnalisme remarquable. Zéro panne depuis 6 mois.', 2, 3);
    Block::setVal($secSvTestiId, 'client_rating', 'text', '5', 2, 4);
    // Testimonial 3
    Block::setVal($secSvTestiId, 'client_name', 'text', 'Yao Diabaté', 3, 0);
    Block::setVal($secSvTestiId, 'client_company', 'text', 'Fondateur, Startup Tech', 3, 1);
    Block::setVal($secSvTestiId, 'client_avatar', 'text', 'YD', 3, 2);
    Block::setVal($secSvTestiId, 'client_quote', 'textarea', 'Nos vidéos TikTok créées par Digitalium ont atteint 500K vues en 2 semaines. Un résultat au-delà de nos attentes !', 3, 3);
    Block::setVal($secSvTestiId, 'client_rating', 'text', '5', 3, 4);

    // Section 3.5 : Services CTA
    $secSvCtaId = (int)Section::addSection($page3Id, 'Services CTA Band', 'cta', 4);
    Block::setVal($secSvCtaId, 'eyebrow', 'text', 'Prêt à booster votre présence digitale ?');
    Block::setVal($secSvCtaId, 'title', 'text', 'Boostez votre présence digitale dès aujourd\'hui');
    Block::setVal($secSvCtaId, 'subtitle', 'textarea', 'Rejoignez 100+ clients satisfaits à Abidjan et à l\'international. Contactez-nous pour un devis gratuit et sans engagement.');
    Block::setVal($secSvCtaId, 'cta_text', 'text', 'Demander un devis gratuit');
    Block::setVal($secSvCtaId, 'cta_url', 'link', '/contact');

    // ----------------------------------------------------
    // PAGE 4 : BLOG (blog)
    // ----------------------------------------------------
    echo "Génération de la Page : Blog...\n";
    $page4Id = (int)Page::createPage(
        'Blog', 
        'blog', 
        'Blog & Actualités | Tendances Technologiques & IA', 
        'Suivez nos analyses techniques, tutoriels et décryptages sur le machine learning et les stratégies de marketing numérique.',
        'published'
    );

    // Section 4.1 : Blog Hero
    $secBgHeroId = (int)Section::addSection($page4Id, 'Bannière Blog', 'blog_hero', 0);
    Block::setVal($secBgHeroId, 'badge', 'text', 'Insights Digitaux & Intelligence Artificielle');
    Block::setVal($secBgHeroId, 'title', 'wysiwyg', 'Explorez l\'avenir du <em>digital</em><br>et de l\'<em>intelligence artificielle</em>');
    Block::setVal($secBgHeroId, 'subtitle', 'textarea', 'Stratégies, tendances et solutions concrètes pour transformer votre entreprise grâce au numérique et à l\'IA — par les experts Digitalium Group.');
    Block::setVal($secBgHeroId, 'cta_text', 'text', 'Explorer les articles');
    Block::setVal($secBgHeroId, 'cta_url', 'link', '#blog-grid');

    // Section 4.2 : Articles Grid (3 seeded articles)
    $secBgGridId = (int)Section::addSection($page4Id, 'Grille des Articles', 'blog_grid', 1);
    Block::setVal($secBgGridId, 'tag', 'text', 'Derniers articles');
    Block::setVal($secBgGridId, 'title', 'text', 'Tous nos contenus');
    // Article 1
    Block::setVal($secBgGridId, 'post_title', 'text', 'Solutions IA : automatiser vos processus et stimuler la croissance', 1, 0);
    Block::setVal($secBgGridId, 'post_category', 'text', 'Intelligence Artificielle', 1, 1);
    Block::setVal($secBgGridId, 'post_date', 'text', '15 Avril 2025', 1, 2);
    Block::setVal($secBgGridId, 'post_summary', 'textarea', 'Chatbots, analyses prédictives, assistants virtuels — comment déployer des solutions IA sur mesure.', 1, 3);
    Block::setVal($secBgGridId, 'post_icon', 'text', 'cpu', 1, 4);
    // Article 2
    Block::setVal($secBgGridId, 'post_title', 'text', 'SEO & réseaux sociaux : l\'IA au service de votre visibilité', 2, 0);
    Block::setVal($secBgGridId, 'post_category', 'text', 'Marketing Digital', 2, 1);
    Block::setVal($secBgGridId, 'post_date', 'text', '05 Avril 2025', 2, 2);
    Block::setVal($secBgGridId, 'post_summary', 'textarea', 'Automatisez votre stratégie de contenu et renforcez votre présence multicanale grâce à l\'IA.', 2, 3);
    Block::setVal($secBgGridId, 'post_icon', 'text', 'trending-up', 2, 4);
    // Article 3
    Block::setVal($secBgGridId, 'post_title', 'text', 'Transformation numérique : repenser l\'expérience client', 3, 0);
    Block::setVal($secBgGridId, 'post_category', 'text', 'Transformation', 3, 1);
    Block::setVal($secBgGridId, 'post_date', 'text', '28 Mars 2025', 3, 2);
    Block::setVal($secBgGridId, 'post_summary', 'textarea', 'Culture agile, modèles d\'affaires innovants et omniprésence en ligne — les clés d\'une transformation.', 3, 3);
    Block::setVal($secBgGridId, 'post_icon', 'text', 'zap', 3, 4);

    // Section 4.3 : Topics Exploration
    $secBgTopicsId = (int)Section::addSection($page4Id, 'Grille des thèmes', 'blog_topics', 2);
    Block::setVal($secBgTopicsId, 'tag', 'text', 'Explorer par thème');
    Block::setVal($secBgTopicsId, 'title', 'text', 'Nos grandes thématiques');
    // Topic 1
    Block::setVal($secBgTopicsId, 'topic_title', 'text', 'Intelligence Artificielle', 1, 0);
    Block::setVal($secBgTopicsId, 'topic_icon', 'text', 'cpu', 1, 1);
    Block::setVal($secBgTopicsId, 'topic_count', 'text', '12 articles', 1, 2);
    // Topic 2
    Block::setVal($secBgTopicsId, 'topic_title', 'text', 'Marketing Digital', 2, 0);
    Block::setVal($secBgTopicsId, 'topic_icon', 'text', 'trending-up', 2, 1);
    Block::setVal($secBgTopicsId, 'topic_count', 'text', '9 articles', 2, 2);
    // Topic 3
    Block::setVal($secBgTopicsId, 'topic_title', 'text', 'Développement Web', 3, 0);
    Block::setVal($secBgTopicsId, 'topic_icon', 'text', 'code', 3, 1);
    Block::setVal($secBgTopicsId, 'topic_count', 'text', '7 articles', 3, 2);
    // Topic 4
    Block::setVal($secBgTopicsId, 'topic_title', 'text', 'Transformation Digitale', 4, 0);
    Block::setVal($secBgTopicsId, 'topic_icon', 'text', 'zap', 4, 1);
    Block::setVal($secBgTopicsId, 'topic_count', 'text', '8 articles', 4, 2);

    // Section 4.4 : Newsletter
    $secBgNewsId = (int)Section::addSection($page4Id, 'Lettre d\'information', 'newsletter', 3);
    Block::setVal($secBgNewsId, 'tag', 'text', 'Newsletter');
    Block::setVal($secBgNewsId, 'title', 'text', 'Restez à la pointe du digital');
    Block::setVal($secBgNewsId, 'subtitle', 'textarea', 'Recevez chaque semaine nos meilleurs articles sur l\'IA, le marketing digital et les stratégies de transformation.');

    // ----------------------------------------------------
    // PAGE 5 : CONTACT (contact)
    // ----------------------------------------------------
    echo "Génération de la Page : Contact...\n";
    $page5Id = (int)Page::createPage(
        'Contact', 
        'contact', 
        'Contactez-nous | Formulaire de demande et devis gratuit', 
        'Discutez de votre projet de transformation numérique ou d\'intégration d\'IA. Notre équipe est basée à Abidjan, Côte d\'Ivoire.',
        'published'
    );

    // Section 5.1 : Contact Hero
    $secCtHeroId = (int)Section::addSection($page5Id, 'Bannière Contact', 'contact_hero', 0);
    Block::setVal($secCtHeroId, 'badge', 'text', 'Parlons de votre projet digital');
    Block::setVal($secCtHeroId, 'title', 'wysiwyg', 'Transformons vos idées en<br><em>solutions performantes</em>');
    Block::setVal($secCtHeroId, 'subtitle', 'textarea', 'Une question, un projet, un partenariat ? Notre équipe d\'experts vous répond sous 24h et vous propose une stratégie sur mesure.');

    // Section 5.2 : Contact Coordinates & Sidebar
    $secCtSidebarId = (int)Section::addSection($page5Id, 'Coordonnées & Horaires', 'contact_details', 1);
    Block::setVal($secCtSidebarId, 'title', 'text', 'Parlez-nous de Votre Projet');
    Block::setVal($secCtSidebarId, 'subtitle', 'textarea', 'Prêt à concevoir un système performant ? Remplissez ce formulaire et notre architecte principal vous contactera.');
    Block::setVal($secCtSidebarId, 'contact_email', 'text', 'contact@digitaliumgroup.com');
    Block::setVal($secCtSidebarId, 'contact_phone', 'text', '+225 07 07 82 02 11');
    Block::setVal($secCtSidebarId, 'contact_address', 'textarea', "Abidjan, Côte d'Ivoire\nPrésence régionale & internationale");
    Block::setVal($secCtSidebarId, 'cta_label', 'text', 'Envoyer la demande');
    Block::setVal($secCtSidebarId, 'hours_title', 'text', 'Horaires d\'ouverture');
    Block::setVal($secCtSidebarId, 'hours_desc', 'textarea', "Lundi – Vendredi : 08h00 – 18h00\nSamedi : 09h00 – 14h00\nDimanche : Fermé");

    // Section 5.3 : Quick Contact Strip
    $secCtStripId = (int)Section::addSection($page5Id, 'Bande des Services Rapides', 'services_strip', 2);
    Block::setVal($secCtStripId, 'title', 'text', 'Des services rapides et efficaces');
    Block::setVal($secCtStripId, 'subtitle', 'textarea', 'Digitalium Group est spécialisé dans la création de sites web, le branding et les solutions digitales innovantes.');
    // repeatable 1
    Block::setVal($secCtStripId, 'strip_label', 'text', 'Création de site web', 1, 0);
    Block::setVal($secCtStripId, 'strip_icon', 'text', 'layout', 1, 1);
    Block::setVal($secCtStripId, 'strip_sub', 'text', 'Développement sur mesure et performant', 1, 2);
    // repeatable 2
    Block::setVal($secCtStripId, 'strip_label', 'text', 'Branding & Design', 2, 0);
    Block::setVal($secCtStripId, 'strip_icon', 'text', 'palette', 2, 1);
    Block::setVal($secCtStripId, 'strip_sub', 'text', 'Identité visuelle et design créatif', 2, 2);
    // repeatable 3
    Block::setVal($secCtStripId, 'strip_label', 'text', 'Marketing Digital', 3, 0);
    Block::setVal($secCtStripId, 'strip_icon', 'text', 'trending-up', 3, 1);
    Block::setVal($secCtStripId, 'strip_sub', 'text', 'Campagnes et gestion des réseaux', 3, 2);
    // repeatable 4
    Block::setVal($secCtStripId, 'strip_label', 'text', 'Maintenance & TI', 4, 0);
    Block::setVal($secCtStripId, 'strip_icon', 'text', 'server', 4, 1);
    Block::setVal($secCtStripId, 'strip_sub', 'text', 'Suivi, mises à jour et assistance', 4, 2);

    // Section 5.4 : Contact CTA
    $secCtCtaId = (int)Section::addSection($page5Id, 'Bande de fin de page', 'cta', 3);
    Block::setVal($secCtCtaId, 'eyebrow', 'text', 'Prêt à booster votre présence en ligne ?');
    Block::setVal($secCtCtaId, 'title', 'text', 'Rejoignez 100+ clients satisfaits. Contactez-nous.');
    Block::setVal($secCtCtaId, 'subtitle', 'textarea', 'Votre transformation digitale commence par une simple conversation. Notre équipe est prête à vous accompagner.');
    Block::setVal($secCtCtaId, 'cta_text', 'text', 'Demander un devis gratuit');
    Block::setVal($secCtCtaId, 'cta_url', 'link', '#contact');

    echo "=== MULTI-PAGES CONFIGURÉES ET SEEDÉES AVEC SUCCÈS ! ===\n\n";
    echo "Pages disponibles :\n";
    echo "  - /           (home)\n";
    echo "  - /about      (À Propos)\n";
    echo "  - /service    (Services)\n";
    echo "  - /blog       (Blog)\n";
    echo "  - /contact    (Contact)\n\n";
    echo "Accès admin: /admin/login  (admin / password123)\n";

} catch (Exception $e) {
    echo "ERREUR LORS DU SEEDING GLOBAL : " . $e->getMessage() . "\n";
    exit(1);
}
