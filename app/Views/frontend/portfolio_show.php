<?php
/**
 * Page /realisations/{slug} — étude de cas.
 *
 * Tout le contenu vient de la fiche saisie dans le module Réalisations.
 * Chaque bloc est CONDITIONNEL : un champ non renseigné n'affiche rien, ni
 * titre de rubrique, ni texte de repli. Aucun client, chiffre, résultat ou
 * témoignage n'est inventé.
 *
 * Les libellés de rubriques viennent des réglages globaux quand ils existent,
 * pour rester administrables sans imposer une saisie par projet.
 */

use App\Models\Project;

$lbl = static function (string $key, string $default) use ($settings): string {
    $v = trim((string)($settings[$key] ?? ''));
    return $v !== '' ? $v : $default;
};

$objectives = Project::toList($project['objectives']   ?? null);
$features   = Project::toList($project['features']     ?? null);
$results    = Project::toList($project['impact']       ?? null);
$techs      = Project::toList($project['technologies'] ?? null);

$gallery = array_values(array_filter(array_map('trim',
    preg_split('/\r\n|\r|\n|,/', (string)($project['gallery'] ?? '')) ?: []
), fn($v) => $v !== ''));

$facts = array_filter([
    $lbl('case_label_client', 'Client')    => trim((string)($project['client']   ?? '')),
    $lbl('case_label_sector', 'Secteur')   => trim((string)($project['sector']   ?? '')),
    $lbl('case_label_category', 'Catégorie') => trim((string)($project['category'] ?? '')),
    $lbl('case_label_year', 'Année')       => trim((string)($project['year']     ?? '')),
], fn($v) => $v !== '');
?>

<!-- 1. Hero du projet -->
<section class="cs-hero">
    <?php if (!empty($project['main_image'])): ?>
        <div class="cs-hero-media">
            <img src="<?= htmlspecialchars(url($project['main_image'])) ?>"
                 alt="<?= htmlspecialchars($project['title'] ?? '') ?>" decoding="async">
        </div>
    <?php endif; ?>

    <div class="container cs-hero-inner">
        <a class="cs-back" href="<?= htmlspecialchars(url('/realisations')) ?>">
            <i data-lucide="arrow-left" style="width:15px;height:15px;"></i>
            <span><?= htmlspecialchars($lbl('case_back_text', 'Toutes les réalisations')) ?></span>
        </a>

        <?php if (!empty($project['category'])): ?>
            <span class="cs-eyebrow"><?= htmlspecialchars($project['category']) ?></span>
        <?php endif; ?>

        <h1 class="cs-title"><?= htmlspecialchars($project['title'] ?? '') ?></h1>

        <?php if (!empty($project['description'])): ?>
            <p class="cs-lead"><?= htmlspecialchars($project['description']) ?></p>
        <?php endif; ?>

        <?php if (!empty($facts)): ?>
            <dl class="cs-facts">
                <?php foreach ($facts as $label => $value): ?>
                    <div>
                        <dt><?= htmlspecialchars($label) ?></dt>
                        <dd><?= htmlspecialchars($value) ?></dd>
                    </div>
                <?php endforeach; ?>
            </dl>
        <?php endif; ?>
    </div>
</section>

