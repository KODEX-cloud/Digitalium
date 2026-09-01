<?php
/**
 * Section: services_grid_v2 — Grille de services simple (icône + titre + texte + flèche)
 * Distincte de "services_grid" (carte avec bannière/tag/liste à puces, utilisée sur
 * d'autres pages) : ici le visuel de référence de la Homepage v2 attend une carte
 * minimaliste avec une icône colorée, un titre, un court texte et une flèche.
 * Données CMS : $single (tag, title, subtitle), $groups (svc_icon, svc_title, svc_points, svc_link)
 * — mêmes clés de blocs que "services_grid" pour permettre de rebasculer un type vers l'autre
 * sans perte de contenu.
 * Design System v4.1 — variables CSS uniquement, zéro hardcode
 */
$svcAccents = ['var(--primary)', 'var(--secondary)', 'var(--accent)'];
?>

<section class="section-padding" id="services-grid" style="background:var(--bg-alt);">
    <div class="container">

        <div class="section-header reveal">
            <?php if (!empty($single['tag'])): ?>
                <span class="section-badge"><?= htmlspecialchars($single['tag']) ?></span>
            <?php endif; ?>
            <h2 class="section-title"><?= htmlspecialchars($single['title'] ?? 'Nos Expertises') ?></h2>
            <?php if (!empty($single['subtitle'])): ?>
                <p class="section-subtitle"><?= htmlspecialchars($single['subtitle']) ?></p>
            <?php endif; ?>
        </div>

        <div class="svc-v2-grid">
            <?php if (!empty($groups)): ?>
                <?php foreach ($groups as $i => $svc):
                    $accent = $svcAccents[$i % count($svcAccents)];
                    $desc = trim(str_replace('|', ' ', $svc['svc_points'] ?? ''));
                ?>
                    <div class="svc-v2-card reveal" style="transition-delay:<?= $i * 0.08 ?>s;">
                        <div class="svc-v2-icon" style="background:<?= $accent ?>18;color:<?= $accent ?>;">
                            <?= \App\Helpers\IconHelper::render($svc['svc_icon'] ?? 'check', ['size' => '26px']) ?>
                        </div>
                        <h3 class="svc-v2-title"><?= htmlspecialchars($svc['svc_title'] ?? '') ?></h3>
                        <?php if (!empty($desc)): ?>
                            <p class="svc-v2-desc"><?= htmlspecialchars($desc) ?></p>
                        <?php endif; ?>
                        <a href="<?= htmlspecialchars(url($svc['svc_link'] ?? '/contact')) ?>" class="svc-v2-arrow" aria-label="En savoir plus">
                            <i data-lucide="arrow-up-right" style="width:16px;height:16px;"></i>
                        </a>
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
    gap: 24px;
}
@media (max-width: 900px) { .svc-v2-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 600px) { .svc-v2-grid { grid-template-columns: 1fr; } }

.svc-v2-card {
    position: relative;
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 28px 26px 26px;
    transition: var(--transition);
}
.svc-v2-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-card-hover);
    border-color: rgba(37,99,235,0.2);
}

.svc-v2-icon {
    width: 52px; height: 52px;
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 20px;
}

.svc-v2-title {
    font-size: 1.02rem;
    font-weight: 700;
    color: var(--text-main);
    margin-bottom: 10px;
    line-height: 1.3;
    font-family: var(--font-heading);
    padding-right: 34px;
}

.svc-v2-desc {
    font-size: 0.85rem;
    line-height: 1.6;
    color: var(--text-muted);
}

.svc-v2-arrow {
    position: absolute;
    top: 26px; right: 26px;
    width: 30px; height: 30px;
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
