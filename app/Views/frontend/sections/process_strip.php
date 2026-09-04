<section class="process-strip section-padding" style="background: var(--bg-surface-alt); border-top: 1px solid var(--border); border-bottom: 1px solid var(--border);">
    <div class="container">
        
        <div style="text-align: center; max-width: 600px; margin: 0 auto 3rem auto;" class="reveal">
            <span class="section-tag" style="display: inline-flex; align-items: center; gap: 8px; font-size: 0.68rem; letter-spacing: 0.3em; text-transform: uppercase; color: var(--primary); margin-bottom: 1rem;"><?= htmlspecialchars($single['tag'] ?? 'Processus') ?></span>
            <h2 class="section-title" style="color: var(--text-main); font-family: var(--font-heading);"><?= htmlspecialchars($single['title'] ?? 'Notre processus en 4 étapes') ?></h2>
            <?php if (!empty($single['subtitle'])): ?>
                <p class="section-subtitle" style="margin-top: 1rem;"><?= htmlspecialchars($single['subtitle']) ?></p>
            <?php endif; ?>
        </div>

        <div class="process-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; max-width: 1100px; margin: 3rem auto 0 auto;">
            <?php if (!empty($groups)): ?>
                <?php foreach ($groups as $proc): 
                    $hasLink = !empty($proc['proc_link']);
                    $cardTag = $hasLink ? 'a' : 'div';
                    $cardAttr = $hasLink ? 'href="' . htmlspecialchars($proc['proc_link']) . '" style="text-decoration:none;"' : '';
                ?>
                    <<?= $cardTag ?> <?= $cardAttr ?> class="proc-card reveal" style="text-align: center; display: block; padding: 2rem 1.2rem; background: var(--bg-surface); border: 1px solid var(--border); border-radius: 12px; transition: var(--transition);">
                        <div class="proc-num" style="font-size: 0.65rem; letter-spacing: 0.3em; color: var(--primary); margin-bottom: 0.8rem; font-weight: 700; font-family: var(--font-heading);"><?= htmlspecialchars($proc['proc_num'] ?? '01') ?></div>
                        <div class="proc-icon" style="width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.2rem auto; border: 1px solid var(--border); background: var(--primary-glow); color: var(--primary); overflow: hidden; padding: 10px;">
                            <?= \App\Helpers\IconHelper::render($proc['proc_icon'] ?? 'check', ['image' => $proc['proc_image'] ?? '', 'size' => '20px']) ?>
                        </div>
                        <h3 class="proc-title" style="font-size: 0.95rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem; font-family: var(--font-heading);"><?= htmlspecialchars($proc['proc_title'] ?? '') ?></h3>
                        <p class="proc-desc" style="font-size: 0.8rem; line-height: 1.6; color: var(--text-muted);"><?= htmlspecialchars($proc['proc_desc'] ?? '') ?></p>
                    </<?= $cardTag ?>>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
</section>

<style>
.proc-card:hover {
    border-color: color-mix(in srgb, var(--primary) 40%, transparent) !important;
    transform: translateY(-3px);
}
</style>
