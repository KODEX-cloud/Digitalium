<?php
/**
 * Section: cta — Bandeau d'appel à l'action final
 * Données CMS : $single (eyebrow, title, subtitle, cta_text, cta_url, cta2_text, cta2_url)
 * Règle #2 (zéro hardcode) : chaque élément non renseigné est simplement masqué.
 * Design System v4.1 — variables CSS uniquement
 */
?>

<section class="cta-sec">
    <div class="container cta-band reveal">

        <div class="cta-band-text">
            <?php if (!empty($single['eyebrow'])): ?>
                <div class="section-badge" style="margin-bottom:16px;">
                    <?= htmlspecialchars($single['eyebrow']) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($single['title'])): ?>
                <h2 class="cta-band-title"><?= $single['title'] ?></h2>
            <?php endif; ?>

            <?php if (!empty($single['subtitle'])): ?>
                <p class="cta-band-sub"><?= htmlspecialchars($single['subtitle']) ?></p>
            <?php endif; ?>
        </div>

        <div class="cta-band-actions">
            <?php if (!empty($single['cta_text']) && !empty($single['cta_url'])): ?>
                <a href="<?= htmlspecialchars(url($single['cta_url'])) ?>" class="cta-band-btn cta-band-btn-primary">
                    <span><?= htmlspecialchars($single['cta_text']) ?></span>
                    <i data-lucide="arrow-right" style="width:15px;height:15px;"></i>
                </a>
            <?php endif; ?>
            <?php if (!empty($single['cta2_text']) && !empty($single['cta2_url'])): ?>
                <a href="<?= htmlspecialchars(url($single['cta2_url'])) ?>" class="cta-band-btn cta-band-btn-ghost">
                    <span><?= htmlspecialchars($single['cta2_text']) ?></span>
                    <i data-lucide="arrow-right" style="width:15px;height:15px;"></i>
                </a>
            <?php endif; ?>
        </div>

    </div>
</section>

<style>
.cta-sec {
    position: relative;
    overflow: hidden;
    padding: 46px 0;
    background: linear-gradient(110deg, var(--primary) 0%, #1e5fd4 45%, #1746a8 100%);
}
/* Motif réseau discret sur la droite, comme le visuel de référence */
.cta-sec::after {
    content: '';
    position: absolute;
    inset: 0;
    background:
        radial-gradient(circle at 92% 30%, rgba(255,255,255,0.16) 0 2px, transparent 2px),
        radial-gradient(circle at 84% 70%, rgba(255,255,255,0.14) 0 2px, transparent 2px),
        radial-gradient(circle at 97% 78%, rgba(255,255,255,0.12) 0 2px, transparent 2px),
        radial-gradient(ellipse 40% 120% at 100% 50%, rgba(255,255,255,0.07) 0%, transparent 70%);
    pointer-events: none;
}

.cta-band {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 36px;
    flex-wrap: wrap;
}

.cta-band-text { flex: 1 1 420px; }

.cta-band-title {
    font-family: var(--font-heading);
    font-size: clamp(1.25rem, 2.4vw, 1.75rem);
    font-weight: 800;
    line-height: 1.25;
    color: #ffffff;
    margin: 0 0 8px;
    letter-spacing: -0.01em;
}

.cta-band-sub {
    font-size: 0.88rem;
    line-height: 1.6;
    color: rgba(255,255,255,0.82);
    margin: 0;
    max-width: 560px;
}

.cta-band-actions {
    display: flex;
    gap: 14px;
    flex-wrap: wrap;
    flex-shrink: 0;
}

.cta-band-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 26px;
    border-radius: var(--radius-btn);
    font-size: 0.88rem;
    font-weight: 700;
    transition: var(--transition);
    white-space: nowrap;
}

.cta-band-btn-primary {
    background: #ffffff;
    color: var(--primary) !important;
    border: 1px solid #ffffff;
    box-shadow: 0 8px 20px -6px rgba(0,0,0,0.28);
}
.cta-band-btn-primary:hover { transform: translateY(-2px); background: #f1f5f9; }

.cta-band-btn-ghost {
    background: rgba(255,255,255,0.10);
    color: #ffffff !important;
    border: 1px solid rgba(255,255,255,0.45);
}
.cta-band-btn-ghost:hover { transform: translateY(-2px); background: rgba(255,255,255,0.18); }

.cta-sec .section-badge {
    background: rgba(255,255,255,0.16);
    border-color: rgba(255,255,255,0.3);
    color: #ffffff;
}
.cta-sec .section-badge::before { background: #ffffff; }

@media (max-width: 820px) {
    .cta-band { flex-direction: column; align-items: flex-start; }
    .cta-band-actions { width: 100%; }
}
</style>
