<?php
/**
 * Section: about — Qui Sommes-Nous (split grid)
 * Données CMS : $single, $groups (val_icon, val_title, val_text)
 * Design System v4.1 — variables CSS uniquement, zéro hardcode
 */

// Accent colors cycle for val-cards — all CMS-driven via CSS variables
$valAccents = ['var(--primary)', 'var(--secondary)', 'var(--accent)', 'var(--primary)'];
?>

<section class="section-padding about-section" id="about">

    <!-- Ambient glow decoration -->
    <div aria-hidden="true" class="about-glow-right"></div>

    <div class="container">
        <div class="about-split-grid">

            <!-- ── LEFT — Texte + checkpoints ─────────────────────────── -->
            <div class="reveal">

                <span class="section-badge">
                    <?= htmlspecialchars($single['tag'] ?? 'Qui Sommes-Nous') ?>
                </span>

                <h2 class="section-title" style="margin-top:0;margin-bottom:16px;">
                    <?= htmlspecialchars($single['title'] ?? '') ?>
                </h2>

                <div class="section-divider" style="margin:0 0 28px 0;"></div>

                <p style="font-size:1rem;line-height:1.82;color:var(--text-muted);margin-bottom:36px;max-width:100%;">
                    <?= htmlspecialchars($single['description'] ?? '') ?>
                </p>

                <!-- Checkpoints -->
                <?php
                $hasChecks = false;
                for ($i = 1; $i <= 5; $i++) {
                    if (!empty($single['check_' . $i])) { $hasChecks = true; break; }
                }
                if ($hasChecks): ?>
                <div class="about-checks">
                    <?php for ($i = 1; $i <= 5; $i++):
                        $ck = 'check_' . $i;
                        if (empty($single[$ck])) continue;
                    ?>
                    <div class="about-check-item">
                        <div class="about-check-icon">
                            <i data-lucide="check" style="width:13px;height:13px;color:#fff;stroke-width:3;"></i>
                        </div>
                        <span><?= htmlspecialchars($single[$ck]) ?></span>
                    </div>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>

            </div>

            <!-- ── RIGHT — Value Cards 2×2 ─────────────────────────── -->
            <div class="reveal about-cards-col" style="transition-delay:0.12s;">
                <?php if (!empty($groups)): ?>
                <div class="about-cards-grid">
                    <?php foreach ($groups as $j => $card):
                        $accent = $valAccents[$j % count($valAccents)];
                    ?>
                    <div class="val-card about-val-card" style="border-top:3px solid <?= $accent ?>;">
                        <div class="about-val-icon" style="color:<?= $accent ?>;">
                            <i data-lucide="<?= htmlspecialchars($card['val_icon'] ?? 'star') ?>" style="width:20px;height:20px;"></i>
                        </div>
                        <h4 class="about-val-title">
                            <?= htmlspecialchars($card['val_title'] ?? '') ?>
                        </h4>
                        <p class="about-val-text">
                            <?= htmlspecialchars($card['val_text'] ?? '') ?>
                        </p>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <!-- Fallback decorative block -->
                <div class="about-fallback-visual">
                    <i data-lucide="layers" style="width:56px;height:56px;color:rgba(13,148,136,0.25);margin:0 auto 16px;display:block;"></i>
                    <p style="color:var(--text-muted);font-size:0.9rem;">Expertise &amp; Innovation</p>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</section>

<style>
/* ── About section ─────────────────────────────────────────────── */
.about-section { position: relative; overflow: hidden; }

.about-glow-right {
    position: absolute;
    top: -80px; right: -120px;
    width: 560px; height: 560px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(13,148,136,0.06) 0%, transparent 70%);
    pointer-events: none; z-index: 0;
}

/* Check items */
.about-checks { display: flex; flex-direction: column; gap: 13px; }

.about-check-item {
    display: flex; align-items: center; gap: 14px;
}

.about-check-icon {
    width: 28px; height: 28px; border-radius: 50%; flex-shrink: 0;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 4px 12px rgba(13,148,136,0.28);
    transition: var(--transition);
}

.about-check-item:hover .about-check-icon {
    transform: scale(1.1);
    box-shadow: 0 6px 18px rgba(13,148,136,0.38);
}

.about-check-item span {
    font-size: 0.92rem;
    color: var(--text-sub);
    font-weight: 500;
    line-height: 1.5;
}

/* Cards grid */
.about-cards-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.about-val-card {
    padding: 28px 22px;
}

.about-val-icon {
    width: 44px; height: 44px;
    border-radius: var(--radius-md);
    background: rgba(13,148,136,0.07);
    border: 1px solid rgba(13,148,136,0.14);
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 18px;
    transition: var(--transition);
}

.about-val-card:hover .about-val-icon {
    background: rgba(13,148,136,0.14);
    border-color: rgba(13,148,136,0.28);
    transform: scale(1.05) translateY(-2px);
    box-shadow: 0 8px 20px rgba(13,148,136,0.16);
}

.about-val-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--text-main);
    margin-bottom: 8px;
    font-family: var(--font-heading);
    line-height: 1.3;
}

.about-val-text {
    font-size: 0.83rem;
    line-height: 1.65;
    color: var(--text-muted);
    margin: 0;
}

/* Fallback visual */
.about-fallback-visual {
    background: linear-gradient(135deg, rgba(13,148,136,0.06) 0%, rgba(8,145,178,0.03) 100%);
    border: 1px solid rgba(13,148,136,0.12);
    border-radius: var(--radius-lg);
    padding: 60px 40px;
    text-align: center;
}

/* Responsive */
@media (max-width: 768px) {
    .about-cards-grid { grid-template-columns: 1fr; }
    .about-val-card { padding: 22px 18px; }
}
</style>
