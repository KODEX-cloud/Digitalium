<section class="hero section-padding" id="contact-hero" style="min-height: 50vh; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; position: relative; overflow: hidden; padding-bottom: 2rem; background: linear-gradient(180deg, var(--bg-surface) 0%, var(--bg-base) 100%); border-bottom: 1px solid var(--border);">
    <div class="container" style="display: flex; flex-direction: column; align-items: center; position: relative; z-index: 2;">
        
        <?php if (!empty($single['badge'])): ?>
            <div class="hero-badge reveal" style="display: inline-flex; align-items: center; gap: 8px; border: 1px solid var(--border); padding: 0.4rem 1.2rem; border-radius: 50px; font-size: 0.72rem; letter-spacing: 0.25em; text-transform: uppercase; color: var(--accent); margin-bottom: 2rem;">
                <div class="badge-dot" style="width: 6px; height: 6px; border-radius: 50%; background: var(--accent); animation: pulse 2s ease-in-out infinite;"></div>
                <?= htmlspecialchars($single['badge']) ?>
            </div>
        <?php endif; ?>

        <h1 class="hero-title reveal" style="font-size: clamp(2.4rem, 5.5vw, 4.2rem); font-weight: 800; line-height: 1.1; color: var(--text-main); margin-bottom: 1rem; font-family: var(--font-heading); max-width: 900px;">
            <?= $single['title'] ?? 'Transformons vos idées en solutions performantes' ?>
        </h1>

        <div class="accent-bar reveal" style="display: flex; gap: 4px; justify-content: center; margin: 1.8rem auto;">
            <span style="width: 40px; height: 3px; border-radius: 2px; background: #e03a3a;"></span>
            <span style="width: 40px; height: 3px; border-radius: 2px; background: #2eaa5c;"></span>
            <span style="width: 40px; height: 3px; border-radius: 2px; background: #f5b800;"></span>
            <span style="width: 40px; height: 3px; border-radius: 2px; background: #f07820;"></span>
            <span style="width: 40px; height: 3px; border-radius: 2px; background: #1a6fba;"></span>
        </div>

        <p class="hero-sub reveal" style="font-size: 1.1rem; line-height: 1.8; color: var(--text-muted); max-width: 680px; margin: 0 auto 1rem auto;">
            <?= htmlspecialchars($single['subtitle'] ?? '') ?>
        </p>

    </div>
</section>
