<?php
/**
 * Section : hero_media_cards — Hero visuel avec cartes flottantes
 *
 * Reproduction du modèle de référence fourni par la direction :
 * colonne texte à gauche (badge, titre bicolore, chapô, 2 CTA),
 * visuel à droite, cartes d'information flottantes superposées au visuel,
 * décors organiques (cercle menthe, courbe, vague, trame de points).
 *
 * Blocs attendus — TOUT est administrable (Règle #2), rien n'est codé en dur :
 *
 *   single :
 *     badge            texte de la pastille (majuscules)
 *     title            titre principal — accepte des retours à la ligne
 *     title_accent     fin du titre, en graisse légère et en couleur d'accent
 *     text             chapô
 *     cta1_text, cta1_url, cta1_icon    bouton principal
 *     cta2_text, cta2_url, cta2_icon    bouton secondaire
 *     image, image_alt                  visuel de droite
 *     decor            '0' pour masquer les décors
 *
 *     layout           'split'   (défaut — texte à gauche, visuel à droite)
 *                      'banner'  (texte en haut, visuel en bandeau large dessous)
 *                      'overlay' (texte centré PAR-DESSUS le visuel, voile teinté)
 *     overlay_opacity  intensité du voile en mode overlay, 0 à 100 — défaut 62
 *     overlay_min_height  hauteur minimale du visuel en px — défaut 420
 *     image_radius     arrondi du visuel en px — défaut 0 (angles droits)
 *     image_max_width  largeur maximale du bandeau en px — défaut 1300
 *     image_ratio      proportions du bandeau, ex. « 1300 / 400 » — défaut 1300 / 400
 *     image_ratio_mobile  proportions sous 760px — défaut 16 / 9
 *
 *     image_max_width  largeur du visuel en px, ou « full » pour du bord à bord
 *
 *   groups en mode 'overlay' — DIAPOSITIVES supplémentaires du carrousel.
 *   La première diapositive vient toujours des blocs simples ci-dessus ; une
 *   seule diapositive au total = ni flèches ni pastilles.
 *     slide_image, slide_alt, slide_badge, slide_title, slide_accent, slide_text
 *
 *   groups en mode 'split' — cartes flottantes, répétables :
 *     card_icon        nom d'icône Lucide
 *     card_label       sur-titre (majuscules)
 *     card_badge       petite pastille (ex. « Actif »)
 *     card_value       grande valeur
 *     card_unit        unité affichée après la valeur
 *     card_title       ligne en gras
 *     card_meta        ligne secondaire
 *     card_progress    0 à 100 — affiche une barre de progression
 *     card_avatar      image ronde à droite
 *     card_top         position verticale en % (défaut réparti)
 *     card_left        position horizontale en % (défaut réparti)
 */

/** Autorise les retours à la ligne saisis en admin sans ouvrir la porte au HTML. */
$heroLine = static function (string $raw): string {
    $safe = htmlspecialchars($raw, ENT_QUOTES, 'UTF-8');
    $safe = str_replace(['&lt;br&gt;', '&lt;br/&gt;', '&lt;br /&gt;'], '<br>', $safe);
    return nl2br($safe, false);
};

$showDecor = !isset($single['decor']) || $single['decor'] !== '0';
$cards     = $groups ?? [];


/* Mise en page — 'split' reste le comportement historique (accueil, /service). */
$layout    = in_array($single['layout'] ?? 'split', ['split', 'banner', 'overlay'], true)
    ? ($single['layout'] ?? 'split')
    : 'split';
$isOverlay = $layout === 'overlay';
$isBanner  = $layout === 'banner';
/* Les deux modes larges partagent le calcul de largeur et de proportions. */
$isWide    = $isBanner || $isOverlay;

/**
 * Proportions du bandeau, saisies en admin sous la forme « 1300 / 400 »
 * (« x » et « : » acceptés). Toute valeur non conforme retombe sur le défaut :
 * une saisie erronée ne peut pas casser la mise en page.
 */
$heroRatio = static function (?string $raw, string $fallback): string {
    $raw = trim((string)$raw);
    if ($raw === '' || !preg_match('#^(\d{1,5})\s*[/x:]\s*(\d{1,5})$#i', $raw, $m)) {
        return $fallback;
    }
    if ((int)$m[1] < 1 || (int)$m[2] < 1) { return $fallback; }
    return $m[1] . ' / ' . $m[2];
};

$bannerRatio   = $heroRatio($single['image_ratio']        ?? null, '1300 / 400');
$bannerRatioSm = $heroRatio($single['image_ratio_mobile'] ?? null, '16 / 9');
/* « full » : le visuel occupe toute la largeur de la fenêtre, bord à bord. */
$rawWidth    = trim((string)($single['image_max_width'] ?? ''));
$isFullBleed = strtolower($rawWidth) === 'full';
$bannerWidth = preg_match('#^\d{2,5}$#', $rawWidth) ? $rawWidth . 'px' : '1300px';

/**
 * Diapositives du hero (mode overlay).
 *
 * La première vient toujours des blocs simples ; les groupes `slide_*` en
 * ajoutent d'autres. Une seule diapositive : ni flèches ni pastilles, le
 * carrousel n'apparaît que lorsqu'il a une raison d'exister.
 */
