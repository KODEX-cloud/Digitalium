<?php
/**
 * Section: process_timeline — Étapes en frise horizontale numérotée
 * Distincte de "process" (grille de cartes, utilisée sur d'autres pages) : le visuel
 * de référence de la Homepage v2 attend une frise avec cercles numérotés reliés par
 * une ligne pointillée, icône au-dessus du numéro.
 * Données CMS : $single (tag, title), $groups (proc_num, proc_icon, proc_title, proc_desc)
 * — mêmes clés de blocs que "process" pour permettre de rebasculer un type vers l'autre
 * sans perte de contenu.
 * Design System v4.1 — variables CSS uniquement, zéro hardcode
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
                ?>
                    <div class="proc-timeline-step reveal" style="transition-delay:<?= $i * 0.08 ?>s;">
                        <?php if (!empty($step['proc_icon'])): ?>
                            <div class="proc-timeline-icon" style="color:<?= $accent ?>;">
                                <?= \App\Helpers\IconHelper::render($step['proc_icon'], ['size' => '20px']) ?>
                            </div>
                        <?php endif; ?>
                        <div class="proc-timeline-num" style="background:<?= $accent ?>;">
                            <?= htmlspecialchars(ltrim($step['proc_num'] ?? (string)($i + 1), '0') ?: (string)($i + 1)) ?>
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
    gap: 16px;
    position: relative;
}
.proc-timeline::before {
    content: '';
    position: absolute;
    top: 62px; left: 8%; right: 8%;
    height: 0;
    border-top: 2px dashed var(--border);
    z-index: 0;
}
@media (max-width: 900px) {
    .proc-timeline { grid-template-columns: repeat(3, 1fr); row-gap: 40px; }
    .proc-timeline::before { display: none; }
}
@media (max-width: 560px) {
    .proc-timeline { grid-template-columns: repeat(2, 1fr); }
}

.proc-timeline-step {
    position: relative;
    z-index: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 0 8px;
}

.proc-timeline-icon {
    height: 22px;
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 8px;
}

.proc-timeline-num {
    width: 40px; height: 40px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    color: #fff;
    font-weight: 800;
    font-size: 0.95rem;
    font-family: var(--font-heading);
    margin-bottom: 14px;
    box-shadow: 0 6px 16px rgba(15,23,42,0.12);
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
    line-height: 1.55;
    color: var(--text-muted);
}
</style>
