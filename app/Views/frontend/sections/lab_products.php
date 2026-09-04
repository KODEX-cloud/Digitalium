<?php
/**
 * Section : lab_products — Grille des produits Digitalium Labs
 *
 * Les produits viennent EXCLUSIVEMENT du module Digitalium Labs (table
 * `lab_products`, écran /admin/labs). Aucun nom, aucune capture, aucune
 * technologie n'est écrite ici : tant qu'aucun produit n'est publié, la section
 * affiche le message d'attente saisi en administration — jamais un produit
 * inventé.
 *
 * La section est réutilisable sur n'importe quelle page : `limit` et
 * `featured_only` permettent d'en montrer un aperçu ailleurs sans dupliquer le
 * catalogue.
 *
 *   single : tag, title, subtitle,
 *            show_filters   '0' pour masquer la barre d'étapes
 *            filter_all     libellé du filtre « Tous »
 *            limit          n'afficher que les N premiers (vide = tous)
 *            featured_only  '1' pour n'afficher que les produits mis en avant
 *            cta_text       libellé du lien d'une carte
 *            tech_label     intitulé de la liste de technologies
 *            availability_label  intitulé de la ligne de disponibilité
 *            empty_text     message affiché quand aucun produit n'est publié
 *            more_text, more_url   bouton sous la grille
 *   groups : stage_value   valeur enregistrée sur le produit (ex. « beta »)
 *            stage_label   libellé affiché à la place
 *
 * Une étape non déclarée en groupe garde le libellé du modèle : la section
 * n'efface jamais l'information faute de configuration.
 */

use App\Models\LabProduct;

$limite   = (int)trim((string)($single['limit'] ?? ''));
$avantSeul = (($single['featured_only'] ?? '0') === '1');
$produits = LabProduct::getPublic($limite, $avantSeul);

/* Libellés d'étape : ceux déclarés en administration d'abord, sinon ceux du
   modèle. */
$libelles = LabProduct::STAGES;
foreach (($groups ?? []) as $g) {
    $valeur = trim((string)($g['stage_value'] ?? ''));
    $texte  = trim((string)($g['stage_label'] ?? ''));
    if ($valeur !== '' && $texte !== '' && isset($libelles[$valeur])) {
        $libelles[$valeur] = $texte;
    }
}

/* Filtres : uniquement les étapes réellement portées par les produits affichés.
   Un filtre menant à une grille vide n'a pas d'intérêt. */
$etapes = [];
foreach (array_keys($libelles) as $cle) {
    foreach ($produits as $p) {
        if (($p['stage'] ?? '') === $cle) { $etapes[$cle] = $libelles[$cle]; break; }
    }
}
$afficheFiltres = (($single['show_filters'] ?? '1') !== '0') && count($etapes) > 1;

$ctaText   = trim((string)($single['cta_text'] ?? ''));
$emptyText = trim((string)($single['empty_text'] ?? ''));
$moreText  = trim((string)($single['more_text'] ?? ''));
$techLabel = trim((string)($single['tech_label'] ?? ''));
$dispoLabel = trim((string)($single['availability_label'] ?? ''));
?>

