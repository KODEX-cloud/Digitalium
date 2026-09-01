<?php
/**
 * Section: logos_strip — Bandeau "Ils nous font confiance"
 * Données CMS : $single (title), $groups (logo_name, logo_icon, logo_image, logo_link)
 * Design System v4.1 — variables CSS uniquement, zéro hardcode
 */
?>

<section class="section-padding-sm logos-strip-section">
    <div class="container">

        <?php if (!empty($single['title'])): ?>
            <div class="logos-strip-title reveal">
                <?= htmlspecialchars($single['title']) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($groups)): ?>
            <div class="logos-strip-row">
                <?php foreach ($groups as $logo): ?>
                    <?php
                        $hasLink = !empty($logo['logo_link']);
                        $tag = $hasLink ? 'a' : 'div';
                        $attr = $hasLink ? 'href="' . htmlspecialchars($logo['logo_link']) . '"' : '';
                    ?>
                    <<?= $tag ?> <?= $attr ?> class="logos-strip-item reveal">
                        <?php if (!empty($logo['logo_image'])): ?>
                            <img src="<?= htmlspecialchars(url($logo['logo_image'])) ?>" alt="<?= htmlspecialchars($logo['logo_name'] ?? 'Client') ?>" loading="lazy">
                        <?php else: ?>
                            <?php if (!empty($logo['logo_icon'])): ?>
                                <?= \App\Helpers\IconHelper::render($logo['logo_icon'], ['size' => '18px']) ?>
                            <?php endif; ?>
                            <span><?= htmlspecialchars($logo['logo_name'] ?? '') ?></span>
                        <?php endif; ?>
                    </<?= $tag ?>>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</section>

<style>
.logos-strip-section { border-top: 1px solid var(--border); border-bottom: 1px solid var(--border); }

.logos-strip-title {
    text-align: center;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--text-muted);
    margin-bottom: 32px;
}

.logos-strip-row {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    align-items: center;
    gap: 12px 16px;
}

.logos-strip-item {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border: 1px solid var(--border);
    border-radius: var(--radius-pill);
    color: var(--text-muted);
    font-size: 0.82rem;
    font-weight: 700;
    letter-spacing: 0.02em;
    filter: grayscale(1);
    opacity: 0.75;
    transition: var(--transition-fast);
    text-decoration: none;
}

.logos-strip-item img {
    height: 20px;
    width: auto;
    max-width: 110px;
    object-fit: contain;
}

.logos-strip-item:hover {
    filter: grayscale(0);
    opacity: 1;
    color: var(--primary);
    border-color: rgba(37,99,235,0.3);
    background: rgba(37,99,235,0.04);
}
</style>
