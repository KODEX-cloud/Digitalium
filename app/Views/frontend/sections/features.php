<section class="section-padding" id="features" style="background:var(--bg-alt);">
    <div class="container">

        <div class="section-header reveal">
            <?php if (!empty($single['tag'])): ?>
                <span class="section-badge"><?= htmlspecialchars($single['tag']) ?></span>
            <?php endif; ?>
            <h2 class="section-title"><?= htmlspecialchars($single['title'] ?? 'Nos Points Forts') ?></h2>
            <?php if (!empty($single['subtitle'])): ?>
                <p class="section-subtitle"><?= htmlspecialchars($single['subtitle']) ?></p>
            <?php endif; ?>
        </div>

        <div class="feat-grid">
            <?php if (!empty($groups)): ?>
                <?php foreach ($groups as $i => $feat):
                    $fIcon  = $feat['feat_icon']  ?? $feat['card_icon']        ?? $feat['icon']        ?? 'check';
                    $fTitle = $feat['feat_title'] ?? $feat['card_title']       ?? $feat['title']       ?? '';
                    $fDesc  = $feat['feat_desc']  ?? $feat['card_description'] ?? $feat['description'] ?? '';
                    $fImage = $feat['feat_image'] ?? $feat['card_image']       ?? $feat['image']       ?? '';
                    $fLink  = $feat['feat_link']  ?? $feat['card_link']        ?? $feat['link']        ?? '';
                    $tag    = !empty($fLink) && $fLink !== '#' ? 'a' : 'div';
                    $attr   = ($tag === 'a') ? 'href="' . htmlspecialchars($fLink) . '"' : '';
                ?>
                <<?= $tag ?> <?= $attr ?> class="feat-item reveal" style="transition-delay:<?= $i * 0.1 ?>s;">
                    <!-- Icon -->
                    <div class="feat-icon">
                        <?= \App\Helpers\IconHelper::render($fIcon, ['image' => $fImage, 'size' => '26px']) ?>
                    </div>
                    <!-- Number accent -->
                    <div style="font-size:0.62rem;font-weight:800;letter-spacing:0.2em;color:rgba(13,148,136,0.35);font-family:var(--font-heading);margin-bottom:14px;"><?= sprintf('%02d', $i + 1) ?></div>
                    <h3 class="feat-title"><?= htmlspecialchars($fTitle) ?></h3>
                    <p class="feat-desc"><?= htmlspecialchars($fDesc) ?></p>
                    <?php if ($tag === 'a'): ?>
                    <div style="margin-top:20px;display:inline-flex;align-items:center;gap:6px;font-size:0.78rem;font-weight:700;color:var(--primary);letter-spacing:0.08em;text-transform:uppercase;">
                        <span>Découvrir</span>
                        <i data-lucide="arrow-right" style="width:13px;height:13px;"></i>
                    </div>
                    <?php endif; ?>
                </<?= $tag ?>>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
</section>
