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
 *   groups (cartes flottantes, répétables) :
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
?>

<section class="hero-mc" id="hero-media-cards">

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
            <?php if (!empty($single['image'])): ?>
                <div class="hero-mc-media">
                    <img src="<?= htmlspecialchars(url($single['image'])) ?>"
                         alt="<?= htmlspecialchars($single['image_alt'] ?? ($single['title'] ?? '')) ?>"
                         class="hero-mc-img">
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
    border-radius: 28px;
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
