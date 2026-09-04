<?php
/**
 * Section : capabilities_grid — Expertises transversales
 *
 * Grille compacte de domaines techniques : pastille d'icône, titre, description.
 * Volontairement plus dense que `services_grid_v2` : ce sont des capacités
 * transverses, pas des offres commerciales à cliquer.
 *
 * Blocs attendus — TOUT est administrable (Règle #2) :
 *   single : tag, title, subtitle
 *            cta_text, cta_url   bouton facultatif sous la grille
 *   groups : cap_icon, cap_title, cap_desc
 *
 * Le bouton est un ajout PUREMENT additif : sans `cta_text`, rien n'est rendu,
 * donc les pages qui utilisaient déjà cette section sont inchangées.
 */
$caps = $groups ?? [];
$capsCta = trim((string)($single['cta_text'] ?? ''));
?>

<section class="section-padding caps-section" style="background:var(--bg-base);">
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

        <div class="caps-grid">
            <?php foreach ($caps as $i => $cap):
                if (empty($cap['cap_title'])) { continue; }
            ?>
                <div class="cap-card reveal" style="transition-delay:<?= min($i * 60, 360) ?>ms;">
                    <?php if (!empty($cap['cap_icon'])): ?>
                        <span class="cap-icon"><?= \App\Helpers\IconHelper::render($cap['cap_icon'], ['size' => '20px']) ?></span>
                    <?php endif; ?>
                    <div class="cap-body">
                        <h3 class="cap-title"><?= htmlspecialchars($cap['cap_title']) ?></h3>
                        <?php if (!empty($cap['cap_desc'])): ?>
                            <p class="cap-desc"><?= htmlspecialchars($cap['cap_desc']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($capsCta !== ''): ?>
            <div class="caps-cta reveal">
                <a class="caps-cta-btn" href="<?= htmlspecialchars(url($single['cta_url'] ?? '/contact')) ?>">
                    <span><?= htmlspecialchars($capsCta) ?></span>
                    <i data-lucide="arrow-right" style="width:16px;height:16px;"></i>
                </a>
            </div>
        <?php endif; ?>

    </div>
</section>

<style>
.caps-cta { display: flex; justify-content: center; margin-top: 38px; }
.caps-cta-btn {
    display: inline-flex; align-items: center; gap: 9px;
    padding: 14px 30px; border-radius: 999px;
    background: var(--primary); color: #ffffff;
    font-size: 0.92rem; font-weight: 650; text-decoration: none;
    transition: var(--transition);
}
.caps-cta-btn:hover { transform: translateY(-2px); gap: 13px; box-shadow: var(--shadow-btn); }

.caps-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

.cap-card {
    display: grid;
    grid-template-columns: 46px 1fr;
    gap: 16px;
    align-items: start;
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 24px 22px;
    transition: var(--transition);
}
.cap-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-card-hover);
    border-color: color-mix(in srgb, var(--primary) 24%, transparent);
}

.cap-icon {
    display: inline-flex; align-items: center; justify-content: center;
    width: 46px; height: 46px;
    border-radius: 50%;
    background: color-mix(in srgb, var(--primary) 11%, transparent);
    color: var(--primary);
    flex-shrink: 0;
}

.cap-body { min-width: 0; }
.cap-title {
    font-size: 1.04rem;
    font-weight: 700;
    color: var(--text-main);
    line-height: 1.3;
    margin: 4px 0 7px;
    font-family: var(--font-heading);
}
.cap-desc { font-size: 0.89rem; line-height: 1.55; color: var(--text-muted); margin: 0; }

@media (max-width: 1000px) { .caps-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 620px)  { .caps-grid { grid-template-columns: 1fr; } }
</style>
