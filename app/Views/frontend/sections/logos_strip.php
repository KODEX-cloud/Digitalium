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
                            <img src="<?= htmlspecialchars(url($logo['logo_image'])) ?>" alt="<?= htmlspecialchars($logo['logo_name'] ?? '') ?>" loading="lazy">
                        <?php else: ?>
                            <?php if (!empty($logo['logo_icon'])): ?>
                                <?= \App\Helpers\IconHelper::render($logo['logo_icon'], ['size' => '16px']) ?>
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
    gap: 0;
    row-gap: 14px;
}

.logos-strip-item {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 0 20px;
    color: var(--text-muted);
    font-size: 0.82rem;
    font-weight: 600;
    letter-spacing: 0.01em;
    opacity: 0.8;
    transition: var(--transition-fast);
    text-decoration: none;
    border-left: 1px solid var(--border);
}

.logos-strip-item:first-child {
    border-left: none;
    padding-left: 0;
}

.logos-strip-item img {
    height: 18px;
    width: auto;
    max-width: 100px;
    object-fit: contain;
    filter: grayscale(1);
    opacity: 0.85;
    transition: var(--transition-fast);
}

.logos-strip-item:hover {
    opacity: 1;
    color: var(--primary);
}

.logos-strip-item:hover img {
    filter: grayscale(0);
    opacity: 1;
}
</style>
