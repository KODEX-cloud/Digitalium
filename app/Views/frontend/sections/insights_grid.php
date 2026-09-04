<?php
/**
 * Section : insights_grid — Derniers articles, filtrables et paginés
 *
 * Les articles viennent EXCLUSIVEMENT du module Blog. Les catégories du filtre
 * viennent de `blog_categories`, gérées dans /admin/blog/categories : ajouter
 * une catégorie en admin l'ajoute ici, sans toucher au code (Règle #2).
 *
 * ── Pourquoi des liens et non du JavaScript ─────────────────────────────────
 * Filtres, recherche et pagination sont des liens et un formulaire GET. Une
 * vue filtrée a donc une adresse partageable, se met en favori, revient avec le
 * bouton « précédent » et reste lisible par un moteur de recherche — ce qui est
 * exactement l'objectif SEO de cette page. Un filtrage en JavaScript aurait
 * masqué tout le catalogue derrière une seule URL.
 *
 * ── Pagination et non chargement progressif ─────────────────────────────────
 * Le brief laisse le choix. Un « charger plus » ne crée aucune URL indexable
 * pour les articles au-delà du premier écran ; la pagination, si.
 *
 *   single : tag, title, subtitle,
 *            filter_all        libellé du filtre « Tous »
 *            show_filters      '0' pour masquer la barre de catégories
 *            show_search       '0' pour masquer la recherche
 *            search_label      intitulé du champ de recherche
 *            search_button     libellé du bouton
 *            per_page          articles par page (défaut 9)
 *            read_label        libellé du lien de chaque carte
 *            read_suffix       unité de durée de lecture
 *            empty_text        message quand aucun article ne sort
 *            reset_text        libellé du lien de réinitialisation
 *            count_label       gabarit du compteur, {n} remplacé par le nombre
 */

use App\Models\Post;
use App\Models\Category;

$base = url('/' . trim((string)($currentSlug ?? 'insights'), '/'));

$catActive = trim((string)($_GET['cat'] ?? ''));
$recherche = trim((string)($_GET['q'] ?? ''));
$pageNum   = max(1, (int)($_GET['page'] ?? 1));

$parPage = (int)trim((string)($single['per_page'] ?? ''));
$parPage = ($parPage >= 1 && $parPage <= 48) ? $parPage : 9;

/* Catégories du filtre : celles qui portent au moins un article publié. Une
   catégorie déclarée mais vide afficherait un filtre menant à une page vide. */
$categories = [];
foreach (Category::getAllWithCount() as $c) {
    if ((int)($c['post_count'] ?? 0) > 0) { $categories[] = $c; }
}

$exclure = isset($GLOBALS['insights_featured_id']) ? [(int)$GLOBALS['insights_featured_id']] : [];

[$articles, $total] = Post::rechercher(
    ['category' => $catActive, 'q' => $recherche, 'exclude' => $exclure],
    $parPage,
    ($pageNum - 1) * $parPage
);

$totalPages = max(1, (int)ceil($total / $parPage));

/** Reconstruit l'URL de la section en ne changeant que les clés fournies. */
$lienVers = static function (array $remplace) use ($base, $catActive, $recherche, $pageNum): string {
    $p = array_merge(['cat' => $catActive, 'q' => $recherche, 'page' => $pageNum], $remplace);
    if ((int)$p['page'] <= 1) { unset($p['page']); }
    $p = array_filter($p, static fn($v) => (string)$v !== '');
    return $base . ($p ? '?' . http_build_query($p) : '') . '#articles';
};

$filtre      = ($catActive !== '' || $recherche !== '');
$showFilters = (($single['show_filters'] ?? '1') !== '0') && count($categories) > 0;
$showSearch  = (($single['show_search'] ?? '1') !== '0');
$readLabel   = trim((string)($single['read_label'] ?? '')) ?: 'Lire';
$readSuffix  = trim((string)($single['read_suffix'] ?? '')) ?: 'min';
?>

