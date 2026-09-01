<?php
/**
 * Section: about_visual — Image + badge d'expérience + checklist
 * Données CMS : $single (tag, title, description, image, badge_years, badge_label, check_1..check_5)
 * Design System v4.1 — variables CSS uniquement, zéro hardcode
 */
?>

<section class="section-padding about-visual-section">
    <div class="container">
        <div class="about-visual-grid">

            <!-- ── LEFT — Image + badge flottant ────────────────────── -->
            <div class="about-visual-media reveal">
                <?php if (!empty($single['image'])): ?>
                    <img src="<?= htmlspecialchars(url($single['image'])) ?>" alt="<?= htmlspecialchars($single['title'] ?? 'Digitalium Group') ?>" loading="lazy">
                <?php else: ?>
                    <div class="about-visual-placeholder">
                        <i data-lucide="image" style="width:48px;height:48px;color:rgba(13,148,136,0.3);"></i>
                    </div>
                <?php endif; ?>

                <?php if (!empty($single['badge_years'])): ?>
                    <div class="about-visual-badge">
                        <span class="about-visual-badge-num"><?= htmlspecialchars($single['badge_years']) ?></span>
                        <span class="about-visual-badge-label"><?= htmlspecialchars($single['badge_label'] ?? '') ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- ── RIGHT — Texte + checklist ────────────────────────── -->
            <div class="reveal" style="transition-delay:0.12s;">
                <?php if (!empty($single['tag'])): ?>
                    <span class="section-badge"><?= htmlspecialchars($single['tag']) ?></span>
                <?php endif; ?>

                <h2 class="section-title" style="margin-top:0;margin-bottom:16px;">
                    <?= htmlspecialchars($single['title'] ?? '') ?>
                </h2>

                <div class="section-divider" style="margin:0 0 24px 0;"></div>

                <p style="font-size:1rem;line-height:1.8;color:var(--text-muted);margin-bottom:32px;">
                    <?= htmlspecialchars($single['description'] ?? '') ?>
                </p>

                <?php
                $hasChecks = false;
                for ($i = 1; $i <= 5; $i++) {
                    if (!empty($single['check_' . $i])) { $hasChecks = true; break; }
                }
                if ($hasChecks): ?>
                <div class="about-visual-checks">
                    <?php for ($i = 1; $i <= 5; $i++):
                        $ck = 'check_' . $i;
                        if (empty($single[$ck])) continue;
                    ?>
                    <div class="about-visual-check-item">
                        <div class="about-visual-check-icon">
                            <i data-lucide="check" style="width:13px;height:13px;color:#fff;stroke-width:3;"></i>
                        </div>
                        <span><?= htmlspecialchars($single[$ck]) ?></span>
                    </div>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</section>

<style>
.about-visual-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 64px;
    align-items: center;
}

.about-visual-media {
    position: relative;
    border-radius: var(--radius-lg);
    overflow: visible;
}

.about-visual-media img {
    width: 100%;
    display: block;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-card);
    aspect-ratio: 4/3;
    object-fit: cover;
}

.about-visual-placeholder {
    width: 100%;
    aspect-ratio: 4/3;
    border-radius: var(--radius-lg);
    background: var(--bg-alt);
    border: 1px dashed var(--border);
    display: flex; align-items: center; justify-content: center;
}

.about-visual-badge {
    position: absolute;
    left: 24px;
    bottom: -24px;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: #fff;
    border-radius: var(--radius-md);
    padding: 16px 22px;
    box-shadow: 0 14px 30px rgba(13,148,136,0.32);
    display: flex;
    flex-direction: column;
    line-height: 1.15;
}

.about-visual-badge-num {
    font-size: 1.5rem;
    font-weight: 800;
    font-family: var(--font-heading);
}

.about-visual-badge-label {
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.03em;
    opacity: 0.9;
}

.about-visual-checks { display: flex; flex-direction: column; gap: 13px; }

.about-visual-check-item { display: flex; align-items: center; gap: 14px; }

.about-visual-check-icon {
    width: 28px; height: 28px; border-radius: 50%; flex-shrink: 0;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 4px 12px rgba(13,148,136,0.28);
}

.about-visual-check-item span {
    font-size: 0.92rem;
    color: var(--text-sub);
    font-weight: 500;
    line-height: 1.5;
}

@media (max-width: 768px) {
    .about-visual-badge { left: 16px; bottom: -18px; padding: 12px 18px; }
}
</style>
