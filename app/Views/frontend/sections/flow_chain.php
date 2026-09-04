<?php
/**
 * Section : flow_chain — Enchaînement vertical d'étapes reliées
 *
 * Distincte de `process` (grille de cartes) et de `process_timeline` (frise
 * horizontale numérotée) : ici les étapes se lisent de haut en bas, reliées par
 * une flèche, parce que ce qui compte est la FILIATION — ce qui sort d'une
 * étape entre dans la suivante. Une frise horizontale numérotée raconterait une
 * méthode en N points, pas une chaîne de transformation.
 *
 * Aucun des deux types existants n'a été modifié : ils servent des pages déjà
 * en ligne, et le cahier des charges demande de ne pas les toucher.
 *
 * Blocs attendus — TOUT est administrable (Règle #2) :
 *   single : tag, title, subtitle
 *   groups : flow_label   le texte de l'étape (vider ce champ retire l'étape)
 *            flow_note    précision affichée sous le libellé
 *            flow_icon    nom d'icône Lucide
 *            flow_accent  '1' pour marquer un jalon (départ, bascule, arrivée)
 *
 * Sur mobile, la chaîne reste verticale : c'est déjà sa lecture naturelle.
 */

$etapes = [];
foreach (($groups ?? []) as $g) {
    $libelle = trim((string)($g['flow_label'] ?? ''));
    if ($libelle === '') { continue; }
    $etapes[] = [
        'label'  => $libelle,
        'note'   => trim((string)($g['flow_note'] ?? '')),
        'icon'   => trim((string)($g['flow_icon'] ?? '')),
        'accent' => (($g['flow_accent'] ?? '0') === '1'),
    ];
}
?>

<?php if ($etapes): ?>
<section class="section-padding flow-chain" id="du-service-au-produit" style="background:var(--bg-alt);">
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

        <ol class="fc-chain">
            <?php foreach ($etapes as $i => $e): ?>
                <li class="fc-step<?= $e['accent'] ? ' is-milestone' : '' ?> reveal"
                    style="transition-delay:<?= min($i * 60, 420) ?>ms;">
                    <div class="fc-box">
                        <?php if ($e['icon'] !== ''): ?>
                            <span class="fc-icon" aria-hidden="true">
                                <?= \App\Helpers\IconHelper::render($e['icon'], ['size' => '18px']) ?>
                            </span>
                        <?php endif; ?>
                        <div class="fc-text">
                            <span class="fc-label"><?= htmlspecialchars($e['label']) ?></span>
                            <?php if ($e['note'] !== ''): ?>
                                <span class="fc-note"><?= htmlspecialchars($e['note']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ($i < count($etapes) - 1): ?>
                        <span class="fc-arrow" aria-hidden="true">
                            <i data-lucide="chevron-down" style="width:18px;height:18px;"></i>
                        </span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ol>

    </div>
</section>

<style>
.fc-chain {
    list-style: none;
    margin: 0 auto;
    padding: 0;
    max-width: 720px;
    display: flex;
    flex-direction: column;
    align-items: stretch;
}
.fc-step { display: flex; flex-direction: column; align-items: center; }

.fc-box {
    display: flex; align-items: center; gap: 15px;
    width: 100%; box-sizing: border-box;
    padding: 20px 26px;
    background: var(--bg-card);
    border: 1.5px solid var(--border);
    border-radius: var(--radius-lg);
    transition: var(--transition);
}
.fc-step:hover .fc-box { border-color: color-mix(in srgb, var(--primary) 35%, transparent); }

/* Un jalon : ce qui entre dans la chaîne, ce qui bascule, ce qui en sort. */
.fc-step.is-milestone .fc-box {
    background: var(--primary);
    border-color: var(--primary);
}
.fc-step.is-milestone .fc-label { color: #ffffff; }
.fc-step.is-milestone .fc-note  { color: rgba(255, 255, 255, 0.82); }
.fc-step.is-milestone .fc-icon  { background: rgba(255, 255, 255, 0.18); color: #ffffff; }

.fc-icon {
    display: inline-flex; align-items: center; justify-content: center;
    width: 42px; height: 42px; flex-shrink: 0;
    border-radius: 12px;
    background: color-mix(in srgb, var(--primary) 10%, transparent);
    color: var(--primary);
}
.fc-text { display: flex; flex-direction: column; gap: 3px; min-width: 0; }
.fc-label {
    font-family: var(--font-heading);
    font-size: 0.95rem; font-weight: 750;
    color: var(--text-main);
    line-height: 1.35;
}
.fc-note { font-size: 0.84rem; color: var(--text-muted); line-height: 1.55; }

.fc-arrow {
    display: inline-flex; align-items: center; justify-content: center;
    height: 34px;
    color: color-mix(in srgb, var(--primary) 55%, transparent);
}

@media (max-width: 620px) {
    .fc-box { padding: 16px 18px; gap: 12px; }
    .fc-icon { width: 36px; height: 36px; }
    .fc-label { font-size: 0.9rem; }
}
</style>
<?php endif; ?>
