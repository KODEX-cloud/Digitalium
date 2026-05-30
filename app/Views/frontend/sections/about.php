<section class="about-sec section-padding" id="about">
    <div class="container about-inner" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 50px; align-items: center;">
        
        <!-- Left Side: Content & Ticks -->
        <div class="reveal">
            <span class="stag" style="display: inline-flex; align-items: center; gap: 8px; font-size: 0.72rem; letter-spacing: 0.25em; text-transform: uppercase; color: var(--primary); margin-bottom: 1rem;">
                <?= htmlspecialchars($single['tag'] ?? 'Qui Sommes-Nous') ?>
            </span>
            <h2 class="stit" style="font-size: clamp(1.8rem, 3vw, 2.5rem); font-weight: 700; color: var(--text-main); margin-bottom: 1.2rem; font-family: var(--font-heading);"><?= htmlspecialchars($single['title'] ?? '') ?></h2>
            <p class="sdesc" style="font-size: 0.95rem; line-height: 1.8; color: var(--text-muted); margin-bottom: 2rem;"><?= htmlspecialchars($single['description'] ?? '') ?></p>
            
            <div class="checkpoints" style="display: flex; flex-direction: column; gap: 12px;">
                <?php for ($i = 1; $i <= 5; $i++): 
                    $checkKey = 'check_' . $i;
                    if (!empty($single[$checkKey])):
                ?>
                    <div class="cp" style="display: flex; align-items: center; gap: 10px; font-size: 0.9rem; color: var(--text-main);">
                        <div class="cp-dot" style="width: 20px; height: 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: rgba(34, 211, 238, 0.12); color: var(--accent); flex-shrink: 0;">
                            <i data-lucide="check" style="width: 12px; height: 12px;"></i>
                        </div>
                        <span><?= htmlspecialchars($single[$checkKey]) ?></span>
                    </div>
                <?php endif; endfor; ?>
            </div>
        </div>

        <!-- Right Side: Value Cards Grid -->
        <div class="about-right reveal" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
            <?php if (!empty($groups)): ?>
                <?php foreach ($groups as $card): ?>
                    <div class="val-card" style="padding: 1.5rem; background: var(--bg-surface); border: 1px solid var(--border); border-radius: 12px; transition: var(--transition);">
                        <div class="val-icon" style="width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem; background: var(--primary-glow); color: var(--primary);">
                            <i data-lucide="<?= htmlspecialchars($card['val_icon'] ?? 'star') ?>" style="width: 18px; height: 18px;"></i>
                        </div>
                        <h4 class="val-title" style="font-size: 0.9rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.4rem; font-family: var(--font-heading);"><?= htmlspecialchars($card['val_title'] ?? '') ?></h4>
                        <p class="val-text" style="font-size: 0.78rem; line-height: 1.5; color: var(--text-muted);"><?= htmlspecialchars($card['val_text'] ?? '') ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
</section>