$slides = [];
if ($isOverlay) {
    $slides[] = [
        'image' => trim((string)($single['image'] ?? '')),
        'alt'   => trim((string)($single['image_alt'] ?? ($single['title'] ?? ''))),
        'badge' => trim((string)($single['badge'] ?? '')),
        'title' => trim((string)($single['title'] ?? '')),
        'accent'=> trim((string)($single['title_accent'] ?? '')),
        'text'  => trim((string)($single['text'] ?? '')),
        'first' => true,
    ];
    foreach (($groups ?? []) as $g) {
        $hasSlide = trim((string)($g['slide_title'] ?? '')) !== ''
                 || trim((string)($g['slide_image'] ?? '')) !== '';
        if (!$hasSlide) { continue; }
        $slides[] = [
            'image' => trim((string)($g['slide_image'] ?? '')),
            'alt'   => trim((string)($g['slide_alt']   ?? ($g['slide_title'] ?? ''))),
            'badge' => trim((string)($g['slide_badge'] ?? '')),
            'title' => trim((string)($g['slide_title'] ?? '')),
            'accent'=> trim((string)($g['slide_accent'] ?? '')),
            'text'  => trim((string)($g['slide_text']  ?? '')),
            'first' => false,
        ];
    }
}
$hasCarousel = count($slides) > 1;

/* Voile du mode overlay : borné 0-100, défaut 62. Une saisie hors bornes ne
   peut donc pas rendre le texte illisible ni faire disparaître la photo. */
$overlayOpacity = is_numeric($single['overlay_opacity'] ?? null)
    ? max(0, min(100, (int)$single['overlay_opacity']))
    : 62;
$overlayMinH = preg_match('#^\d{2,4}$#', trim((string)($single['overlay_min_height'] ?? '')))
    ? trim((string)$single['overlay_min_height']) . 'px'
    : '420px';

/* En mode overlay le visuel devient le fond du hero : une carte flottante n'a
   plus de panneau sur lequel se poser, et passerait sous le texte centré. */
if ($isOverlay) { $cards = []; }
$showDecor = $showDecor && !$isOverlay;

/* Arrondi du visuel — 0 par défaut (angles droits), réactivable en admin. */
$mediaRadius = preg_match('#^\d{1,3}$#', trim((string)($single['image_radius'] ?? '')))
    ? trim((string)$single['image_radius']) . 'px'
    : '0px';

$bannerVars = $isWide
    ? ' style="--hero-media-radius:' . htmlspecialchars($mediaRadius, ENT_QUOTES, 'UTF-8')
      . ';--hero-banner-ratio:' . htmlspecialchars($bannerRatio, ENT_QUOTES, 'UTF-8')
      . ';--hero-banner-ratio-sm:' . htmlspecialchars($bannerRatioSm, ENT_QUOTES, 'UTF-8')
      . ';--hero-banner-w:' . htmlspecialchars($bannerWidth, ENT_QUOTES, 'UTF-8')
      . ($isOverlay
          ? ';--hero-overlay-a:' . ($overlayOpacity / 100)
            . ';--hero-overlay-minh:' . htmlspecialchars($overlayMinH, ENT_QUOTES, 'UTF-8')
          : '')
      . ';"'
    : ' style="--hero-media-radius:' . htmlspecialchars($mediaRadius, ENT_QUOTES, 'UTF-8') . ';"';
?>

