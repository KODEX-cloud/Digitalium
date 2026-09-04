<?php
/**
 * Section : projects_cms — Grille des réalisations, filtrable
 *
 * Les projets viennent EXCLUSIVEMENT du module Réalisations (table `projects`).
 * Aucun projet, client, chiffre ou résultat n'est écrit ici : si la table est
 * vide, la section affiche le message d'attente saisi en admin.
 *
 * Les habillages (titres, libellés de filtres, textes de repli) sont eux des
 * blocs CMS — Règle #2, rien n'est codé en dur.
 *
 *   single : tag, title, subtitle,
 *            filter_all      libellé du filtre « Tous »
 *            empty_text      message affiché quand aucune réalisation ne sort
 *            cta_text        libellé du bouton d'une carte
 *            show_filters    '0' pour masquer la barre de filtres
 *   groups : cat_value  valeur exacte de la catégorie enregistrée sur le projet
 *            cat_label  libellé affiché à la place (facultatif)
 *
 * Si aucun groupe n'est saisi, les filtres sont déduits des catégories
 * réellement présentes en base : la barre reste juste sans administration.
 */

use App\Models\Project;

$allProjects = Project::getPublic();

/* Catégories : celles déclarées en admin d'abord (ordre maîtrisé), sinon
   celles réellement présentes en base. Une catégorie déclarée mais qu'aucun
   projet n'utilise est masquée — un filtre vide n'a pas d'intérêt. */
$usedCats = [];
foreach ($allProjects as $p) {
    $c = trim((string)($p['category'] ?? ''));
    if ($c !== '') { $usedCats[$c] = true; }
}

$filters = [];
foreach (($groups ?? []) as $g) {
    $value = trim((string)($g['cat_value'] ?? ''));
    if ($value === '' || !isset($usedCats[$value])) { continue; }
    $filters[$value] = trim((string)($g['cat_label'] ?? '')) ?: $value;
    unset($usedCats[$value]);
}
foreach (array_keys($usedCats) as $leftover) {
    $filters[$leftover] = $leftover;
}

$showFilters = (($single['show_filters'] ?? '1') !== '0') && count($filters) > 1;
$ctaText     = trim((string)($single['cta_text'] ?? ''));
?>

<section class="section-padding projects-cms" id="projets" style="background:var(--bg-base);">
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

        <?php if ($showFilters): ?>
            <div class="pj-filters reveal" role="group" aria-label="Filtrer les réalisations">
                <button type="button" class="pj-filter active" data-cat="*">
                    <?= htmlspecialchars(trim((string)($single['filter_all'] ?? '')) ?: 'Tous') ?>
                </button>
                <?php foreach ($filters as $value => $label): ?>
                    <button type="button" class="pj-filter" data-cat="<?= htmlspecialchars($value) ?>">
                        <?= htmlspecialchars($label) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($allProjects)): ?>
            <?php if (!empty($single['empty_text'])): ?>
                <p class="pj-empty"><?= htmlspecialchars($single['empty_text']) ?></p>
            <?php endif; ?>
        <?php else: ?>
            <div class="pj-grid" id="pjGrid">
                <?php foreach ($allProjects as $i => $p):
                    $techs = array_slice(Project::toList($p['technologies'] ?? null), 0, 4);
                    $url   = url('/realisations/' . ($p['slug'] ?? ''));
                ?>
                    <article class="pj-card reveal"
                             data-cat="<?= htmlspecialchars((string)($p['category'] ?? '')) ?>"
                             style="transition-delay:<?= min($i * 55, 330) ?>ms;">

                        <?php if (!empty($p['main_image'])): ?>
                            <a class="pj-media" href="<?= htmlspecialchars($url) ?>">
                                <img src="<?= htmlspecialchars(url($p['main_image'])) ?>"
                                     alt="<?= htmlspecialchars($p['title'] ?? '') ?>"
                                     loading="lazy" decoding="async">
                                <?php if (!empty($p['category'])): ?>
                                    <span class="pj-cat"><?= htmlspecialchars($p['category']) ?></span>
                                <?php endif; ?>
                            </a>
                        <?php endif; ?>

                        <div class="pj-body">
                            <?php if (!empty($p['client']) || !empty($p['sector']) || !empty($p['year'])): ?>
                                <div class="pj-meta">
                                    <?php if (!empty($p['client'])): ?><span><?= htmlspecialchars($p['client']) ?></span><?php endif; ?>
                                    <?php if (!empty($p['sector'])): ?><span><?= htmlspecialchars($p['sector']) ?></span><?php endif; ?>
                                    <?php if (!empty($p['year'])): ?><span><?= htmlspecialchars($p['year']) ?></span><?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <h3 class="pj-title">
                                <a href="<?= htmlspecialchars($url) ?>"><?= htmlspecialchars($p['title'] ?? '') ?></a>
                            </h3>

                            <?php if (!empty($p['description'])): ?>
                                <p class="pj-desc"><?= htmlspecialchars($p['description']) ?></p>
                            <?php endif; ?>

                            <?php if (!empty($techs)): ?>
                                <ul class="pj-techs">
                                    <?php foreach ($techs as $t): ?>
                                        <li><?= htmlspecialchars($t) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>

                            <?php if ($ctaText !== ''): ?>
                                <a class="pj-link" href="<?= htmlspecialchars($url) ?>">
                                    <span><?= htmlspecialchars($ctaText) ?></span>
                                    <i data-lucide="arrow-right" style="width:15px;height:15px;"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <?php if (!empty($single['empty_text'])): ?>
                <p class="pj-empty" id="pjEmpty" hidden><?= htmlspecialchars($single['empty_text']) ?></p>
            <?php endif; ?>
        <?php endif; ?>

    </div>
