<?php
/**
 * Section : related_pages — les autres pages de la même rubrique
 *
 * Posée sur une sous-page (ex. /solutions/ia-automatisation), elle liste ses
 * pages sœurs : même parent, publiées, dans l'ordre d'affichage du CMS. Rien
 * n'est recopié — titre et résumé viennent de la fiche de chaque page
 * (`pages.title`, `pages.meta_description`). Une page ajoutée, renommée ou
 * dépubliée apparaît, change ou disparaît d'elle-même.
 *
 * Habillage administrable (Règle #2) :
 *   single : tag, title, subtitle,
 *            link_text  libellé du lien de chaque carte (vide = carte cliquable seule)
 *            more_text  bouton sous la grille (vide = pas de bouton)
 *            more_url   sa destination — par défaut la page parente
 *
 * Sur une page sans parent, ou sans page sœur publiée, la section n'est pas
 * rendue : jamais de bloc vide en ligne.
 */

use App\Models\Page;

$pageCourante = Page::findBySlug((string)($currentSlug ?? ''));
$parentSlug   = trim((string)($pageCourante['parent_slug'] ?? ''));
if ($parentSlug === '') {
    return;
}

$soeurs = array_values(array_filter(
    Page::childrenOf($parentSlug),
    static fn(array $p): bool => (int)$p['id'] !== (int)$pageCourante['id']
));
if (empty($soeurs)) {
    return;
}

$moreUrl = trim((string)($single['more_url'] ?? '')) !== '' ? $single['more_url'] : '/' . $parentSlug;

/* Les méta-descriptions se terminent souvent par « — Digitalium Group. », utile
   dans un résultat de recherche, redondant sur une carte du site lui-même. */
$nomSite = trim((string)($settings['site_name'] ?? ''));
$resume = static function (string $desc) use ($nomSite): string {
    $desc = trim($desc);
    if ($nomSite !== '') {
        $desc = preg_replace('/\s*[—–-]\s*' . preg_quote($nomSite, '/') . '\.?$/u', '.', $desc);
    }
    return $desc;
};
?>

<section class="section-padding related-pages-section" id="autres-<?= htmlspecialchars($parentSlug) ?>">
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

        <div class="related-pages-grid">
            <?php foreach ($soeurs as $i => $p):
                $lien = '/' . $parentSlug . '/' . $p['slug'];
            ?>
                <a href="<?= htmlspecialchars(url($lien)) ?>" class="related-page-card reveal"
                   style="transition-delay:<?= min($i * 60, 300) ?>ms;">
                    <span class="related-page-title"><?= htmlspecialchars($p['title']) ?></span>
                    <?php if (!empty($p['meta_description'])): ?>
                        <span class="related-page-desc"><?= htmlspecialchars($resume((string)$p['meta_description'])) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($single['link_text'])): ?>
                        <span class="related-page-link">
                            <span><?= htmlspecialchars($single['link_text']) ?></span>
                            <i data-lucide="arrow-right" style="width:15px;height:15px;"></i>
                        </span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($single['more_text'])): ?>
            <div class="related-pages-more reveal">
                <a href="<?= htmlspecialchars(url($moreUrl)) ?>" class="btn-outline related-pages-btn">
                    <?= htmlspecialchars($single['more_text']) ?>
                </a>
            </div>
        <?php endif; ?>

    </div>
</section>

<style>
.related-pages-section { background: var(--bg-alt); }
.related-pages-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 20px;
}
.related-page-card {
    display: flex;
    flex-direction: column;
    gap: 10px;
    padding: 26px 24px 24px;
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    text-decoration: none;
    transition: var(--transition);
}
.related-page-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-card-hover);
    border-color: color-mix(in srgb, var(--primary) 26%, transparent);
}
.related-page-title {
    font-family: var(--font-heading);
    font-size: 1.12rem;
    font-weight: 700;
    color: var(--text-main);
    line-height: 1.3;
}
.related-page-desc { font-size: 0.9rem; line-height: 1.6; color: var(--text-muted); flex: 1; }
.related-page-link {
    display: inline-flex; align-items: center; gap: 8px;
    margin-top: 6px;
    font-size: 0.9rem; font-weight: 600;
    color: var(--primary);
}
.related-pages-more { display: flex; justify-content: center; margin-top: 36px; }
.related-pages-btn { display: inline-flex; align-items: center; padding: 13px 28px; font-weight: 600; text-decoration: none; }
</style>
