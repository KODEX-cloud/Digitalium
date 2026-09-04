<?php
/**
 * Section : problems_solutions — Paires « problème → solution »
 *
 * Chaque ligne oppose une situation constatée et la réponse technique apportée.
 *
 * Blocs attendus — TOUT est administrable (Règle #2) :
 *   single : tag, title, subtitle, problem_label, solution_label
 *            layout   'stack' (défaut — constat au-dessus, réponse en dessous)
 *                     ou 'row' (constat et réponse côte à côte)
 *            columns  nombre de colonnes de la grille, 1 à 4 — défaut 2
 *   groups : ps_icon, ps_problem, ps_solution, ps_detail
 *
 * Aucun texte de repli en dur : un champ vide est simplement masqué.
 */
$pairs = $groups ?? [];

/* Disposition administrable ; toute valeur inattendue retombe sur le défaut. */
$psLayout = in_array($single['layout'] ?? 'stack', ['stack', 'row'], true)
    ? ($single['layout'] ?? 'stack')
    : 'stack';
$psCols = (int)($single['columns'] ?? 2);
if ($psCols < 1 || $psCols > 4) { $psCols = 2; }
/* Une seule paire n'a pas besoin d'une grille : elle occupe la largeur. */
if (count($pairs) < 2) { $psCols = 1; }
?>

<section class="section-padding ps-section" style="background:var(--bg-alt);">
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

        <div class="ps-list ps-list-<?= $psLayout ?>" style="--ps-cols:<?= $psCols ?>;">
            <?php foreach ($pairs as $i => $p):
                if (empty($p['ps_problem']) && empty($p['ps_solution'])) { continue; }
            ?>
                <article class="ps-row reveal" style="transition-delay:<?= min($i * 70, 350) ?>ms;">

                    <div class="ps-side ps-side-problem">
                        <?php if (!empty($single['problem_label'])): ?>
                            <span class="ps-label ps-label-problem"><?= htmlspecialchars($single['problem_label']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($p['ps_problem'])): ?>
                            <p class="ps-text"><?= htmlspecialchars($p['ps_problem']) ?></p>
                        <?php endif; ?>
                    </div>

                    <span class="ps-arrow" aria-hidden="true">
                        <i data-lucide="arrow-right" style="width:18px;height:18px;"></i>
                    </span>

                    <div class="ps-side ps-side-solution">
                        <div class="ps-solution-head">
                            <?php if (!empty($p['ps_icon'])): ?>
                                <span class="ps-icon"><?= \App\Helpers\IconHelper::render($p['ps_icon'], ['size' => '18px']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($single['solution_label'])): ?>
                                <span class="ps-label ps-label-solution"><?= htmlspecialchars($single['solution_label']) ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($p['ps_solution'])): ?>
                            <p class="ps-text ps-text-solution"><?= htmlspecialchars($p['ps_solution']) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($p['ps_detail'])): ?>
                            <p class="ps-detail"><?= htmlspecialchars($p['ps_detail']) ?></p>
                        <?php endif; ?>
                    </div>

                </article>
            <?php endforeach; ?>
        </div>

    </div>
</section>

<style>
.ps-list { display: grid; gap: 18px; }

/* Disposition « stack » (défaut) : chaque paire se lit de haut en bas —
   constat, flèche descendante, réponse — et les paires se rangent en grille.
   Disposition « row » : constat et réponse côte à côte, une paire par ligne. */
.ps-list-stack { grid-template-columns: repeat(var(--ps-cols, 2), minmax(0, 1fr)); }
.ps-list-row   { grid-template-columns: minmax(0, 1fr); }

.ps-row {
    display: grid;
    /* `stretch` — les deux côtés occupent toute la hauteur de la carte : le
       constat ne flotte plus au milieu d'un vide quand la réponse est longue. */
    align-items: stretch;
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    transition: var(--transition);
}
.ps-list-row .ps-row   { grid-template-columns: 0.95fr 44px 1.3fr; gap: 20px; padding: 20px; }
.ps-list-stack .ps-row { grid-template-columns: minmax(0, 1fr); gap: 14px; padding: 22px; }
.ps-row:hover {
    box-shadow: var(--shadow-card-hover);
    border-color: color-mix(in srgb, var(--primary) 24%, transparent);
}

.ps-side {
    min-width: 0;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

/* Le constat reçoit sa propre surface : sans elle, la moitié gauche de la
   carte se lisait comme un vide plutôt que comme un des deux termes. */
.ps-side-problem {
    background: color-mix(in srgb, var(--primary) 5%, var(--bg-alt));
    border-left: 3px solid color-mix(in srgb, var(--primary) 22%, transparent);
    border-radius: var(--radius-md, 12px);
    padding: 20px 22px;
}
.ps-list-row   .ps-side-solution { padding: 6px 8px 6px 0; }
.ps-list-stack .ps-side-solution { padding: 0 2px; }

.ps-label {
    display: inline-block;
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.09em;
    text-transform: uppercase;
    margin-bottom: 9px;
}
.ps-label-problem  { color: var(--text-muted); }
.ps-label-solution { color: var(--primary); }

.ps-text {
    font-size: 1.04rem;
    line-height: 1.45;
    color: var(--text-main);
    margin: 0;
    font-weight: 600;
}
.ps-text-solution {
    font-size: 1.12rem;
    font-weight: 700;
    font-family: var(--font-heading);
}
.ps-detail { font-size: 0.89rem; line-height: 1.5; color: var(--text-muted); margin: 7px 0 0; }

.ps-solution-head { display: flex; align-items: center; gap: 10px; margin-bottom: 9px; }
.ps-icon {
    display: inline-flex; align-items: center; justify-content: center;
    width: 34px; height: 34px;
    border-radius: 50%;
    background: var(--primary);
    color: #ffffff;
    flex-shrink: 0;
}
.ps-solution-head .ps-label { margin-bottom: 0; }

.ps-arrow {
    display: inline-flex; align-items: center; justify-content: center;
    align-self: center;
    width: 40px; height: 40px;
    border-radius: 50%;
    background: color-mix(in srgb, var(--primary) 10%, transparent);
    color: var(--primary);
    margin: 0 auto;
    flex-shrink: 0;
}

/* En lecture verticale, la flèche descend : elle relie le constat à la réponse. */
.ps-list-stack .ps-arrow { transform: rotate(90deg); margin: 0 auto; }

@media (max-width: 1000px) {
    .ps-list-row .ps-row { grid-template-columns: 1fr 40px 1.15fr; gap: 16px; }
    .ps-side-problem { padding: 16px 18px; }
}
@media (max-width: 860px) {
    /* Sous 860px la grille passe en colonne unique, quel que soit le réglage. */
    .ps-list-stack { grid-template-columns: minmax(0, 1fr); }
    .ps-list-row .ps-row { grid-template-columns: 1fr; gap: 14px; padding: 18px; }
    /* La flèche bascule vers le bas : la lecture reste « problème puis solution ». */
    .ps-list-row .ps-arrow { transform: rotate(90deg); margin: 0; }
    .ps-list-row .ps-side-solution { padding: 0 4px 4px; }
}
</style>
