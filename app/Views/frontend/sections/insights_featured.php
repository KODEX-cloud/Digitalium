<?php
/**
 * Section : insights_featured — Article à la une
 *
 * L'article vient EXCLUSIVEMENT du module Blog : c'est celui coché « À la une »
 * dans /admin/blog. Aucun titre, auteur ni date n'est écrit ici. Si aucun
 * article n'est coché, la section retombe sur le plus récent (réglable), et si
 * la base est vide elle ne s'affiche pas du tout plutôt que d'exposer un cadre
 * creux.
 *
 * Seuls les habillages sont des blocs CMS — Règle #2 :
 *   single : tag, title, subtitle,
 *            badge_label     pastille posée sur l'article (ex. « À la une »)
 *            cta_text        libellé du bouton
 *            read_suffix     unité de la durée de lecture (ex. « min de lecture »)
 *            fallback_latest '0' pour n'afficher QUE les articles cochés
 *
 * L'identifiant de l'article affiché est publié dans $GLOBALS pour que la
 * grille qui suit ne le répète pas. Si cette section est désactivée en admin,
 * la variable n'existe pas et la grille montre simplement tout.
 */

use App\Models\Post;

$aLaUne = Post::getFeatured(1)[0] ?? null;

if (!$aLaUne && (($single['fallback_latest'] ?? '1') !== '0')) {
    $aLaUne = Post::getPublished(1, 0)[0] ?? null;
}

if ($aLaUne) {
    $GLOBALS['insights_featured_id'] = (int)$aLaUne['id'];
}

$lien       = url('/insights/' . ($aLaUne['slug'] ?? ''));
$duree      = $aLaUne ? Post::dureeLecture($aLaUne) : 0;
$readSuffix = trim((string)($single['read_suffix'] ?? '')) ?: 'min de lecture';
$ctaText    = trim((string)($single['cta_text'] ?? '')) ?: 'Lire l’analyse';
?>

<?php if ($aLaUne): ?>
<section class="section-padding ins-featured" id="a-la-une" style="background:var(--bg-base);">
    <div class="container">

        <?php if (!empty($single['tag']) || !empty($single['title']) || !empty($single['subtitle'])): ?>
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
        <?php endif; ?>

        <article class="insf-card reveal">
            <a class="insf-media" href="<?= htmlspecialchars($lien) ?>"
               aria-label="<?= htmlspecialchars($aLaUne['title'] ?? '') ?>">
                <?php if (!empty($aLaUne['featured_image'])): ?>
                    <img src="<?= htmlspecialchars(url($aLaUne['featured_image'])) ?>"
                         alt="<?= htmlspecialchars($aLaUne['title'] ?? '') ?>"
                         loading="lazy" decoding="async">
                <?php else: ?>
                    <span class="insf-placeholder" aria-hidden="true">
                        <i data-lucide="newspaper" style="width:34px;height:34px;"></i>
                    </span>
                <?php endif; ?>
                <?php if (!empty($single['badge_label'])): ?>
                    <span class="insf-badge"><?= htmlspecialchars($single['badge_label']) ?></span>
                <?php endif; ?>
            </a>

            <div class="insf-body">
                <?php if (!empty($aLaUne['category'])): ?>
                    <span class="insf-cat"><?= htmlspecialchars($aLaUne['category']) ?></span>
                <?php endif; ?>

                <h3 class="insf-title">
                    <a href="<?= htmlspecialchars($lien) ?>"><?= htmlspecialchars($aLaUne['title'] ?? '') ?></a>
                </h3>

                <?php if (!empty($aLaUne['excerpt'])): ?>
                    <p class="insf-excerpt"><?= htmlspecialchars($aLaUne['excerpt']) ?></p>
                <?php endif; ?>

                <div class="insf-meta">
                    <span class="insf-author">
                        <i data-lucide="user" style="width:14px;height:14px;"></i>
                        <?= htmlspecialchars((string)($aLaUne['author'] ?? '')) ?>
                    </span>
                    <?php if (!empty($aLaUne['published_at'])): ?>
                        <time datetime="<?= htmlspecialchars(date('Y-m-d', strtotime($aLaUne['published_at']))) ?>">
                            <i data-lucide="calendar" style="width:14px;height:14px;"></i>
                            <?= htmlspecialchars(date('d/m/Y', strtotime($aLaUne['published_at']))) ?>
                        </time>
                    <?php endif; ?>
                    <?php if ($duree > 0): ?>
                        <span>
                            <i data-lucide="clock" style="width:14px;height:14px;"></i>
                            <?= (int)$duree ?> <?= htmlspecialchars($readSuffix) ?>
                        </span>
                    <?php endif; ?>
                </div>

                <a class="insf-btn" href="<?= htmlspecialchars($lien) ?>">
                    <span><?= htmlspecialchars($ctaText) ?></span>
                    <i data-lucide="arrow-right" style="width:16px;height:16px;"></i>
                </a>
            </div>
        </article>

    </div>
</section>

<style>
.insf-card {
    display: grid;
    grid-template-columns: 1.05fr 0.95fr;
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow-card);
}
.insf-media {
    position: relative;
    display: block;
    min-height: 340px;
    background: color-mix(in srgb, var(--primary) 7%, transparent);
}
.insf-media img { width: 100%; height: 100%; object-fit: cover; display: block; }
.insf-placeholder {
    position: absolute; inset: 0;
    display: flex; align-items: center; justify-content: center;
    color: var(--primary); opacity: 0.35;
}
.insf-badge {
    position: absolute; top: 18px; left: 18px;
    padding: 6px 15px;
    border-radius: 999px;
    background: var(--primary);
    color: #ffffff;
    font-size: 0.7rem; font-weight: 700;
    letter-spacing: 0.09em; text-transform: uppercase;
}
.insf-body {
    display: flex; flex-direction: column;
    justify-content: center;
    gap: 15px;
    padding: 44px 46px;
}
.insf-cat {
    align-self: flex-start;
    font-size: 0.72rem; font-weight: 700;
    letter-spacing: 0.13em; text-transform: uppercase;
    color: var(--primary);
}
.insf-title { margin: 0; font-family: var(--font-heading); font-size: clamp(1.4rem, 2.4vw, 2rem); line-height: 1.22; font-weight: 800; }
.insf-title a { color: var(--text-main); text-decoration: none; transition: var(--transition); }
.insf-title a:hover { color: var(--primary); }
.insf-excerpt { margin: 0; color: var(--text-muted); font-size: 1rem; line-height: 1.72; }
.insf-meta {
    display: flex; flex-wrap: wrap; gap: 8px 20px;
    font-size: 0.82rem; color: var(--text-muted);
}
.insf-meta > * { display: inline-flex; align-items: center; gap: 6px; }
.insf-btn {
    align-self: flex-start;
    display: inline-flex; align-items: center; gap: 9px;
    margin-top: 6px;
    padding: 13px 28px;
    border-radius: 999px;
    background: var(--primary);
    color: #ffffff;
    font-size: 0.9rem; font-weight: 650;
    text-decoration: none;
    transition: var(--transition);
}
.insf-btn:hover { transform: translateY(-2px); gap: 13px; box-shadow: var(--shadow-btn); }

@media (max-width: 900px) {
    .insf-card { grid-template-columns: 1fr; }
    .insf-media { min-height: 220px; aspect-ratio: 16 / 9; }
    .insf-body { padding: 30px 26px; }
}
</style>
<?php endif; ?>
