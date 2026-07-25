<section class="section-padding" id="process">
    <div class="container">

        <div class="section-header reveal">
            <span class="section-badge"><?= htmlspecialchars($single['tag'] ?? 'Méthodologie') ?></span>
            <h2 class="section-title"><?= htmlspecialchars($single['title'] ?? 'Six étapes vers votre transformation') ?></h2>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:20px;">
            <?php if (!empty($groups)): ?>
                <?php foreach ($groups as $i => $step):
                    $pNum   = $step['proc_num']   ?? $step['num']         ?? sprintf('%02d', $i + 1);
                    $pIcon  = $step['proc_icon']  ?? $step['icon']        ?? 'check';
                    $pTitle = $step['proc_title'] ?? $step['title']       ?? '';
                    $pDesc  = $step['proc_desc']  ?? $step['description'] ?? '';
                    $pImage = $step['proc_image'] ?? $step['image']       ?? '';
                    $pLink  = $step['proc_link']  ?? $step['link']        ?? '';
                    $tag    = !empty($pLink) ? 'a' : 'div';
                    $attr   = !empty($pLink) ? 'href="' . htmlspecialchars($pLink) . '"' : '';
                ?>
                    <<?= $tag ?> <?= $attr ?> class="proc-card reveal" style="transition-delay:<?= $i * 0.08 ?>s;">
                        <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
                            <span style="font-size:0.65rem;font-weight:800;font-family:var(--font-heading);color:rgba(13,148,136,0.4);letter-spacing:0.15em;">
                                <?= htmlspecialchars($pNum) ?>
                            </span>
                            <div style="width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;background:rgba(13,148,136,0.1);border:1px solid rgba(13,148,136,0.2);color:var(--primary);overflow:hidden;padding:10px;flex-shrink:0;">
                                <?= \App\Helpers\IconHelper::render($pIcon, ['image' => $pImage, 'size' => '20px']) ?>
                            </div>
                        </div>
                        <h3 style="font-size:1.05rem;font-weight:700;color:var(--text-main);margin-bottom:10px;font-family:var(--font-heading);">
                            <?= htmlspecialchars($pTitle) ?>
                        </h3>
                        <p style="font-size:0.85rem;line-height:1.65;color:var(--text-muted);">
                            <?= htmlspecialchars($pDesc) ?>
                        </p>
                    </<?= $tag ?>>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
</section>
