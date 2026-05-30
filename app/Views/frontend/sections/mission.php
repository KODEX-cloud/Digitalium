<section class="section-padding" id="mission">
    <div class="container">
        <div class="mission-wrap" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 60px; align-items: center;">
            
            <!-- Left Side -->
            <div class="reveal">
                <span class="section-tag" style="display: inline-flex; align-items: center; gap: 8px; font-size: 0.68rem; letter-spacing: 0.3em; text-transform: uppercase; color: var(--primary); margin-bottom: 1rem;">
                    <?= htmlspecialchars($single['tag'] ?? 'Notre Mission') ?>
                </span>
                <h2 class="section-title" style="font-size: clamp(1.8rem, 3vw, 2.6rem); font-weight: 700; color: var(--text-main); line-height: 1.2; margin-bottom: 1.5rem; font-family: var(--font-heading);"><?= $single['title'] ?? 'Votre succès, notre engagement' ?></h2>
                <div class="mission-text" style="font-size: 0.95rem; line-height: 1.85; color: var(--text-muted);">
                    <?= $single['description'] ?? '' ?>
                </div>
            </div>

            <!-- Right Side (Cards List) -->
            <div class="mission-cards reveal" style="display: flex; flex-direction: column; gap: 20px;">
                <?php if (!empty($groups)): ?>
                    <?php foreach ($groups as $card): ?>
                        <div class="mcard" style="display: flex; gap: 16px; align-items: flex-start; padding: 1.5rem; background: var(--bg-surface); border: 1px solid var(--border); border-radius: 10px; transition: var(--transition);">
                            <div class="mcard-icon" style="width: 42px; height: 42px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; background: var(--primary-glow); color: var(--primary);">
                                <i data-lucide="<?= htmlspecialchars($card['card_icon'] ?? 'check') ?>" style="width: 18px; height: 18px;"></i>
                            </div>
                            <div class="mcard-body">
                                <h4 style="font-size: 0.88rem; letter-spacing: 0.1em; text-transform: uppercase; color: var(--text-main); margin-bottom: 0.4rem; font-family: var(--font-heading); font-weight: 700;"><?= htmlspecialchars($card['card_title'] ?? '') ?></h4>
                                <p style="font-size: 0.82rem; line-height: 1.6; color: var(--text-muted);"><?= htmlspecialchars($card['card_description'] ?? '') ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>
    </div>
</section>
