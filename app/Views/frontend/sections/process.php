<section class="process-sec section-padding" id="process" style="background: var(--bg-surface); position: relative; overflow: hidden;">
    <div class="container proc-inner" style="position: relative; z-index: 2;">
        
        <div class="proc-head reveal" style="text-align: center; margin-bottom: 4rem;">
            <span class="section-badge"><?= htmlspecialchars($single['tag'] ?? 'Méthodologie') ?></span>
            <h2 class="section-title" style="color: var(--text-main); font-family: var(--font-heading);"><?= htmlspecialchars($single['title'] ?? 'Six étapes vers votre transformation') ?></h2>
        </div>

        <div class="proc-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
            <?php if (!empty($groups)): ?>
                <?php foreach ($groups as $step): 
                    $pNum = $step['proc_num'] ?? $step['num'] ?? '01';
                    $pIcon = $step['proc_icon'] ?? $step['icon'] ?? 'check';
                    $pTitle = $step['proc_title'] ?? $step['title'] ?? '';
                    $pDesc = $step['proc_desc'] ?? $step['description'] ?? '';
                    $pImage = $step['proc_image'] ?? $step['image'] ?? '';
                    $pLink = $step['proc_link'] ?? $step['link'] ?? '';
                    $hasLink = !empty($pLink);
                    $cardTag = $hasLink ? 'a' : 'div';
                    $cardAttr = $hasLink ? 'href="' . htmlspecialchars($pLink) . '" style="text-decoration:none;"' : '';
                ?>
                    <<?= $cardTag ?> <?= $cardAttr ?> class="pc reveal" style="display: block; padding: 2rem; border: 1px solid var(--border); border-radius: 12px; background: var(--bg-surface-alt); transition: var(--transition);">
                        <div class="pc-num" style="font-size: 0.72rem; letter-spacing: 0.2em; color: var(--primary); font-weight: 700; margin-bottom: 0.8rem; font-family: var(--font-heading);">
                            <?= htmlspecialchars($pNum) ?>
                        </div>
                        <div class="pc-icon" style="width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 1.2rem; background: var(--primary-glow); color: var(--primary); overflow: hidden; padding: 10px;">
                            <?= \App\Helpers\IconHelper::render($pIcon, ['image' => $pImage, 'size' => '20px']) ?>
                        </div>
                        <h3 class="pc-title" style="font-size: 1.1rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.6rem; font-family: var(--font-heading);"><?= htmlspecialchars($pTitle) ?></h3>
                        <p class="pc-desc" style="font-size: 0.8rem; line-height: 1.6; color: var(--text-muted);"><?= htmlspecialchars($pDesc) ?></p>
                    </<?= $cardTag ?>>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
</section>
