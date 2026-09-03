<style>
.project-hero-section {
    padding: 120px 0 60px;
    position: relative;
    z-index: 2;
}
.breadcrumb {
    display: flex; align-items: center; gap: 8px; margin-bottom: 24px;
    font-size: 0.82rem; color: var(--text-muted);
}
.breadcrumb a { color: var(--primary); text-decoration: none; }
.breadcrumb a:hover { text-decoration: underline; }

.project-detail-grid {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 40px;
    align-items: start;
    padding-bottom: 80px;
}
@media (max-width: 900px) { .project-detail-grid { grid-template-columns: 1fr; } }

.project-main-image {
    width: 100%; border-radius: 20px; object-fit: cover;
    aspect-ratio: 16/9; margin-bottom: 32px;
    border: 1px solid var(--border);
}
.gallery-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 12px; margin-top: 24px;
}
.gallery-img {
    border-radius: 10px; width: 100%; aspect-ratio: 4/3;
    object-fit: cover; border: 1px solid var(--border);
    cursor: pointer; transition: transform 0.2s;
}
.gallery-img:hover { transform: scale(1.03); }

.info-card {
    background: var(--bg-surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 20px;
}
.info-row { display: flex; gap: 10px; padding: 10px 0; border-bottom: 1px solid var(--border); align-items: flex-start; }
.info-row:last-child { border-bottom: none; }
.info-label { font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--text-muted); flex-shrink: 0; min-width: 90px; padding-top: 2px; }
.info-value { font-size: 0.9rem; color: var(--text-main); font-weight: 500; }

.tech-tag { display: inline-flex; padding: 3px 10px; background: color-mix(in srgb, var(--primary) 6%, transparent); border: 1px solid color-mix(in srgb, var(--primary) 15%, transparent); border-radius: 4px; font-size: 0.72rem; font-weight: 600; color: var(--primary); margin: 2px; }

.project-section { margin-bottom: 36px; }
.project-section h2 { font-size: 1.2rem; font-weight: 700; margin-bottom: 14px; padding-bottom: 10px; border-bottom: 1px solid var(--border); }
.project-section p, .project-section div { font-size: 0.97rem; line-height: 1.85; color: var(--text-muted); }

.related-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
.related-card { background: var(--bg-surface); border: 1px solid var(--border); border-radius: 16px; overflow: hidden; text-decoration: none; color: inherit; transition: all 0.25s; display: flex; flex-direction: column; }
.related-card:hover { transform: translateY(-4px); box-shadow: 0 16px 32px -8px color-mix(in srgb, var(--primary) 10%, transparent); border-color: color-mix(in srgb, var(--primary) 20%, transparent); }
.related-card img { width: 100%; aspect-ratio: 16/9; object-fit: cover; }
.related-card-body { padding: 16px; }
</style>

<section class="project-hero-section">
    <div class="container">
        <div class="breadcrumb">
            <a href="<?= url('/') ?>">Accueil</a>
            <i data-lucide="chevron-right" style="width:13px;height:13px;"></i>
            <a href="<?= url('/realisations') ?>">Réalisations</a>
            <i data-lucide="chevron-right" style="width:13px;height:13px;"></i>
            <span><?= htmlspecialchars($project['title']) ?></span>
        </div>

        <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap; margin-bottom: 12px;">
            <?php if (!empty($project['category'])): ?>
                <span style="background: color-mix(in srgb, var(--primary) 10%, transparent); color: var(--primary); padding: 4px 14px; border-radius: 50px; font-size: 0.72rem; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; border: 1px solid color-mix(in srgb, var(--primary) 20%, transparent);">
                    <?= htmlspecialchars($project['category']) ?>
                </span>
            <?php endif; ?>
            <?php if (!empty($project['project_date'])): ?>
                <span style="font-size: 0.82rem; color: var(--text-muted);">
                    <i data-lucide="calendar" style="width:13px;height:13px;vertical-align:middle;margin-right:4px;"></i>
                    <?= date('F Y', strtotime($project['project_date'])) ?>
                </span>
            <?php endif; ?>
        </div>
        <h1 style="font-size: clamp(1.8rem, 3.5vw, 2.8rem); font-weight: 800; line-height: 1.15; margin-bottom: 16px;">
            <?= htmlspecialchars($project['title']) ?>
        </h1>
    </div>
</section>

