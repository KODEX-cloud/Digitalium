<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    /**
     * Adresse canonique de la page courante.
     *
     * Auparavant déduite du seul `$currentSlug`, elle était FAUSSE dans deux cas :
     *   — un article, servi sous /insights/{slug}, déclarait /blog comme canonique,
     *     si bien que tous les articles se déclaraient être la même page ;
     *   — une sous-page (/solutions/…) omettait son parent.
     * Un contrôleur peut désormais imposer le chemin exact via `canonical_path`.
     */
    $_canonPath = trim((string)($page['canonical_path'] ?? ''));
    if ($_canonPath === '') {
        $_parent    = trim((string)($page['parent_slug'] ?? ''));
        $_canonPath = ($currentSlug === 'home' || $currentSlug === '')
            ? ''
            : '/' . ($_parent !== '' ? $_parent . '/' : '') . $currentSlug;
    }
    $_canonUrl = 'https://digitaliumgroup.com' . $_canonPath;

    /* Visuel de partage : celui de la page si elle en porte un, sinon celui du site. */
    $_ogImage = trim((string)($page['og_image'] ?? ''));
    if ($_ogImage === '') {
        $_ogImage = '/assets/images/og-image.jpg';
    }
    if (!preg_match('#^https?://#i', $_ogImage)) {
        $_ogImage = 'https://digitaliumgroup.com' . url($_ogImage);
    }

    $_ogType  = trim((string)($page['og_type'] ?? '')) ?: 'website';
    $_metaDsc = (string)($page['meta_description'] ?? '')
        ?: 'Solutions logicielles de pointe et transformation digitale sur-mesure pour votre entreprise.';
    $_metaTtl = ($page['meta_title'] ?? '') ?: ($page['title'] ?? '');
    $_siteNom = $settings['site_name'] ?? 'Digitalium Group';
    ?>
    <title><?= htmlspecialchars($_metaTtl) ?> | <?= htmlspecialchars($_siteNom) ?></title>
    <meta name="description" content="<?= htmlspecialchars($_metaDsc) ?>">
    <link rel="canonical" href="<?= htmlspecialchars($_canonUrl) ?>">

    <meta property="og:type" content="<?= htmlspecialchars($_ogType) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($_canonUrl) ?>">
    <meta property="og:title" content="<?= htmlspecialchars($_metaTtl) ?> | <?= htmlspecialchars($_siteNom) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($_metaDsc) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($_ogImage) ?>">

    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:title" content="<?= htmlspecialchars($_metaTtl) ?> | <?= htmlspecialchars($_siteNom) ?>">
    <meta property="twitter:description" content="<?= htmlspecialchars($_metaDsc) ?>">
    <meta property="twitter:image" content="<?= htmlspecialchars($_ogImage) ?>">

    <link rel="stylesheet" href="<?= url('/assets/css/index.css') ?>?v=<?= filemtime(ROOT_PATH . '/public/assets/css/index.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php
    // ─── CMS Theme Builder — Inject design tokens from settings ──────────────
    $_thP    = htmlspecialchars($settings['theme_primary']                ?? 'var(--primary)', ENT_QUOTES);
    $_thS    = htmlspecialchars($settings['theme_secondary']              ?? '#0891b2', ENT_QUOTES);
    $_thAc   = htmlspecialchars($settings['theme_accent']                 ?? '#f59e0b', ENT_QUOTES);
    $_thTM   = htmlspecialchars($settings['theme_text_main']              ?? 'var(--surface-dark)', ENT_QUOTES);
    $_thTS   = htmlspecialchars($settings['theme_text_sub']               ?? '#334155', ENT_QUOTES);
    $_thTMu  = htmlspecialchars($settings['theme_text_muted']             ?? '#64748b', ENT_QUOTES);
    $_thBB   = htmlspecialchars($settings['theme_bg_base']                ?? '#ffffff', ENT_QUOTES);
    $_thBA   = htmlspecialchars($settings['theme_bg_alt']                 ?? 'var(--bg-base)', ENT_QUOTES);
    $_thBC   = htmlspecialchars($settings['theme_bg_card']                ?? '#ffffff', ENT_QUOTES);
    $_thShC  = htmlspecialchars($settings['theme_shadow_card']            ?? '0 1px 3px rgba(0,0,0,0.04), 0 8px 32px rgba(0,0,0,0.06)', ENT_QUOTES);
    $_thShB  = htmlspecialchars($settings['theme_shadow_btn']             ?? '0 4px 18px color-mix(in srgb, var(--primary) 28%, transparent)', ENT_QUOTES);
    $_thBtnBg = htmlspecialchars($settings['theme_btn_primary_bg']    ?? '#272727', ENT_QUOTES);
    $_thBtnFg = htmlspecialchars($settings['theme_btn_primary_text']  ?? '#ffffff', ENT_QUOTES);
    $_thBdgBg = htmlspecialchars($settings['theme_badge_bg']          ?? '#e0f1df', ENT_QUOTES);
    $_thBdgFg = htmlspecialchars($settings['theme_badge_text']        ?? '#004d3f', ENT_QUOTES);
    $_thFtBg  = htmlspecialchars($settings['theme_footer_bg']         ?? '#ffffff', ENT_QUOTES);
    $_thSfDk  = htmlspecialchars($settings['theme_surface_dark']      ?? '#12202c', ENT_QUOTES);
    $_thRP   = (int)($settings['theme_radius_pill']            ?? 9);
    $_thRC   = (int)($settings['theme_radius_card']            ?? 20);
    $_thRBtn = (int)($settings['theme_radius_btn']             ?? 14);
    $_thRMd  = (int)($settings['theme_radius_md']              ?? 12);
    $_thRSm  = (int)($settings['theme_radius_sm']              ?? 8);
    $_thSS   = (int)($settings['theme_space_section']          ?? 92);
    $_thSSSm = max(48, $_thSS - 32);
    $_thSSXs = max(32, $_thSS - 50);
    $_thFH1  = (float)($settings['theme_font_h1']              ?? 4.3);
    $_thFH2  = (float)($settings['theme_font_h2']              ?? 3);
    $_thFH3  = (float)($settings['theme_font_h3']              ?? 1.25);
    $_thFB   = (float)($settings['theme_font_body']            ?? 1);
    $_thWH   = (int)($settings['theme_font_weight_heading']    ?? 800);
    $_thWB   = (int)($settings['theme_font_weight_body']       ?? 400);
    $_thLH   = (float)($settings['theme_line_height_body']     ?? 1.55);
    $_thLS   = (float)($settings['theme_letter_spacing_heading']?? -0.02);
    ?>
    <style id="cms-theme">
    :root {
        --primary:                  <?= $_thP ?>;
        --primary-dark:             <?= $_thP ?>;
        --secondary:                <?= $_thS ?>;
        --accent:                   <?= $_thAc ?>;
        --text-main:                <?= $_thTM ?>;
        --text-sub:                 <?= $_thTS ?>;
        --text-muted:               <?= $_thTMu ?>;
        --bg-base:                  <?= $_thBB ?>;
        --bg-alt:                   <?= $_thBA ?>;
        --bg-card:                  <?= $_thBC ?>;
        --btn-primary-bg:           <?= $_thBtnBg ?>;
        --btn-primary-text:         <?= $_thBtnFg ?>;
        --badge-bg:                 <?= $_thBdgBg ?>;
        --badge-text:               <?= $_thBdgFg ?>;
        --footer-bg:                <?= $_thFtBg ?>;
        --surface-dark:             <?= $_thSfDk ?>;
        --radius-pill:              <?= $_thRP ?>px;
        --radius-lg:                <?= $_thRC ?>px;
        --radius-md:                <?= $_thRMd ?>px;
        --radius-sm:                <?= $_thRSm ?>px;
        --radius-btn:               <?= $_thRBtn ?>px;
        --space-section:            <?= $_thSS ?>px;
        --space-section-sm:         <?= $_thSSSm ?>px;
        --space-section-xs:         <?= $_thSSXs ?>px;
        --shadow-card:              <?= $_thShC ?>;
        --shadow-btn:               <?= $_thShB ?>;
        --font-size-h1:             clamp(2rem, 5vw, <?= $_thFH1 ?>rem);
        --font-size-h2:             clamp(1.5rem, 3.5vw, <?= $_thFH2 ?>rem);
        --font-size-h3:             <?= $_thFH3 ?>rem;
        --font-size-body:           <?= $_thFB ?>rem;
        --font-weight-heading:      <?= $_thWH ?>;
        --font-weight-body:         <?= $_thWB ?>;
        --line-height-body:         <?= $_thLH ?>;
        --letter-spacing-heading:   <?= $_thLS ?>em;
    }
    </style>
    <?php if (!empty($settings['site_favicon'])): ?>
        <link rel="icon" href="<?= htmlspecialchars(url($settings['site_favicon'])) ?>" type="image/x-icon">
    <?php else: ?>
        <link rel="icon" href="<?= url('/assets/images/favicon.png') ?>" type="image/x-icon">
    <?php endif; ?>
    <script src="https://unpkg.com/lucide@latest"></script>
    <?php
    // Prepare visual style settings from current page configuration
    $headerBgMode = $page['header_bg_mode'] ?? 'glass';
    $headerOpacity = isset($page['header_opacity']) ? (float)$page['header_opacity'] : 0.65;
    $headerBlur = isset($page['header_blur']) ? (int)$page['header_blur'] : 20;
    $headerShadow = $page['header_shadow'] ?? 'moyen';
    $headerContrastMode = $page['header_contrast_mode'] ?? 'default';
    $logoLight = !empty($page['logo_light']) ? $page['logo_light'] : ($settings['site_logo_light'] ?? '');
    $logoDark = !empty($page['logo_dark']) ? $page['logo_dark'] : ($settings['site_logo_dark'] ?? '');
    $logoSize = isset($page['logo_size']) ? (int)$page['logo_size'] : 38;

    // Decode responsive settings JSON
    $respSettings = [];
    if (!empty($page['responsive_settings'])) {
        $respSettings = json_decode($page['responsive_settings'], true) ?: [];
    }
    $mobileHeaderVisible = $respSettings['mobile']['header_visible'] ?? true;
    $mobileLogoSize = $respSettings['mobile']['logo_size'] ?? '';
    $tabletLogoSize = $respSettings['tablet']['logo_size'] ?? '';

    // Light-first header background modes
    $headerBgColorNormal   = 'rgba(255,255,255,0.95)';
    $headerBgColorScrolled = 'rgba(255,255,255,0.98)';
    $headerBackdropFilter  = "blur({$headerBlur}px)";
    $headerBorder          = '1px solid rgba(0,0,0,0.07)';

    if ($headerBgMode === 'sombre' || $headerBgMode === 'glass') {
        $headerBgColorNormal   = 'rgba(255,255,255,0.95)';
        $headerBgColorScrolled = 'rgba(255,255,255,0.98)';
        $headerBorder          = '1px solid rgba(0,0,0,0.07)';
    } elseif ($headerBgMode === 'clair') {
        $headerBgColorNormal   = 'rgba(255,255,255,0.97)';
        $headerBgColorScrolled = 'rgba(255,255,255,1)';
    } elseif ($headerBgMode === 'plein') {
        $headerBgColorNormal   = '#ffffff';
        $headerBgColorScrolled = '#ffffff';
        $headerBackdropFilter  = 'none';
        $headerBorder          = '1px solid rgba(0,0,0,0.08)';
    }

    // Shadow strength
    $headerShadowCss = '0 1px 0 rgba(0,0,0,0.06), 0 4px 20px rgba(0,0,0,0.06)';
    if ($headerShadow === 'leger') {
        $headerShadowCss = '0 1px 0 rgba(0,0,0,0.05)';
    } elseif ($headerShadow === 'fort') {
        $headerShadowCss = '0 4px 32px rgba(0,0,0,0.12)';
    }

    // Dynamic Logo Selection
    $selectedLogo = $settings['site_logo'] ?? '';
    if ($headerContrastMode === 'light_on_dark' || $headerContrastMode === 'light on dark') {
        $selectedLogo = !empty($logoLight) ? $logoLight : ($settings['site_logo_light'] ?? ($settings['site_logo'] ?? ''));
    } elseif ($headerContrastMode === 'dark_on_light' || $headerContrastMode === 'dark on light') {
        $selectedLogo = !empty($logoDark) ? $logoDark : ($settings['site_logo_dark'] ?? ($settings['site_logo'] ?? ''));
    } else {
        if ($headerBgMode === 'sombre') {
            $selectedLogo = !empty($logoLight) ? $logoLight : ($settings['site_logo_light'] ?? ($settings['site_logo'] ?? ''));
        } else {
            $selectedLogo = !empty($logoDark) ? $logoDark : ($settings['site_logo_dark'] ?? ($settings['site_logo'] ?? ''));
        }
    }
    if (empty($selectedLogo)) {
        $selectedLogo = $settings['site_logo'] ?? '';
    }
    ?>
    <style>
        .logo-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        .logo-d-mark {
            width: 20px;
            height: 28px;
            background: #1a3a6b;
            border-radius: 0 4px 4px 0;
            border-left: 3px solid #1a6fba;
        }
        .logo-ring-mark {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: 3px solid transparent;
            border-top-color: #e03a3a;
            border-right-color: #2eaa5c;
            border-bottom-color: #f5b800;
            border-left-color: #f07820;
            margin-left: -5px;
        }
        .logo-text-col {
            display: flex;
            flex-direction: column;
            margin-left: 6px;
            line-height: 1;
        }
        /* Light theme — logo text sombre */
        .logo-text-col strong {
            font-size: 1.1rem;
            font-weight: 700;
            color: #111827 !important;
            letter-spacing: -0.01em;
            font-family: var(--font-heading);
            transition: var(--transition);
        }
        .logo-text-col span {
            font-size: .5rem;
            letter-spacing: .25em;
            color: #6b7280 !important;
            text-transform: uppercase;
            font-weight: 500;
            transition: var(--transition);
        }

        /* Dynamic Header Styling — light */
        #siteHeader {
            background-color: <?= $headerBgColorNormal ?> !important;
            backdrop-filter: <?= $headerBackdropFilter ?> !important;
            -webkit-backdrop-filter: <?= $headerBackdropFilter ?> !important;
            border-bottom: <?= $headerBorder ?> !important;
            border-top: none !important;
            border-left: none !important;
            border-right: none !important;
            box-shadow: <?= $headerShadowCss ?> !important;
            top: 0 !important;
            border-radius: 0 !important;
        }
        #siteHeader.scrolled {
            background-color: <?= $headerBgColorScrolled ?> !important;
            height: 64px !important;
            top: 0 !important;
            box-shadow: 0 4px 32px rgba(0,0,0,0.1) !important;
        }
        #siteHeader.scrolled img { height: <?= max(26, $logoSize - 6) ?>px !important; }
        #siteHeader img {
            height: <?= $logoSize ?>px !important;
            width: auto;
            object-fit: contain;
            transition: var(--transition);
        }

        /* Navigation links — light theme */
        #siteHeader .nav-link { color: #374151 !important; }
        #siteHeader .nav-link:hover { color: #111827 !important; background: rgba(0,0,0,0.04) !important; }
        #siteHeader .nav-link.active { color: var(--primary) !important; background: color-mix(in srgb, var(--primary) 8%, transparent) !important; }
        #siteHeader .menu-toggle { color: #111827 !important; }

        /* Responsive Breakpoint styling overrides */
        @media (max-width: 768px) {
            <?php if (!$mobileHeaderVisible): ?>
            #siteHeader {
                display: none !important;
            }
            <?php endif; ?>
            <?php if (!empty($mobileLogoSize)): ?>
            #siteHeader img {
                height: <?= $mobileLogoSize ?>px !important;
            }
            <?php endif; ?>
        }
        @media (min-width: 769px) and (max-width: 1024px) {
            <?php if (!empty($tabletLogoSize)): ?>
            #siteHeader img {
                height: <?= $tabletLogoSize ?>px !important;
            }
            <?php endif; ?>
        }
    </style>
    <?php if (!empty($settings['header_scripts'])): ?>
    <!-- Header Scripts (admin-configurable: analytics, GTM, tracking) -->
    <?= $settings['header_scripts'] ?>
    <?php endif; ?>
    <?php if (!empty($settings['custom_css'])): ?>
    <style>/* Custom CSS (admin-configurable) */
    <?= $settings['custom_css'] ?>
    </style>
    <?php endif; ?>
    <?php
    // Inject admin-configurable CSS variable overrides from Settings (Couleurs & Thème)
    $colorOverrides = array_filter([
        '--primary'    => $settings['color_primary']    ?? '',
        '--accent'     => $settings['color_accent']     ?? '',
        '--text-main'  => $settings['color_text_main']  ?? '',
        '--text-muted' => $settings['color_text_muted'] ?? '',
        '--bg-base'    => $settings['color_bg_base']    ?? '',
    ]);
    if (!empty($colorOverrides)):
    ?>
    <style>:root {
        <?php foreach ($colorOverrides as $var => $val): ?>
        <?= htmlspecialchars($var) ?>: <?= htmlspecialchars($val) ?>;
        <?php endforeach; ?>
    }</style>
    <?php endif; ?>
    <?php
    /**
     * Couleur d'accent PAR PAGE.
     *
     * Champ vide : la page suit le thème global, rien n'est émis. Champ rempli :
     * la couleur ne s'applique qu'à cette page — en-tête et pied de page
     * compris, sinon un header resté sur l'ancienne teinte fausserait la
     * comparaison. Aucune couleur n'est figée dans les gabarits : tout reste
     * piloté par `--primary`, donc réversible en vidant le champ.
     *
     * Format validé : #RGB ou #RRGGBB uniquement. Une saisie non conforme est
     * ignorée et ne peut donc rien injecter dans la feuille de style.
     */
    $pageAccent = trim((string)($page['accent_color'] ?? ''));
    if ($pageAccent !== '' && preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', $pageAccent)):
    ?>
    <style id="page-accent">:root {
        --primary: <?= htmlspecialchars($pageAccent, ENT_QUOTES) ?>;
        --primary-dark: <?= htmlspecialchars($pageAccent, ENT_QUOTES) ?>;
    }</style>
    <?php endif; ?>
</head>
<body>

    <header class="site-header" id="siteHeader">
        <div class="container nav-container">
            <a href="<?= url('/') ?>" class="logo-wrap">
                <picture style="display: flex; align-items: center;">
                    <?php if (!empty($settings['site_logo_mobile'])): ?>
                        <source srcset="<?= htmlspecialchars(url($settings['site_logo_mobile'])) ?>" media="(max-width: 768px)">
                    <?php endif; ?>
                    <?php if (!empty($selectedLogo)): ?>
                        <img src="<?= htmlspecialchars(url($selectedLogo)) ?>" alt="<?= htmlspecialchars($settings['site_name'] ?? 'Digitalium Group') ?>" style="height: 38px; width: auto; object-fit: contain;">
                    <?php endif; ?>
                </picture>

                <?php if (!empty($settings['site_logo_text'])): ?>
                    <div class="logo-text-col" style="margin-left: 6px;">
                        <strong style="color: var(--primary); font-size: 1.05rem; font-weight: 700; font-family: var(--font-heading);"><?= htmlspecialchars($settings['site_logo_text']) ?></strong>
                        <span style="font-size: .55rem; color: #8aa0be; text-transform: uppercase; letter-spacing: .25em; font-weight: 500;"><?= htmlspecialchars($settings['site_logo_subtext'] ?? 'Group') ?></span>
                    </div>
                <?php elseif (empty($settings['site_logo'])): ?>
                    <div class="logo-d-mark"></div>
                    <div class="logo-ring-mark"></div>
                    <div class="logo-text-col">
                        <strong>Digitalium</strong>
                        <span>Group</span>
                    </div>
                <?php endif; ?>
            </a>

            <nav>
                <ul class="nav-menu" id="navMenu">
                    <?php
                    // Load primary menu from DB; fallback to pages with in_navigation=1
                    $primaryMenu = \App\Models\Menu::findByLocation('primary');
                    $navItems = [];
                    if ($primaryMenu) {
                        $navItems = \App\Models\MenuItem::getActiveByMenu((int)$primaryMenu['id']);
                    }
                    if (empty($navItems) && !empty($menuPages)) {
                        // Fallback: build nav from published pages
                        foreach (array_filter($menuPages, fn($p) => (int)($p['in_navigation'] ?? 1) === 1) as $p) {
                            $navItems[] = [
                                'label'      => $p['title'],
                                'url'        => $p['slug'] === 'home' ? '/' : '/' . $p['slug'],
                                'page_slug'  => $p['slug'],
                                'target'     => '_self',
                                'parent_id'  => null,
                                'icon'       => '',
                                'is_active'  => 1,
                            ];
                        }
                    }
                    // Separate root items and children
                    $rootItems     = array_filter($navItems, fn($i) => empty($i['parent_id']));
                    $childrenItems = array_filter($navItems, fn($i) => !empty($i['parent_id']));
                    $childrenByParent = [];
                    foreach ($childrenItems as $child) {
                        $childrenByParent[$child['parent_id']][] = $child;
                    }

                    foreach ($rootItems as $navItem):
                        $itemUrl   = url(\App\Models\MenuItem::resolveUrl($navItem));
                        $itemSlug  = ltrim($navItem['page_slug'] ?? parse_url($navItem['url'] ?? '', PHP_URL_PATH) ?? '', '/');
                        $isActive  = ($currentSlug === $itemSlug || '/'.$currentSlug === ($navItem['url'] ?? '')) ? 'active' : '';
                        $hasChildren = !empty($childrenByParent[$navItem['id'] ?? '']);
                    ?>
                        <li class="<?= $hasChildren ? 'has-dropdown' : '' ?>">
                            <a href="<?= htmlspecialchars($itemUrl) ?>"
                               class="nav-link <?= $isActive ?>"
                               <?= ($navItem['target'] ?? '_self') === '_blank' ? 'target="_blank" rel="noopener"' : '' ?>>
                                <?php if (!empty($navItem['icon'])): ?>
                                    <i data-lucide="<?= htmlspecialchars($navItem['icon']) ?>" style="width:15px;height:15px;vertical-align:middle;margin-right:4px;"></i>
                                <?php endif; ?>
                                <?= htmlspecialchars($navItem['label']) ?>
                                <?php if ($hasChildren): ?>
                                    <i data-lucide="chevron-down" style="width:13px;height:13px;vertical-align:middle;margin-left:2px;opacity:.7;"></i>
                                <?php endif; ?>
                            </a>
                            <?php if ($hasChildren): ?>
                            <ul class="nav-dropdown">
                                <?php foreach ($childrenByParent[$navItem['id']] as $child):
                                    $childUrl = url(\App\Models\MenuItem::resolveUrl($child));
                                ?>
                                <li>
                                    <a href="<?= htmlspecialchars($childUrl) ?>"
                                       class="nav-link"
                                       <?= ($child['target'] ?? '_self') === '_blank' ? 'target="_blank" rel="noopener"' : '' ?>>
                                        <?= htmlspecialchars($child['label']) ?>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                    <li>
                        <a href="<?= htmlspecialchars(url($settings['header_cta_link'] ?? '/contact')) ?>" class="btn-cta-nav">
                            <?= htmlspecialchars($settings['header_cta_text'] ?? 'Discuter de mon projet') ?>
                        </a>
                    </li>
                </ul>
            </nav>

            <button class="menu-toggle" id="menuToggle" aria-label="Toggle Menu">
                <i data-lucide="menu" style="width: 28px; height: 28px;"></i>
            </button>
        </div>
    </header>

    <main style="min-height: 80vh;">
        <?= $content ?>
    </main>

    <footer class="site-footer">

        <?php if (!empty($settings['footer_newsletter_title']) || !empty($settings['footer_newsletter_text'])): ?>
        <div class="container">
            <div class="footer-promo">
                <?php if (!empty($settings['footer_newsletter_image'])): ?>
                    <div class="footer-promo-visual">
                        <img src="<?= htmlspecialchars(url($settings['footer_newsletter_image'])) ?>"
                             alt="<?= htmlspecialchars($settings['footer_newsletter_title'] ?? '') ?>" loading="lazy">
                    </div>
                <?php endif; ?>

                <div class="footer-promo-body">
                    <?php if (!empty($settings['footer_newsletter_title'])): ?>
                        <h2 class="footer-promo-title"><?= htmlspecialchars($settings['footer_newsletter_title']) ?></h2>
                    <?php endif; ?>
                    <?php if (!empty($settings['footer_newsletter_text'])): ?>
                        <p class="footer-promo-text"><?= htmlspecialchars($settings['footer_newsletter_text']) ?></p>
                    <?php endif; ?>

                    <form class="footer-promo-form" method="POST" action="<?= url('/contact') ?>">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                        <input type="hidden" name="subject" value="<?= htmlspecialchars($settings['footer_newsletter_title'] ?? '') ?>">
                        <input type="hidden" name="message" value="<?= htmlspecialchars($settings['footer_newsletter_text'] ?? '') ?>">
                        <input type="hidden" name="name" value="Newsletter">
                        <input type="text" name="website" value="" style="display:none" tabindex="-1" autocomplete="off">
                        <span class="footer-promo-icon"><i data-lucide="mail" style="width:19px;height:19px;"></i></span>
                        <input type="email" name="email" class="footer-promo-input" required
                               placeholder="<?= htmlspecialchars($settings['footer_newsletter_placeholder'] ?? '') ?>">
                        <?php if (!empty($settings['footer_newsletter_button'])): ?>
                            <button type="submit" class="footer-promo-btn"><?= htmlspecialchars($settings['footer_newsletter_button']) ?></button>
                        <?php endif; ?>
                    </form>

                    <?php if (!empty($settings['footer_newsletter_note']) || !empty($settings['footer_newsletter_privacy_text'])): ?>
                        <p class="footer-promo-note">
                            <?php if (!empty($settings['footer_newsletter_note'])): ?>
                                <?= htmlspecialchars($settings['footer_newsletter_note']) ?>
                            <?php endif; ?>
                            <?php if (!empty($settings['footer_newsletter_privacy_text'])): ?>
                                <?php
                                /* Meme raison qu'en bas de page : sans adresse
                                   configuree, la mention reste lisible mais ne
                                   mene pas a un 404. */
                                $_urlConfid = trim((string)($settings['footer_newsletter_privacy_url'] ?? ''));
                                ?>
                                <?php if ($_urlConfid !== ''): ?>
                                    <a href="<?= htmlspecialchars(url($_urlConfid)) ?>">
                                        <?= htmlspecialchars($settings['footer_newsletter_privacy_text']) ?>
                                    </a>
                                <?php else: ?>
                                    <span><?= htmlspecialchars($settings['footer_newsletter_privacy_text']) ?></span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="container footer-layout">

            <div class="footer-brand">
                <a href="<?= url('/') ?>" class="logo-wrap">
                    <?php if (!empty($settings['site_logo'])): ?>
                        <img src="<?= htmlspecialchars(url($settings['site_logo'])) ?>" alt="<?= htmlspecialchars($settings['site_name'] ?? '') ?>" style="height: 40px; width: auto; object-fit: contain;">
                    <?php endif; ?>
                    <?php if (!empty($settings['site_logo_text'])): ?>
                        <div class="logo-text-col" style="margin-left: 6px;">
                            <strong style="font-size:1.2rem; color: var(--text-main); font-family: var(--font-heading);"><?= htmlspecialchars($settings['site_logo_text']) ?></strong>
                            <?php if (!empty($settings['site_logo_subtext'])): ?>
                                <span style="font-size:0.6rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: .2em; font-weight: 600;"><?= htmlspecialchars($settings['site_logo_subtext']) ?></span>
                            <?php endif; ?>
                        </div>
                    <?php elseif (empty($settings['site_logo']) && !empty($settings['site_name'])): ?>
                        <div class="logo-text-col">
                            <strong style="font-size:1.2rem;"><?= htmlspecialchars($settings['site_name']) ?></strong>
                        </div>
                    <?php endif; ?>
                </a>

                <?php if (!empty($settings['footer_slogan'])): ?>
                    <p class="footer-description"><?= htmlspecialchars($settings['footer_slogan']) ?></p>
                <?php endif; ?>

                <div class="footer-socials">
                    <?php
                    // Réseaux sociaux : uniquement ceux renseignés dans les réglages (Règle #2).
                    $socialLinks = [
                        'social_facebook'  => ['facebook',  'Facebook'],
                        'social_twitter'   => ['twitter',   'Twitter / X'],
                        'social_instagram' => ['instagram', 'Instagram'],
                        'social_linkedin'  => ['linkedin',  'LinkedIn'],
                        'social_youtube'   => ['youtube',   'YouTube'],
                        'social_github'    => ['github',    'GitHub'],
                    ];
                    foreach ($socialLinks as $key => [$icon, $label]):
                        if (empty($settings[$key])) { continue; }
                    ?>
                        <a href="<?= htmlspecialchars($settings[$key]) ?>" target="_blank" rel="noopener" class="footer-social-link" title="<?= htmlspecialchars($label) ?>">
                            <i data-lucide="<?= $icon ?>" style="width: 21px; height: 21px;"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div>
                <?php if (!empty($settings['footer_nav_title'])): ?>
                    <h4 class="footer-col-title"><?= htmlspecialchars($settings['footer_nav_title']) ?></h4>
                <?php endif; ?>
                <ul class="footer-links">
                    <?php
                    /* Colonne pilotée par le menu d'emplacement « footer », géré
                       dans /admin/menus. Tant que ce menu est vide — migration
                       non passée, menu supprimé par erreur — on retombe sur les
                       pages en navigation : un pied de page vide serait pire
                       qu'un pied de page automatique. */
                    $footItems = \App\Models\MenuItem::getActiveByLocation('footer');
                    $footItems = array_filter($footItems, fn($i) => empty($i['parent_id']));

                    if (empty($footItems)) {
                        foreach (array_filter($menuPages, fn($p) => (int)($p['in_navigation'] ?? 1) === 1) as $p) {
                            $footItems[] = [
                                'label'     => $p['title'],
                                'url'       => '',
                                'page_slug' => $p['slug'],
                                'target'    => '_self',
                                'parent_id' => null,
                            ];
                        }
                    }

                    foreach ($footItems as $footItem):
                        $footUrl = url(\App\Models\MenuItem::resolveUrl($footItem));
                    ?>
                        <li>
                            <a href="<?= htmlspecialchars($footUrl) ?>" class="footer-link"
                               <?= ($footItem['target'] ?? '_self') === '_blank' ? 'target="_blank" rel="noopener"' : '' ?>>
                                <?= htmlspecialchars($footItem['label']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div>
                <?php if (!empty($settings['footer_services_title'])): ?>
                    <h4 class="footer-col-title"><?= htmlspecialchars($settings['footer_services_title']) ?></h4>
                <?php endif; ?>
                <ul class="footer-links">
                    <?php
                    /* Colonne pilotée par le menu d'emplacement « footer_services ».
                       Repli : la section Services de la page d'accueil, comme
                       auparavant — aucune liste écrite en dur (Règle #2). */
                    $svcItems = \App\Models\MenuItem::getActiveByLocation('footer_services');
                    $svcItems = array_filter($svcItems, fn($i) => empty($i['parent_id']));

                    if (empty($svcItems)) {
                        $servicesSec = \App\Services\Database::fetch(
                            "SELECT id FROM sections WHERE type IN ('services_grid_v2', 'services_grid') AND status = 'active' ORDER BY FIELD(type, 'services_grid_v2', 'services_grid') LIMIT 1"
                        );
                        $servicesList = [];
                        if ($servicesSec) {
                            $servicesBlocks = \App\Models\Block::getStructuredContent($servicesSec['id']);
                            $servicesList = $servicesBlocks['groups'] ?? [];
                        }
                        foreach (array_slice($servicesList, 0, 6) as $svc) {
                            if (trim((string)($svc['svc_title'] ?? '')) === '') { continue; }
                            $svcItems[] = [
                                'label'     => $svc['svc_title'],
                                'url'       => $svc['svc_link'] ?? '',
                                'page_slug' => '',
                                'target'    => '_self',
                                'parent_id' => null,
                            ];
                        }
                    }

                    foreach (array_slice($svcItems, 0, 8) as $svcItem):
                        $svcLabel = trim((string)($svcItem['label'] ?? ''));
                        if ($svcLabel === '') { continue; }
                        $svcHref  = \App\Models\MenuItem::resolveUrl($svcItem);
                    ?>
                        <li>
                            <?php if ($svcHref !== '#'): ?>
                                <a href="<?= htmlspecialchars(url($svcHref)) ?>" class="footer-link"
                                   <?= ($svcItem['target'] ?? '_self') === '_blank' ? 'target="_blank" rel="noopener"' : '' ?>>
                                    <?= htmlspecialchars($svcLabel) ?>
                                </a>
                            <?php else: ?>
                                <span class="footer-link"><?= htmlspecialchars($svcLabel) ?></span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div>
                <?php if (!empty($settings['footer_contact_title'])): ?>
                    <h4 class="footer-col-title"><?= htmlspecialchars($settings['footer_contact_title']) ?></h4>
                <?php endif; ?>

                <?php if (!empty($settings['contact_phone'])): ?>
                    <div class="footer-contact-item">
                        <span class="footer-contact-icon"><i data-lucide="phone" style="width:17px;height:17px;"></i></span>
                        <a href="tel:<?= htmlspecialchars(preg_replace('/[^0-9+]/', '', $settings['contact_phone'])) ?>"><?= htmlspecialchars($settings['contact_phone']) ?></a>
                    </div>
                <?php endif; ?>

                <?php if (!empty($settings['contact_email'])): ?>
                    <div class="footer-contact-item">
                        <span class="footer-contact-icon"><i data-lucide="mail" style="width:17px;height:17px;"></i></span>
                        <a href="mailto:<?= htmlspecialchars($settings['contact_email']) ?>"><?= htmlspecialchars($settings['contact_email']) ?></a>
                    </div>
                <?php endif; ?>

                <?php if (!empty($settings['site_whatsapp'])): ?>
                    <div class="footer-contact-item">
                        <span class="footer-contact-icon"><i data-lucide="message-square" style="width:17px;height:17px;"></i></span>
                        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $settings['site_whatsapp']) ?>" target="_blank" rel="noopener"><?= htmlspecialchars($settings['site_whatsapp']) ?></a>
                    </div>
                <?php endif; ?>

                <?php if (!empty($settings['contact_address'])): ?>
                    <div class="footer-contact-item">
                        <span class="footer-contact-icon"><i data-lucide="map-pin" style="width:17px;height:17px;"></i></span>
                        <span><?= htmlspecialchars($settings['contact_address']) ?></span>
                    </div>
                <?php endif; ?>
            </div>

        </div>

        <div class="container footer-bottom">
            <?php if (!empty($settings['footer_copyright'])): ?>
                <span><?= htmlspecialchars($settings['footer_copyright']) ?></span>
            <?php endif; ?>
            <div class="footer-bottom-links">
                <?php
                /* Sans adresse configuree, l'intitule s'affiche SANS lien.
                   Ces deux liens retombaient sur « /mentions-legales », une page
                   qui n'existe pas : le pied de page envoyait donc tous les
                   visiteurs sur un 404. Mieux vaut une mention non cliquable
                   qu'un lien mort. */
                $_lienPied = static function (?string $texte, ?string $url): void {
                    $texte = trim((string)$texte);
                    if ($texte === '') { return; }
                    $url = trim((string)$url);
                    if ($url === '') {
                        echo '<span>' . htmlspecialchars($texte) . '</span>';
                        return;
                    }
                    echo '<a href="' . htmlspecialchars(url($url)) . '">' . htmlspecialchars($texte) . '</a>';
                };
                $_lienPied($settings['footer_legal_text']   ?? '', $settings['footer_legal_url']   ?? '');
                $_lienPied($settings['footer_privacy_text'] ?? '', $settings['footer_privacy_url'] ?? '');
                ?>
                <?php if (!empty($settings['footer_sitemap_text'])): ?>
                    <a href="<?= htmlspecialchars(url($settings['footer_sitemap_url'] ?? '/sitemap.xml')) ?>"><?= htmlspecialchars($settings['footer_sitemap_text']) ?></a>
                <?php endif; ?>
                <?php if (!empty($settings['footer_backtotop_text'])): ?>
                    <a href="#siteHeader"><?= htmlspecialchars($settings['footer_backtotop_text']) ?></a>
                <?php endif; ?>
            </div>
        </div>
    </footer>

    <script>
        lucide.createIcons();

        // Scroll Reveal Animation (Intersection Observer)
        const observer = new IntersectionObserver(entries => {
            entries.forEach(e => {
                if (e.isIntersecting) {
                    e.target.classList.add('visible');
                    e.target.classList.add('vis');
                }
            });
        }, { threshold: 0.08 });
        
        document.querySelectorAll('.reveal, .rev, .svc-card, .sc, .pc, .feat-item').forEach(el => observer.observe(el));

        /* ── Menu mobile ────────────────────────────────────────────────────
           Le tiroir est la MEME liste que le menu de bureau : un second
           balisage se serait desynchronise du menu administrable.
           Les sous-menus s'ouvrent au toucher, car `:hover` ne se declenche
           pas de facon fiable sur un ecran tactile. */
        const menuToggle = document.getElementById('menuToggle');
        const navMenu    = document.getElementById('navMenu');

        if (menuToggle && navMenu) {
            const surMobile = () => window.matchMedia('(max-width: 991px)').matches;

            const poserIcone = (nom) => {
                const icone = menuToggle.querySelector('i');
                if (icone) { icone.setAttribute('data-lucide', nom); }
                if (typeof lucide !== 'undefined') { lucide.createIcons(); }
            };

            const replier = () => {
                navMenu.querySelectorAll('.has-dropdown.expanded')
                       .forEach(li => li.classList.remove('expanded'));
            };

            const fermer = () => {
                navMenu.classList.remove('open');
                document.body.classList.remove('nav-open');
                menuToggle.setAttribute('aria-expanded', 'false');
                replier();
                poserIcone('menu');
            };

            const ouvrir = () => {
                navMenu.classList.add('open');
                document.body.classList.add('nav-open');
                menuToggle.setAttribute('aria-expanded', 'true');
                poserIcone('x');
            };

            menuToggle.setAttribute('aria-controls', 'navMenu');
            menuToggle.setAttribute('aria-expanded', 'false');

            menuToggle.addEventListener('click', () => {
                navMenu.classList.contains('open') ? fermer() : ouvrir();
            });

            navMenu.querySelectorAll('a').forEach(lien => {
                const parentDeroulant = lien.parentElement
                    && lien.parentElement.classList.contains('has-dropdown')
                    && lien.classList.contains('nav-link');

                lien.addEventListener('click', (e) => {
                    if (!navMenu.classList.contains('open')) { return; }

                    // Un parent de sous-menu se deploie au premier appui ;
                    // le second appui suit son lien.
                    if (parentDeroulant && surMobile()) {
                        const li = lien.parentElement;
                        if (!li.classList.contains('expanded')) {
                            e.preventDefault();
                            replier();
                            li.classList.add('expanded');
                            return;
                        }
                    }
                    fermer();
                });
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && navMenu.classList.contains('open')) { fermer(); }
            });

            // Repasser en bureau doit rendre la barre normale, jamais un
            // tiroir fige par-dessus la page.
            window.addEventListener('resize', () => {
                if (!surMobile() && navMenu.classList.contains('open')) { fermer(); }
            });

            // Le tiroir a la hauteur de son contenu : la page reste visible
            // en dessous. Un appui a cote doit donc le fermer. Le bouton
            // hamburger est dans l'en-tete, il n'est jamais concerne ici.
            document.addEventListener('click', (e) => {
                if (!navMenu.classList.contains('open')) { return; }
                if (e.target.closest && e.target.closest('#siteHeader')) { return; }
                fermer();
            });
        }

        window.addEventListener('scroll', () => {
            const header = document.getElementById('siteHeader');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    </script>
    <?php if (!empty($settings['footer_scripts'])): ?>
    <!-- Footer Scripts (admin-configurable) -->
    <?= $settings['footer_scripts'] ?>
    <?php endif; ?>
</body>
</html>
