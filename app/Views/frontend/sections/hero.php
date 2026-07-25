<section class="hero-section" id="hero-<?= $sectionId ?>">

    <!-- Gradient de fond -->
    <div style="position:absolute;inset:0;background:radial-gradient(ellipse 70% 60% at 50% -10%,rgba(99,102,241,0.14) 0%,transparent 65%);pointer-events:none;z-index:0;"></div>

    <div class="container" style="position:relative;z-index:2;">
        <div class="hero-layout">

            <!-- Colonne gauche : contenu -->
            <div class="hero-title">

                <?php if (!empty($single['badge'])): ?>
                    <div class="hero-badge">
                        <span class="dot"></span>
                        <?= htmlspecialchars($single['badge']) ?>
                    </div>
                <?php endif; ?>

                <div style="font-size:clamp(2.6rem,5vw,4.2rem);line-height:1.06;font-family:var(--font-heading);font-weight:900;letter-spacing:-0.04em;color:var(--text-main);margin-bottom:28px;">
                    <?= $single['title'] ?? 'Des solutions<br>technologiques<br><span class="hi">innovantes</span>' ?>
                </div>

                <p class="hero-description">
                    <?= htmlspecialchars($single['subtitle'] ?? '') ?>
                </p>

                <div class="hero-actions">
                    <?php if (!empty($single['cta_text'])): ?>
                        <a href="<?= htmlspecialchars($single['cta_url'] ?? '#services') ?>" class="btn-hero-primary">
                            <span><?= htmlspecialchars($single['cta_text']) ?></span>
                            <i data-lucide="arrow-up-right" style="width:18px;height:18px;"></i>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($single['cta2_text'])): ?>
                        <a href="<?= htmlspecialchars($single['cta2_url'] ?? '#about') ?>" class="btn-hero-secondary">
                            <span><?= htmlspecialchars($single['cta2_text']) ?></span>
                            <i data-lucide="chevron-right" style="width:16px;height:16px;opacity:0.6;"></i>
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Stats -->
                <div class="hero-stats">
                    <div class="hstat">
                        <div class="stat-num" style="font-size:2rem;font-weight:900;font-family:var(--font-heading);background:linear-gradient(135deg,var(--primary),var(--secondary));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;line-height:1;">
                            <?= htmlspecialchars($single['stats_years'] ?? '10+') ?>
                        </div>
                        <div class="stat-label" style="font-size:0.72rem;color:var(--text-muted);font-weight:500;margin-top:6px;text-transform:uppercase;letter-spacing:0.08em;">
                            <?= htmlspecialchars($single['stats_label_years'] ?? 'Expérience') ?>
                        </div>
                    </div>
                    <div style="width:1px;background:var(--border);align-self:stretch;"></div>
                    <div class="hstat">
                        <div class="stat-num" style="font-size:2rem;font-weight:900;font-family:var(--font-heading);background:linear-gradient(135deg,var(--primary),var(--secondary));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;line-height:1;">
                            <?= htmlspecialchars($single['stats_clients'] ?? '100+') ?>
                        </div>
                        <div class="stat-label" style="font-size:0.72rem;color:var(--text-muted);font-weight:500;margin-top:6px;text-transform:uppercase;letter-spacing:0.08em;">
                            <?= htmlspecialchars($single['stats_label_clients'] ?? 'Clients') ?>
                        </div>
                    </div>
                    <div style="width:1px;background:var(--border);align-self:stretch;"></div>
                    <div class="hstat">
                        <div class="stat-num" style="font-size:2rem;font-weight:900;font-family:var(--font-heading);background:linear-gradient(135deg,var(--primary),var(--secondary));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;line-height:1;">
                            <?= htmlspecialchars($single['stats_satisfaction'] ?? '98%') ?>
                        </div>
                        <div class="stat-label" style="font-size:0.72rem;color:var(--text-muted);font-weight:500;margin-top:6px;text-transform:uppercase;letter-spacing:0.08em;">
                            <?= htmlspecialchars($single['stats_label_satisfaction'] ?? 'Satisfaction') ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Colonne droite : visuel -->
            <div class="hero-image-wrapper">
                <div class="ai-frame">
                    <div class="hero-image-box">
                        <?php if (!empty($single['bg_image'])): ?>
                            <img src="<?= htmlspecialchars(url($single['bg_image'])) ?>" alt="Digitalium Hero" loading="eager">
                        <?php else: ?>
                            <img src="<?= htmlspecialchars(url('/assets/images/hero_3d.png')) ?>" alt="Digitalium Hero" loading="eager">
                        <?php endif; ?>
                    </div>
                    <div style="display:flex;align-items:center;gap:10px;padding:12px 16px;background:rgba(99,102,241,0.08);border:1px solid rgba(99,102,241,0.2);border-radius:12px;width:100%;margin-top:4px;">
                        <div style="width:8px;height:8px;border-radius:50%;background:var(--success);box-shadow:0 0 8px var(--success);flex-shrink:0;"></div>
                        <span style="font-size:0.72rem;color:var(--text-sub);font-weight:500;letter-spacing:0.05em;">
                            <?= htmlspecialchars($settings['site_name'] ?? 'Digitalium Group') ?> — Systèmes actifs
                        </span>
                        <?php if (!empty($single['visual_label'])): ?>
                            <span style="margin-left:auto;font-size:0.72rem;color:#818cf8;font-weight:700;"><?= htmlspecialchars($single['visual_label']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div><!-- /.hero-layout -->
    </div>
</section>