<section class="section-padding lab-products" id="produits" style="background:var(--bg-base);">
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

        <?php if ($afficheFiltres): ?>
            <div class="lp-filters reveal" role="group" aria-label="Filtrer par étape">
                <button type="button" class="lp-filter is-on" data-stage="*">
                    <?= htmlspecialchars(trim((string)($single['filter_all'] ?? '')) ?: 'Tous') ?>
                </button>
                <?php foreach ($etapes as $cle => $texte): ?>
                    <button type="button" class="lp-filter" data-stage="<?= htmlspecialchars($cle, ENT_QUOTES) ?>">
                        <?= htmlspecialchars($texte) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($produits)): ?>
            <?php if ($emptyText !== ''): ?>
                <div class="lp-empty">
                    <span class="lp-empty-icon" aria-hidden="true">
                        <i data-lucide="flask-conical" style="width:26px;height:26px;"></i>
                    </span>
                    <p><?= htmlspecialchars($emptyText) ?></p>
                    <?php if (!empty($single['more_text'])): ?>
                        <a class="lp-empty-btn" href="<?= htmlspecialchars(url($single['more_url'] ?? '/contact')) ?>">
                            <span><?= htmlspecialchars($single['more_text']) ?></span>
                            <i data-lucide="arrow-right" style="width:15px;height:15px;"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="lp-grid" id="lpGrid">
                <?php foreach ($produits as $i => $p):
                    $stage   = (string)($p['stage'] ?? '');
                    $libelle = $libelles[$stage] ?? '';
                    $techs   = array_slice(LabProduct::toList($p['technologies'] ?? null), 0, 5);
                    $lien    = trim((string)($p['external_link'] ?? ''));
                    $ancre   = 'produit-' . trim((string)($p['slug'] ?? ''), '-');
                ?>
                    <article class="lp-card reveal"
                             id="<?= htmlspecialchars($ancre, ENT_QUOTES) ?>"
                             data-stage="<?= htmlspecialchars($stage, ENT_QUOTES) ?>"
                             style="transition-delay:<?= min($i * 55, 330) ?>ms;">

                        <?php if (!empty($p['main_image'])): ?>
                            <div class="lp-media">
                                <img src="<?= htmlspecialchars(url($p['main_image'])) ?>"
                                     alt="<?= htmlspecialchars($p['name'] ?? '') ?>"
                                     loading="lazy" decoding="async">
                            </div>
                        <?php endif; ?>

                        <div class="lp-body">
                            <div class="lp-head">
                                <?php if (!empty($p['logo'])): ?>
                                    <span class="lp-logo">
                                        <img src="<?= htmlspecialchars(url($p['logo'])) ?>"
                                             alt="" loading="lazy" decoding="async">
                                    </span>
                                <?php endif; ?>
                                <div class="lp-titles">
                                    <h3 class="lp-name"><?= htmlspecialchars($p['name'] ?? '') ?></h3>
                                    <?php if (!empty($p['tagline'])): ?>
                                        <p class="lp-tagline"><?= htmlspecialchars($p['tagline']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="lp-tags">
                                <?php if ($libelle !== ''): ?>
                                    <span class="lp-stage lp-stage-<?= htmlspecialchars($stage, ENT_QUOTES) ?>">
                                        <?= htmlspecialchars($libelle) ?>
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($p['sector'])): ?>
                                    <span class="lp-sector"><?= htmlspecialchars($p['sector']) ?></span>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($p['description'])): ?>
                                <p class="lp-desc"><?= htmlspecialchars($p['description']) ?></p>
                            <?php endif; ?>

                            <?php if (!empty($techs)): ?>
                                <div class="lp-techs">
                                    <?php if ($techLabel !== ''): ?>
                                        <span class="lp-techs-label"><?= htmlspecialchars($techLabel) ?></span>
                                    <?php endif; ?>
                                    <ul>
                                        <?php foreach ($techs as $t): ?>
                                            <li><?= htmlspecialchars($t) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($p['availability'])): ?>
                                <p class="lp-availability">
                                    <i data-lucide="map-pin" style="width:14px;height:14px;"></i>
                                    <?php if ($dispoLabel !== ''): ?>
                                        <strong><?= htmlspecialchars($dispoLabel) ?></strong>
                                    <?php endif; ?>
                                    <?= htmlspecialchars($p['availability']) ?>
                                </p>
                            <?php endif; ?>

                            <?php if ($lien !== '' && $ctaText !== ''): ?>
                                <a class="lp-link" href="<?= htmlspecialchars(url($lien)) ?>"
                                   <?= preg_match('#^https?://#i', $lien) ? 'target="_blank" rel="noopener noreferrer"' : '' ?>>
                                    <span><?= htmlspecialchars($ctaText) ?></span>
                                    <i data-lucide="arrow-up-right" style="width:15px;height:15px;"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <?php if ($emptyText !== ''): ?>
                <p class="lp-none" id="lpNone" hidden><?= htmlspecialchars($emptyText) ?></p>
            <?php endif; ?>

            <?php if ($moreText !== ''): ?>
                <div class="lp-more">
                    <a class="lp-more-btn" href="<?= htmlspecialchars(url($single['more_url'] ?? '/contact')) ?>">
                        <?= htmlspecialchars($moreText) ?>
                        <i data-lucide="arrow-right" style="width:16px;height:16px;"></i>
                    </a>
                </div>
            <?php endif; ?>
        <?php endif; ?>

    </div>
</section>

<?php if ($afficheFiltres): ?>
<script>
/* Filtrage par étape. Sans JavaScript, TOUTES les cartes restent visibles et la
   barre de filtres est simplement sans effet : on ne cache jamais un produit
   derrière un script. */
(function () {
    const grille = document.getElementById('lpGrid');
    if (!grille) return;
    const boutons = document.querySelectorAll('.lp-filter');
    const cartes  = grille.querySelectorAll('.lp-card');
    const vide    = document.getElementById('lpNone');

    boutons.forEach(function (b) {
        b.addEventListener('click', function () {
            const cible = b.dataset.stage;
            boutons.forEach(x => x.classList.toggle('is-on', x === b));
            let visibles = 0;
            cartes.forEach(function (c) {
                const ok = (cible === '*' || c.dataset.stage === cible);
                c.hidden = !ok;
                if (ok) visibles++;
            });
            if (vide) vide.hidden = visibles !== 0;
        });
    });
})();
</script>
<?php endif; ?>

<style>
.lp-filters { display: flex; flex-wrap: wrap; justify-content: center; gap: 10px; margin-bottom: 40px; }
.lp-filter {
    padding: 9px 20px;
    border-radius: 999px;
    border: 1.5px solid var(--border);
    background: var(--bg-card);
    color: var(--text-muted);
    font-family: var(--font-heading);
    font-size: 0.86rem; font-weight: 600;
    cursor: pointer; transition: var(--transition);
}
.lp-filter:hover { color: var(--primary); border-color: color-mix(in srgb, var(--primary) 35%, transparent); }
.lp-filter.is-on { background: var(--primary); border-color: var(--primary); color: #ffffff; }

.lp-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 26px; }

