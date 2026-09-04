<?php
/**
 * Section : needs_router — Aiguillage « Je veux… » vers la bonne solution
 *
 * Le visiteur qui arrive sur une page Solutions ne connaît pas toujours le nom
 * de la famille technologique qu'il lui faut ; il sait en revanche formuler son
 * besoin. Cette section part de la phrase du client et le renvoie vers la
 * réponse — une liste de lignes cliquables, volontairement différente des
 * grilles de cartes qui l'entourent, pour ne pas donner trois fois le même
 * rythme de lecture sur une même page.
 *
 * Blocs attendus — TOUT est administrable (Règle #2) :
 *   single : tag, title, subtitle, intro_label
 *   groups : need_icon, need_text (le besoin), need_solution (la réponse),
 *            need_link (destination)
 *
 * Un besoin dont le texte est vide est ignoré : c'est ainsi qu'on le masque
 * sans le supprimer. Sans lien, la ligne s'affiche mais n'est pas cliquable.
 */
$needs = array_values(array_filter($groups ?? [], static function ($n) {
    return trim((string)($n['need_text'] ?? '')) !== '';
}));
$introLabel = trim((string)($single['intro_label'] ?? ''));
?>

<?php if ($needs): ?>
<section class="section-padding needs-section" style="background:var(--bg-alt);">
    <div class="container needs-wrap">

        <div class="needs-intro reveal">
            <?php if (!empty($single['tag'])): ?>
                <span class="section-badge"><?= htmlspecialchars($single['tag']) ?></span>
            <?php endif; ?>
            <?php if (!empty($single['title'])): ?>
                <h2 class="section-title needs-title"><?= htmlspecialchars($single['title']) ?></h2>
            <?php endif; ?>
            <?php if (!empty($single['subtitle'])): ?>
                <p class="section-subtitle needs-sub"><?= htmlspecialchars($single['subtitle']) ?></p>
            <?php endif; ?>
        </div>

        <div class="needs-list">
            <?php if ($introLabel !== ''): ?>
                <span class="needs-label"><?= htmlspecialchars($introLabel) ?></span>
            <?php endif; ?>

            <?php foreach ($needs as $i => $need):
                $link = trim((string)($need['need_link'] ?? ''));
                $tag  = $link !== '' ? 'a' : 'div';
            ?>
                <<?= $tag ?> class="need-row reveal"<?= $link !== '' ? ' href="' . htmlspecialchars(url($link)) . '"' : '' ?>
                   style="transition-delay:<?= min($i * 55, 330) ?>ms;">
                    <?php if (!empty($need['need_icon'])): ?>
                        <span class="need-icon"><?= \App\Helpers\IconHelper::render($need['need_icon'], ['size' => '18px']) ?></span>
                    <?php endif; ?>
                    <span class="need-body">
                        <span class="need-text"><?= htmlspecialchars($need['need_text']) ?></span>
                        <?php if (!empty($need['need_solution'])): ?>
                            <span class="need-solution"><?= htmlspecialchars($need['need_solution']) ?></span>
                        <?php endif; ?>
                    </span>
                    <?php if ($link !== ''): ?>
                        <span class="need-arrow" aria-hidden="true"><?= \App\Helpers\IconHelper::render('arrow-right', ['size' => '17px']) ?></span>
                    <?php endif; ?>
                </<?= $tag ?>>
            <?php endforeach; ?>
        </div>

    </div>
</section>

<style>
.needs-wrap {
    display: grid;
    grid-template-columns: minmax(0, 0.85fr) minmax(0, 1.15fr);
    gap: 56px;
    align-items: start;
}
.needs-intro { position: sticky; top: 100px; }
.needs-title { text-align: left; }
.needs-sub   { text-align: left; margin-left: 0; }

.needs-label {
    display: block;
    font-size: 0.68rem;
    letter-spacing: 0.28em;
    text-transform: uppercase;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 14px;
}

.needs-list { display: flex; flex-direction: column; }

.need-row {
    display: grid;
    grid-template-columns: auto 1fr auto;
    gap: 16px;
    align-items: center;
    padding: 20px 4px;
    border-bottom: 1px solid var(--border);
    text-decoration: none;
    color: inherit;
    transition: var(--transition);
}
.need-row:first-of-type { border-top: 1px solid var(--border); }
a.need-row:hover {
    padding-left: 14px;
    padding-right: 14px;
    background: var(--bg-card);
    border-color: color-mix(in srgb, var(--primary) 26%, transparent);
}

.need-icon {
    display: inline-flex; align-items: center; justify-content: center;
    width: 38px; height: 38px;
    border-radius: 10px;
    background: color-mix(in srgb, var(--primary) 10%, transparent);
    color: var(--primary);
    flex-shrink: 0;
}

.need-body { min-width: 0; display: flex; flex-direction: column; gap: 3px; }
.need-text {
    font-size: 1.02rem;
    font-weight: 650;
    color: var(--text-main);
    line-height: 1.35;
    font-family: var(--font-heading);
}
.need-solution { font-size: 0.85rem; color: var(--text-muted); line-height: 1.5; }

.need-arrow {
    display: inline-flex;
    color: var(--primary);
    opacity: 0.45;
    transition: var(--transition);
}
a.need-row:hover .need-arrow { opacity: 1; transform: translateX(4px); }

@media (max-width: 980px) {
    .needs-wrap { grid-template-columns: 1fr; gap: 34px; }
    .needs-intro { position: static; }
}
@media (max-width: 560px) {
    .need-row { grid-template-columns: auto 1fr; padding: 17px 2px; }
    /* La flèche n'apporte rien sur mobile : la ligne entière est tactile. */
    .need-arrow { display: none; }
    a.need-row:hover { padding-left: 2px; padding-right: 2px; }
}
</style>
<?php endif; ?>