<section class="hero-mc hero-mc-<?= $layout ?>" id="hero-media-cards"<?= $bannerVars ?>>

    <?php if ($showDecor): ?>
        <div class="hero-mc-decor" aria-hidden="true">
            <span class="hero-mc-circle"></span>
            <svg class="hero-mc-curve" viewBox="0 0 600 700" fill="none" preserveAspectRatio="none">
                <path d="M600 0C520 130 330 190 250 330C170 470 250 580 120 700" stroke="currentColor" stroke-width="2" opacity="0.45"/>
            </svg>
            <svg class="hero-mc-wave" viewBox="0 0 800 320" fill="none" preserveAspectRatio="none">
                <path d="M0 190C160 120 250 250 420 200C590 150 680 250 800 210V320H0V190Z" fill="currentColor" opacity="0.55"/>
                <path d="M0 250C150 200 260 300 430 260C600 220 690 300 800 275V320H0V250Z" fill="currentColor" opacity="0.4"/>
            </svg>
            <span class="hero-mc-dots"></span>
        </div>
    <?php endif; ?>

    <?php if ($isOverlay): ?>
        <?php /* Carrousel plein cadre : une diapositive = un visuel + son texte. */ ?>
        <div class="hero-ov<?= $isFullBleed ? ' hero-ov-full' : '' ?>">
            <?php foreach ($slides as $i => $s): ?>
                <div class="hero-ov-slide<?= $i === 0 ? ' active' : '' ?>" data-slide="<?= $i ?>">
                    <div class="hero-ov-media">
                        <?php if ($s['image'] !== ''): ?>
                            <img src="<?= htmlspecialchars(url($s['image'])) ?>"
                                 alt="<?= htmlspecialchars($s['alt']) ?>"
                                 <?= $i === 0 ? 'fetchpriority="high"' : 'loading="lazy"' ?> decoding="async">
                        <?php endif; ?>
                    </div>

                    <div class="hero-ov-inner">
                        <div class="hero-ov-text">
                            <?php if ($s['badge'] !== ''): ?>
                                <span class="hero-ov-badge"><?= htmlspecialchars($s['badge']) ?></span>
                            <?php endif; ?>

                            <?php if ($s['title'] !== '' || $s['accent'] !== ''):
                                /* Un seul <h1> par page : les diapositives suivantes
                                   sont des <h2>, visuellement identiques. */
                                $tag = $i === 0 ? 'h1' : 'h2';
                            ?>
                                <<?= $tag ?> class="hero-ov-title">
                                    <?php if ($s['title'] !== ''): ?><?= $heroLine($s['title']) ?><?php endif; ?>
                                    <?php if ($s['accent'] !== ''): ?>
                                        <span class="hero-ov-accent"><?= $heroLine($s['accent']) ?></span>
                                    <?php endif; ?>
                                </<?= $tag ?>>
                            <?php endif; ?>

                            <?php if ($s['text'] !== ''): ?>
                                <p class="hero-ov-lead"><?= $heroLine($s['text']) ?></p>
                            <?php endif; ?>

                            <?php if ($s['first'] && (!empty($single['cta1_text']) || !empty($single['cta2_text']))): ?>
                                <div class="hero-ov-actions">
                                    <?php if (!empty($single['cta1_text'])): ?>
                                        <a href="<?= htmlspecialchars(url($single['cta1_url'] ?? '/')) ?>" class="hero-ov-btn hero-ov-btn-primary">
                                            <span><?= htmlspecialchars($single['cta1_text']) ?></span>
                                            <?php if (!empty($single['cta1_icon'])): ?>
                                                <?= \App\Helpers\IconHelper::render($single['cta1_icon'], ['size' => '15px']) ?>
                                            <?php endif; ?>
                                        </a>
                                    <?php endif; ?>
                                    <?php if (!empty($single['cta2_text'])): ?>
                                        <a href="<?= htmlspecialchars(url($single['cta2_url'] ?? '/')) ?>" class="hero-ov-btn hero-ov-btn-ghost">
                                            <?php if (!empty($single['cta2_icon'])): ?>
                                                <?= \App\Helpers\IconHelper::render($single['cta2_icon'], ['size' => '15px']) ?>
                                            <?php endif; ?>
                                            <span><?= htmlspecialchars($single['cta2_text']) ?></span>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if ($hasCarousel): ?>
                <button type="button" class="hero-ov-arrow hero-ov-prev" aria-label="Diapositive précédente">
                    <i data-lucide="chevron-left" style="width:20px;height:20px;"></i>
                </button>
                <button type="button" class="hero-ov-arrow hero-ov-next" aria-label="Diapositive suivante">
                    <i data-lucide="chevron-right" style="width:20px;height:20px;"></i>
                </button>
                <div class="hero-ov-dots" role="tablist">
                    <?php foreach ($slides as $i => $s): ?>
                        <button type="button" class="hero-ov-dot<?= $i === 0 ? ' active' : '' ?>"
                                data-go="<?= $i ?>" aria-label="Diapositive <?= $i + 1 ?>"></button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php /* Le mode overlay a son propre balisage ci-dessus : rendre aussi la
             grille produirait un second <h1> caché dans le document. */ ?>
    <?php if (!$isOverlay): ?>
    <div class="container hero-mc-grid">

        <div class="hero-mc-text">
            <?php if (!empty($single['badge'])): ?>
                <span class="hero-mc-badge"><?= htmlspecialchars($single['badge']) ?></span>
            <?php endif; ?>

            <?php if (!empty($single['title']) || !empty($single['title_accent'])): ?>
                <h1 class="hero-mc-title">
                    <?php if (!empty($single['title'])): ?><?= $heroLine($single['title']) ?><?php endif; ?>
                    <?php if (!empty($single['title_accent'])): ?>
                        <span class="hero-mc-title-accent"><?= $heroLine($single['title_accent']) ?></span>
                    <?php endif; ?>
                </h1>
            <?php endif; ?>

            <?php if (!empty($single['text'])): ?>
                <p class="hero-mc-lead"><?= $heroLine($single['text']) ?></p>
            <?php endif; ?>

            <?php if (!empty($single['cta1_text']) || !empty($single['cta2_text'])): ?>
                <div class="hero-mc-actions">
                    <?php if (!empty($single['cta1_text'])): ?>
                        <a href="<?= htmlspecialchars(url($single['cta1_url'] ?? '/')) ?>" class="hero-mc-btn hero-mc-btn-primary">
                            <span><?= htmlspecialchars($single['cta1_text']) ?></span>
                            <?php if (!empty($single['cta1_icon'])): ?>
                                <span class="hero-mc-btn-icon"><?= \App\Helpers\IconHelper::render($single['cta1_icon'], ['size' => '15px']) ?></span>
                            <?php endif; ?>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($single['cta2_text'])): ?>
                        <a href="<?= htmlspecialchars(url($single['cta2_url'] ?? '/')) ?>" class="hero-mc-btn hero-mc-btn-ghost">
                            <?php if (!empty($single['cta2_icon'])): ?>
                                <span class="hero-mc-btn-icon"><?= \App\Helpers\IconHelper::render($single['cta2_icon'], ['size' => '15px']) ?></span>
                            <?php endif; ?>
                            <span><?= htmlspecialchars($single['cta2_text']) ?></span>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="hero-mc-visual">
            <?php
            /* En mode overlay le texte est blanc : le fond coloré doit exister
               MÊME sans image, sinon on obtient du blanc sur blanc tant qu'aucun
               visuel n'a été choisi en admin. Le cadre est donc toujours rendu ;
               seule la balise <img> est conditionnelle. */
            if ($isOverlay || !empty($single['image'])): ?>
                <div class="hero-mc-media">
                    <?php if (!empty($single['image'])): ?>
                        <img src="<?= htmlspecialchars(url($single['image'])) ?>"
                             alt="<?= htmlspecialchars($single['image_alt'] ?? ($single['title'] ?? '')) ?>"
                             class="hero-mc-img">
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php
            $defaultsTop  = [6, 36, 62, 88];
            $defaultsLeft = [48, 32, 20, 8];
            foreach ($cards as $i => $card):
                $top      = ($card['card_top']  ?? '') !== '' ? $card['card_top']  : $defaultsTop[$i % 4];
                $left     = ($card['card_left'] ?? '') !== '' ? $card['card_left'] : $defaultsLeft[$i % 4];
                $progress = isset($card['card_progress']) && $card['card_progress'] !== ''
                    ? max(0, min(100, (float)$card['card_progress'])) : null;
            ?>
                <div class="hero-mc-card" style="top:<?= htmlspecialchars((string)$top) ?>%;left:<?= htmlspecialchars((string)$left) ?>%;animation-delay:<?= $i * 0.35 ?>s;">
                    <div class="hero-mc-card-row">
                        <?php if (!empty($card['card_icon'])): ?>
                            <span class="hero-mc-card-icon"><?= \App\Helpers\IconHelper::render($card['card_icon'], ['size' => '16px']) ?></span>
                        <?php endif; ?>

                        <div class="hero-mc-card-main">
                            <?php if (!empty($card['card_label'])): ?>
                                <div class="hero-mc-card-labelrow">
                                    <span class="hero-mc-card-label"><?= htmlspecialchars($card['card_label']) ?></span>
                                    <?php if (!empty($card['card_badge'])): ?>
                                        <span class="hero-mc-card-badge"><?= htmlspecialchars($card['card_badge']) ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($card['card_value'])): ?>
                                <div class="hero-mc-card-value">
                                    <?= htmlspecialchars($card['card_value']) ?>
                                    <?php if (!empty($card['card_unit'])): ?>
                                        <span class="hero-mc-card-unit"><?= htmlspecialchars($card['card_unit']) ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($card['card_title'])): ?>
                                <div class="hero-mc-card-title"><?= htmlspecialchars($card['card_title']) ?></div>
                            <?php endif; ?>
                            <?php if (!empty($card['card_meta'])): ?>
                                <div class="hero-mc-card-meta"><?= htmlspecialchars($card['card_meta']) ?></div>
                            <?php endif; ?>

                            <?php if ($progress !== null): ?>
                                <div class="hero-mc-card-bar"><span style="width:<?= $progress ?>%;"></span></div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($card['card_avatar'])): ?>
                            <img src="<?= htmlspecialchars(url($card['card_avatar'])) ?>"
                                 alt="<?= htmlspecialchars($card['card_title'] ?? '') ?>"
                                 class="hero-mc-card-avatar" loading="lazy">
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
    <?php endif; ?>