<section class="section-padding ins-grid-sec" id="articles" style="background:var(--bg-alt);">
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

        <?php if ($showFilters): ?>
            <nav class="insg-filters reveal" aria-label="Filtrer par catégorie">
                <a class="insg-filter<?= $catActive === '' ? ' is-on' : '' ?>"
                   href="<?= htmlspecialchars($lienVers(['cat' => '', 'page' => 1])) ?>">
                    <?= htmlspecialchars(trim((string)($single['filter_all'] ?? '')) ?: 'Tous') ?>
                </a>
                <?php foreach ($categories as $c): ?>
                    <a class="insg-filter<?= $catActive === $c['name'] ? ' is-on' : '' ?>"
                       href="<?= htmlspecialchars($lienVers(['cat' => $c['name'], 'page' => 1])) ?>">
                        <?= htmlspecialchars($c['name']) ?>
                        <span class="insg-count"><?= (int)$c['post_count'] ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>

        <?php if ($showSearch): ?>
            <form class="insg-search reveal" method="get" action="<?= htmlspecialchars($base) ?>" role="search">
                <?php if ($catActive !== ''): ?>
                    <input type="hidden" name="cat" value="<?= htmlspecialchars($catActive, ENT_QUOTES) ?>">
                <?php endif; ?>
                <label class="insg-search-label" for="insgQ">
                    <?= htmlspecialchars(trim((string)($single['search_label'] ?? '')) ?: 'Rechercher une ressource') ?>
                </label>
                <div class="insg-search-row">
                    <i data-lucide="search" style="width:17px;height:17px;" aria-hidden="true"></i>
                    <input type="search" id="insgQ" name="q"
                           value="<?= htmlspecialchars($recherche, ENT_QUOTES) ?>"
                           placeholder="<?= htmlspecialchars(trim((string)($single['search_placeholder'] ?? '')) ?: 'Un sujet, une technologie, un secteur…', ENT_QUOTES) ?>">
                    <button type="submit">
                        <?= htmlspecialchars(trim((string)($single['search_button'] ?? '')) ?: 'Rechercher') ?>
                    </button>
                </div>
            </form>
        <?php endif; ?>

        <?php if ($filtre): ?>
            <p class="insg-summary reveal">
                <?php
                $gabarit = trim((string)($single['count_label'] ?? '')) ?: '{n} résultat(s)';
                echo htmlspecialchars(str_replace('{n}', (string)$total, $gabarit));
                ?>
                <a href="<?= htmlspecialchars($base . '#articles') ?>">
                    <?= htmlspecialchars(trim((string)($single['reset_text'] ?? '')) ?: 'Tout afficher') ?>
                </a>
            </p>
        <?php endif; ?>

        <?php if (empty($articles)): ?>
            <div class="insg-empty">
                <span class="insg-empty-icon" aria-hidden="true">
                    <i data-lucide="search-x" style="width:26px;height:26px;"></i>
                </span>
                <p><?= htmlspecialchars(trim((string)($single['empty_text'] ?? '')) ?: 'Aucune ressource ne correspond à cette recherche.') ?></p>
                <?php if ($filtre): ?>
                    <a class="insg-empty-btn" href="<?= htmlspecialchars($base . '#articles') ?>">
                        <?= htmlspecialchars(trim((string)($single['reset_text'] ?? '')) ?: 'Tout afficher') ?>
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="insg-grid">
                <?php foreach ($articles as $i => $a):
                    $lien  = url('/insights/' . ($a['slug'] ?? ''));
                    $duree = Post::dureeLecture($a);
                ?>
                    <article class="insg-card reveal" style="transition-delay:<?= min($i * 55, 330) ?>ms;">
                        <a class="insg-media" href="<?= htmlspecialchars($lien) ?>" tabindex="-1" aria-hidden="true">
                            <?php if (!empty($a['featured_image'])): ?>
                                <img src="<?= htmlspecialchars(url($a['featured_image'])) ?>"
                                     alt="" loading="lazy" decoding="async">
                            <?php else: ?>
                                <span class="insg-placeholder">
                                    <i data-lucide="file-text" style="width:26px;height:26px;"></i>
                                </span>
                            <?php endif; ?>
                        </a>

                        <div class="insg-body">
                            <?php if (!empty($a['category'])): ?>
                                <span class="insg-cat"><?= htmlspecialchars($a['category']) ?></span>
                            <?php endif; ?>

                            <h3 class="insg-title">
                                <a href="<?= htmlspecialchars($lien) ?>"><?= htmlspecialchars($a['title'] ?? '') ?></a>
                            </h3>

                            <?php if (!empty($a['excerpt'])): ?>
                                <p class="insg-excerpt"><?= htmlspecialchars($a['excerpt']) ?></p>
                            <?php endif; ?>

                            <div class="insg-meta">
                                <span><?= htmlspecialchars((string)($a['author'] ?? '')) ?></span>
                                <?php if (!empty($a['published_at'])): ?>
                                    <time datetime="<?= htmlspecialchars(date('Y-m-d', strtotime($a['published_at']))) ?>">
                                        <?= htmlspecialchars(date('d/m/Y', strtotime($a['published_at']))) ?>
                                    </time>
                                <?php endif; ?>
                                <?php if ($duree > 0): ?>
                                    <span><?= (int)$duree ?> <?= htmlspecialchars($readSuffix) ?></span>
                                <?php endif; ?>
                            </div>

                            <a class="insg-link" href="<?= htmlspecialchars($lien) ?>">
                                <span><?= htmlspecialchars($readLabel) ?></span>
                                <i data-lucide="arrow-right" style="width:15px;height:15px;"></i>
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <?php if ($totalPages > 1): ?>
                <nav class="insg-pager" aria-label="Pagination des ressources">
                    <?php if ($pageNum > 1): ?>
                        <a class="insg-page" rel="prev" href="<?= htmlspecialchars($lienVers(['page' => $pageNum - 1])) ?>">
                            <i data-lucide="chevron-left" style="width:15px;height:15px;"></i>
                        </a>
                    <?php endif; ?>

                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <?php if ($p === $pageNum): ?>
                            <span class="insg-page is-on" aria-current="page"><?= $p ?></span>
                        <?php else: ?>
                            <a class="insg-page" href="<?= htmlspecialchars($lienVers(['page' => $p])) ?>"><?= $p ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($pageNum < $totalPages): ?>
                        <a class="insg-page" rel="next" href="<?= htmlspecialchars($lienVers(['page' => $pageNum + 1])) ?>">
                            <i data-lucide="chevron-right" style="width:15px;height:15px;"></i>
                        </a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>

    </div>
