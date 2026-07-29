<?php
/**
 * Section: values — Ce qui nous définit (grille de cartes valeurs)
 * Données CMS : $single (tag, title), $groups (val_icon, val_title, val_text)
 * Design System v4.1 — variables CSS uniquement
 */
?>

<section class="section-padding values-section" id="values">
    <div class="container values-inner">

        <!-- Section header -->
        <div class="section-header reveal">
            <span class="section-badge">
                <?= htmlspecialchars($single['tag'] ?? 'Valeurs') ?>
            </span>
            <h2 class="section-title">
                <?= htmlspecialchars($single['title'] ?? 'Ce qui nous définit') ?>
            </h2>
            <div class="section-divider"></div>
        </div>

        <!-- Values grid -->
        <div class="values-grid">
            <?php if (!empty($groups)):
                $vAccents = ['var(--primary)', 'var(--secondary)', 'var(--accent)', 'var(--primary)', 'var(--secondary)', 'var(--accent)'];
                foreach ($groups as $idx => $card):
                    $accent = $vAccents[$idx % count($vAccents)];
            ?>
                <div class="vcard reveal" style="transition-delay:<?= min($idx * 80, 400) ?>ms;">
                    <!-- Gradient top bar (animated on hover) -->
                    <div class="vbar-top" style="background:linear-gradient(90deg,<?= $accent ?>,var(--secondary));"></div>

                    <!-- Icon -->
                    <div class="vcard-icon-wrap" style="--vcard-accent:<?= $accent ?>;">
                        <i data-lucide="<?= htmlspecialchars($card['val_icon'] ?? 'check') ?>" style="width:22px;height:22px;"></i>
                    </div>

                    <!-- Label + Text -->
                    <h3 class="vcard-title" style="color:<?= $accent ?>;">
                        <?= htmlspecialchars($card['val_title'] ?? '') ?>
                    </h3>
                    <p class="vcard-text">
                        <?= htmlspecialchars($card['val_text'] ?? '') ?>
                    </p>
                </div>
            <?php endforeach; endif; ?>
        </div>

    </div>
</section>

<style>
/* ── Values section ────────────────────────────────────────────── */
.values-section { background: var(--bg-base); }

.values-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
    gap: 22px;
}

/* Value card */
.vcard {
    text-align: center;
    padding: 2.4rem 1.4rem 2rem;
    position: relative;
    overflow: hidden;
}

/* Gradient top accent bar — slides in on hover */
.vbar-top {
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}
.vcard:hover .vbar-top { transform: scaleX(1); }

/* Icon wrapper */
.vcard-icon-wrap {
    width: 58px; height: 58px;
    border-radius: 50%;
    background: rgba(13,148,136,0.08);
    border: 1.5px solid rgba(13,148,136,0.18);
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 1.4rem auto;
    color: var(--vcard-accent, var(--primary));
    transition: var(--transition);
}
.vcard:hover .vcard-icon-wrap {
    background: var(--vcard-accent, var(--primary));
    border-color: var(--vcard-accent, var(--primary));
    color: #fff;
    box-shadow: 0 10px 28px rgba(13,148,136,0.28);
    transform: scale(1.08) translateY(-3px);
}

.vcard-title {
    font-size: 0.8rem;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    margin-bottom: 0.8rem;
    font-weight: 700;
    font-family: var(--font-heading);
    line-height: 1.3;
}

.vcard-text {
    font-size: 0.84rem;
    line-height: 1.7;
    color: var(--text-muted);
    margin: 0;
}

.vcard:hover {
    border-color: rgba(13,148,136,0.2) !important;
    transform: translateY(-6px) !important;
}

/* Responsive */
@media (max-width: 768px) {
    .values-grid { grid-template-columns: 1fr 1fr; gap: 16px; }
    .vcard { padding: 2rem 1rem 1.6rem; }
}

@media (max-width: 480px) {
    .values-grid { grid-template-columns: 1fr; }
}
</style>
