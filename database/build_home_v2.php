<?php
/**
 * Build Homepage v2 — Digitalium Group
 * Assemble la page d'accueil (hero + sections + blocs) selon la maquette validée.
 *
 * Auto-exécuté à chaque déploiement (voir .github/workflows/deploy.yml, étape MIGRATIONS)
 * pour que l'activation ne demande plus aucune action manuelle. Protégé par un flag
 * (storage/homepage_v2.lock) : une fois la homepage construite une première fois,
 * les exécutions suivantes ne font RIEN — afin de ne jamais écraser le contenu que
 * l'admin aura ensuite modifié depuis /admin/pages. Supprimer ce flag force une
 * reconstruction complète (retour aux valeurs par défaut ci-dessous).
 */

define('SECURE_ACCESS', true);
define('ROOT_PATH', dirname(__DIR__));
require ROOT_PATH . '/config/config.php';
require ROOT_PATH . '/app/Services/Database.php';

// PSR-4 Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = ROOT_PATH . '/app/';
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

use App\Models\Page;
use App\Models\Section;
use App\Models\Block;
use App\Services\Database;

echo "=== BUILD HOMEPAGE V2 ===\n";

$lockFile = ROOT_PATH . '/storage/homepage_v2.lock';
if (file_exists($lockFile)) {
    echo "Homepage v2 déjà activée (flag storage/homepage_v2.lock présent) — script ignoré, aucune donnée écrasée.\n";
    echo "Pour forcer une reconstruction complète : supprimer ce fichier puis relancer.\n";
    exit(0);
}

