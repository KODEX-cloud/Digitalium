<?php
/**
 * Section: projects_showcase — Vitrine curatée de réalisations (page d'accueil)
 * Distinct de la section "portfolio" (qui liste TOUTE la table `projects` avec filtres) :
 * ici le contenu est piloté par blocs, pour une sélection éditoriale de 3 projets phares.
 * Données CMS : $single (tag, title, subtitle, more_text, more_url),
 *               $groups (proj_image, proj_category, proj_title, proj_desc, proj_result, proj_link)
 * Design System v4.1 — variables CSS uniquement, zéro hardcode
 */
$projAccents = ['var(--primary)', 'var(--secondary)', 'var(--accent)'];
?>

<section class="section-padding projects-showcase-section" style="background:var(--bg-alt);">
    <div class="container">

        <div class="section-header reveal">
            <?php if (!empty($single['tag'])): ?>
                <span class="section-badge"><?= htmlspecialchars($single['tag']) ?></span>
            <?php endif; ?>
            <?php if (!empty($single['title'])): ?>
                <h2 class="section-title"><?= htmlspecialchars($single['title']) ?></h2>
            <?php endif; ?>
            <?php if (!empty($single['subtitle'])): ?>
                <p class="section-subtitle"><?= htmlspecialchars($single['subtitle']) ?></p>
            <?php endif; ?>
        </div>

        <div class="projects-showcase-grid">
            <?php if (!empty($groups)): ?>
                <?php foreach ($groups as $i => $proj): ?>
                    <div class="card project-showcase-card reveal" style="transition-delay:<?= $i * 0.08 ?>s;">
                        <div class="project-showcase-media">
                            <?php if (!empty($proj['proj_image'])): ?>
                                <img src="<?= htmlspecialchars(url($proj['proj_image'])) ?>" alt="<?= htmlspecialchars($proj['proj_title'] ?? '') ?>" loading="lazy">
                            <?php else: ?>
                                <div class="project-showcase-media-fallback">
                                    <i data-lucide="layout-dashboard" style="width:36px;height:36px;color:color-mix(in srgb, var(--primary) 30%, transparent);"></i>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($proj['proj_category'])): ?>
                                <span class="project-showcase-tag" style="background:<?= $projAccents[$i % count($projAccents)] ?>;"><?= htmlspecialchars($proj['proj_category']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="project-showcase-body">
                            <h3 class="project-showcase-title"><?= htmlspecialchars($proj['proj_title'] ?? '') ?></h3>
                            <p class="project-showcase-desc"><?= htmlspecialchars($proj['proj_desc'] ?? '') ?></p>
                            <?php if (!empty($proj['proj_result'])): ?>
                                <div class="project-showcase-result">
                                    <?php if (!empty($single['result_label'])): ?>
                                        <strong><?= htmlspecialchars($single['result_label']) ?></strong>
                                    <?php endif; ?>
                                    <?= htmlspecialchars($proj['proj_result']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($proj['proj_link'])): ?>
                            <a href="<?= htmlspecialchars(url($proj['proj_link'])) ?>" class="project-showcase-overlay-link" aria-label="Voir le projet"></a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if (!empty($single['more_text']) && !empty($single['more_url'])): ?>
            <div class="projects-showcase-more reveal">
                <a href="<?= htmlspecialchars(url($single['more_url'])) ?>" style="display:inline-flex;align-items:center;gap:8px;font-size:0.85rem;font-weight:700;color:var(--primary);letter-spacing:0.04em;">
                    <span><?= htmlspecialchars($single['more_text']) ?></span>
                    <i data-lucide="arrow-right" style="width:15px;height:15px;"></i>
                </a>
            </div>
        <?php endif; ?>

    </div>
</section>

<style>
.projects-showcase-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 28px;
}

.project-showcase-card { display: flex; flex-direction: column; }

.project-showcase-media { position: relative; height: 190px; overflow: hidden; }
.project-showcase-media img { width: 100%; height: 100%; object-fit: cover; transition: var(--transition); }
.project-showcase-card:hover .project-showcase-media img { transform: scale(1.05); }

.project-showcase-media-fallback {
    width: 100%; height: 100%;
    display: flex; align-items: center; justify-content: center;
    background: linear-gradient(135deg, color-mix(in srgb, var(--primary) 8%, transparent), color-mix(in srgb, var(--secondary) 5%, transparent));
}

.project-showcase-tag {
    position: absolute; top: 14px; left: 14px;
    padding: 4px 12px; border-radius: 100px;
    font-size: 0.62rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase;
    color: #fff;
    box-shadow: 0 4px 12px rgba(15,23,42,0.2);
}

.project-showcase-body { padding: 24px; flex: 1; }

.project-showcase-title {
    font-size: 1rem; font-weight: 700; color: var(--text-main);
    margin-bottom: 10px; font-family: var(--font-heading); line-height: 1.3;
}

.project-showcase-desc { font-size: 0.85rem; line-height: 1.6; color: var(--text-muted); margin-bottom: 16px; }

.project-showcase-result {
    font-size: 0.8rem; color: var(--primary); font-weight: 600;
    padding-top: 14px; border-top: 1px solid var(--border);
}
.project-showcase-result strong { color: var(--text-main); }

.project-showcase-overlay-link { position: absolute; inset: 0; z-index: 2; }

.projects-showcase-more { text-align: center; margin-top: 44px; }
</style>
