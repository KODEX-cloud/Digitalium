<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page['meta_title'] ?? $page['title']) ?> | <?= htmlspecialchars($settings['site_name'] ?? 'Digitalium Group') ?></title>
    <meta name="description" content="<?= htmlspecialchars($page['meta_description'] ?? 'Solutions logicielles de pointe et transformation digitale sur-mesure pour votre entreprise.') ?>">
    <link rel="canonical" href="https://digitaliumgroup.com<?= $currentSlug === 'home' ? '' : '/' . htmlspecialchars($currentSlug) ?>">

    <meta property="og:type" content="website">
    <meta property="og:url" content="https://digitaliumgroup.com<?= $currentSlug === 'home' ? '' : '/' . htmlspecialchars($currentSlug) ?>">
    <meta property="og:title" content="<?= htmlspecialchars($page['meta_title'] ?? $page['title']) ?> | <?= htmlspecialchars($settings['site_name'] ?? 'Digitalium Group') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($page['meta_description'] ?? 'Solutions logicielles de pointe et transformation digitale sur-mesure pour votre entreprise.') ?>">
    <meta property="og:image" content="https://digitaliumgroup.com/assets/images/og-image.jpg">

    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:title" content="<?= htmlspecialchars($page['meta_title'] ?? $page['title']) ?> | <?= htmlspecialchars($settings['site_name'] ?? 'Digitalium Group') ?>">
    <meta property="twitter:description" content="<?= htmlspecialchars($page['meta_description'] ?? 'Solutions logicielles de pointe et transformation digitale sur-mesure pour votre entreprise.') ?>">

    <link rel="stylesheet" href="<?= url('/assets/css/index.css') ?>?v=<?= filemtime(ROOT_PATH . '/public/assets/css/index.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php
    // ─── CMS Theme Builder — Inject design tokens from settings ──────────────
    $_thP    = htmlspecialchars($settings['theme_primary']                ?? '#2563eb', ENT_QUOTES);
    $_thS    = htmlspecialchars($settings['theme_secondary']              ?? '#0891b2', ENT_QUOTES);
    $_thAc   = htmlspecialchars($settings['theme_accent']                 ?? '#f59e0b', ENT_QUOTES);
    $_thTM   = htmlspecialchars($settings['theme_text_main']              ?? '#0f172a', ENT_QUOTES);
    $_thTS   = htmlspecialchars($settings['theme_text_sub']               ?? '#334155', ENT_QUOTES);
    $_thTMu  = htmlspecialchars($settings['theme_text_muted']             ?? '#64748b', ENT_QUOTES);
    $_thBB   = htmlspecialchars($settings['theme_bg_base']                ?? '#ffffff', ENT_QUOTES);
    $_thBA   = htmlspecialchars($settings['theme_bg_alt']                 ?? '#f8fafc', ENT_QUOTES);
    $_thBC   = htmlspecialchars($settings['theme_bg_card']                ?? '#ffffff', ENT_QUOTES);
    $_thShC  = htmlspecialchars($settings['theme_shadow_card']            ?? '0 1px 3px rgba(0,0,0,0.04), 0 8px 32px rgba(0,0,0,0.06)', ENT_QUOTES);
    $_thShB  = htmlspecialchars($settings['theme_shadow_btn']             ?? '0 4px 18px rgba(37,99,235,0.28)', ENT_QUOTES);
    $_thRP   = (int)($settings['theme_radius_pill']            ?? 100);
    $_thRC   = (int)($settings['theme_radius_card']            ?? 20);
    $_thRBtn = (int)($settings['theme_radius_btn']             ?? 100);
    $_thRMd  = (int)($settings['theme_radius_md']              ?? 12);
    $_thRSm  = (int)($settings['theme_radius_sm']              ?? 8);
    $_thSS   = (int)($settings['theme_space_section']          ?? 130);
    $_thSSSm = max(60, $_thSS - 50);
    $_thSSXs = max(40, $_thSS - 76);
    $_thFH1  = (float)($settings['theme_font_h1']              ?? 4.2);
    $_thFH2  = (float)($settings['theme_font_h2']              ?? 2.8);
    $_thFH3  = (float)($settings['theme_font_h3']              ?? 1.08);
    $_thFB   = (float)($settings['theme_font_body']            ?? 1);
    $_thWH   = (int)($settings['theme_font_weight_heading']    ?? 800);
    $_thWB   = (int)($settings['theme_font_weight_body']       ?? 400);
    $_thLH   = (float)($settings['theme_line_height_body']     ?? 1.78);
    $_thLS   = (float)($settings['theme_letter_spacing_heading']?? -0.032);
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
        #siteHeader .nav-link.active { color: #2563eb !important; background: rgba(37,99,235,0.08) !important; }
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
        <div class="container footer-layout">
            
            <div class="footer-brand">
                <a href="<?= url('/') ?>" class="logo-wrap" style="margin-bottom:10px;">
                    <?php if (!empty($settings['site_logo'])): ?>
                        <img src="<?= htmlspecialchars(url($settings['site_logo'])) ?>" alt="<?= htmlspecialchars($settings['site_name'] ?? 'Digitalium Group') ?>" style="height: 38px; width: auto; object-fit: contain;">
                    <?php endif; ?>
                    <?php if (!empty($settings['site_logo_text'])): ?>
                        <div class="logo-text-col" style="margin-left: 6px;">
                            <strong style="font-size:1.15rem; color: var(--primary); font-family: var(--font-heading);"><?= htmlspecialchars($settings['site_logo_text']) ?></strong>
                            <span style="font-size:0.6rem; color: #8aa0be; text-transform: uppercase; letter-spacing: .2em; font-weight: 500;"><?= htmlspecialchars($settings['site_logo_subtext'] ?? 'Group') ?></span>
                        </div>
                    <?php elseif (empty($settings['site_logo'])): ?>
                        <div class="logo-d-mark"></div>
                        <div class="logo-ring-mark"></div>
                        <div class="logo-text-col">
                            <strong style="font-size:1.15rem;">Digitalium</strong>
                            <span style="font-size:0.6rem;">Group</span>
                        </div>
                    <?php endif; ?>
                </a>
                <p class="footer-description">
                    <?= htmlspecialchars($settings['footer_slogan'] ?? $settings['footer_pitch'] ?? 'Nous concevons des produits technologiques haut de gamme et des solutions digitales.') ?>
                </p>
                <?php if (!empty($settings['footer_cta_text'])): ?>
                    <div style="margin-top: 15px; margin-bottom: 20px;">
                        <a href="<?= htmlspecialchars(url($settings['footer_cta_link'] ?? '/contact')) ?>" class="btn-primary" style="padding: 8px 18px; font-size: 0.82rem; height: auto; text-decoration: none;">
                            <span><?= htmlspecialchars($settings['footer_cta_text']) ?></span>
                            <i data-lucide="arrow-right" style="width: 14px; height: 14px; display: inline-block; vertical-align: middle;"></i>
                        </a>
                    </div>
                <?php endif; ?>
                <div class="footer-socials">
                    <?php if (!empty($settings['social_facebook'])): ?>
                        <a href="<?= htmlspecialchars($settings['social_facebook']) ?>" target="_blank" class="footer-social-link" title="Facebook">
                            <i data-lucide="facebook" style="width: 18px; height: 18px;"></i>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($settings['social_linkedin'])): ?>
                        <a href="<?= htmlspecialchars($settings['social_linkedin']) ?>" target="_blank" class="footer-social-link" title="LinkedIn">
                            <i data-lucide="linkedin" style="width: 18px; height: 18px;"></i>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($settings['social_twitter'])): ?>
                        <a href="<?= htmlspecialchars($settings['social_twitter']) ?>" target="_blank" class="footer-social-link" title="Twitter / X">
                            <i data-lucide="twitter" style="width: 18px; height: 18px;"></i>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($settings['social_instagram'])): ?>
                        <a href="<?= htmlspecialchars($settings['social_instagram']) ?>" target="_blank" class="footer-social-link" title="Instagram">
                            <i data-lucide="instagram" style="width: 18px; height: 18px;"></i>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($settings['social_youtube'])): ?>
                        <a href="<?= htmlspecialchars($settings['social_youtube']) ?>" target="_blank" class="footer-social-link" title="YouTube">
                            <i data-lucide="youtube" style="width: 18px; height: 18px;"></i>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($settings['social_github'])): ?>
                        <a href="<?= htmlspecialchars($settings['social_github']) ?>" target="_blank" class="footer-social-link" title="GitHub">
                            <i data-lucide="github" style="width: 18px; height: 18px;"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div>
                <h4 class="footer-col-title">Services</h4>
                <ul class="footer-links">
                    <?php 
                    $servicesSec = \App\Services\Database::fetch("SELECT id FROM sections WHERE type = 'services_grid' AND status = 'active' LIMIT 1");
                    $servicesList = [];
                    if ($servicesSec) {
                        $servicesBlocks = \App\Models\Block::getStructuredContent($servicesSec['id']);
                        $servicesList = $servicesBlocks['groups'] ?? [];
                    }
                    if (empty($servicesList)) {
                        $servicesList = [
                            ['svc_title' => 'Ingénierie Logicielle', 'svc_link' => '/service'],
                            ['svc_title' => 'Applications Cloud', 'svc_link' => '/service'],
                            ['svc_title' => 'Audit & Conseil', 'svc_link' => '/service'],
                            ['svc_title' => 'SEO & Stratégie', 'svc_link' => '/service']
                        ];
                    }
                    $footerServices = array_slice($servicesList, 0, 4);
                    foreach ($footerServices as $svc):
                        $svcTitle = $svc['svc_title'] ?? $svc['title'] ?? '';
                        $svcLink = $svc['svc_link'] ?? $svc['link'] ?? '/service';
                    ?>
                        <li><a href="<?= htmlspecialchars(url($svcLink)) ?>" class="footer-link"><?= htmlspecialchars($svcTitle) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div>
                <h4 class="footer-col-title">Navigation</h4>
                <ul class="footer-links">
                    <?php 
                    $footPages = array_filter($menuPages, function($p) {
                        return (int)($p['in_navigation'] ?? 1) === 1;
                    });
                    foreach ($footPages as $menuPage): 
                        $menuSlug = $menuPage['slug'];
                        $menuUrl = url($menuSlug === 'home' ? '/' : '/' . htmlspecialchars($menuSlug));
                    ?>
                        <li>
                            <a href="<?= $menuUrl ?>" class="footer-link">
                                <?= htmlspecialchars($menuPage['title']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div>
                <h4 class="footer-col-title">Coordonnées</h4>
                <ul class="footer-links" style="color: var(--text-muted); font-size: 0.95rem; display: flex; flex-direction: column; gap: 10px;">
                    <li style="display: flex; gap: 8px; align-items: flex-start;">
                        <i data-lucide="map-pin" style="width: 16px; height: 16px; color: var(--primary); flex-shrink: 0; margin-top: 3px;"></i>
                        <span><?= htmlspecialchars($settings['contact_address'] ?? 'Paris, France') ?></span>
                    </li>
                    <li style="display: flex; gap: 8px; align-items: center;">
                        <i data-lucide="phone" style="width: 16px; height: 16px; color: var(--primary); flex-shrink: 0;"></i>
                        <span style="font-family: monospace;">
                            <a href="tel:<?= htmlspecialchars($settings['contact_phone'] ?? '0101782919') ?>">
                                <?= htmlspecialchars($settings['contact_phone'] ?? '0101782919') ?>
                            </a>
                        </span>
                    </li>
                    <li style="display: flex; gap: 8px; align-items: center;">
                        <i data-lucide="mail" style="width: 16px; height: 16px; color: var(--primary); flex-shrink: 0;"></i>
                        <span>
                            <a href="mailto:<?= htmlspecialchars($settings['contact_email'] ?? 'contact@digitaliumgroup.com') ?>">
                                <?= htmlspecialchars($settings['contact_email'] ?? 'contact@digitaliumgroup.com') ?>
                            </a>
                        </span>
                    </li>
                    <?php if (!empty($settings['site_whatsapp'])): ?>
                        <li style="display: flex; gap: 8px; align-items: center;">
                            <i data-lucide="message-square" style="width: 16px; height: 16px; color: var(--success); flex-shrink: 0;"></i>
                            <span style="font-family: monospace; font-weight: 700;">
                                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $settings['site_whatsapp']) ?>" target="_blank" style="color: #16a34a;">
                                    WhatsApp : <?= htmlspecialchars($settings['site_whatsapp']) ?>
                                </a>
                            </span>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <div class="container footer-bottom">
            <span><?= htmlspecialchars($settings['footer_copyright'] ?? '© ' . date('Y') . ' Digitalium Group. Tous droits réservés.') ?></span>
            <div style="display: flex; gap: 20px; align-items: center;">
                <?php if (!empty($settings['footer_legal_text'])): ?>
                    <a href="<?= htmlspecialchars(url($settings['footer_legal_url'] ?? '/mentions-legales')) ?>" style="color: var(--text-muted); font-size: 0.88rem; font-weight: 500;">
                        <?= htmlspecialchars($settings['footer_legal_text']) ?>
                    </a>
                <?php endif; ?>
                <a href="#siteHeader" style="display: flex; align-items: center; gap: 6px; color: var(--text-muted); font-weight: 500;" title="Remonter">
                    <span>Remonter</span>
                    <i data-lucide="arrow-up-circle" style="width: 18px; height: 18px; color: var(--primary);"></i>
                </a>
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

        const menuToggle = document.getElementById('menuToggle');
        const navMenu = document.getElementById('navMenu');
        
        if (menuToggle && navMenu) {
            menuToggle.addEventListener('click', () => {
                navMenu.classList.toggle('open');
                
                const icon = menuToggle.querySelector('i');
                if (navMenu.classList.contains('open')) {
                    icon.setAttribute('data-lucide', 'x');
                } else {
                    icon.setAttribute('data-lucide', 'menu');
                }
                lucide.createIcons();
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