<div class="cs-body section-padding">
    <div class="container cs-layout">

        <div class="cs-main">

            <!-- 3. Le problème -->
            <?php if (!empty($project['context'])): ?>
                <section class="cs-block reveal">
                    <h2 class="cs-h2"><?= htmlspecialchars($lbl('case_label_problem', 'Le problème')) ?></h2>
                    <div class="cs-prose"><?= nl2br(htmlspecialchars($project['context'])) ?></div>
                </section>
            <?php endif; ?>

            <!-- 4. Les objectifs -->
            <?php if (!empty($objectives)): ?>
                <section class="cs-block reveal">
                    <h2 class="cs-h2"><?= htmlspecialchars($lbl('case_label_objectives', 'Les objectifs')) ?></h2>
                    <ul class="cs-checks">
                        <?php foreach ($objectives as $o): ?>
                            <li><i data-lucide="target" style="width:16px;height:16px;"></i><span><?= htmlspecialchars($o) ?></span></li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endif; ?>

            <!-- 5. La solution Digitalium -->
            <?php if (!empty($project['solution'])): ?>
                <section class="cs-block reveal">
                    <h2 class="cs-h2"><?= htmlspecialchars($lbl('case_label_solution', 'La solution Digitalium')) ?></h2>
                    <div class="cs-prose"><?= nl2br(htmlspecialchars($project['solution'])) ?></div>
                </section>
            <?php endif; ?>

            <!-- 7. Fonctionnalités principales -->
            <?php if (!empty($features)): ?>
                <section class="cs-block reveal">
                    <h2 class="cs-h2"><?= htmlspecialchars($lbl('case_label_features', 'Fonctionnalités principales')) ?></h2>
                    <ul class="cs-checks">
                        <?php foreach ($features as $f): ?>
                            <li><i data-lucide="check" style="width:16px;height:16px;"></i><span><?= htmlspecialchars($f) ?></span></li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endif; ?>

            <!-- 8. Images / captures du projet -->
            <?php if (!empty($gallery)): ?>
                <section class="cs-block reveal">
                    <h2 class="cs-h2"><?= htmlspecialchars($lbl('case_label_gallery', 'Le projet en images')) ?></h2>
                    <div class="cs-gallery">
                        <?php foreach ($gallery as $g): ?>
                            <figure>
                                <img src="<?= htmlspecialchars(url($g)) ?>"
                                     alt="<?= htmlspecialchars($project['title'] ?? '') ?>"
                                     loading="lazy" decoding="async">
                            </figure>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- 9. Résultats obtenus -->
            <?php if (!empty($results)): ?>
                <section class="cs-block reveal">
                    <h2 class="cs-h2"><?= htmlspecialchars($lbl('case_label_results', 'Résultats obtenus')) ?></h2>
                    <ul class="cs-results">
                        <?php foreach ($results as $r): ?>
                            <li><i data-lucide="trending-up" style="width:17px;height:17px;"></i><span><?= htmlspecialchars($r) ?></span></li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endif; ?>

            <!-- 10. Témoignage client, seulement s'il existe -->
            <?php if (!empty($project['testimonial_quote'])): ?>
                <section class="cs-block reveal">
                    <blockquote class="cs-quote">
                        <i data-lucide="quote" style="width:22px;height:22px;"></i>
                        <p><?= nl2br(htmlspecialchars($project['testimonial_quote'])) ?></p>
                        <?php if (!empty($project['testimonial_author'])): ?>
                            <footer>
                                <b><?= htmlspecialchars($project['testimonial_author']) ?></b>
                                <?php if (!empty($project['testimonial_role'])): ?>
                                    <span><?= htmlspecialchars($project['testimonial_role']) ?></span>
                                <?php endif; ?>
                            </footer>
                        <?php endif; ?>
                    </blockquote>
                </section>
            <?php endif; ?>

        </div>

        <!-- 2 & 6. Fiche projet et technologies -->
        <aside class="cs-aside">
            <div class="cs-card">
                <?php if (!empty($facts)): ?>
                    <h3 class="cs-card-title"><?= htmlspecialchars($lbl('case_label_project', 'Le projet')) ?></h3>
                    <dl class="cs-aside-facts">
                        <?php foreach ($facts as $label => $value): ?>
                            <div><dt><?= htmlspecialchars($label) ?></dt><dd><?= htmlspecialchars($value) ?></dd></div>
                        <?php endforeach; ?>
                    </dl>
                <?php endif; ?>

                <?php if (!empty($techs)): ?>
                    <h3 class="cs-card-title"><?= htmlspecialchars($lbl('case_label_tech', 'Technologies')) ?></h3>
                    <ul class="cs-techs">
                        <?php foreach ($techs as $t): ?>
                            <li><?= htmlspecialchars($t) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <?php if (!empty($project['external_link'])): ?>
                    <a class="cs-visit" href="<?= htmlspecialchars($project['external_link']) ?>" target="_blank" rel="noopener">
                        <i data-lucide="external-link" style="width:15px;height:15px;"></i>
                        <span><?= htmlspecialchars($lbl('case_visit_text', 'Voir le projet en ligne')) ?></span>
                    </a>
                <?php endif; ?>
            </div>
        </aside>

    </div>
</div>