</section>

<style>
/* ── Hero « media cards » — proportions relevées sur le modèle de référence ──
   Échelle de mesure : 2.222 px image = 1 px CSS (maquette 3200px / viewport 1440). */
.hero-mc {
    position: relative;
    overflow: hidden;
    padding: 104px 0 84px;
    background: var(--bg-base);
    isolation: isolate;
}

/* ── Décors ── */
.hero-mc-decor { position: absolute; inset: 0; z-index: 0; pointer-events: none; }

.hero-mc-circle {
    position: absolute;
    top: 4%; right: 6%;
    width: 620px; height: 620px;
    border-radius: 50%;
    background: radial-gradient(circle at 40% 35%,
        color-mix(in srgb, var(--primary) 16%, transparent) 0%,
        color-mix(in srgb, var(--primary) 7%, transparent) 55%,
        transparent 72%);
}
.hero-mc-curve {
    position: absolute;
    top: 0; right: 26%;
    width: 460px; height: 100%;
    color: color-mix(in srgb, var(--primary) 32%, transparent);
}
.hero-mc-wave {
    position: absolute;
    left: -4%; bottom: -2%;
    width: 62%; height: 300px;
    color: color-mix(in srgb, var(--primary) 14%, transparent);
}
.hero-mc-dots {
    position: absolute;
    top: 4%; right: 3%;
    width: 190px; height: 110px;
    background-image: radial-gradient(color-mix(in srgb, var(--primary) 45%, transparent) 2.4px, transparent 2.4px);
    background-size: 26px 26px;
}

/* ── Grille ── */
.hero-mc-grid {
    position: relative;
    z-index: 1;
    display: grid;
    grid-template-columns: 0.92fr 1.08fr;
    gap: 48px;
    align-items: center;
    min-height: 560px;
}

/* ── Colonne texte ── */
.hero-mc-badge {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    padding: 9px 20px;
    border-radius: 999px;
    background: color-mix(in srgb, var(--primary) 12%, transparent);
    color: var(--primary);
    font-size: 0.94rem;
    font-weight: 700;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    font-family: var(--font-heading);
    margin-bottom: 26px;
}
.hero-mc-badge::before {
    content: '';
    width: 7px; height: 7px;
    border-radius: 50%;
    background: var(--primary);
    flex-shrink: 0;
}

