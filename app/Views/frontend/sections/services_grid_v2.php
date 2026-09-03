<?php
/**
 * Section: services_grid_v2 — Grille de services (icône à gauche, titre + texte à droite)
 * Distincte de "services_grid" (carte avec bannière/tag/liste à puces, utilisée sur
 * d'autres pages) : ici le visuel de référence de la Homepage v2 attend une carte
 * horizontale — pastille d'icône colorée à gauche, titre et description à droite,
 * flèche ronde en bas à droite.
 * Données CMS : $single (tag, title, subtitle), $groups (svc_icon, svc_title, svc_points, svc_link)
 * — mêmes clés de blocs que "services_grid" pour permettre de rebasculer un type vers
 * l'autre sans perte de contenu.
 * Règle #2 (zéro hardcode) : chaque élément non renseigné est simplement masqué.
 * Design System v4.1 — variables CSS uniquement
 */
$svcAccents = [
    ['var(--primary)',   'color-mix(in srgb, var(--primary) 10%, transparent)'],
    ['var(--secondary)', 'color-mix(in srgb, var(--secondary) 10%, transparent)'],
    ['var(--accent)',    'color-mix(in srgb, var(--accent) 12%, transparent)'],
];
?>

<section class="section-padding" id="services-grid" style="background:var(--bg-alt);">
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

        <div class="svc-v2-grid">
            <?php if (!empty($groups)): ?>
                <?php foreach ($groups as $i => $svc):
                    [$accent, $accentBg] = $svcAccents[$i % count($svcAccents)];
                    $desc = trim(str_replace('|', ' ', $svc['svc_points'] ?? ''));
                ?>
                    <div class="svc-v2-card reveal" style="transition-delay:<?= $i * 0.07 ?>s;">
                        <?php if (!empty($svc['svc_icon'])): ?>
                            <div class="svc-v2-icon" style="background:<?= $accentBg ?>;color:<?= $accent ?>;">
                                <?= \App\Helpers\IconHelper::render($svc['svc_icon'], ['size' => '24px']) ?>
                            </div>
                        <?php endif; ?>

                        <div class="svc-v2-body">
                            <?php if (!empty($svc['svc_title'])): ?>
                                <h3 class="svc-v2-title"><?= htmlspecialchars($svc['svc_title']) ?></h3>
                            <?php endif; ?>
                            <?php if (!empty($desc)): ?>
                                <p class="svc-v2-desc"><?= htmlspecialchars($desc) ?></p>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($svc['svc_link'])): ?>
                            <a href="<?= htmlspecialchars(url($svc['svc_link'])) ?>" class="svc-v2-arrow" aria-label="<?= htmlspecialchars($svc['svc_title'] ?? '') ?>">
                                <i data-lucide="arrow-right" style="width:15px;height:15px;"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
</section>

<style>
.svc-v2-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 22px;
}
@media (max-width: 1000px) { .svc-v2-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 660px)  { .svc-v2-grid { grid-template-columns: 1fr; } }

.svc-v2-card {
    position: relative;
    display: grid;
    grid-template-columns: 52px 1fr;
    gap: 18px;
    align-items: start;
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 24px 24px 42px;
    transition: var(--transition);
}
.svc-v2-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-card-hover);
    border-color: color-mix(in srgb, var(--primary) 20%, transparent);
}

.svc-v2-icon {
    width: 52px; height: 52px;
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}

.svc-v2-body { min-width: 0; }

.svc-v2-title {
    font-size: 0.98rem;
    font-weight: 700;
    color: var(--text-main);
    margin: 2px 0 8px;
    line-height: 1.35;
    font-family: var(--font-heading);
}

.svc-v2-desc {
    font-size: 0.82rem;
    line-height: 1.6;
    color: var(--text-muted);
    margin: 0;
}

.svc-v2-arrow {
    position: absolute;
    right: 20px; bottom: 18px;
    width: 28px; height: 28px;
    border-radius: 50%;
    border: 1px solid var(--border);
    display: flex; align-items: center; justify-content: center;
    color: var(--text-muted);
    transition: var(--transition-fast);
}
.svc-v2-card:hover .svc-v2-arrow {
    background: var(--primary);
    border-color: var(--primary);
    color: #fff;
}
</style>