</section>

<style>
.insg-filters { display: flex; flex-wrap: wrap; justify-content: center; gap: 10px; margin-bottom: 26px; }
.insg-filter {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 9px 20px;
    border-radius: 999px;
    border: 1.5px solid var(--border);
    background: var(--bg-card);
    color: var(--text-muted);
    font-family: var(--font-heading);
    font-size: 0.86rem; font-weight: 600;
    text-decoration: none;
    transition: var(--transition);
}
.insg-filter:hover { color: var(--primary); border-color: color-mix(in srgb, var(--primary) 35%, transparent); }
.insg-filter.is-on { background: var(--primary); border-color: var(--primary); color: #ffffff; }
.insg-count { font-size: 0.74rem; opacity: 0.72; }

.insg-search { max-width: 620px; margin: 0 auto 30px; }
.insg-search-label {
    display: block; text-align: center;
    font-size: 0.74rem; font-weight: 700;
    letter-spacing: 0.1em; text-transform: uppercase;
    color: var(--text-muted); margin-bottom: 10px;
}
.insg-search-row {
    display: flex; align-items: center; gap: 10px;
    padding: 6px 6px 6px 18px;
    background: var(--bg-card);
    border: 1.5px solid var(--border);
    border-radius: 999px;
}
.insg-search-row:focus-within { border-color: color-mix(in srgb, var(--primary) 45%, transparent); }
.insg-search-row i { color: var(--text-muted); flex-shrink: 0; }
.insg-search-row input {
    flex: 1; min-width: 0;
    border: 0; outline: 0; background: transparent;
    font-family: inherit; font-size: 0.92rem; color: var(--text-main);
    padding: 10px 0;
}
.insg-search-row button {
    flex-shrink: 0;
    padding: 11px 24px;
    border: 0; border-radius: 999px;
    background: var(--primary); color: #ffffff;
    font-family: var(--font-heading); font-size: 0.85rem; font-weight: 650;
    cursor: pointer; transition: var(--transition);
}
.insg-search-row button:hover { box-shadow: var(--shadow-btn); }

.insg-summary { text-align: center; font-size: 0.87rem; color: var(--text-muted); margin: 0 0 26px; }
.insg-summary a { color: var(--primary); font-weight: 650; text-decoration: none; margin-left: 10px; }
.insg-summary a:hover { text-decoration: underline; }

.insg-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 26px; }