</section>

<style>
.pj-filters {
    display: flex; flex-wrap: wrap; justify-content: center;
    gap: 10px;
    margin-bottom: 40px;
}
.pj-filter {
    padding: 9px 20px;
    border-radius: 999px;
    border: 1.5px solid var(--border);
    background: var(--bg-card);
    color: var(--text-muted);
    font-family: var(--font-heading);
    font-size: 0.86rem;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
}
.pj-filter:hover {
    color: var(--primary);
    border-color: color-mix(in srgb, var(--primary) 35%, transparent);
}
.pj-filter.active {
    background: var(--primary);
    border-color: var(--primary);
    color: #ffffff;
}

.pj-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 26px;
}

.pj-card {
    display: flex;
    flex-direction: column;
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    overflow: hidden;
    transition: var(--transition);
}
.pj-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-card-hover);
    border-color: color-mix(in srgb, var(--primary) 24%, transparent);
}

.pj-media {
    position: relative;
    display: block;
    aspect-ratio: 16 / 10;
    overflow: hidden;
    background: var(--bg-alt);
}
.pj-media img {
    width: 100%; height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}
.pj-card:hover .pj-media img { transform: scale(1.04); }

.pj-cat {
    position: absolute; top: 12px; left: 12px;
    padding: 5px 12px;
    border-radius: 999px;
    background: rgba(255,255,255,0.92);
    color: var(--primary);
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.03em;
}

.pj-body { display: flex; flex-direction: column; gap: 9px; padding: 20px 22px 22px; flex: 1; }

.pj-meta {
    display: flex; flex-wrap: wrap; gap: 6px 14px;
    font-size: 0.76rem;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: 600;
}
.pj-meta span + span { position: relative; padding-left: 14px; }
.pj-meta span + span::before {
    content: ""; position: absolute; left: 0; top: 50%;
    width: 4px; height: 4px; border-radius: 50%;
    background: color-mix(in srgb, var(--primary) 45%, transparent);
    transform: translateY(-50%);
}

.pj-title { font-size: 1.16rem; font-weight: 700; line-height: 1.3; margin: 0; font-family: var(--font-heading); }
.pj-title a { color: var(--text-main); text-decoration: none; transition: var(--transition); }
.pj-title a:hover { color: var(--primary); }

.pj-desc { font-size: 0.9rem; line-height: 1.55; color: var(--text-muted); margin: 0; }

.pj-techs { display: flex; flex-wrap: wrap; gap: 6px; list-style: none; margin: 2px 0 0; padding: 0; }
.pj-techs li {
    padding: 3px 10px;
    border-radius: 6px;
    background: color-mix(in srgb, var(--primary) 8%, transparent);
    color: var(--primary);
    font-size: 0.73rem;
    font-weight: 600;
}

.pj-link {
    display: inline-flex; align-items: center; gap: 7px;
    margin-top: auto; padding-top: 12px;
    color: var(--primary);
    font-size: 0.87rem;
    font-weight: 700;
    text-decoration: none;
}
.pj-link:hover { gap: 11px; }

.pj-empty {
    text-align: center;
    color: var(--text-muted);
    padding: 48px 0;
    margin: 0;
    font-size: 0.95rem;
}

@media (max-width: 1050px) { .pj-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (max-width: 660px)  { .pj-grid { grid-template-columns: minmax(0, 1fr); } }
</style>

<?php if ($showFilters): ?>
<script>
(function () {
    // Filtrage purement client : aucun rechargement, et la page reste
    // fonctionnelle sans JavaScript puisque toutes les cartes sont déjà rendues.
    const grid = document.getElementById('pjGrid');
    if (!grid) { return; }
    const empty = document.getElementById('pjEmpty');

    document.querySelectorAll('.pj-filter').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.pj-filter').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const wanted = btn.dataset.cat;
            let visible = 0;
            grid.querySelectorAll('.pj-card').forEach(function (card) {
                const match = (wanted === '*' || card.dataset.cat === wanted);
                card.hidden = !match;
                if (match) { visible++; }
            });
            if (empty) { empty.hidden = visible > 0; }
        });
    });
})();
</script>
<?php endif; ?>
