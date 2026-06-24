<section class="cta-sec section-padding" style="text-align: center; background: linear-gradient(135deg, var(--bg-surface) 0%, var(--bg-surface-alt) 100%); border-top: 1px solid var(--border); border-bottom: 1px solid var(--border);">
    <div class="container">
        
        <?php if (!empty($single['eyebrow'])): ?>
            <div style="font-size: 0.72rem; letter-spacing: 0.25em; text-transform: uppercase; color: var(--accent); margin-bottom: 1.2rem; font-family: var(--font-heading); font-weight: 600;">
                <?= htmlspecialchars($single['eyebrow']) ?>
            </div>
        <?php endif; ?>

        <h2 style="font-size: clamp(2rem, 4vw, 3rem); font-weight: 700; color: var(--text-main); margin-bottom: 1.2rem; line-height: 1.2; font-family: var(--font-heading);">
            <?= $single['title'] ?? 'Travaillons ensemble sur votre projet' ?>
        </h2>

        <p style="font-size: 1rem; color: var(--text-muted); margin-bottom: 2.5rem; line-height: 1.8; max-width: 750px; margin-left: auto; margin-right: auto;">
            <?= htmlspecialchars($single['subtitle'] ?? 'Concevons des architectures performantes à forte valeur ajoutée.') ?>
        </p>

        <div class="cta-btns" style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
            <a href="<?= htmlspecialchars(url($single['cta_url'] ?? '/contact')) ?>" class="btn-hero-primary" style="padding: 14px 30px; font-weight: 600; border-radius: 8px;">
                <span><?= htmlspecialchars($single['cta_text'] ?? 'Commencer aujourd\'hui') ?></span>
                <i data-lucide="arrow-up-right" style="width: 16px; height: 16px;"></i>
            </a>
            <a href="<?= htmlspecialchars(url($single['cta2_url'] ?? '/service')) ?>" class="btn-hero-secondary" style="padding: 14px 30px; font-weight: 600; border-radius: 8px;">
                <span><?= htmlspecialchars($single['cta2_text'] ?? 'Découvrir nos Expertises') ?></span>
            </a>
        </div>

    </div>
</section>