.insg-card {
    display: flex; flex-direction: column;
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    overflow: hidden;
    transition: var(--transition);
}
.insg-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-card); }
.insg-media {
    position: relative; display: block;
    aspect-ratio: 16 / 9;
    background: color-mix(in srgb, var(--primary) 7%, transparent);
}
.insg-media img { width: 100%; height: 100%; object-fit: cover; display: block; }
.insg-placeholder {
    position: absolute; inset: 0;
    display: flex; align-items: center; justify-content: center;
    color: var(--primary); opacity: 0.32;
}
.insg-body { display: flex; flex-direction: column; gap: 11px; padding: 24px 24px 22px; flex: 1; }
.insg-cat {
    font-size: 0.69rem; font-weight: 700;
    letter-spacing: 0.13em; text-transform: uppercase;
    color: var(--primary);
}
.insg-title { margin: 0; font-family: var(--font-heading); font-size: 1.08rem; font-weight: 750; line-height: 1.36; }
.insg-title a { color: var(--text-main); text-decoration: none; transition: var(--transition); }
.insg-title a:hover { color: var(--primary); }
.insg-excerpt { margin: 0; font-size: 0.89rem; line-height: 1.68; color: var(--text-muted); flex: 1; }
.insg-meta {
    display: flex; flex-wrap: wrap; gap: 4px 14px;
    font-size: 0.77rem; color: var(--text-muted);
    padding-top: 13px; border-top: 1px solid var(--border);
}
.insg-link {
    display: inline-flex; align-items: center; gap: 7px;
    color: var(--primary); font-size: 0.86rem; font-weight: 650;
    text-decoration: none; transition: var(--transition);
}
.insg-link:hover { gap: 11px; }

.insg-empty {
    display: flex; flex-direction: column; align-items: center; gap: 14px;
    padding: 60px 24px;
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    text-align: center;
}
.insg-empty p { margin: 0; color: var(--text-muted); font-size: 1rem; }
.insg-empty-icon {
    display: inline-flex; align-items: center; justify-content: center;
    width: 58px; height: 58px; border-radius: 50%;
    background: color-mix(in srgb, var(--primary) 10%, transparent);
    color: var(--primary);
}
.insg-empty-btn {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 11px 24px; border-radius: 999px;
    background: var(--primary); color: #ffffff;
    font-size: 0.87rem; font-weight: 650; text-decoration: none;
}

.insg-pager { display: flex; flex-wrap: wrap; justify-content: center; gap: 8px; margin-top: 42px; }
.insg-page {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 42px; height: 42px; padding: 0 13px;
    border: 1.5px solid var(--border); border-radius: 12px;
    background: var(--bg-card); color: var(--text-main);
    font-size: 0.88rem; font-weight: 650; text-decoration: none;
    transition: var(--transition);
}
.insg-page:hover { border-color: color-mix(in srgb, var(--primary) 40%, transparent); color: var(--primary); }
.insg-page.is-on { background: var(--primary); border-color: var(--primary); color: #ffffff; }

@media (max-width: 1000px) { .insg-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (max-width: 660px) {
    .insg-grid { grid-template-columns: 1fr; }
    .insg-search-row { flex-wrap: wrap; border-radius: var(--radius-md); padding: 12px; }
    .insg-search-row input { width: 100%; }
    .insg-search-row button { width: 100%; }
}
</style>
