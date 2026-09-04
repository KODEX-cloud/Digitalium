<?php
/**
 * Section : services_grid_v2 — Grille de services (forme du modèle de référence)
 *
 * Carte verticale : pastille d'icône circulaire, titre, description, lien.
 * Une carte peut être mise en avant en aplat de couleur primaire (svc_featured = 1).
 *
 * Blocs attendus :
 *   single : tag, title, subtitle, card_link_text
 *   groups : svc_icon, svc_title, svc_points, svc_link, svc_featured
 *
 * Lit exactement les mêmes clés que `services_grid`, afin qu'une section puisse
 * basculer d'un type à l'autre sans perte de contenu.
 * Règle #2 (zéro hardcode) : chaque élément non renseigné est simplement masqué.
 */
$cardLinkText = trim($single['card_link_text'] ?? '');
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
                    $desc = trim(str_replace('|', ' ', $svc['svc_points'] ?? ''));
                    $featured = !empty($svc['svc_featured']) && $svc['svc_featured'] !== '0';
                ?>
                    <div class="svc-v2-card reveal<?= $featured ? ' is-featured' : '' ?>" style="transition-delay:<?= $i * 0.07 ?>s;">
                        <?php if (!empty($svc['svc_icon'])): ?>
                            <div class="svc-v2-icon">
                                <?= \App\Helpers\IconHelper::render($svc['svc_icon'], ['size' => '24px']) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($svc['svc_tag'])): ?>
                            <span class="svc-v2-tag"><?= htmlspecialchars($svc['svc_tag']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($svc['svc_title'])): ?>
                            <h3 class="svc-v2-title"><?= htmlspecialchars($svc['svc_title']) ?></h3>
                        <?php endif; ?>
                        <?php if (!empty($desc)): ?>
                            <p class="svc-v2-desc"><?= htmlspecialchars($desc) ?></p>
                        <?php endif; ?>

                        <?php if (!empty($svc['svc_link'])): ?>
                            <?php if ($featured && $cardLinkText !== ''): ?>
                                <a href="<?= htmlspecialchars(url($svc['svc_link'])) ?>" class="svc-v2-btn">
                                    <?= htmlspecialchars($cardLinkText) ?>
                                </a>
                            <?php else: ?>
                                <a href="<?= htmlspecialchars(url($svc['svc_link'])) ?>" class="svc-v2-arrow" aria-label="<?= htmlspecialchars($svc['svc_title'] ?? '') ?>">
                                    <i data-lucide="arrow-right" style="width:16px;height:16px;"></i>
                                </a>
                            <?php endif; ?>
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
    gap: 24px;
}
@media (max-width: 1000px) { .svc-v2-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 660px)  { .svc-v2-grid { grid-template-columns: 1fr; } }

/* Carte verticale — proportions relevées sur le modèle */
.svc-v2-card {
    position: relative;
    display: flex;
    flex-direction: column;
    min-height: 268px;
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 30px 28px 30px;
    transition: var(--transition);
}
.svc-v2-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-card-hover);
    border-color: color-mix(in srgb, var(--primary) 24%, transparent);
}

/* Pastille circulaire pleine, icône blanche */
.svc-v2-icon {
    width: 56px; height: 56px;
    border-radius: 50%;
    background: var(--primary);
    color: #ffffff;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    margin-bottom: 26px;
}

/* Catégorie du service (svc_tag) — masquée si le bloc n'est pas renseigné. */
.svc-v2-tag {
    display: inline-block;
    margin-bottom: 10px;
    padding: 4px 12px;
    border-radius: 999px;
    background: var(--badge-bg);
    color: var(--badge-text);
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
}
.svc-v2-card.is-featured .svc-v2-tag { background: rgba(255,255,255,0.18); color: #ffffff; }

.svc-v2-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text-main);
    margin: 0 0 12px;
    line-height: 1.25;
    letter-spacing: -0.01em;
    font-family: var(--font-heading);
}

.svc-v2-desc {
    font-size: 0.95rem;
    line-height: 1.55;
    color: var(--text-muted);
    margin: 0 0 18px;
}

/* Carte mise en avant : aplat de couleur primaire */
.svc-v2-card.is-featured {
    background: var(--primary);
    border-color: var(--primary);
}
.svc-v2-card.is-featured .svc-v2-title { color: #ffffff; }
.svc-v2-card.is-featured .svc-v2-desc  { color: rgba(255,255,255,0.82); }
.svc-v2-card.is-featured .svc-v2-icon  { background: #ffffff; color: var(--primary); }
.svc-v2-card.is-featured:hover         { border-color: var(--primary); }

.svc-v2-btn {
    align-self: flex-start;
    margin-top: auto;
    display: inline-flex; align-items: center; gap: 8px;
    padding: 11px 22px;
    background: #ffffff;
    color: var(--primary) !important;
    border-radius: var(--radius-btn);
    font-size: 0.92rem;
    font-weight: 600;
    text-decoration: none;
    transition: var(--transition-fast);
}
.svc-v2-btn:hover { transform: translateY(-2px); }

.svc-v2-arrow {
    position: absolute;
    right: 24px; bottom: 24px;
    width: 38px; height: 38px;
    border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    background: color-mix(in srgb, var(--primary) 8%, transparent);
    color: var(--primary);
    text-decoration: none;
    transition: var(--transition-fast);
}
.svc-v2-arrow:hover { background: var(--primary); color: #ffffff; }
.svc-v2-card.is-featured .svc-v2-arrow { background: rgba(255,255,255,0.16); color: #ffffff; }
</style>