try {
    $page = Page::findBySlug('home');
    if (!$page) {
        echo "ERREUR: page 'home' introuvable. Exécutez d'abord database/master_migration.php.\n";
        exit(1);
    }
    $pageId = (int)$page['id'];
    echo "Page 'home' trouvée (ID {$pageId}).\n";

    // ─── 1. HERO — mise à jour des champs page.hero_* (conserve le reste) ────
    $heroData = $page; // repart des valeurs existantes pour ne rien écraser d'autre
    $heroData['hero_title']      = 'Digitaliser.<br>Automatiser.<br><span style="color:var(--primary);">Faire avancer votre entreprise.</span>';
    $heroData['hero_subtitle']   = "Digitalium Group est votre partenaire de confiance pour la transformation digitale, l'intelligence artificielle, le développement logiciel, la maintenance et l'innovation en Côte d'Ivoire et en Afrique.";
    $heroData['hero_badge']      = '';
    $heroData['hero_variant']    = 'hero_corporate';
    $heroData['hero_text_alignment'] = 'left';
    $heroData['hero_image_layout'] = 'right';
    $heroData['hero_cta1_text']  = 'Découvrir nos services';
    $heroData['hero_cta1_url']   = '#services-grid';
    $heroData['hero_cta2_text']  = 'Demander un audit';
    $heroData['hero_cta2_url']   = '/contact';
    $heroData['hero_status']     = 1;
    if (empty($heroData['hero_image'])) {
        $heroData['hero_image'] = '/assets/images/digitalium-hero-team.png';
    }
    Page::updatePage($pageId, $heroData);
    echo "Hero mis à jour (variant hero_corporate).\n";

    // ─── 2. Sections cibles (ordre exact du visuel) ───────────────────────────
    $targetTypes = [
        'logos_strip', 'stats_intro', 'about_visual', 'services_grid',
        'process', 'projects_showcase', 'testimonials_carousel', 'team', 'cta',
    ];

    $existingSections = Section::getByPage($pageId);
    $sectionIdByType = [];
    foreach ($existingSections as $sec) {
        if (in_array($sec['type'], $targetTypes, true) && !isset($sectionIdByType[$sec['type']])) {
            $sectionIdByType[$sec['type']] = (int)$sec['id'];
        }
    }

    // Désactive (sans supprimer) toute section pré-existante hors de la nouvelle structure
    foreach ($existingSections as $sec) {
        if (!in_array($sec['type'], $targetTypes, true)) {
            Database::query("UPDATE sections SET status = 'inactive' WHERE id = :id", ['id' => $sec['id']]);
            echo "Section existante désactivée (hors nouvelle structure) : #{$sec['id']} [{$sec['type']}] {$sec['name']}\n";
        }
    }

    $order = 0;
    $sec = function (string $type, string $name) use (&$order, &$sectionIdByType, $pageId) {
        if (isset($sectionIdByType[$type])) {
            $id = $sectionIdByType[$type];
            Database::query("UPDATE sections SET status = 'active', sort_order = :o, name = :n WHERE id = :id", [
                'o' => $order, 'n' => $name, 'id' => $id,
            ]);
        } else {
            $id = (int)Section::addSection($pageId, $name, $type, $order);
            echo "Section créée : #{$id} [{$type}] {$name}\n";
        }
        $order++;
        return $id;
    };

    // ─── 3. logos_strip ────────────────────────────────────────────────────────
    $id = $sec('logos_strip', 'Ils nous font confiance');
    Block::setVal($id, 'title', 'text', 'Ils nous font confiance');
    $logos = [
        ['SOGEFI', 'shield-check'], ['Africom', 'radio-tower'], ['Ivoire Services', 'briefcase'],
        ['Proxima CI', 'zap'], ['Novatech', 'cpu'], ['Bizao', 'link-2'],
        ['Groupe Kali', 'building-2'], ['Teranga Solutions', 'globe'],
    ];
    foreach ($logos as $g => $l) {
        Block::setVal($id, 'logo_name', 'text', $l[0], $g + 1, 0);
        Block::setVal($id, 'logo_icon', 'text', $l[1], $g + 1, 1);
    }

    // ─── 4. stats_intro ────────────────────────────────────────────────────────
    $id = $sec('stats_intro', "L'innovation au service de votre performance");
    Block::setVal($id, 'badge', 'text', 'À propos de Digitalium Group');
    Block::setVal($id, 'title', 'text', "L'innovation au service de votre performance");
    Block::setVal($id, 'description', 'textarea', "Nous accompagnons les entreprises et organisations dans leur croissance grâce à des solutions digitales fiables, sécurisées et évolutives.");
    Block::setVal($id, 'link_text', 'text', 'En savoir plus sur nous');
    Block::setVal($id, 'link_url', 'link', '/a-propos');
    $stats = [
        ['users', '100+', "Clients accompagnés dans divers secteurs d'activité"],
        ['star', '95%', 'Taux de satisfaction grâce à notre engagement et à la qualité de nos services'],
        ['headphones', 'Support réactif', 'Une équipe disponible et réactive pour vous accompagner au quotidien'],
        ['puzzle', 'Solutions sur mesure', 'Des solutions adaptées à vos besoins et à vos objectifs business'],
    ];
    foreach ($stats as $g => $s) {
        Block::setVal($id, 'stat_icon', 'text', $s[0], $g + 1, 0);
        Block::setVal($id, 'stat_value', 'text', $s[1], $g + 1, 1);
        Block::setVal($id, 'stat_desc', 'textarea', $s[2], $g + 1, 2);
    }

    // ─── 5. about_visual ───────────────────────────────────────────────────────
    $id = $sec('about_visual', 'Votre partenaire de confiance');
    Block::setVal($id, 'image', 'image', '/assets/uploads/digitalium-pic-3-1780069686.webp');
    Block::setVal($id, 'badge_years', 'text', '8+');
    Block::setVal($id, 'badge_label', 'text', "Années d'expérience");
    Block::setVal($id, 'title', 'text', 'Votre partenaire de confiance pour la transformation digitale');
    Block::setVal($id, 'description', 'textarea', "Depuis notre création, nous aidons les entreprises à relever leurs défis grâce à la technologie. Notre mission est simple : créer de la valeur, optimiser vos processus et vous préparer à l'avenir.");
    Block::setVal($id, 'check_1', 'text', 'Stratégie digitale & conseil');
    Block::setVal($id, 'check_2', 'text', "Développement d'applications web et mobiles");
    Block::setVal($id, 'check_3', 'text', 'Intelligence artificielle & automatisation');
    Block::setVal($id, 'check_4', 'text', 'Maintenance informatique & support');
    Block::setVal($id, 'check_5', 'text', 'Accompagnement & formation des équipes');

    // ─── 6. services_grid ──────────────────────────────────────────────────────
    $id = $sec('services_grid', 'Nos services');
    Block::setVal($id, 'tag', 'text', 'Nos services');
    Block::setVal($id, 'title', 'text', 'Des solutions complètes pour accélérer votre transformation');
    Block::setVal($id, 'subtitle', 'textarea', '');
    $services = [
        ['Web', 'code-2', 'Développement Web & Applications', 'Sites web, applications métiers et mobiles performants, sécurisés et évolutifs.'],
        ['IA', 'brain-circuit', 'Intelligence Artificielle & Automatisation', 'Automatisez vos processus et exploitez la puissance des données et de l\'IA.'],
        ['Support', 'wrench', 'Maintenance & Support Informatique', 'Infogérance, dépannage et support technique pour assurer la continuité de vos activités.'],
        ['Réseaux', 'network', 'Câblage Réseaux & Infrastructure', 'Installation, configuration et sécurisation de vos infrastructures réseau.'],
        ['Data', 'bar-chart-3', 'Data & Business Intelligence', 'Tableaux de bord, reporting et analyse de données pour guider vos décisions.'],
        ['Content', 'megaphone', 'Création de Contenu & Communication Digitale', 'Stratégie de contenu, branding et gestion de votre présence digitale.'],
    ];
    foreach ($services as $g => $s) {
        Block::setVal($id, 'svc_tag', 'text', $s[0], $g + 1, 0);
        Block::setVal($id, 'svc_icon', 'text', $s[1], $g + 1, 1);
        Block::setVal($id, 'svc_title', 'text', $s[2], $g + 1, 2);
        Block::setVal($id, 'svc_points', 'textarea', $s[3], $g + 1, 3);
        Block::setVal($id, 'svc_link', 'link', '/contact', $g + 1, 4);
    }

    // ─── 7. process ────────────────────────────────────────────────────────────
    $id = $sec('process', 'Notre approche en 6 étapes');
    Block::setVal($id, 'tag', 'text', 'Pourquoi Digitalium ?');
    Block::setVal($id, 'title', 'text', 'Notre approche en 6 étapes');
    $steps = [
        ['01', 'search', 'Analyse', 'Nous analysons vos besoins, vos défis et votre environnement.'],
        ['02', 'target', 'Stratégie', 'Nous définissons une stratégie digitale alignée à vos objectifs.'],
        ['03', 'pencil-ruler', 'Conception', 'Nous concevons des solutions sur mesure centrées utilisateur.'],
        ['04', 'code', 'Déploiement', 'Nous développons et intégrons avec agilité et qualité.'],
        ['05', 'trending-up', 'Optimisation', 'Nous améliorons en continu pour plus de performance.'],
        ['06', 'headphones', 'Support', 'Nous restons à vos côtés pour assurer votre succès.'],
    ];
    foreach ($steps as $g => $s) {
        Block::setVal($id, 'proc_num', 'text', $s[0], $g + 1, 0);
        Block::setVal($id, 'proc_icon', 'text', $s[1], $g + 1, 1);
        Block::setVal($id, 'proc_title', 'text', $s[2], $g + 1, 2);
        Block::setVal($id, 'proc_desc', 'textarea', $s[3], $g + 1, 3);
    }

    // ─── 8. projects_showcase ──────────────────────────────────────────────────
    $id = $sec('projects_showcase', 'Des projets qui créent de la valeur');
    Block::setVal($id, 'tag', 'text', 'Réalisations');
    Block::setVal($id, 'title', 'text', 'Des projets qui créent de la valeur');
    Block::setVal($id, 'subtitle', 'textarea', '');
    Block::setVal($id, 'more_text', 'text', 'Voir plus de réalisations');
    Block::setVal($id, 'more_url', 'link', '/realisations');
    $projects = [
        ['FINANCE', 'Plateforme de gestion financière', 'Solution web de gestion financière et reporting pour une institution financière.', '-40% de temps de traitement des rapports', '/assets/uploads/website-design-featuring-user-interface-elements-displaying-the-latest-trends-in-web-design-interfa-1780069994.webp'],
        ['LOGISTIQUE', 'Application mobile de livraison', 'Application mobile de suivi des livraisons en temps réel avec géolocalisation.', '+60% de satisfaction client', '/assets/uploads/digitalium-pic-8-1780069994.webp'],
        ['SANTÉ', "Système d'information hospitalier", 'Digitalisation des processus hospitaliers et gestion des dossiers patients.', 'Meilleure traçabilité et efficacité opérationnelle', '/assets/uploads/ivoire-kita-1780071304.webp'],
    ];
    foreach ($projects as $g => $p) {
        Block::setVal($id, 'proj_image', 'image', $p[4], $g + 1, 0);
        Block::setVal($id, 'proj_category', 'text', $p[0], $g + 1, 1);
        Block::setVal($id, 'proj_title', 'text', $p[1], $g + 1, 2);
        Block::setVal($id, 'proj_desc', 'textarea', $p[2], $g + 1, 3);
        Block::setVal($id, 'proj_result', 'text', $p[3], $g + 1, 4);
        Block::setVal($id, 'proj_link', 'link', '/realisations', $g + 1, 5);
    }

    // ─── 9. testimonials_carousel ──────────────────────────────────────────────
    $id = $sec('testimonials_carousel', 'Témoignages');
    Block::setVal($id, 'tag', 'text', 'Témoignages');
    Block::setVal($id, 'title', 'text', 'La satisfaction de nos clients, notre priorité');
    Block::setVal($id, 'subtitle', 'textarea', '');
    $testimonials = [
        ['Digitalium Group a totalement transformé notre manière de travailler. Leur expertise et leur écoute font toute la différence.', 'Aissatou Diabaté', 'Directrice des Opérations, Africom'],
        ['Une équipe professionnelle, réactive et innovante. Nos projets ont été livrés dans les délais avec une qualité exceptionnelle.', "Koffi N'Guesan", 'Directeur Général, Ivoire Services'],
        ["Leur accompagnement en IA et automatisation nous a permis d'optimiser nos processus et de réduire nos coûts.", 'Fotou Traoré', 'CEO, Proxima CI'],
    ];
    foreach ($testimonials as $g => $t) {
        Block::setVal($id, 'client_quote', 'textarea', $t[0], $g + 1, 0);
        Block::setVal($id, 'client_name', 'text', $t[1], $g + 1, 1);
        Block::setVal($id, 'client_role', 'text', $t[2], $g + 1, 2);
        Block::setVal($id, 'client_avatar', 'image', '', $g + 1, 3);
    }

    // ─── 10. team ──────────────────────────────────────────────────────────────
    $id = $sec('team', 'Les experts derrière Digitalium');
    Block::setVal($id, 'title', 'text', 'Les experts derrière Digitalium');
    Block::setVal($id, 'subtitle', 'textarea', '');
    Block::setVal($id, 'more_text', 'text', 'Rejoignez notre équipe');
    Block::setVal($id, 'more_url', 'link', '/contact');
    $team = [
        ["Yannick N'Dri", 'CEO & Fondateur', 'Direction'],
        ['Christelle Kouassi', 'Directrice des Opérations', 'Direction'],
        ['Jean-Marc Bomba', 'Lead Développeur', 'Développement'],
        ['Mariam Diallo', 'IA & Data Scientist', 'IA & Data'],
        ['Ismaël Koné', 'Ingénieur Réseaux', 'Infrastructure'],
        ['Aicha Touré', 'Responsable Support', 'Support'],
    ];
    foreach ($team as $g => $m) {
        Block::setVal($id, 'member_avatar', 'image', '', $g + 1, 0);
        Block::setVal($id, 'member_name', 'text', $m[0], $g + 1, 1);
        Block::setVal($id, 'member_role', 'text', $m[1], $g + 1, 2);
        Block::setVal($id, 'member_dept', 'text', $m[2], $g + 1, 3);
    }
    echo "  -> ATTENTION: photos d'équipe (6) laissées vides à dessein — voir note ci-dessous.\n";

    // ─── 11. cta ───────────────────────────────────────────────────────────────
    $id = $sec('cta', 'CTA final');
    Block::setVal($id, 'eyebrow', 'text', '');
    Block::setVal($id, 'title', 'text', 'Prêt à accélérer votre transformation digitale ?');
    Block::setVal($id, 'subtitle', 'textarea', 'Parlons de vos objectifs et construisons ensemble des solutions qui font la différence.');
    Block::setVal($id, 'cta_text', 'text', 'Parler à un expert');
    Block::setVal($id, 'cta_url', 'link', '/contact');
    Block::setVal($id, 'cta2_text', 'text', 'Nous contacter');
    Block::setVal($id, 'cta2_url', 'link', '/contact');

    \App\Services\Cache::clear();
    file_put_contents($lockFile, date('Y-m-d H:i:s') . " — Homepage v2 construite (page_id={$pageId})\n");
    echo "\n=== HOMEPAGE V2 CONSTRUITE AVEC SUCCÈS ===\n";
    echo "Flag créé : storage/homepage_v2.lock (empêche toute réexécution automatique future)\n";
    echo "Hero, photo équipe et 3 visuels de réalisations : assignés automatiquement depuis la médiathèque existante.\n";
    echo "Reste à faire manuellement (Médiathèque, /admin/pages/edit/{$pageId}) : les 6 avatars de l'équipe —\n";
    echo "  volontairement non assignés, faute de photos réelles correctement associées à chaque nom.\n";

} catch (\Throwable $e) {
    echo "ERREUR: " . $e->getMessage() . " (" . $e->getFile() . ":" . $e->getLine() . ")\n";
    exit(1);
}