.hero-mc-title {
    font-size: clamp(2.4rem, 5.2vw, 4.5rem);
    line-height: 1.05;
    font-weight: 800;
    letter-spacing: -0.025em;
    color: var(--text-main);
    margin: 0 0 26px;
    font-family: var(--font-heading);
}
/* Dans le modèle, la fin du titre est plus LÉGÈRE, pas plus grasse. */
.hero-mc-title-accent {
    display: block;
    font-weight: 300;
    color: var(--primary);
}

.hero-mc-lead {
    font-size: 1.2rem;
    line-height: 1.55;
    color: var(--text-muted);
    max-width: 520px;
    margin: 0 0 38px;
}

.hero-mc-actions { display: flex; flex-wrap: wrap; gap: 16px; }

.hero-mc-btn {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    height: 50px;
    padding: 0 12px 0 28px;
    border-radius: 999px;
    font-size: 1.02rem;
    font-weight: 600;
    text-decoration: none;
    transition: var(--transition);
    font-family: var(--font-main);
}
.hero-mc-btn-primary {
    background: var(--primary);
    color: #ffffff !important;
    border: 1.5px solid var(--primary);
}
.hero-mc-btn-ghost {
    background: var(--bg-card);
    color: var(--text-main) !important;
    border: 1.5px solid var(--border-md);
    padding: 0 28px 0 12px;
}
.hero-mc-btn:hover { transform: translateY(-2px); }

.hero-mc-btn-icon {
    display: inline-flex; align-items: center; justify-content: center;
    width: 30px; height: 30px;
    border-radius: 50%;
    flex-shrink: 0;
}
.hero-mc-btn-primary .hero-mc-btn-icon { background: rgba(255,255,255,0.20); color: #ffffff; }
.hero-mc-btn-ghost   .hero-mc-btn-icon { background: color-mix(in srgb, var(--primary) 12%, transparent); color: var(--primary); }

/* ── Colonne visuelle ── */
.hero-mc-visual { position: relative; min-height: 520px; }

.hero-mc-media {
    position: relative;
    height: 100%;
    min-height: 520px;
    /* Angles droits par défaut ; arrondi réactivable en admin (`image_radius`). */
    border-radius: var(--hero-media-radius, 0);
    overflow: hidden;
}
.hero-mc-img {
    width: 100%; height: 100%;
    min-height: 520px;
    object-fit: cover;
    object-position: center 30%;
    display: block;
}

/* ── Cartes flottantes ── */
.hero-mc-card {
    position: absolute;
    z-index: 2;
    min-width: 232px;
    max-width: 300px;
    padding: 14px 16px;
    border-radius: 16px;
    background: var(--bg-card);
    box-shadow: 0 2px 6px rgba(18,32,44,0.06), 0 14px 38px rgba(18,32,44,0.12);
    animation: hero-mc-float 6s ease-in-out infinite;
}
@keyframes hero-mc-float {
    0%, 100% { transform: translateY(0); }
    50%      { transform: translateY(-9px); }
}
@media (prefers-reduced-motion: reduce) {
    .hero-mc-card { animation: none; }
}

.hero-mc-card-row { display: flex; align-items: center; gap: 12px; }
.hero-mc-card-main { min-width: 0; flex: 1; }

.hero-mc-card-icon {
    display: inline-flex; align-items: center; justify-content: center;
    width: 34px; height: 34px;
    border-radius: 50%;
    background: color-mix(in srgb, var(--primary) 12%, transparent);
    color: var(--primary);
    flex-shrink: 0;
}

.hero-mc-card-labelrow { display: flex; align-items: center; gap: 8px; justify-content: space-between; }
.hero-mc-card-label {
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--text-muted);
}
.hero-mc-card-badge {
    font-size: 0.66rem;
    font-weight: 600;
    padding: 3px 9px;
    border-radius: 999px;
    background: color-mix(in srgb, var(--primary) 12%, transparent);
    color: var(--primary);
    white-space: nowrap;
}

.hero-mc-card-value {
    font-size: 1.55rem;
    font-weight: 800;
    color: var(--text-main);
    line-height: 1.2;
    margin-top: 4px;
    font-family: var(--font-heading);
}
.hero-mc-card-unit { font-size: 0.8rem; font-weight: 600; color: var(--text-muted); margin-left: 2px; }

.hero-mc-card-title { font-size: 0.95rem; font-weight: 700; color: var(--text-main); line-height: 1.3; margin-top: 2px; }
.hero-mc-card-meta  { font-size: 0.8rem; color: var(--text-muted); line-height: 1.4; margin-top: 2px; }

.hero-mc-card-bar {
    margin-top: 10px;
    height: 5px;
    border-radius: 999px;
    background: color-mix(in srgb, var(--primary) 14%, transparent);
    overflow: hidden;
}
.hero-mc-card-bar > span { display: block; height: 100%; border-radius: 999px; background: var(--primary); }

.hero-mc-card-avatar { width: 34px; height: 34px; border-radius: 50%; object-fit: cover; flex-shrink: 0; }

