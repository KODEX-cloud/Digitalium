<section class="cta-sec section-padding" style="position:relative;overflow:hidden;text-align:center;background:linear-gradient(135deg,rgba(99,102,241,0.07) 0%,rgba(139,92,246,0.04) 100%);border-top:1px solid rgba(99,102,241,0.15);border-bottom:1px solid rgba(99,102,241,0.15);">

    <!-- Glow -->
    <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:600px;height:300px;background:radial-gradient(ellipse,rgba(99,102,241,0.1) 0%,transparent 70%);pointer-events:none;"></div>

    <div class="container reveal" style="position:relative;z-index:1;">

        <?php if (!empty($single['eyebrow'])): ?>
            <div style="font-size:0.68rem;letter-spacing:0.25em;text-transform:uppercase;color:#818cf8;margin-bottom:16px;font-family:var(--font-heading);font-weight:700;">
                <?= htmlspecialchars($single['eyebrow']) ?>
            </div>
        <?php endif; ?>

        <h2 style="font-size:clamp(2rem,4vw,3rem);font-weight:800;color:var(--text-main);margin-bottom:20px;line-height:1.15;font-family:var(--font-heading);letter-spacing:-0.03em;">
            <?= $single['title'] ?? 'Travaillons ensemble sur votre projet' ?>
        </h2>

        <p style="font-size:1rem;color:var(--text-sub);margin-bottom:40px;line-height:1.8;max-width:680px;margin-left:auto;margin-right:auto;">
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
