<section class="section-padding" id="features">
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
                    $fIcon  = $feat['feat_icon']  ?? $feat['icon']        ?? 'check';
                    $fTitle = $feat['feat_title'] ?? $feat['title']       ?? '';
                    $fDesc  = $feat['feat_desc']  ?? $feat['description'] ?? '';
                    $fImage = $feat['feat_image'] ?? $feat['image']       ?? '';
                    $fLink  = $feat['feat_link']  ?? $feat['link']        ?? '';
                    $tag    = !empty($fLink) ? 'a' : 'div';
                    $attr   = !empty($fLink) ? 'href="' . htmlspecialchars($fLink) . '"' : '';
                ?>
                    <<?= $tag ?> <?= $attr ?> class="feat-item reveal" style="transition-delay:<?= $i * 0.08 ?>s;">
                        <div class="feat-icon">
                            <?= \App\Helpers\IconHelper::render($fIcon, ['image' => $fImage, 'size' => '24px']) ?>
                        </div>
                        <h3 class="feat-title"><?= htmlspecialchars($fTitle) ?></h3>
                        <p class="feat-desc"><?= htmlspecialchars($fDesc) ?></p>
                    </<?= $tag ?>>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
</section>
