<section class="section-padding" id="values">
    <div class="container values-inner">
        
        <div style="text-align: center; margin-bottom: 4rem;" class="reveal">
            <span class="section-tag" style="display: inline-flex; align-items: center; gap: 8px; font-size: 0.68rem; letter-spacing: 0.3em; text-transform: uppercase; color: var(--primary); margin-bottom: 1rem;"><?= htmlspecialchars($single['tag'] ?? 'Valeurs') ?></span>
            <h2 class="section-title" style="font-family: var(--font-heading); color: var(--text-main);"><?= htmlspecialchars($single['title'] ?? 'Ce qui nous définit') ?></h2>
        </div>

        <div class="values-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
            <?php if (!empty($groups)): ?>
                <?php foreach ($groups as $card): ?>
                    <div class="vcard reveal" style="text-align: center; padding: 2.2rem 1.2rem; border: 1px solid var(--border); border-radius: 12px; background: var(--bg-surface); transition: var(--transition); position: relative; overflow: hidden;">
                        <div class="vcard-icon" style="width: 48px; height: 48px; border-radius: 50%; border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; margin: 0 auto 1.2rem auto; color: var(--accent); background: var(--primary-glow);">
                            <i data-lucide="<?= htmlspecialchars($card['val_icon'] ?? 'check') ?>" style="width: 20px; height: 20px;"></i>
                        </div>
                        <h3 class="vcard-title" style="font-size: 0.8rem; letter-spacing: 0.1em; text-transform: uppercase; color: var(--accent); margin-bottom: 0.8rem; font-weight: 700; font-family: var(--font-heading);"><?= htmlspecialchars($card['val_title'] ?? '') ?></h3>
                        <p class="vcard-text" style="font-size: 0.8rem; line-height: 1.6; color: var(--text-muted);"><?= htmlspecialchars($card['val_text'] ?? '') ?></p>
                        
                        <!-- Colored bottom accent bar -->
                        <div style="position: absolute; bottom: 0; left: 0; right: 0; height: 3px; background: var(--primary); transform: scaleX(0); transition: transform 0.3s;" class="vbar"></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
</section>

<style>
.vcard:hover .vbar {
    transform: scaleX(1) !important;
}
.vcard:hover {
    border-color: rgba(99, 102, 241, 0.4) !important;
    transform: translateY(-5px) !important;
}
</style>
