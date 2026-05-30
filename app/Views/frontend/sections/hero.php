<section class="hero-section" id="hero-<?= $sectionId ?>" style="position: relative; padding-top: 140px; padding-bottom: 80px; display: flex; align-items: center; overflow: hidden; background: var(--bg-base);">
    
    <div class="container hero-layout" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 50px; align-items: center; width: 100%; position: relative; z-index: 2;">
        
        <!-- Hero Left Content -->
        <div class="hero-title reveal">
            <?php if (!empty($single['badge'])): ?>
                <div class="hero-tag" style="display: inline-flex; align-items: center; gap: 8px; background: rgba(99, 102, 241, 0.12); border: 1px solid rgba(99, 102, 241, 0.3); padding: 0.35rem 1rem; border-radius: 50px; font-size: 0.68rem; letter-spacing: 0.22em; text-transform: uppercase; color: var(--accent); margin-bottom: 1.5rem;">
                    <div class="bdot" style="width: 6px; height: 6px; border-radius: 50%; background: var(--accent); animation: pulse 2s ease-in-out infinite;"></div>
                    <?= htmlspecialchars($single['badge']) ?>
                </div>
            <?php endif; ?>

            <div style="font-size: clamp(2.2rem, 4.5vw, 3.8rem); line-height: 1.12; color: var(--text-main); font-family: var(--font-heading); margin-bottom: 1.2rem;">
                <?= $single['title'] ?? '<h1>Des solutions technologiques innovantes</h1>' ?>
            </div>

            <!-- Colorful Accent Bar -->
            <div class="abar" style="display: flex; gap: 4px; margin: 1.4rem 0;">
                <span style="width: 32px; height: 3px; border-radius: 2px; background: #e03a3a;"></span>
                <span style="width: 32px; height: 3px; border-radius: 2px; background: #2eaa5c;"></span>
                <span style="width: 32px; height: 3px; border-radius: 2px; background: #f5b800;"></span>
                <span style="width: 32px; height: 3px; border-radius: 2px; background: #f07820;"></span>
                <span style="width: 32px; height: 3px; border-radius: 2px; background: #2d8fe0;"></span>
            </div>

            <p class="hero-description" style="font-size: 1.05rem; line-height: 1.8; color: var(--text-muted); margin-bottom: 2.2rem; max-width: 500px;">
                <?= htmlspecialchars($single['subtitle'] ?? '') ?>
            </p>

            <div class="hero-actions" style="display: flex; gap: 16px; flex-wrap: wrap;">
                <?php if (!empty($single['cta_text'])): ?>
                    <a href="<?= htmlspecialchars($single['cta_url'] ?? '#services') ?>" class="btn-hero-primary" style="padding: 14px 30px; font-weight: 600; border-radius: 8px;">
                        <span><?= htmlspecialchars($single['cta_text']) ?></span>
                        <i data-lucide="arrow-up-right" style="width: 18px; height: 18px;"></i>
                    </a>
                <?php endif; ?>
                <a href="#about" class="btn-hero-secondary" style="padding: 14px 30px; font-weight: 600; border-radius: 8px;">
                    <span>En savoir plus</span>
                </a>
            </div>
        </div>

        <!-- Hero Right AI visual & stats panel -->
        <div class="hero-right reveal" style="display: flex; flex-direction: column; gap: 20px; align-items: center; width: 100%;">
            <div class="ai-frame" style="width: 100%; max-width: 380px;">
                <div class="hero-image-box">
                    <?php if (!empty($single['bg_image'])): ?>
                        <img src="<?= htmlspecialchars($single['bg_image']) ?>" alt="Digitalium Hero" loading="lazy">
                    <?php else: ?>
                        <img src="/assets/images/hero_3d.png" alt="Digitalium Hero" loading="lazy">
                    <?php endif; ?>
                </div>
                <div class="ai-label" style="font-size: 0.72rem; letter-spacing: 0.2rem; text-transform: uppercase; color: var(--primary); text-align: center; font-weight: 700; margin-top: 6px;">
                    Digitalium Group
                    <strong style="display: block; font-size: 1.15rem; color: var(--text-main); letter-spacing: 0; margin-top: 4px; font-family: var(--font-heading);">Digital Innovation</strong>
                </div>
            </div>

            <!-- Stats strip -->
            <div class="hero-stats" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; width: 100%; max-width: 380px;">
                <div class="hstat" style="text-align: center; padding: 1rem 0.5rem; background: var(--bg-surface); border: 1px solid var(--border); border-radius: 10px;">
                    <div class="hstat-n" style="font-size: 1.6rem; font-weight: 800; color: var(--text-main); font-family: var(--font-heading);"><?= htmlspecialchars($single['stats_years'] ?? '10+') ?></div>
                    <div class="hstat-l" style="font-size: 0.65rem; letter-spacing: 0.1em; text-transform: uppercase; color: var(--text-muted); margin-top: 4px; font-weight: 600;">Expérience</div>
                </div>
                <div class="hstat" style="text-align: center; padding: 1rem 0.5rem; background: var(--bg-surface); border: 1px solid var(--border); border-radius: 10px;">
                    <div class="hstat-n" style="font-size: 1.6rem; font-weight: 800; color: var(--text-main); font-family: var(--font-heading);"><?= htmlspecialchars($single['stats_clients'] ?? '100+') ?></div>
                    <div class="hstat-l" style="font-size: 0.65rem; letter-spacing: 0.1em; text-transform: uppercase; color: var(--text-muted); margin-top: 4px; font-weight: 600;">Clients</div>
                </div>
                <div class="hstat" style="text-align: center; padding: 1rem 0.5rem; background: var(--bg-surface); border: 1px solid var(--border); border-radius: 10px;">
                    <div class="hstat-n" style="font-size: 1.6rem; font-weight: 800; color: var(--text-main); font-family: var(--font-heading);"><?= htmlspecialchars($single['stats_satisfaction'] ?? '98%') ?></div>
                    <div class="hstat-l" style="font-size: 0.65rem; letter-spacing: 0.1em; text-transform: uppercase; color: var(--text-muted); margin-top: 4px; font-weight: 600;">Satisfaction</div>
                </div>
            </div>
        </div>

    </div>
</section>

<style>
@keyframes glowPulse {
    0%, 100% { filter: drop-shadow(0 0 4px var(--accent)); transform: scale(1); }
    50% { filter: drop-shadow(0 0 15px var(--accent)); transform: scale(1.05); }
}
</style>
