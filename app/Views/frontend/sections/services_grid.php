<section class="services-section section-padding" id="services-grid">
    <div class="container">
        
        <div class="reveal" style="margin-bottom: 4rem;">
            <span class="section-tag" style="display: inline-flex; align-items: center; gap: 8px; font-size: 0.68rem; letter-spacing: 0.3em; text-transform: uppercase; color: var(--primary); margin-bottom: 1rem;"><?= htmlspecialchars($single['tag'] ?? 'Services') ?></span>
            <h2 class="section-title" style="color: var(--text-main); font-family: var(--font-heading);"><?= htmlspecialchars($single['title'] ?? 'Nos Expertises') ?></h2>
            <p class="section-sub" style="font-size: 0.92rem; color: var(--text-muted); line-height: 1.8; max-width: 700px;"><?= htmlspecialchars($single['subtitle'] ?? '') ?></p>
        </div>

        <div class="svc-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 30px;">
            <?php if (!empty($groups)): ?>
                <?php foreach ($groups as $svc): 
                    $points = explode('|', $svc['svc_points'] ?? '');
                ?>
                    <div class="svc-card reveal" style="background: var(--bg-surface); border: 1px solid var(--border); border-radius: 14px; overflow: hidden; transition: var(--transition);">
                        
                        <!-- Header with dynamic image or gradient background -->
                        <div class="svc-card-header" style="height: 140px; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden; background: <?= !empty($svc['svc_image']) ? "url('" . htmlspecialchars($svc['svc_image']) . "') center center / cover" : "linear-gradient(135deg, var(--primary-glow), rgba(13,34,71,0.4))" ?>;">
                            <?php if (!empty($svc['svc_image'])): ?>
                                <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.45); z-index: 1;"></div>
                            <?php endif; ?>
                            <span class="svc-tag" style="position: absolute; top: 14px; left: 14px; padding: 0.25rem 0.85rem; border-radius: 50px; font-size: 0.68rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; background: rgba(255,255,255,0.95); color: var(--primary); border: 1px solid var(--border); z-index: 2;">
                                <?= htmlspecialchars($svc['svc_tag'] ?? 'Expertise') ?>
                            </span>
                            <div class="svc-icon-wrap" style="width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid rgba(255,255,255,0.06); background: var(--bg-surface-alt); color: var(--primary); z-index: 2; overflow: hidden; padding: 12px;">
                                <?= \App\Helpers\IconHelper::render($svc['svc_icon'] ?? 'check', ['size' => '24px']) ?>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="svc-body" style="padding: 1.8rem 1.8rem 2rem 1.8rem;">
                            <h3 class="svc-title" style="font-size: 1.15rem; font-weight: 700; color: var(--text-main); margin-bottom: 1.2rem; line-height: 1.3; font-family: var(--font-heading);"><?= htmlspecialchars($svc['svc_title'] ?? '') ?></h3>
                            
                            <div class="svc-points" style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 1.5rem; min-height: 100px;">
                                <?php foreach ($points as $point): if (trim($point) !== ''): ?>
                                    <div class="svc-point" style="display: flex; align-items: flex-start; gap: 10px; font-size: 0.85rem; line-height: 1.5; color: var(--text-muted);">
                                        <div class="svc-point-dot" style="width: 6px; height: 6px; border-radius: 50%; background: var(--accent); flex-shrink: 0; margin-top: 6px;"></div>
                                        <span><?= htmlspecialchars(trim($point)) ?></span>
                                    </div>
                                <?php endif; endforeach; ?>
                            </div>

                            <div class="svc-footer" style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border); padding-top: 1.2rem;">
                                <a href="<?= htmlspecialchars($svc['svc_link'] ?? '/contact') ?>" class="svc-cta" style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.78rem; letter-spacing: 0.1em; text-transform: uppercase; text-decoration: none; color: var(--accent); font-weight: 600;">
                                    <span>Découvrir</span>
                                    <i data-lucide="arrow-right" style="width: 14px; height: 14px;"></i>
                                </a>
                                <span class="svc-badge" style="font-size: 0.65rem; letter-spacing: 0.08em; text-transform: uppercase; padding: 0.2rem 0.6rem; border-radius: 4px; font-weight: 700; background: var(--primary-glow); color: var(--primary);">Service Pro</span>
                            </div>
                        </div>

                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
</section>

<style>
.svc-card:hover {
    border-color: rgba(99, 102, 241, 0.45) !important;
    transform: translateY(-5px);
}
.svc-card:hover .svc-cta {
    gap: 10px !important;
}
</style>
