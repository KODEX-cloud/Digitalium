<section class="section-padding" id="services-grid">
    <div class="container">

        <div class="section-header reveal">
            <?php if (!empty($single['tag'])): ?>
                <span class="section-badge"><?= htmlspecialchars($single['tag']) ?></span>
            <?php endif; ?>
            <h2 class="section-title"><?= htmlspecialchars($single['title'] ?? 'Nos Expertises') ?></h2>
            <?php if (!empty($single['subtitle'])): ?>
                <p class="section-subtitle"><?= htmlspecialchars($single['subtitle']) ?></p>
            <?php endif; ?>
        </div>

        <div class="svc-grid">
            <?php if (!empty($groups)): ?>
                <?php foreach ($groups as $i => $svc):
                    $points = array_filter(array_map('trim', explode('|', $svc['svc_points'] ?? '')));
                ?>
                    <div class="svc-card reveal" style="transition-delay:<?= $i * 0.08 ?>s;">

                        <!-- En-tête avec image ou dégradé -->
                        <div class="svc-card-header" style="height:150px;display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden;background:<?= !empty($svc['svc_image']) ? "url('" . htmlspecialchars($svc['svc_image']) . "') center/cover" : 'linear-gradient(135deg,rgba(99,102,241,0.15),rgba(139,92,246,0.08))' ?>;">
                            <?php if (!empty($svc['svc_image'])): ?>
                                <div style="position:absolute;inset:0;background:rgba(6,12,24,0.55);z-index:1;"></div>
                            <?php endif; ?>
                            <span style="position:absolute;top:14px;left:14px;padding:4px 12px;border-radius:100px;font-size:0.65rem;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;background:rgba(99,102,241,0.15);color:#a5b4fc;border:1px solid rgba(99,102,241,0.3);z-index:2;">
                                <?= htmlspecialchars($svc['svc_tag'] ?? 'Expertise') ?>
                            </span>
                            <div style="width:56px;height:56px;border-radius:50%;display:flex;align-items:center;justify-content:center;border:1px solid rgba(255,255,255,0.1);background:rgba(255,255,255,0.07);color:#a5b4fc;z-index:2;overflow:hidden;padding:12px;">
                                <?= \App\Helpers\IconHelper::render($svc['svc_icon'] ?? 'check', ['size' => '22px']) ?>
                            </div>
                        </div>

                        <!-- Corps de la carte -->
                        <div style="padding:28px;">
                            <h3 style="font-size:1.1rem;font-weight:700;color:var(--text-main);margin-bottom:16px;line-height:1.3;font-family:var(--font-heading);">
                                <?= htmlspecialchars($svc['svc_title'] ?? '') ?>
                            </h3>

                            <?php if (!empty($points)): ?>
                                <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:24px;min-height:90px;">
                                    <?php foreach ($points as $point): ?>
                                        <div style="display:flex;align-items:flex-start;gap:10px;font-size:0.85rem;line-height:1.55;color:var(--text-sub);">
                                            <div style="width:5px;height:5px;border-radius:50%;background:var(--primary);flex-shrink:0;margin-top:7px;opacity:0.8;"></div>
                                            <span><?= htmlspecialchars($point) ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div style="display:flex;justify-content:space-between;align-items:center;border-top:1px solid var(--border);padding-top:20px;">
                                <a href="<?= htmlspecialchars($svc['svc_link'] ?? '/contact') ?>" style="display:inline-flex;align-items:center;gap:6px;font-size:0.78rem;letter-spacing:0.08em;text-transform:uppercase;color:#818cf8;font-weight:700;transition:all 0.25s ease;">
                                    <span>Découvrir</span>
                                    <i data-lucide="arrow-right" style="width:14px;height:14px;"></i>
                                </a>
                                <span style="font-size:0.62rem;letter-spacing:0.1em;text-transform:uppercase;padding:3px 10px;border-radius:100px;font-weight:700;background:rgba(99,102,241,0.1);color:#818cf8;border:1px solid rgba(99,102,241,0.2);">
                                    Service Pro
                                </span>
                            </div>
                        </div>

                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
</section>