.lp-card {
    display: flex; flex-direction: column;
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    overflow: hidden;
    transition: var(--transition);
}
.lp-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-card); }
.lp-media { aspect-ratio: 16 / 10; background: color-mix(in srgb, var(--primary) 7%, transparent); }
.lp-media img { width: 100%; height: 100%; object-fit: cover; display: block; }

.lp-body { display: flex; flex-direction: column; gap: 12px; padding: 24px; flex: 1; }
.lp-head { display: flex; align-items: flex-start; gap: 13px; }
.lp-logo {
    display: inline-flex; align-items: center; justify-content: center;
    width: 44px; height: 44px; flex-shrink: 0;
    border: 1px solid var(--border); border-radius: 12px;
    background: var(--bg-base); overflow: hidden;
}
.lp-logo img { width: 100%; height: 100%; object-fit: contain; padding: 5px; box-sizing: border-box; }
.lp-titles { min-width: 0; }
.lp-name { margin: 0; font-family: var(--font-heading); font-size: 1.1rem; font-weight: 750; line-height: 1.3; color: var(--text-main); }
.lp-tagline { margin: 3px 0 0; font-size: 0.84rem; color: var(--text-muted); line-height: 1.5; }

.lp-tags { display: flex; flex-wrap: wrap; gap: 8px; }
.lp-stage, .lp-sector {
    display: inline-flex; align-items: center;
    padding: 4px 12px; border-radius: 999px;
    font-size: 0.7rem; font-weight: 700;
    letter-spacing: 0.06em; text-transform: uppercase;
}
.lp-sector { background: var(--bg-alt); border: 1px solid var(--border); color: var(--text-muted); }

/* Le dégradé d'intensité suit la maturité : une idée est discrète, un produit
   disponible s'affirme. Toutes les teintes dérivent de la couleur de marque. */
.lp-stage { background: color-mix(in srgb, var(--primary) 10%, transparent); color: var(--primary); border: 1px solid color-mix(in srgb, var(--primary) 22%, transparent); }
.lp-stage-idee          { background: var(--bg-alt); color: var(--text-muted); border-color: var(--border); }
.lp-stage-prototype     { background: color-mix(in srgb, var(--primary) 8%, transparent); }
.lp-stage-developpement { background: color-mix(in srgb, var(--primary) 14%, transparent); }
.lp-stage-beta          { background: color-mix(in srgb, var(--primary) 20%, transparent); }
.lp-stage-disponible    { background: var(--primary); color: #ffffff; border-color: var(--primary); }

.lp-desc { margin: 0; font-size: 0.9rem; line-height: 1.68; color: var(--text-muted); flex: 1; }

.lp-techs { display: flex; flex-wrap: wrap; align-items: center; gap: 7px; }
.lp-techs-label { font-size: 0.68rem; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: var(--text-muted); }
.lp-techs ul { display: flex; flex-wrap: wrap; gap: 7px; list-style: none; margin: 0; padding: 0; }
.lp-techs li {
    padding: 3px 10px; border-radius: 6px;
    background: var(--bg-alt); border: 1px solid var(--border);
    font-size: 0.72rem; color: var(--text-muted);
}

.lp-availability { display: flex; align-items: center; gap: 7px; margin: 0; font-size: 0.79rem; color: var(--text-muted); }
.lp-availability strong { font-weight: 650; color: var(--text-main); }

.lp-link {
    align-self: flex-start;
    display: inline-flex; align-items: center; gap: 7px;
    color: var(--primary); font-size: 0.87rem; font-weight: 650;
    text-decoration: none; transition: var(--transition);
}
.lp-link:hover { gap: 11px; }

.lp-empty, .lp-none {
    display: flex; flex-direction: column; align-items: center; gap: 14px;
    padding: 60px 24px; text-align: center;
    background: var(--bg-card);
    border: 1px dashed var(--border);
    border-radius: var(--radius-lg);
}
.lp-empty p, .lp-none { margin: 0; color: var(--text-muted); font-size: 1rem; max-width: 520px; }
.lp-none { margin-top: 26px; }
.lp-empty-icon {
    display: inline-flex; align-items: center; justify-content: center;
    width: 58px; height: 58px; border-radius: 50%;
    background: color-mix(in srgb, var(--primary) 10%, transparent);
    color: var(--primary);
}
.lp-empty-btn {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 12px 26px; border-radius: 999px;
    background: var(--primary); color: #ffffff;
    font-size: 0.88rem; font-weight: 650; text-decoration: none;
}

.lp-more { display: flex; justify-content: center; margin-top: 38px; }
.lp-more-btn {
    display: inline-flex; align-items: center; gap: 9px;
    padding: 14px 30px; border-radius: 999px;
    background: var(--primary); color: #ffffff;
    font-size: 0.92rem; font-weight: 650; text-decoration: none;
    transition: var(--transition);
}
.lp-more-btn:hover { transform: translateY(-2px); gap: 13px; box-shadow: var(--shadow-btn); }

@media (max-width: 1000px) { .lp-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (max-width: 660px)  { .lp-grid { grid-template-columns: 1fr; } }
</style>