/* ── Mise en page « bandeau » ──────────────────────────────────────────────
   Texte en haut, visuel large dessous. Le bandeau déborde volontairement du
   conteneur (1240px) pour atteindre la largeur demandée, sans jamais provoquer
   de défilement horizontal : `.hero-mc` est en `overflow: hidden`.
   Les sélecteurs sont préfixés par `.hero-mc-banner` : le mode « split » de
   l'accueil et de /service n'est pas touché.                                */
.hero-mc-banner .hero-mc-grid {
    grid-template-columns: 1fr;
    gap: 44px;
    min-height: 0;
}
.hero-mc-banner .hero-mc-lead { max-width: 720px; }

.hero-mc-banner .hero-mc-visual {
    min-height: 0;
    /* Centrage indépendant de la largeur du conteneur ; la marge de 48px tient
       compte de la barre de défilement pour ne pas rogner les bords. */
    width: min(var(--hero-banner-w, 1300px), calc(100vw - 48px));
    margin-left: 50%;
    transform: translateX(-50%);
}

.hero-mc-banner .hero-mc-media {
    height: auto;
    min-height: 0;
    aspect-ratio: var(--hero-banner-ratio, 1300 / 400);
}
.hero-mc-banner .hero-mc-img {
    height: 100%;
    min-height: 0;
    object-position: center 42%;
}

/* Une carte ne peut pas flotter sur un bandeau de 400px : elle se range dessous. */
.hero-mc-banner .hero-mc-card {
    position: static;
    animation: none;
    max-width: none;
    margin-top: 14px;
}
.hero-mc-banner .hero-mc-curve,
.hero-mc-banner .hero-mc-dots { display: none; }
.hero-mc-banner .hero-mc-circle { width: 480px; height: 480px; top: -8%; right: -4%; }

/* Sous 760px, un rapport 3.25:1 donnerait une bande trop fine : on l'ouvre. */
@media (max-width: 760px) {
    .hero-mc-banner .hero-mc-media { aspect-ratio: var(--hero-banner-ratio-sm, 16 / 9); }
    .hero-mc-banner .hero-mc-visual { width: calc(100vw - 32px); }
}

/* ── Hero plein cadre, façon carrousel ─────────────────────────────────────
   Visuel bord à bord, dégradé sombre depuis la gauche pour que le texte reste
   lisible sans masquer la photo, badge en pavé plein, titre en capitales.   */
.hero-mc-overlay { padding: 0; overflow: hidden; }

.hero-ov {
    position: relative;
    width: min(var(--hero-banner-w, 1300px), calc(100vw - 48px));
    margin-left: 50%;
    transform: translateX(-50%);
    min-height: var(--hero-overlay-minh, 460px);
    overflow: hidden;
    border-radius: var(--hero-media-radius, 0);
    background: var(--primary);
}
/* Bord à bord : le visuel occupe toute la largeur de la fenêtre. */
.hero-ov-full { width: 100vw; border-radius: 0; }

.hero-ov-slide {
    position: absolute;
    inset: 0;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.6s ease;
}
.hero-ov-slide.active { opacity: 1; visibility: visible; }
/* La première diapositive porte la hauteur du bloc : sans elle, toutes étant
   absolues, le conteneur s'effondrerait à zéro. */
.hero-ov-slide:first-child { position: relative; }

.hero-ov-media { position: absolute; inset: 0; background: var(--primary); }
.hero-ov-media img { width: 100%; height: 100%; object-fit: cover; display: block; }
.hero-ov-media::after {
    content: "";
    position: absolute; inset: 0;
    background: linear-gradient(90deg,
        rgba(0, 0, 0, 0.82) 0%,
        rgba(0, 0, 0, 0.66) 34%,
        rgba(0, 0, 0, 0.28) 64%,
        rgba(0, 0, 0, 0.06) 100%),
      linear-gradient(180deg,
        color-mix(in srgb, var(--primary) 34%, transparent) 0%,
        color-mix(in srgb, var(--primary) 62%, transparent) 100%);
}

.hero-ov-inner {
    position: relative;
    z-index: 2;
    max-width: var(--max-width, 1240px);
    margin: 0 auto;
    padding: 96px 40px;
    min-height: var(--hero-overlay-minh, 460px);
    display: flex;
    align-items: center;
}
.hero-ov-text { max-width: 640px; }

.hero-ov-badge {
    display: inline-block;
    margin-bottom: 20px;
    padding: 9px 18px;
    background: var(--primary);
    color: #ffffff;
    font-family: var(--font-heading);
    font-size: 0.76rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
}

.hero-ov-title {
    margin: 0 0 18px;
    color: #ffffff;
    font-family: var(--font-heading);
    font-size: clamp(2.1rem, 5vw, 3.6rem);
    line-height: 1.06;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: -0.01em;
    text-shadow: 0 2px 18px rgba(0, 0, 0, 0.35);
}
.hero-ov-accent { display: block; font-weight: 300; text-transform: none; }

.hero-ov-lead {
    margin: 0;
    color: rgba(255, 255, 255, 0.92);
    font-size: 1.04rem;
    line-height: 1.7;
    max-width: 560px;
    text-shadow: 0 1px 12px rgba(0, 0, 0, 0.35);
}

