<?php
/**
 * Section : insights_resources — Contenus stratégiques
 *
 * Guides, études, checklists, livres blancs, cas d'usage et comparatifs. Ce
 * sont des articles du module Blog portant un `resource_type` : pas de second
 * système à administrer, la même fiche, le même éditeur, la même médiathèque.
 *
 * Un contenu peut être téléchargeable : il suffit de lui rattacher un fichier
 * (`resource_file`) depuis la médiathèque. Sans fichier, la carte mène à
 * l'article.
 *
 *   single : tag, title, subtitle,
 *            empty_text     message si aucun contenu stratégique n'est publié
 *            read_text      libellé par défaut quand il n'y a pas de fichier
 *            download_text  libellé par défaut d'un téléchargement
 *   groups : type_value  valeur enregistrée sur l'article (ex. « guide »)
 *            type_label  libellé affiché (ex. « Guide pratique »)
 *            type_icon   nom d'icône Lucide
 *
 * Un type non déclaré en groupe s'affiche tel quel avec une icône neutre : la
 * section ne cache jamais un contenu publié faute de configuration.
 */

use App\Models\Post;

$ressources = Post::getResources(12);

/* Table de correspondance type → libellé + icône, entièrement administrable. */
$libelles = [];
$icones   = [];
foreach (($groups ?? []) as $g) {
    $valeur = trim((string)($g['type_value'] ?? ''));
    if ($valeur === '') { continue; }
    $libelles[$valeur] = trim((string)($g['type_label'] ?? '')) ?: $valeur;
    $icones[$valeur]   = trim((string)($g['type_icon'] ?? '')) ?: 'file-text';
}

$readText     = trim((string)($single['read_text'] ?? '')) ?: 'Consulter';
$downloadText = trim((string)($single['download_text'] ?? '')) ?: 'Télécharger';
$emptyText    = trim((string)($single['empty_text'] ?? ''));
?>

<?php if ($ressources || $emptyText !== ''): ?>
<section class="section-padding ins-res" id="ressources" style="background:var(--bg-base);">
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

        <?php if (empty($ressources)): ?>
            <div class="insr-empty">
                <span class="insr-empty-icon" aria-hidden="true">
                    <i data-lucide="library" style="width:26px;height:26px;"></i>
                </span>
                <p><?= htmlspecialchars($emptyText) ?></p>
            </div>
        <?php else: ?>
            <div class="insr-grid">
                <?php foreach ($ressources as $i => $r):
                    $type    = trim((string)($r['resource_type'] ?? ''));
                    $libelle = $libelles[$type] ?? $type;
                    $icone   = $icones[$type] ?? 'file-text';
                    $fichier = trim((string)($r['resource_file'] ?? ''));
                    $lien    = $fichier !== '' ? url($fichier) : url('/insights/' . ($r['slug'] ?? ''));
                    $libBtn  = trim((string)($r['resource_cta'] ?? ''))
                             ?: ($fichier !== '' ? $downloadText : $readText);
                ?>
                    <article class="insr-card reveal" style="transition-delay:<?= min($i * 55, 330) ?>ms;">
                        <span class="insr-icon" aria-hidden="true">
                            <?= \App\Helpers\IconHelper::render($icone, ['size' => '20px']) ?>
                        </span>

                        <?php if ($libelle !== ''): ?>
                            <span class="insr-type"><?= htmlspecialchars($libelle) ?></span>
                        <?php endif; ?>

                        <h3 class="insr-title">
                            <a href="<?= htmlspecialchars(url('/insights/' . ($r['slug'] ?? ''))) ?>">
                                <?= htmlspecialchars($r['title'] ?? '') ?>
                            </a>
                        </h3>

                        <?php if (!empty($r['excerpt'])): ?>
                            <p class="insr-desc"><?= htmlspecialchars($r['excerpt']) ?></p>
                        <?php endif; ?>

                        <a class="insr-btn" href="<?= htmlspecialchars($lien) ?>"
                           <?= $fichier !== '' ? 'download' : '' ?>>
                            <span><?= htmlspecialchars($libBtn) ?></span>
                            <i data-lucide="<?= $fichier !== '' ? 'download' : 'arrow-right' ?>"
                               style="width:15px;height:15px;"></i>
                        </a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</section>

<style>
.insr-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 24px; }
.insr-card {
    display: flex; flex-direction: column; gap: 12px;
    padding: 30px 28px;
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    transition: var(--transition);
}
.insr-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-card); border-color: color-mix(in srgb, var(--primary) 28%, transparent); }
.insr-icon {
    display: inline-flex; align-items: center; justify-content: center;
    width: 48px; height: 48px; border-radius: 14px;
    background: color-mix(in srgb, var(--primary) 10%, transparent);
    color: var(--primary);
}
.insr-type {
    font-size: 0.69rem; font-weight: 700;
    letter-spacing: 0.13em; text-transform: uppercase;
    color: var(--primary);
}
.insr-title { margin: 0; font-family: var(--font-heading); font-size: 1.05rem; font-weight: 750; line-height: 1.38; }
.insr-title a { color: var(--text-main); text-decoration: none; transition: var(--transition); }
.insr-title a:hover { color: var(--primary); }
.insr-desc { margin: 0; font-size: 0.89rem; line-height: 1.68; color: var(--text-muted); flex: 1; }
.insr-btn {
    align-self: flex-start;
    display: inline-flex; align-items: center; gap: 8px;
    margin-top: 4px;
    padding: 10px 22px;
    border: 1.5px solid var(--border);
    border-radius: 999px;
    color: var(--text-main);
    font-size: 0.85rem; font-weight: 650;
    text-decoration: none;
    transition: var(--transition);
}
.insr-btn:hover { background: var(--primary); border-color: var(--primary); color: #ffffff; gap: 12px; }

.insr-empty {
    display: flex; flex-direction: column; align-items: center; gap: 14px;
    padding: 56px 24px; text-align: center;
    background: var(--bg-card);
    border: 1px dashed var(--border);
    border-radius: var(--radius-lg);
}
.insr-empty p { margin: 0; color: var(--text-muted); font-size: 0.98rem; max-width: 460px; }
.insr-empty-icon {
    display: inline-flex; align-items: center; justify-content: center;
    width: 58px; height: 58px; border-radius: 50%;
    background: color-mix(in srgb, var(--primary) 10%, transparent);
    color: var(--primary);
}

@media (max-width: 1000px) { .insr-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (max-width: 660px)  { .insr-grid { grid-template-columns: 1fr; } }
</style>
<?php endif; ?>
