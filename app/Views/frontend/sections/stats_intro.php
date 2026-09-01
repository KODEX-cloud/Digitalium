<?php
/**
 * Section: stats_intro — Intro (badge/titre/texte/lien) + grille de statistiques
 * Données CMS : $single (badge, title, description, link_text, link_url),
 *               $groups (stat_icon, stat_value, stat_desc)
 * Design System v4.1 — variables CSS uniquement, zéro hardcode
 */
?>

<section class="section-padding stats-intro-section" style="background:var(--bg-alt);">
    <div class="container">
        <div class="stats-intro-grid">

            <!-- ── LEFT — Badge + Titre + Texte + Lien ─────────────── -->
            <div class="reveal">
                <?php if (!empty($single['badge'])): ?>
                    <span class="section-badge"><?= htmlspecialchars($single['badge']) ?></span>
                <?php endif; ?>

                <h2 class="section-title" style="margin-top:0;margin-bottom:16px;">
                    <?= htmlspecialchars($single['title'] ?? '') ?>
                </h2>

                <div class="section-divider" style="margin:0 0 24px 0;"></div>

                <p style="font-size:1rem;line-height:1.8;color:var(--text-muted);margin-bottom:28px;">
                    <?= htmlspecialchars($single['description'] ?? '') ?>
                </p>

                <?php if (!empty($single['link_text'])): ?>
                    <a href="<?= htmlspecialchars(url($single['link_url'] ?? '/contact')) ?>" style="display:inline-flex;align-items:center;gap:8px;font-size:0.85rem;font-weight:700;color:var(--primary);letter-spacing:0.04em;">
                        <span><?= htmlspecialchars($single['link_text']) ?></span>
                        <i data-lucide="arrow-right" style="width:15px;height:15px;"></i>
                    </a>
                <?php endif; ?>
            </div>

            <!-- ── RIGHT — Stat cards 2×2 ───────────────────────────── -->
            <div class="stats-intro-cards reveal" style="transition-delay:0.12s;">
                <?php if (!empty($groups)): ?>
                    <?php foreach ($groups as $stat): ?>
                        <div class="card stat-intro-card">
                            <div class="stat-intro-icon">
                                <?= \App\Helpers\IconHelper::render($stat['stat_icon'] ?? 'star', ['size' => '22px']) ?>
                            </div>
                            <div class="stat-intro-value"><?= htmlspecialchars($stat['stat_value'] ?? '') ?></div>
                            <p class="stat-intro-desc"><?= htmlspecialchars($stat['stat_desc'] ?? '') ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>
    </div>
</section>

<style>
.stats-intro-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 64px;
    align-items: center;
}

.stats-intro-cards {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.stat-intro-card { padding: 26px 22px; }

.stat-intro-icon {
    width: 44px; height: 44px;
    border-radius: var(--radius-md);
    background: rgba(13,148,136,0.08);
    border: 1px solid rgba(13,148,136,0.16);
    color: var(--primary);
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 16px;
}

.stat-intro-value {
    font-size: 1.35rem;
    font-weight: 800;
    color: var(--text-main);
    font-family: var(--font-heading);
    margin-bottom: 8px;
    line-height: 1.2;
}

.stat-intro-desc {
    font-size: 0.8rem;
    line-height: 1.6;
    color: var(--text-muted);
    margin: 0;
}

@media (max-width: 480px) {
    .stats-intro-cards { grid-template-columns: 1fr; }
}
</style>
