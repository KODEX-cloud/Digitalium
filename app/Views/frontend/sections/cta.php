<section class="cta-sec section-padding" style="position:relative;overflow:hidden;text-align:center;">

    <div class="container reveal" style="position:relative;z-index:1;">

        <?php if (!empty($single['eyebrow'])): ?>
            <div class="section-badge" style="margin-bottom:24px;">
                <?= htmlspecialchars($single['eyebrow']) ?>
            </div>
        <?php endif; ?>

        <h2 class="section-title">
            <?= $single['title'] ?? 'Travaillons ensemble sur votre projet' ?>
        </h2>

        <p class="section-subtitle" style="margin:20px auto 44px;max-width:640px;">
            <?= htmlspecialchars($single['subtitle'] ?? 'Concevons des architectures performantes à forte valeur ajoutée.') ?>
        </p>

        <div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap;">
            <a href="<?= htmlspecialchars(url($single['cta_url'] ?? '/contact')) ?>" class="btn-hero-primary">
                <span><?= htmlspecialchars($single['cta_text'] ?? 'Commencer aujourd\'hui') ?></span>
                <i data-lucide="arrow-up-right" style="width:16px;height:16px;"></i>
            </a>
            <?php if (!empty($single['cta2_text'])): ?>
                <a href="<?= htmlspecialchars(url($single['cta2_url'] ?? '/service')) ?>" class="btn-hero-secondary">
                    <span><?= htmlspecialchars($single['cta2_text']) ?></span>
                </a>
            <?php endif; ?>
        </div>

    </div>
</section>