<section style="position: relative; z-index: 2;">
    <div class="container">
        <div class="project-detail-grid">

            <!-- Left: content -->
            <div>
                <?php if (!empty($project['main_image'])): ?>
                    <img src="<?= htmlspecialchars(url($project['main_image'])) ?>"
                         alt="<?= htmlspecialchars($project['title']) ?>"
                         class="project-main-image">
                <?php endif; ?>

                <?php if (!empty($project['description'])): ?>
                <div class="project-section">
                    <h2>Description du projet</h2>
                    <div><?= nl2br(htmlspecialchars($project['description'])) ?></div>
                </div>
                <?php endif; ?>

                <?php if (!empty($project['context'])): ?>
                <div class="project-section">
                    <h2>Contexte</h2>
                    <div><?= nl2br(htmlspecialchars($project['context'])) ?></div>
                </div>
                <?php endif; ?>

                <?php if (!empty($project['impact'])): ?>
                <div class="project-section">
                    <h2>Impact & Résultats</h2>
                    <div><?= nl2br(htmlspecialchars($project['impact'])) ?></div>
                </div>
                <?php endif; ?>

                <?php
                // Gallery
                $galleryImages = [];
                if (!empty($project['gallery'])) {
                    $galleryImages = array_filter(array_map('trim', explode(',', $project['gallery'])));
                }
                if (!empty($galleryImages)): ?>
                <div class="project-section">
                    <h2>Galerie</h2>
                    <div class="gallery-grid">
                        <?php foreach ($galleryImages as $img): ?>
                            <img src="<?= htmlspecialchars(url($img)) ?>"
                                 alt="Galerie — <?= htmlspecialchars($project['title']) ?>"
                                 class="gallery-img" loading="lazy">
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Right: info sidebar -->
            <div>
                <?php if (!empty($project['logo'])): ?>
                <div class="info-card" style="text-align: center; padding: 20px;">
                    <img src="<?= htmlspecialchars(url($project['logo'])) ?>"
                         alt="Logo client" style="max-height: 60px; max-width: 180px; object-fit: contain;">
                </div>
                <?php endif; ?>

                <div class="info-card">
                    <?php if (!empty($project['client'])): ?>
                    <div class="info-row">
                        <span class="info-label"><i data-lucide="user" style="width:13px;height:13px;vertical-align:middle;"></i> Client</span>
                        <span class="info-value"><?= htmlspecialchars($project['client']) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($project['category'])): ?>
                    <div class="info-row">
                        <span class="info-label"><i data-lucide="tag" style="width:13px;height:13px;vertical-align:middle;"></i> Secteur</span>
                        <span class="info-value"><?= htmlspecialchars($project['category']) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($project['project_date'])): ?>
                    <div class="info-row">
                        <span class="info-label"><i data-lucide="calendar" style="width:13px;height:13px;vertical-align:middle;"></i> Date</span>
                        <span class="info-value"><?= date('M Y', strtotime($project['project_date'])) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($project['technologies'])): ?>
                    <div class="info-row">
                        <span class="info-label"><i data-lucide="cpu" style="width:13px;height:13px;vertical-align:middle;"></i> Stack</span>
                        <div>
                            <?php foreach (array_map('trim', explode(',', $project['technologies'])) as $tech): ?>
                                <span class="tech-tag"><?= htmlspecialchars($tech) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($project['external_link'])): ?>
                <a href="<?= htmlspecialchars($project['external_link']) ?>"
                   target="_blank" rel="noopener"
                   class="btn-primary" style="width: 100%; justify-content: center; margin-bottom: 12px; text-decoration: none;">
                    <i data-lucide="external-link" style="width:16px;height:16px;"></i>
                    <span>Voir le projet en ligne</span>
                </a>
                <?php endif; ?>

                <a href="<?= url('/contact') ?>" class="btn-secondary" style="width: 100%; justify-content: center; text-decoration: none;">
                    <i data-lucide="mail" style="width:16px;height:16px;"></i>
                    <span>Discuter d'un projet similaire</span>
                </a>
            </div>
        </div>

        <!-- Related projects -->
        <?php if (!empty($related)): ?>
        <div style="padding-bottom: 80px; border-top: 1px solid var(--border); padding-top: 48px;">
            <h2 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 28px; text-align: center;">Projets similaires</h2>
            <div class="related-grid">
                <?php foreach ($related as $rel):
                    $relSlug = $rel['slug'] ?: 'projet-' . $rel['id'];
                ?>
                <a href="<?= url('/realisations/' . htmlspecialchars($relSlug)) ?>" class="related-card">
                    <?php if (!empty($rel['main_image'])): ?>
                        <img src="<?= htmlspecialchars(url($rel['main_image'])) ?>" alt="<?= htmlspecialchars($rel['title']) ?>" loading="lazy">
                    <?php endif; ?>
                    <div class="related-card-body">
                        <div style="font-size: 0.7rem; color: var(--primary); font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; margin-bottom: 6px;"><?= htmlspecialchars($rel['category']) ?></div>
                        <div style="font-weight: 700; font-size: 0.95rem;"><?= htmlspecialchars($rel['title']) ?></div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>
