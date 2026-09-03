<section class="services-strip section-padding" id="services-strip-sec" style="background: var(--bg-surface-alt); border-top: 1px solid var(--border); border-bottom: 1px solid var(--border);">
    <div class="container">
        
        <div class="strip-title reveal" style="text-align: center; margin-bottom: 3.5rem;">
            <h2 style="font-size: 1.6rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem; font-family: var(--font-heading);"><?= htmlspecialchars($single['title'] ?? 'Des services rapides et efficaces') ?></h2>
            <p style="font-size: 0.88rem; color: var(--text-muted); max-width: 600px; margin: 0 auto;"><?= htmlspecialchars($single['subtitle'] ?? '') ?></p>
        </div>

        <div class="strip-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px;">
            <?php if (!empty($groups)): ?>
                <?php foreach ($groups as $card): ?>
                    <div class="strip-card reveal" style="text-align: center; padding: 2rem 1.2rem; border: 1px solid var(--border); border-radius: 12px; transition: var(--transition); background: var(--bg-surface);">
                        <div class="strip-icon" style="width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.2rem auto; background: var(--primary-glow); color: var(--primary);">
                            <i data-lucide="<?= htmlspecialchars($card['strip_icon'] ?? 'check') ?>" style="width: 20px; height: 20px;"></i>
                        </div>
                        <h3 class="strip-label" style="font-size: 0.88rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.4rem; font-family: var(--font-heading);"><?= htmlspecialchars($card['strip_label'] ?? '') ?></h3>
                        <div class="strip-sub" style="font-size: 0.72rem; color: var(--text-muted); line-height: 1.5;"><?= htmlspecialchars($card['strip_sub'] ?? '') ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
</section>

<style>
.strip-card:hover {
    border-color: color-mix(in srgb, var(--primary) 45%, transparent) !important;
    transform: translateY(-3px);
    background: var(--bg-surface-alt) !important;
}
</style>