.hero-ov-actions { display: flex; flex-wrap: wrap; gap: 14px; margin-top: 30px; }
.hero-ov-btn {
    display: inline-flex; align-items: center; gap: 10px;
    padding: 15px 30px;
    border-radius: 999px;
    font-family: var(--font-main);
    font-size: 1rem;
    font-weight: 700;
    text-decoration: none;
    border: 1.5px solid transparent;
    transition: var(--transition);
}
.hero-ov-btn-primary { background: #ffffff; color: var(--primary) !important; }
.hero-ov-btn-primary:hover { transform: translateY(-2px); }
.hero-ov-btn-ghost {
    background: rgba(255, 255, 255, 0.10);
    border-color: rgba(255, 255, 255, 0.65);
    color: #ffffff !important;
}
.hero-ov-btn-ghost:hover { background: rgba(255, 255, 255, 0.22); }

.hero-ov-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    z-index: 3;
    width: 46px; height: 46px;
    display: inline-flex; align-items: center; justify-content: center;
    background: rgba(0, 0, 0, 0.28);
    border: 1.5px solid rgba(255, 255, 255, 0.6);
    color: #ffffff;
    cursor: pointer;
    transition: var(--transition);
}
.hero-ov-arrow:hover { background: var(--primary); border-color: var(--primary); }
.hero-ov-prev { left: 24px; }
.hero-ov-next { right: 24px; }

.hero-ov-dots {
    position: absolute;
    bottom: 22px; left: 50%;
    transform: translateX(-50%);
    z-index: 3;
    display: flex; gap: 8px;
}
.hero-ov-dot {
    width: 26px; height: 6px;
    border: none; padding: 0;
    background: rgba(255, 255, 255, 0.45);
    cursor: pointer;
    transition: var(--transition);
}
.hero-ov-dot.active { background: var(--primary); }

@media (max-width: 900px) {
    .hero-ov-inner { padding: 72px 24px; min-height: 400px; }
    .hero-ov, .hero-ov-full { width: 100vw; border-radius: 0; min-height: 400px; }
    .hero-ov-arrow { width: 38px; height: 38px; }
    .hero-ov-prev { left: 10px; }
    .hero-ov-next { right: 10px; }
}
@media (max-width: 560px) {
    .hero-ov-inner { padding: 60px 18px 68px; min-height: 340px; }
    .hero-ov, .hero-ov-full { min-height: 340px; }
    .hero-ov-actions { flex-direction: column; align-items: stretch; }
    .hero-ov-btn { justify-content: center; }
    /* Les flèches recouvriraient le texte sur un écran étroit : seules les
       pastilles restent, et le balayage n'est pas nécessaire au sens. */
    .hero-ov-arrow { display: none; }
}

/* ── Mise en page « overlay » (ancien rendu, conservé pour référence) ───────
   Le texte est centré PAR-DESSUS le visuel, voilé pour rester lisible.
   Texte et visuel occupent la même cellule de grille : aucun décalage
   possible entre les deux, quelle que soit la longueur du titre.          */
.hero-mc-overlay { padding: 0; }

.hero-mc-overlay .hero-mc-grid {
    grid-template-columns: 1fr;
    grid-template-areas: "stack";
    gap: 0;
    min-height: 0;
    align-items: stretch;
}
.hero-mc-overlay .hero-mc-text,
.hero-mc-overlay .hero-mc-visual { grid-area: stack; }

.hero-mc-overlay .hero-mc-visual {
    position: relative;
    z-index: 0;
    min-height: 0;
    /* Les proportions restent administrables ; la hauteur réelle est le plus
       grand des deux — proportions du visuel ou hauteur du texte. */
    aspect-ratio: var(--hero-banner-ratio, 1300 / 400);
    width: min(var(--hero-banner-w, 1300px), calc(100vw - 48px));
    margin-left: 50%;
    transform: translateX(-50%);
}
.hero-mc-overlay .hero-mc-media {
    position: absolute;
    inset: 0;
    height: 100%;
    min-height: 0;
    /* Fond de sécurité : garantit un contraste suffisant pour le texte blanc
       tant qu'aucun visuel n'est choisi, et pendant le chargement de l'image. */
    background: var(--primary);
}
.hero-mc-overlay .hero-mc-img { height: 100%; min-height: 0; object-position: center 40%; }

/* Le voile : teinte de marque, opacité administrable. */
.hero-mc-overlay .hero-mc-media::after {
    content: "";
    position: absolute;
    inset: 0;
    /* Dégradé HORIZONTAL : dense à gauche, sous le texte, puis transparent à
       droite pour que la photo reste visible — un voile plein la masquerait. */
    background: linear-gradient(
        90deg,
        var(--primary) 0%,
        color-mix(in srgb, var(--primary) 88%, transparent) 38%,
        color-mix(in srgb, var(--primary) 42%, transparent) 66%,
        transparent 92%
    );
    /* L'opacité porte le réglage : pas de calc() imbriqué dans color-mix,
       dont le support est plus incertain que celui d'une simple opacité. */
    opacity: var(--hero-overlay-a, 0.62);
}

/* La colonne texte porte la hauteur : le visuel, absolu, s'y adapte. Le titre
   ne peut donc jamais déborder du cadre, même très long ou très traduit. */
.hero-mc-overlay .hero-mc-text {
    position: relative;
    z-index: 2;
    display: flex;
    flex-direction: column;
    /* Le texte se tient à gauche et n'occupe pas toute la largeur : la moitié
       droite de la photo reste dégagée. */
    align-items: flex-start;
    justify-content: center;
    text-align: left;
    max-width: 600px;
    gap: 6px;
    min-height: var(--hero-overlay-minh, 420px);
    padding: 72px 0;
}

