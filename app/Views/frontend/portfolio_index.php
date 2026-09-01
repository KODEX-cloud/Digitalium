<?php
$settings = $settings ?? [];
?>
<style>
.portfolio-hero {
    padding: 120px 0 60px;
    text-align: center;
    position: relative;
    z-index: 2;
}
.portfolio-filter {
    display: flex; flex-wrap: wrap; gap: 10px; justify-content: center;
    margin-bottom: 48px;
}
.filter-btn {
    padding: 8px 20px; border-radius: 50px; font-size: 0.82rem; font-weight: 600;
    border: 1px solid var(--border); background: var(--bg-surface);
    color: var(--text-muted); cursor: pointer; text-decoration: none; transition: all 0.2s;
    letter-spacing: 0.02em;
}
.filter-btn:hover, .filter-btn.active {
    background: var(--primary); color: #fff; border-color: var(--primary);
}
.portfolio-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 28px;
}
.project-card {
    background: var(--bg-surface);
    border: 1px solid var(--border);
    border-radius: 20px;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    text-decoration: none;
    color: inherit;
    display: flex; flex-direction: column;
}
.project-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 24px 48px -12px rgba(37,99,235,0.12);
    border-color: rgba(37,99,235,0.25);
}
.project-image {
    width: 100%; aspect-ratio: 16/9; object-fit: cover;
    background: var(--border);
}
.project-image-placeholder {
    width: 100%; aspect-ratio: 16/9;
    background: linear-gradient(135deg, rgba(37,99,235,0.08) 0%, rgba(8,145,178,0.05) 100%);
    display: flex; align-items: center; justify-content: center;
}
.project-body { padding: 24px; flex: 1; display: flex; flex-direction: column; }
.project-category {
    font-size: 0.7rem; font-weight: 700; letter-spacing: 0.15em; text-transform: uppercase;
    color: var(--primary); margin-bottom: 8px;
}
.project-title { font-size: 1.15rem; font-weight: 700; margin-bottom: 8px; line-height: 1.3; }
.project-desc { font-size: 0.88rem; color: var(--text-muted); line-height: 1.65; flex: 1; }
.project-footer {
    padding: 16px 24px; border-top: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
}
.tech-tag {
    display: inline-flex; padding: 3px 10px; background: rgba(37,99,235,0.06);
    border: 1px solid rgba(37,99,235,0.15); border-radius: 4px;
    font-size: 0.7rem; font-weight: 600; color: var(--primary);
    margin-right: 4px; margin-bottom: 4px;
}
</style>

<section class="portfolio-hero">
    <div class="container">
        <div class="hero-tag reveal" style="display: inline-flex; align-items: center; gap: 8px; background: rgba(37,99,235,0.1); border: 1px solid rgba(37,99,235,0.25); padding: 0.35rem 1rem; border-radius: 50px; font-size: 0.68rem; letter-spacing: 0.2em; text-transform: uppercase; color: var(--accent); margin-bottom: 24px;">
            <div style="width: 6px; height: 6px; border-radius: 50%; background: var(--accent); animation: pulse 2s ease-in-out infinite;"></div>
            Portfolio
        </div>
        <h1 class="reveal" style="font-size: clamp(2rem, 4vw, 3.2rem); font-weight: 800; line-height: 1.15; margin-bottom: 16px;">
            Nos Réalisations
        </h1>
        <p class="reveal" style="font-size: 1.05rem; color: var(--text-muted); max-width: 560px; margin: 0 auto 32px;">
            Des projets digitaux ambitieux, conçus avec précision et livrés avec excellence.
        </p>

        <!-- Category Filter -->
        <?php if (!empty($categories)): ?>
        <div class="portfolio-filter reveal">
            <a href="<?= url('/realisations') ?>" class="filter-btn <?= empty($activeCategory) ? 'active' : '' ?>">
                Tous les projets
            </a>
            <?php foreach ($categories as $cat): ?>
            <a href="<?= url('/realisations?categorie=' . urlencode($cat)) ?>"
               class="filter-btn <?= $activeCategory === $cat ? 'active' : '' ?>">
                <?= htmlspecialchars($cat) ?>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<section style="padding: 0 0 100px; position: relative; z-index: 2;">
    <div class="container">
        <?php if (empty($projects)): ?>
            <div style="text-align: center; padding: 60px 20px; color: var(--text-muted);">
                <i data-lucide="briefcase" style="width: 48px; height: 48px; opacity: 0.3; display: block; margin: 0 auto 16px;"></i>
                <p style="font-size: 1.05rem;">Aucune réalisation dans cette catégorie pour le moment.</p>
                <a href="<?= url('/realisations') ?>" style="color: var(--primary); margin-top: 12px; display: inline-block;">Voir tous les projets →</a>
            </div>
        <?php else: ?>
        <div class="portfolio-grid">
            <?php foreach ($projects as $project):
                $projectSlug = $project['slug'] ?: 'projet-' . $project['id'];
                $techs = !empty($project['technologies']) ? array_slice(explode(',', $project['technologies']), 0, 4) : [];
                $desc = $project['description'] ?: $project['context'] ?: '';
            ?>
            <a href="<?= url('/realisations/' . htmlspecialchars($projectSlug)) ?>" class="project-card reveal">
                <?php if (!empty($project['main_image'])): ?>
                    <img src="<?= htmlspecialchars(url($project['main_image'])) ?>"
                         alt="<?= htmlspecialchars($project['title']) ?>"
                         class="project-image" loading="lazy">
                <?php else: ?>
                    <div class="project-image-placeholder">
                        <i data-lucide="image" style="width: 40px; height: 40px; color: var(--border);"></i>
                    </div>
                <?php endif; ?>
                <div class="project-body">
                    <div class="project-category">
                        <?= htmlspecialchars($project['category']) ?>
                        <?php if (!empty($project['client'])): ?>
                            · <?= htmlspecialchars($project['client']) ?>
                        <?php endif; ?>
                    </div>
                    <div class="project-title"><?= htmlspecialchars($project['title']) ?></div>
                    <?php if ($desc): ?>
                    <p class="project-desc"><?= htmlspecialchars(mb_substr(strip_tags($desc), 0, 140)) ?>...</p>
                    <?php endif; ?>
                </div>
                <?php if (!empty($techs) || !empty($project['project_date'])): ?>
                <div class="project-footer">
                    <div>
                        <?php foreach ($techs as $tech): ?>
                            <span class="tech-tag"><?= htmlspecialchars(trim($tech)) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php if (!empty($project['project_date'])): ?>
                    <span style="font-size: 0.78rem; color: var(--text-muted);">
                        <?= date('Y', strtotime($project['project_date'])) ?>
                    </span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>
