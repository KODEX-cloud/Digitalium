<?php
/**
 * Seeder to insert a dedicated AI & Automation Page
 */

define('SECURE_ACCESS', true);
require_once __DIR__ . '/../config/config.php';

// PSR-4 Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = dirname(__DIR__) . '/app/';
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
use App\Models\Page;
use App\Models\Section;
use App\Models\Block;

try {
    echo "=== GENERATING AI & AUTOMATION PAGE ===\n";
    
    // Check if page exists
    $exists = Page::findBySlug('ia-automatisation');
    if ($exists) {
        echo "Page 'ia-automatisation' already exists. Skipping.\n";
        exit(0);
    }
    
    $pageId = (int)Page::createPage(
        'IA & Automatisation', 
        'ia-automatisation', 
        'Automatisation IA | Propulsez votre entreprise avec l\'Intelligence Artificielle', 
        'Découvrez nos solutions d\'automatisation par intelligence artificielle : intégration de modèles LLM, chatbots intelligents et workflows autonomes.',
        'published'
    );
    
    echo "Page 'ia-automatisation' created with ID: {$pageId}\n";
    
    // Section 1: Hero
    $secHeroId = (int)Section::addSection($pageId, 'Bannière IA & Automatisation', 'hero', 0);
    Block::setVal($secHeroId, 'badge', 'text', 'Solutions Intelligentes');
    Block::setVal($secHeroId, 'title', 'wysiwyg', 'Automatisez vos<br>processus par<br><span class="hi">l\'IA</span>');
    Block::setVal($secHeroId, 'subtitle', 'textarea', 'Nous concevons des agents autonomes et des intégrations de grands modèles de langage (LLM) pour multiplier par 10 la productivité de vos équipes.');
    Block::setVal($secHeroId, 'cta_text', 'text', 'Commencer l\'automatisation');
    Block::setVal($secHeroId, 'cta_url', 'link', '/contact');
    Block::setVal($secHeroId, 'bg_image', 'image', '/assets/images/services_3d.png');
    Block::setVal($secHeroId, 'stats_years', 'text', '10x');
    Block::setVal($secHeroId, 'stats_clients', 'text', '50+');
    Block::setVal($secHeroId, 'stats_satisfaction', 'text', '99%');
    
    // Section 2: Solutions Grid (services_grid style)
    $secGridId = (int)Section::addSection($pageId, 'Nos Solutions IA', 'services_grid', 1);
    Block::setVal($secGridId, 'tag', 'text', 'Nos Solutions');
    Block::setVal($secGridId, 'title', 'text', 'Des outils d\'automatisation de pointe');
    Block::setVal($secGridId, 'subtitle', 'textarea', 'Des agents intelligents et intégrations robustes pour une efficacité opérationnelle maximale.');
    
    // Solutions Group 1
    Block::setVal($secGridId, 'card_title', 'text', 'Agents de Support Autonomes', 1, 0);
    Block::setVal($secGridId, 'card_icon', 'text', 'message-square', 1, 1);
    Block::setVal($secGridId, 'card_description', 'textarea', 'Intégration de chatbots intelligents capables de résoudre 80% des demandes de support client de manière autonome en temps réel.', 1, 2);
    
    // Solutions Group 2
    Block::setVal($secGridId, 'card_title', 'text', 'Automatisation de Workflows (RPA)', 2, 0);
    Block::setVal($secGridId, 'card_icon', 'text', 'git-branch', 2, 1);
    Block::setVal($secGridId, 'card_description', 'textarea', 'Connexion de vos outils métiers (CRM, ERP, Slack) pour automatiser le transfert et le traitement des données sans erreur.', 2, 2);
    
    // Solutions Group 3
    Block::setVal($secGridId, 'card_title', 'text', 'Analyse Prédictive & Dashboard', 3, 0);
    Block::setVal($secGridId, 'card_icon', 'text', 'pie-chart', 3, 1);
    Block::setVal($secGridId, 'card_description', 'textarea', 'Modélisation de vos données historiques pour prévoir les tendances de ventes et optimiser vos stocks automatiquement.', 3, 2);

    // Section 3: Process Strip (process_strip style)
    $secProcessId = (int)Section::addSection($pageId, 'Méthodologie IA', 'process_strip', 2);
    Block::setVal($secProcessId, 'tag', 'text', 'Notre Méthode');
    Block::setVal($secProcessId, 'title', 'text', 'Déploiement en 3 étapes');
    
    // Steps
    Block::setVal($secProcessId, 'card_title', 'text', '1. Audit Technique', 1, 0);
    Block::setVal($secProcessId, 'card_icon', 'text', 'eye', 1, 1);
    Block::setVal($secProcessId, 'card_description', 'textarea', 'Analyse de vos processus manuels répétitifs et identification des opportunités d\'automatisation.', 1, 2);

    Block::setVal($secProcessId, 'card_title', 'text', '2. Développement & Fine-tuning', 2, 0);
    Block::setVal($secProcessId, 'card_icon', 'text', 'code', 2, 1);
    Block::setVal($secProcessId, 'card_description', 'textarea', 'Création et entraînement de modèles d\'IA spécialisés sur vos propres bases de connaissances.', 2, 2);

    Block::setVal($secProcessId, 'card_title', 'text', '3. Intégration & Suivi', 3, 0);
    Block::setVal($secProcessId, 'card_icon', 'text', 'rocket', 3, 1);
    Block::setVal($secProcessId, 'card_description', 'textarea', 'Mise en production, branchement des APIs, et surveillance continue des gains de productivité.', 3, 2);

    // Section 4: CTA
    $secCtaId = (int)Section::addSection($pageId, 'CTA Final IA', 'cta', 3);
    Block::setVal($secCtaId, 'eyebrow', 'text', 'Prêt pour le futur de la productivité ?');
    Block::setVal($secCtaId, 'title', 'text', 'Propulsez votre entreprise dans l\'ère de l\'automatisation par l\'IA');
    Block::setVal($secCtaId, 'subtitle', 'textarea', 'Prenez rendez-vous avec l\'un de nos architectes IA pour obtenir une démonstration personnalisée et gratuite sur vos workflows.');
    Block::setVal($secCtaId, 'cta_text', 'text', 'Planifier ma démo');
    Block::setVal($secCtaId, 'cta_url', 'link', '/contact');

    \App\Services\Cache::clear();
    echo "=== AI & AUTOMATION PAGE CREATED SUCCESSFULLY ===\n";
    
} catch (Exception $e) {
    echo "Error creating page: " . $e->getMessage() . "\n";
    exit(1);
}