.hero-mc-overlay .hero-mc-badge {
    background: rgba(255, 255, 255, 0.16);
    border-color: rgba(255, 255, 255, 0.34);
    color: #ffffff;
}
.hero-mc-overlay .hero-mc-title { color: #ffffff; }
.hero-mc-overlay .hero-mc-title-accent { color: rgba(255, 255, 255, 0.88); }
.hero-mc-overlay .hero-mc-lead {
    color: rgba(255, 255, 255, 0.92);
    max-width: 520px;
    margin-left: 0;
    margin-right: 0;
}
.hero-mc-overlay .hero-mc-actions { justify-content: flex-start; }

/* Sur photo, un bouton à la couleur de marque se fond dans le voile :
   le bouton principal passe en blanc plein, le secondaire en contour clair. */
/* `!important` obligatoire : la règle de base pose `color: #ffffff !important`
   sur ce bouton, ce qui rendait le libellé blanc sur fond blanc. */
.hero-mc-overlay .hero-mc-btn-primary {
    background: #ffffff;
    color: var(--primary) !important;
    border-color: #ffffff;
    box-shadow: 0 10px 26px -12px rgba(0, 0, 0, 0.55);
}
.hero-mc-overlay .hero-mc-btn-primary .hero-mc-btn-icon {
    background: color-mix(in srgb, var(--primary) 14%, transparent);
    color: var(--primary);
}
.hero-mc-overlay .hero-mc-btn-ghost {
    background: rgba(255, 255, 255, 0.12);
    border-color: rgba(255, 255, 255, 0.55);
    color: #ffffff !important;
}
.hero-mc-overlay .hero-mc-btn-ghost .hero-mc-btn-icon {
    background: rgba(255, 255, 255, 0.20);
    color: #ffffff;
}

@media (max-width: 760px) {
    .hero-mc-overlay .hero-mc-visual { width: 100vw; }
    .hero-mc-overlay .hero-mc-media { border-radius: 0; }
    .hero-mc-overlay .hero-mc-text { padding: 56px 0; min-height: 340px; max-width: none; }
    /* Sur mobile le texte occupe toute la largeur : un dégradé latéral le
       laisserait à cheval sur la partie claire. Il redevient vertical. */
    .hero-mc-overlay .hero-mc-media::after {
        background: linear-gradient(
            180deg,
            color-mix(in srgb, var(--primary) 78%, transparent) 0%,
            var(--primary) 100%
        );
    }
}

/* ── Adaptatif ── */
@media (max-width: 1180px) {
    .hero-mc-card { min-width: 200px; padding: 12px 14px; }
    .hero-mc-circle { width: 460px; height: 460px; }
}
@media (max-width: 1000px) {
    .hero-mc { padding: 84px 0 64px; }
    .hero-mc-grid { grid-template-columns: 1fr; gap: 40px; min-height: 0; }
    .hero-mc-lead { max-width: none; }
    .hero-mc-visual { min-height: 0; }
    .hero-mc-media, .hero-mc-img { min-height: 380px; height: 380px; }
    /* Les cartes cessent de flotter et passent en grille sous le visuel. */
    .hero-mc-card {
        position: static;
        animation: none;
        max-width: none;
        margin-top: 14px;
    }
    .hero-mc-curve, .hero-mc-dots { display: none; }
}
@media (max-width: 560px) {
    .hero-mc-actions { flex-direction: column; align-items: stretch; }
    .hero-mc-btn { justify-content: center; }
    .hero-mc-media, .hero-mc-img { min-height: 300px; height: 300px; }
}
</style>

<?php if ($hasCarousel): ?>
<script>
(function () {
    // Carrousel du hero. Chaque section porte son propre identifiant : deux
    // heros sur une même page ne se pilotent pas mutuellement.
    var root = document.currentScript.previousElementSibling;
    while (root && !root.classList.contains('hero-ov')) { root = root.previousElementSibling; }
    if (!root) { root = document.querySelector('.hero-ov'); }
    if (!root) { return; }

    var slides = root.querySelectorAll('.hero-ov-slide');
    var dots   = root.querySelectorAll('.hero-ov-dot');
    if (slides.length < 2) { return; }

    var index = 0;
    var timer = null;

    function show(i) {
        index = (i + slides.length) % slides.length;
        slides.forEach(function (s, n) { s.classList.toggle('active', n === index); });
        dots.forEach(function (d, n) { d.classList.toggle('active', n === index); });
    }

    function start() {
        stop();
        // Respecte le réglage système « animations réduites ».
        if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) { return; }
        timer = setInterval(function () { show(index + 1); }, 7000);
    }
    function stop() { if (timer) { clearInterval(timer); timer = null; } }

    var prev = root.querySelector('.hero-ov-prev');
    var next = root.querySelector('.hero-ov-next');
    if (prev) { prev.addEventListener('click', function () { show(index - 1); start(); }); }
    if (next) { next.addEventListener('click', function () { show(index + 1); start(); }); }
    dots.forEach(function (d) {
        d.addEventListener('click', function () { show(parseInt(d.dataset.go, 10) || 0); start(); });
    });

    root.addEventListener('mouseenter', stop);
    root.addEventListener('mouseleave', start);
    start();
})();
</script>
<?php endif; ?>
