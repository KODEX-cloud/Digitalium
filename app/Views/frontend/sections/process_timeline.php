<?php
/**
 * Section: process_timeline — Étapes en frise horizontale numérotée
 * Distincte de "process" (grille de cartes, utilisée sur d'autres pages) : le visuel
 * de référence de la Homepage v2 attend une frise avec, pour chaque étape, une pastille
 * numérotée pleine suivie d'une pastille claire portant l'icône, le tout relié par une
 * ligne pointillée, puis le titre et la description en dessous.
 * Données CMS : $single (tag, title), $groups (proc_num, proc_icon, proc_title, proc_desc)
 * — mêmes clés de blocs que "process" pour permettre de rebasculer un type vers l'autre
 * sans perte de contenu.
 * Règle #2 (zéro hardcode) : chaque élément non renseigné est simplement masqué.
 * Design System v4.1 — variables CSS uniquement
 */
$stepAccents = ['var(--primary)', 'var(--secondary)'];
?>

<section class="section-padding" id="process">
    <div class="container">

        <div class="section-header reveal">
            <?php if (!empty($single['tag'])): ?>
                <span class="section-badge"><?= htmlspecialchars($single['tag']) ?></span>
            <?php endif; ?>
            <?php if (!empty($single['title'])): ?>
                <h2 class="section-title"><?= htmlspecialchars($single['title']) ?></h2>
            <?php endif; ?>
        </div>

        <div class="proc-timeline">
            <?php if (!empty($groups)): ?>
                <?php foreach ($groups as $i => $step):
                    $accent = $stepAccents[$i % count($stepAccents)];
                    $num = ltrim((string)($step['proc_num'] ?? ''), '0');
                    if ($num === '') { $num = (string)($i + 1); }
                ?>
                    <div class="proc-timeline-step reveal" style="transition-delay:<?= $i * 0.07 ?>s;">
                        <div class="proc-timeline-head">
                            <span class="proc-timeline-num" style="background:<?= $accent ?>;">
                                <?= htmlspecialchars($num) ?>
                            </span>
                            <?php if (!empty($step['proc_icon'])): ?>
                                <span class="proc-timeline-icon" style="color:<?= $accent ?>;">
                                    <?= \App\Helpers\IconHelper::render($step['proc_icon'], ['size' => '18px']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($step['proc_title'])): ?>
                            <h3 class="proc-timeline-title"><?= htmlspecialchars($step['proc_title']) ?></h3>
                        <?php endif; ?>
                        <?php if (!empty($step['proc_desc'])): ?>
                            <p class="proc-timeline-desc"><?= htmlspecialchars($step['proc_desc']) ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
</section>

<style>
.proc-timeline {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 18px;
    position: relative;
}
/* Ligne pointillée reliant les pastilles */
.proc-timeline::before {
    content: '';
    position: absolute;
    top: 18px;
    left: 7%;
    right: 7%;
    border-top: 2px dashed var(--border);
    z-index: 0;
}

.proc-timeline-step {
    position: relative;
    z-index: 1;
    padding-right: 6px;
}

.proc-timeline-head {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 16px;
}

.proc-timeline-num {
    width: 36px; height: 36px;
    border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    color: #fff;
    font-weight: 800;
    font-size: 0.88rem;
    font-family: var(--font-heading);
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(15,23,42,0.12);
}

.proc-timeline-icon {
    width: 36px; height: 36px;
    border-radius: 50%;
    background: var(--bg-card);
    border: 1px solid var(--border);
    display: inline-flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}

.proc-timeline-title {
    font-size: 0.92rem;
    font-weight: 700;
    color: var(--text-main);
    margin-bottom: 6px;
    font-family: var(--font-heading);
}

.proc-timeline-desc {
    font-size: 0.78rem;
    line-height: 1.6;
    color: var(--text-muted);
    margin: 0;
}

@media (max-width: 1000px) {
    .proc-timeline { grid-template-columns: repeat(3, 1fr); row-gap: 34px; }
    .proc-timeline::before { display: none; }
}
@media (max-width: 620px) {
    .proc-timeline { grid-template-columns: repeat(2, 1fr); }
}
</style>
