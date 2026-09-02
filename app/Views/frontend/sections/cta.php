<section class="cta-sec section-padding" style="position:relative;overflow:hidden;text-align:center;">

    <div class="container reveal" style="position:relative;z-index:1;">

        <?php if (!empty($single['eyebrow'])): ?>
            <div class="section-badge" style="margin-bottom:24px;">
                <?= htmlspecialchars($single['eyebrow']) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($single['title'])): ?>
            <h2 class="section-title">
                <?= $single['title'] ?>
            </h2>
        <?php endif; ?>

        <?php if (!empty($single['subtitle'])): ?>
            <p class="section-subtitle" style="margin:20px auto 44px;max-width:640px;">
                <?= htmlspecialchars($single['subtitle']) ?>
            </p>
        <?php endif; ?>

        <div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap;">
            <?php if (!empty($single['cta_text']) && !empty($single['cta_url'])): ?>
                <a href="<?= htmlspecialchars(url($single['cta_url'])) ?>" class="btn-hero-primary">
                    <span><?= htmlspecialchars($single['cta_text']) ?></span>
                    <i data-lucide="arrow-up-right" style="width:16px;height:16px;"></i>
                </a>
            <?php endif; ?>
            <?php if (!empty($single['cta2_text']) && !empty($single['cta2_url'])): ?>
                <a href="<?= htmlspecialchars(url($single['cta2_url'])) ?>" class="btn-hero-secondary">
                    <span><?= htmlspecialchars($single['cta2_text']) ?></span>
                </a>
            <?php endif; ?>
        </div>

    </div>
</section>
