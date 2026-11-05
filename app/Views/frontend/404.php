<?php
/**
 * Page 404 — mêmes textes et mêmes liens qu'avant ; mise en forme reprise
 * sur les jetons du thème (couleurs de la marque au lieu du violet et du
 * rouge d'alerte, échelle typographique, boutons et rythme communs).
 */
?>
<style>
.error-section {
    position: relative;
    isolation: isolate;
    overflow: hidden;
    min-height: 72vh;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: calc(var(--space-section) + 32px) 0 var(--space-section);
    background: var(--section-tint);
}
/* Trame de points discrète, estompée vers les bords. */
.error-section::before {
    content: '';
    position: absolute; inset: 0; z-index: -1;
    background-image: radial-gradient(color-mix(in srgb, var(--primary) 22%, transparent) 1px, transparent 1.5px);
    background-size: 24px 24px;
    -webkit-mask-image: radial-gradient(ellipse 60% 55% at 50% 45%, #000 0%, transparent 75%);
    mask-image: radial-gradient(ellipse 60% 55% at 50% 45%, #000 0%, transparent 75%);
    pointer-events: none;
}
.error-code {
    display: block;
    font-family: var(--font-heading);
    font-size: clamp(6rem, 4rem + 12vw, 13rem);
    font-weight: 800;
    line-height: 0.9;
    letter-spacing: -0.06em;
    background: linear-gradient(180deg, var(--brand-300) 0%, color-mix(in srgb, var(--brand-100) 60%, transparent) 100%);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: calc(-1 * var(--space-3));
    user-select: none;
}
.error-card {
    position: relative;
    max-width: 560px;
    margin: 0 auto;
    padding: var(--space-5) var(--space-4);
    background: color-mix(in srgb, var(--bg-card) 82%, transparent);
    border: 1px solid var(--border);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-2);
    -webkit-backdrop-filter: blur(10px);
    backdrop-filter: blur(10px);
}
.error-card .section-badge { margin-bottom: var(--space-2); }
.error-card .section-badge svg { width: 14px; height: 14px; }
.error-title {
    font-size: var(--fs-2xl);
    font-weight: 800;
    letter-spacing: var(--tracking-tight);
    line-height: var(--lh-heading);
    margin-bottom: var(--space-2);
}
.error-text {
    color: var(--text-sub);
    font-size: var(--fs-base);
    line-height: var(--line-height-body);
    margin: 0 auto var(--space-4);
    max-width: 44ch;
}
.error-actions { display: flex; gap: var(--space-1-5); justify-content: center; flex-wrap: wrap; }
.error-actions svg { width: 16px; height: 16px; }
@media (max-width: 560px) {
    .error-card { padding: var(--space-4) var(--space-3); }
    .error-actions { flex-direction: column; }
    .error-actions > a { width: 100%; }
}
</style>

<section class="error-section">
    <div class="container">
        <span class="error-code" aria-hidden="true">404</span>
        <div class="error-card reveal">
            <span class="section-badge">
                <i data-lucide="compass"></i>
                Erreur 404
            </span>
            <h1 class="error-title">Page introuvable</h1>
            <p class="error-text">
                La page que vous recherchez n'existe pas ou a été déplacée.
                Retournez à l'accueil ou explorez nos services.
            </p>
            <div class="error-actions">
                <a href="<?= url('/') ?>" class="btn-primary">
                    <i data-lucide="home"></i>
                    <span>Retour à l'accueil</span>
                </a>
                <a href="<?= url('/realisations') ?>" class="btn-secondary">
                    <i data-lucide="briefcase"></i>
                    <span>Nos réalisations</span>
                </a>
            </div>
        </div>
    </div>
</section>
