<?php
/**
 * Section : sectors_grid — Grille de secteurs d'activité numérotés
 *
 * Carte : numéro d'ordre, pastille d'icône (ou image), titre, description,
 * liste de besoins, lien « Explorer ».
 *
 * Blocs attendus — TOUT est administrable (Règle #2) :
 *   single : tag, title, subtitle,
 *            more_text  libellé d'un bouton sous la grille (vide = pas de bouton)
 *            more_url   sa destination
 *   groups : sec_num, sec_icon, sec_image, sec_title, sec_desc,
 *            sec_needs (séparés par « | »), sec_link, sec_link_text
 *
 * Chaque champ vide est simplement masqué : aucun texte de repli en dur.
 * Un secteur se désactive en vidant son titre, ou se supprime depuis
 * /admin/pages (suppression de groupe).
 */
$sectors = $groups ?? [];
?>

<section class="section-padding sectors-section" id="secteurs" style="background:var(--bg-base);">
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

        <div class="sectors-grid">
            <?php foreach ($sectors as $i => $sec):
                if (empty($sec['sec_title'])) { continue; }
                $needs = array_values(array_filter(array_map('trim', explode('|', $sec['sec_needs'] ?? ''))));
            ?>
                <article class="sector-card reveal" style="transition-delay:<?= min($i * 60, 420) ?>ms;">

                    <div class="sector-card-head">
                        <?php if (!empty($sec['sec_image'])): ?>
                            <span class="sector-card-media">
                                <img src="<?= htmlspecialchars(url($sec['sec_image'])) ?>"
                                     alt="<?= htmlspecialchars($sec['sec_title']) ?>" loading="lazy">
                            </span>
                        <?php elseif (!empty($sec['sec_icon'])): ?>
                            <span class="sector-card-icon">
                                <?= \App\Helpers\IconHelper::render($sec['sec_icon'], ['size' => '22px']) ?>
                            </span>
                        <?php endif; ?>

                        <?php if (!empty($sec['sec_num'])): ?>
                            <span class="sector-card-num"><?= htmlspecialchars($sec['sec_num']) ?></span>
                        <?php endif; ?>
                    </div>

                    <h3 class="sector-card-title"><?= htmlspecialchars($sec['sec_title']) ?></h3>

                    <?php if (!empty($sec['sec_desc'])): ?>
                        <p class="sector-card-desc"><?= htmlspecialchars($sec['sec_desc']) ?></p>
                    <?php endif; ?>

                    <?php if (!empty($needs)): ?>
                        <ul class="sector-card-needs">
                            <?php foreach ($needs as $need): ?>
                                <li>
                                    <i data-lucide="check" style="width:14px;height:14px;"></i>
                                    <span><?= htmlspecialchars($need) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <?php if (!empty($sec['sec_link']) && !empty($sec['sec_link_text'])): ?>
                        <a href="<?= htmlspecialchars(url($sec['sec_link'])) ?>" class="sector-card-link">
                            <span><?= htmlspecialchars($sec['sec_link_text']) ?></span>
                            <i data-lucide="arrow-right" style="width:15px;height:15px;"></i>
                        </a>
                    <?php endif; ?>

                </article>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($single['more_text'])): ?>
            <div class="sec-more reveal">
                <a class="sec-more-btn" href="<?= htmlspecialchars(url($single['more_url'] ?? '/secteurs')) ?>">
                    <?= htmlspecialchars($single['more_text']) ?>
                    <i data-lucide="arrow-right" style="width:16px;height:16px;"></i>
                </a>
            </div>
        <?php endif; ?>

    </div>
</section>

<style>
.sec-more { display: flex; justify-content: center; margin-top: 40px; }
.sec-more-btn {
    display: inline-flex; align-items: center; gap: 9px;
    padding: 14px 30px; border-radius: 999px;
    background: var(--primary); color: #ffffff;
    font-size: 0.92rem; font-weight: 650; text-decoration: none;
    transition: var(--transition);
}
.sec-more-btn:hover { transform: translateY(-2px); gap: 13px; box-shadow: var(--shadow-btn); }


.sectors-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 24px;
}

.sector-card {
    display: flex;
    flex-direction: column;
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 26px 24px 24px;
    transition: var(--transition);
}
.sector-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-card-hover);
    border-color: color-mix(in srgb, var(--primary) 26%, transparent);
}

.sector-card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 20px;
}
.sector-card-icon {
    display: inline-flex; align-items: center; justify-content: center;
    width: 50px; height: 50px;
    border-radius: 50%;
    background: var(--primary);
    color: #ffffff;
    flex-shrink: 0;
}
.sector-card-media {
    display: inline-flex; align-items: center; justify-content: center;
    width: 50px; height: 50px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
    background: var(--bg-alt);
}
.sector-card-media img { width: 100%; height: 100%; object-fit: cover; }

/* Numéro d'ordre — repère de lecture, pas un élément décoratif */
.sector-card-num {
    font-size: 1.5rem;
    font-weight: 800;
    line-height: 1;
    color: color-mix(in srgb, var(--primary) 22%, transparent);
    font-family: var(--font-heading);
    letter-spacing: -0.02em;
}

.sector-card-title {
    font-size: 1.12rem;
    font-weight: 700;
    color: var(--text-main);
    line-height: 1.3;
    margin: 0 0 10px;
    font-family: var(--font-heading);
}
.sector-card-desc {
    font-size: 0.92rem;
    line-height: 1.55;
    color: var(--text-muted);
    margin: 0 0 18px;
}

.sector-card-needs { list-style: none; margin: 0 0 20px; padding: 0; }
.sector-card-needs li {
    display: flex; align-items: flex-start; gap: 8px;
    font-size: 0.87rem;
    line-height: 1.45;
    color: var(--text-main);
    margin-bottom: 9px;
}
.sector-card-needs li i { color: var(--primary); flex-shrink: 0; margin-top: 3px; }

.sector-card-link {
    margin-top: auto;
    display: inline-flex; align-items: center; gap: 7px;
    font-size: 0.88rem;
    font-weight: 700;
    color: var(--primary) !important;
    text-decoration: none;
    transition: var(--transition-fast);
}
.sector-card-link:hover { gap: 11px; }

@media (max-width: 1200px) { .sectors-grid { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 900px)  { .sectors-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 560px)  { .sectors-grid { grid-template-columns: 1fr; } }
</style>
