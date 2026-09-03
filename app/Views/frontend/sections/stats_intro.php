<?php
/**
 * Section: stats_intro — Intro (badge/titre/texte/lien) + rangée de 4 statistiques
 * Données CMS : $single (badge, title, description, link_text, link_url),
 *               $groups (stat_icon, stat_value, stat_label, stat_desc)
 * Règle #2 (zéro hardcode) : aucun texte affiché n'est écrit en dur — tout provient
 * des blocs CMS, et chaque élément non renseigné est simplement masqué.
 * Design System v4.1 — variables CSS uniquement
 */
?>

<section class="section-padding stats-intro-section" style="background:var(--bg-alt);">
    <div class="container">
        <div class="stats-intro-grid">

            <!-- ── GAUCHE — Badge + Titre + Texte + Lien ────────────── -->
            <div class="stats-intro-text reveal">
                <?php if (!empty($single['badge'])): ?>
                    <span class="section-badge"><?= htmlspecialchars($single['badge']) ?></span>
                <?php endif; ?>

                <?php if (!empty($single['title'])): ?>
                    <h2 class="section-title" style="margin-top:0;margin-bottom:16px;">
                        <?= htmlspecialchars($single['title']) ?>
                    </h2>
                <?php endif; ?>

                <div class="section-divider" style="margin:0 0 24px 0;"></div>

                <?php if (!empty($single['description'])): ?>
                    <p class="stats-intro-lead">
                        <?= htmlspecialchars($single['description']) ?>
                    </p>
                <?php endif; ?>

                <?php if (!empty($single['link_text']) && !empty($single['link_url'])): ?>
                    <a href="<?= htmlspecialchars(url($single['link_url'])) ?>" class="stats-intro-link">
                        <span><?= htmlspecialchars($single['link_text']) ?></span>
                        <i data-lucide="arrow-right" style="width:15px;height:15px;"></i>
                    </a>
                <?php endif; ?>
            </div>

            <!-- ── DROITE — Rangée de cartes statistiques ───────────── -->
            <div class="stats-intro-cards reveal" style="transition-delay:0.12s;">
                <?php if (!empty($groups)): ?>
                    <?php foreach ($groups as $i => $stat): ?>
                        <div class="card stat-intro-card" style="transition-delay:<?= $i * 0.06 ?>s;">
                            <?php if (!empty($stat['stat_icon'])): ?>
                                <div class="stat-intro-icon">
                                    <?= \App\Helpers\IconHelper::render($stat['stat_icon'], ['size' => '22px']) ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($stat['stat_value'])): ?>
                                <div class="stat-intro-value"><?= htmlspecialchars($stat['stat_value']) ?></div>
                            <?php endif; ?>
                            <?php if (!empty($stat['stat_label'])): ?>
                                <div class="stat-intro-label"><?= htmlspecialchars($stat['stat_label']) ?></div>
                            <?php endif; ?>
                            <?php if (!empty($stat['stat_desc'])): ?>
                                <p class="stat-intro-desc"><?= htmlspecialchars($stat['stat_desc']) ?></p>
                            <?php endif; ?>
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
    grid-template-columns: 1fr 1.55fr;
    gap: 48px;
    align-items: center;
}

.stats-intro-lead {
    font-size: 0.92rem;
    line-height: 1.75;
    color: var(--text-muted);
    margin-bottom: 22px;
}

.stats-intro-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 0.85rem;
    font-weight: 700;
    color: var(--primary);
    letter-spacing: 0.02em;
}

.stats-intro-cards {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 18px;
}

.stat-intro-card {
    padding: 24px 20px 22px;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.stat-intro-icon {
    width: 42px; height: 42px;
    border-radius: var(--radius-md);
    background: rgba(37,99,235,0.08);
    color: var(--primary);
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 14px;
}

.stat-intro-value {
    font-size: 1.75rem;
    font-weight: 800;
    color: var(--primary);
    font-family: var(--font-heading);
    line-height: 1.1;
    margin-bottom: 6px;
}

.stat-intro-label {
    font-size: 0.86rem;
    font-weight: 700;
    color: var(--text-main);
    font-family: var(--font-heading);
    line-height: 1.3;
    margin-bottom: 8px;
}

.stat-intro-desc {
    font-size: 0.76rem;
    line-height: 1.55;
    color: var(--text-muted);
    margin: 0;
}

@media (max-width: 1100px) {
    .stats-intro-grid { grid-template-columns: 1fr; gap: 40px; }
    .stats-intro-cards { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 820px) {
    .stats-intro-cards { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 460px) {
    .stats-intro-cards { grid-template-columns: 1fr; }
}
</style>