<!-- Réalisations proches -->
<?php if (!empty($related)): ?>
    <section class="section-padding cs-related">
        <div class="container">
            <h2 class="section-title" style="text-align:center;">
                <?= htmlspecialchars($lbl('case_label_related', 'Autres réalisations')) ?>
            </h2>
            <div class="cs-related-grid">
                <?php foreach ($related as $r): ?>
                    <a class="cs-related-card" href="<?= htmlspecialchars(url('/realisations/' . $r['slug'])) ?>">
                        <?php if (!empty($r['main_image'])): ?>
                            <span class="cs-related-media">
                                <img src="<?= htmlspecialchars(url($r['main_image'])) ?>"
                                     alt="<?= htmlspecialchars($r['title']) ?>" loading="lazy" decoding="async">
                            </span>
                        <?php endif; ?>
                        <span class="cs-related-body">
                            <?php if (!empty($r['category'])): ?>
                                <span class="cs-related-cat"><?= htmlspecialchars($r['category']) ?></span>
                            <?php endif; ?>
                            <b><?= htmlspecialchars($r['title']) ?></b>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- 11. CTA final -->
<?php
$ctaTitle = trim((string)($settings['case_cta_title'] ?? ''));
$ctaText  = trim((string)($settings['case_cta_text']  ?? ''));
$ctaBtn   = trim((string)($settings['case_cta_button'] ?? ''));
$ctaUrl   = trim((string)($settings['case_cta_url']   ?? '/contact'));
?>
<?php if ($ctaTitle !== '' || $ctaBtn !== ''): ?>
    <section class="cs-cta">
        <div class="container">
            <?php if ($ctaTitle !== ''): ?><h2><?= htmlspecialchars($ctaTitle) ?></h2><?php endif; ?>
            <?php if ($ctaText !== ''): ?><p><?= htmlspecialchars($ctaText) ?></p><?php endif; ?>
            <?php if ($ctaBtn !== ''): ?>
                <a class="cs-cta-btn" href="<?= htmlspecialchars(url($ctaUrl)) ?>">
                    <span><?= htmlspecialchars($ctaBtn) ?></span>
                    <i data-lucide="arrow-right" style="width:16px;height:16px;"></i>
                </a>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>

