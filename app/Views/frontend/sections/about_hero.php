<section class="hero section-padding" style="min-height: 85vh; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; position: relative; overflow: hidden; padding-bottom: 2rem;">
    <div class="container" style="display: flex; flex-direction: column; align-items: center; position: relative; z-index: 2;">
        
        <?php if (!empty($single['badge'])): ?>
            <div class="hero-badge reveal" style="display: inline-flex; align-items: center; gap: 8px; border: 1px solid var(--border); padding: 0.4rem 1.2rem; border-radius: 50px; font-size: 0.72rem; letter-spacing: 0.25em; text-transform: uppercase; color: var(--accent); margin-bottom: 2rem;">
                <div class="badge-dot" style="width: 6px; height: 6px; border-radius: 50%; background: var(--accent); animation: pulse 2s ease-in-out infinite;"></div>
                <?= htmlspecialchars($single['badge']) ?>
            </div>
        <?php endif; ?>

        <h1 class="hero-title reveal" style="font-size: clamp(2.4rem, 5.5vw, 4.2rem); font-weight: 800; line-height: 1.1; color: var(--text-main); margin-bottom: 1rem; font-family: var(--font-heading); max-width: 900px;">
            <?= $single['title'] ?? 'L\'excellence digitale au service de votre croissance' ?>
        </h1>

        <div class="accent-bar reveal" style="display: flex; gap: 4px; justify-content: center; margin: 1.8rem auto;">
            <span style="width: 40px; height: 3px; border-radius: 2px; background: #e03a3a;"></span>
            <span style="width: 40px; height: 3px; border-radius: 2px; background: #2eaa5c;"></span>
            <span style="width: 40px; height: 3px; border-radius: 2px; background: #f5b800;"></span>
            <span style="width: 40px; height: 3px; border-radius: 2px; background: #f07820;"></span>
            <span style="width: 40px; height: 3px; border-radius: 2px; background: #1a6fba;"></span>
        </div>

        <p class="hero-sub reveal" style="font-size: 1.1rem; line-height: 1.8; color: var(--text-muted); max-width: 680px; margin: 0 auto 3rem auto;">
            <?= htmlspecialchars($single['subtitle'] ?? '') ?>
        </p>

        <!-- Stats row -->
        <div class="hero-stats reveal" style="display: flex; gap: 50px; flex-wrap: wrap; justify-content: center; margin-top: 2rem;">
            <?php if (!empty($single['stats_years'])): ?>
                <div style="text-align: center;">
                    <div class="hero-stat-num" style="font-size: 2.5rem; font-weight: 800; color: var(--text-main); font-family: var(--font-heading);"><?= htmlspecialchars($single['stats_years']) ?><span class="unit" style="color: var(--primary);">+</span></div>
                    <div class="hero-stat-label" style="font-size: 0.72rem; letter-spacing: 0.15em; text-transform: uppercase; color: var(--text-muted); margin-top: 0.4rem; font-weight: 600;">Années d'expérience</div>
                </div>
            <?php endif; ?>
            <?php if (!empty($single['stats_clients'])): ?>
                <div style="text-align: center;">
                    <div class="hero-stat-num" style="font-size: 2.5rem; font-weight: 800; color: var(--text-main); font-family: var(--font-heading);"><?= htmlspecialchars($single['stats_clients']) ?><span class="unit" style="color: var(--primary);">+</span></div>
                    <div class="hero-stat-label" style="font-size: 0.72rem; letter-spacing: 0.15em; text-transform: uppercase; color: var(--text-muted); margin-top: 0.4rem; font-weight: 600;">Clients mondiaux</div>
                </div>
            <?php endif; ?>
            <?php if (!empty($single['stats_satisfaction'])): ?>
                <div style="text-align: center;">
                    <div class="hero-stat-num" style="font-size: 2.5rem; font-weight: 800; color: var(--text-main); font-family: var(--font-heading);"><?= htmlspecialchars($single['stats_satisfaction']) ?></div>
                    <div class="hero-stat-label" style="font-size: 0.72rem; letter-spacing: 0.15em; text-transform: uppercase; color: var(--text-muted); margin-top: 0.4rem; font-weight: 600;">Satisfaction client</div>
                </div>
            <?php endif; ?>
        </div>

    </div>
</section>

<style>
@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(0.7); }
}
</style>
