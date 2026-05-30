<section class="features" style="padding: 3.5rem 0; background: var(--bg-surface); border-bottom: 1px solid var(--border);">
    <div class="container">
        
        <?php if (!empty($single['title'])): ?>
            <div class="section-header" style="margin-bottom: 3rem;">
                <span class="section-badge">Atouts</span>
                <h2 class="section-title"><?= htmlspecialchars($single['title']) ?></h2>
            </div>
        <?php endif; ?>

        <div class="feat-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 30px;">
            <?php if (!empty($groups)): ?>
                <?php foreach ($groups as $card): 
                    $hasLink = !empty($card['card_link']);
                    $cardTag = $hasLink ? 'a' : 'div';
                    $cardAttr = $hasLink ? 'href="' . htmlspecialchars($card['card_link']) . '" style="text-decoration:none;"' : '';
                ?>
                    <<?= $cardTag ?> <?= $cardAttr ?> class="feat-item reveal" style="display: block; padding: 2rem; background: var(--bg-surface-alt); border: 1px solid var(--border); border-radius: 12px; transition: var(--transition);">
                        <div class="feat-icon" style="width: 54px; height: 54px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.2rem auto; background: var(--primary-glow); color: var(--primary); overflow: hidden; padding: 12px;">
                            <?= \App\Helpers\IconHelper::render($card['card_icon'] ?? 'star', ['image' => $card['card_image'] ?? '', 'size' => '24px']) ?>
                        </div>
                        <h3 class="feat-title" style="font-size: 1.1rem; font-weight: 700; color: var(--accent); margin-bottom: 0.8rem; font-family: var(--font-heading);"><?= htmlspecialchars($card['card_title'] ?? '') ?></h3>
                        <p class="feat-desc" style="font-size: 0.85rem; line-height: 1.6; color: var(--text-muted);"><?= htmlspecialchars($card['card_description'] ?? '') ?></p>
                    </<?= $cardTag ?>>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
</section>