<style>
/* ── Hero de l'étude de cas ── */
.cs-hero { position: relative; overflow: hidden; background: var(--bg-alt); }
.cs-hero-media { position: absolute; inset: 0; }
.cs-hero-media img { width: 100%; height: 100%; object-fit: cover; display: block; }
.cs-hero-media::after {
    content: ""; position: absolute; inset: 0;
    background: linear-gradient(90deg,
        var(--primary) 0%,
        color-mix(in srgb, var(--primary) 86%, transparent) 42%,
        color-mix(in srgb, var(--primary) 38%, transparent) 72%,
        transparent 95%);
    opacity: 0.88;
}
.cs-hero-inner { position: relative; z-index: 2; padding: 96px 40px 72px; max-width: 1240px; }
.cs-hero-media ~ .cs-hero-inner .cs-back,
.cs-hero-media ~ .cs-hero-inner .cs-eyebrow,
.cs-hero-media ~ .cs-hero-inner .cs-title,
.cs-hero-media ~ .cs-hero-inner .cs-lead,
.cs-hero-media ~ .cs-hero-inner .cs-facts { color: #ffffff; }

.cs-back {
    display: inline-flex; align-items: center; gap: 7px;
    color: var(--text-muted); text-decoration: none;
    font-size: 0.85rem; font-weight: 600; margin-bottom: 22px;
}
.cs-back:hover { gap: 11px; }

.cs-eyebrow {
    display: inline-block; margin-bottom: 14px;
    font-size: 0.78rem; font-weight: 700;
    letter-spacing: 0.09em; text-transform: uppercase;
    color: var(--primary);
}
.cs-hero-media ~ .cs-hero-inner .cs-eyebrow { color: rgba(255,255,255,0.85); }

.cs-title { font-size: clamp(2rem, 4.2vw, 3.1rem); line-height: 1.1; font-weight: 800; margin: 0 0 16px; max-width: 800px; }
.cs-lead  { font-size: 1.05rem; line-height: 1.65; max-width: 620px; margin: 0; opacity: 0.94; }

.cs-facts { display: flex; flex-wrap: wrap; gap: 30px; margin: 30px 0 0; padding: 0; }
.cs-facts dt { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.07em; opacity: 0.75; margin-bottom: 4px; }
.cs-facts dd { margin: 0; font-size: 1rem; font-weight: 700; }

/* ── Corps ── */
.cs-layout { display: grid; grid-template-columns: minmax(0, 1fr) 320px; gap: 48px; align-items: start; }
.cs-block { margin-bottom: 44px; }
.cs-block:last-child { margin-bottom: 0; }
.cs-h2 { font-size: 1.5rem; font-weight: 700; margin: 0 0 16px; font-family: var(--font-heading); color: var(--text-main); }
.cs-prose { font-size: 1rem; line-height: 1.75; color: var(--text-muted); }

.cs-checks, .cs-results { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 12px; }
.cs-checks li, .cs-results li { display: flex; align-items: flex-start; gap: 11px; font-size: 0.97rem; line-height: 1.6; color: var(--text-main); }
.cs-checks li i, .cs-results li i { color: var(--primary); flex-shrink: 0; margin-top: 3px; }
.cs-results li {
    background: color-mix(in srgb, var(--primary) 6%, transparent);
    border-left: 3px solid var(--primary);
    border-radius: 10px;
    padding: 14px 16px;
}

.cs-gallery { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
.cs-gallery figure { margin: 0; border-radius: var(--radius-lg); overflow: hidden; border: 1px solid var(--border); }
.cs-gallery img { width: 100%; height: 100%; object-fit: cover; display: block; }

.cs-quote {
    margin: 0; padding: 30px 32px;
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
}
.cs-quote i { color: var(--primary); margin-bottom: 12px; display: block; }
.cs-quote p { font-size: 1.1rem; line-height: 1.7; font-style: italic; color: var(--text-main); margin: 0 0 16px; }
.cs-quote footer { display: flex; flex-direction: column; gap: 2px; font-size: 0.88rem; }
.cs-quote footer b { color: var(--text-main); }
.cs-quote footer span { color: var(--text-muted); }

/* ── Colonne latérale ── */
.cs-aside { position: sticky; top: 24px; }
.cs-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 24px; }
.cs-card-title { font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.07em; color: var(--text-muted); margin: 0 0 14px; font-weight: 700; }
.cs-card-title + * { margin-bottom: 24px; }
.cs-aside-facts { margin: 0; padding: 0; display: flex; flex-direction: column; gap: 12px; }
.cs-aside-facts dt { font-size: 0.75rem; color: var(--text-muted); margin-bottom: 2px; }
.cs-aside-facts dd { margin: 0; font-size: 0.94rem; font-weight: 600; color: var(--text-main); }
.cs-techs { list-style: none; display: flex; flex-wrap: wrap; gap: 7px; margin: 0; padding: 0; }
.cs-techs li {
    padding: 5px 12px; border-radius: 7px;
    background: color-mix(in srgb, var(--primary) 9%, transparent);
    color: var(--primary); font-size: 0.79rem; font-weight: 600;
}
.cs-visit {
    display: inline-flex; align-items: center; gap: 8px;
    margin-top: 20px; padding: 11px 18px;
    border-radius: 999px; background: var(--primary); color: #ffffff;
    font-size: 0.86rem; font-weight: 700; text-decoration: none;
}

/* ── Réalisations proches ── */
.cs-related { background: var(--bg-alt); }
.cs-related-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 22px; margin-top: 34px; }
.cs-related-card {
    display: flex; flex-direction: column; text-decoration: none;
    background: var(--bg-card); border: 1px solid var(--border);
    border-radius: var(--radius-lg); overflow: hidden; transition: var(--transition);
}
.cs-related-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-card-hover); }
.cs-related-media { display: block; aspect-ratio: 16 / 10; overflow: hidden; }
.cs-related-media img { width: 100%; height: 100%; object-fit: cover; display: block; }
.cs-related-body { display: flex; flex-direction: column; gap: 5px; padding: 16px 18px; }
.cs-related-cat { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--primary); font-weight: 700; }
.cs-related-body b { color: var(--text-main); font-size: 1rem; line-height: 1.35; }

/* ── CTA ── */
.cs-cta { background: var(--primary); color: #ffffff; padding: 68px 0; text-align: center; }
.cs-cta h2 { font-size: clamp(1.6rem, 3vw, 2.2rem); margin: 0 0 12px; font-weight: 800; }
.cs-cta p { max-width: 560px; margin: 0 auto 26px; opacity: 0.9; line-height: 1.65; }
.cs-cta-btn {
    display: inline-flex; align-items: center; gap: 9px;
    padding: 15px 32px; border-radius: 999px;
    background: #ffffff; color: var(--primary);
    font-weight: 700; text-decoration: none;
}

@media (max-width: 1000px) {
    .cs-layout { grid-template-columns: minmax(0, 1fr); gap: 34px; }
    .cs-aside { position: static; }
    .cs-related-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 660px) {
    .cs-hero-inner { padding: 72px 20px 56px; }
    .cs-facts { gap: 20px; }
    .cs-gallery, .cs-related-grid { grid-template-columns: minmax(0, 1fr); }
}
</style>
