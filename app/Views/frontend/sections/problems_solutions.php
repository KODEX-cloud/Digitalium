<?php
/**
 * Section : problems_solutions — Paires « problème → solution »
 *
 * Chaque ligne oppose une situation constatée et la réponse technique apportée.
 *
 * Blocs attendus — TOUT est administrable (Règle #2) :
 *   single : tag, title, subtitle, problem_label, solution_label
 *   groups : ps_icon, ps_problem, ps_solution, ps_detail
 *
 * Aucun texte de repli en dur : un champ vide est simplement masqué.
 */
$pairs = $groups ?? [];
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

        <div class="ps-list">
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
.ps-list { display: flex; flex-direction: column; gap: 18px; }

.ps-row {
    display: grid;
    grid-template-columns: 1fr 52px 1.15fr;
    align-items: center;
    gap: 16px;
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 26px 28px;
    transition: var(--transition);
}
.ps-row:hover {
    box-shadow: var(--shadow-card-hover);
    border-color: color-mix(in srgb, var(--primary) 24%, transparent);
}

.ps-side { min-width: 0; }

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
    font-size: 1.02rem;
    line-height: 1.45;
    color: var(--text-main);
    margin: 0;
    font-weight: 500;
}
.ps-text-solution { font-weight: 700; }
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
    width: 40px; height: 40px;
    border-radius: 50%;
    background: color-mix(in srgb, var(--primary) 10%, transparent);
    color: var(--primary);
    margin: 0 auto;
    flex-shrink: 0;
}

@media (max-width: 860px) {
    .ps-row { grid-template-columns: 1fr; gap: 14px; padding: 22px 20px; }
    /* La flèche bascule vers le bas : la lecture reste « problème puis solution ». */
    .ps-arrow { transform: rotate(90deg); margin: 0; }
}
</style>
