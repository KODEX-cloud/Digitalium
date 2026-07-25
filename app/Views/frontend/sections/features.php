<section class="features section-padding-sm" style="background:rgba(255,255,255,0.018);border-top:1px solid var(--border);border-bottom:1px solid var(--border);">
    <div class="container">

        <?php if (!empty($single['title'])): ?>
            <div class="section-header" style="margin-bottom:3rem;">
                <span class="section-badge">Atouts</span>
                <h2 class="section-title"><?= htmlspecialchars($single['title']) ?></h2>
            </div>
        <?php endif; ?>

        <div class="feat-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:20px;">
            <?php if (!empty($groups)): ?>
                <?php foreach ($groups as $i => $card):
                    $hasLink = !empty($card['card_link']);
                    $tag = $hasLink ? 'a' : 'div';
                    $attr = $hasLink ? 'href="' . htmlspecialchars($card['card_link']) . '"' : '';
                ?>
                    <<?= $tag ?> <?= $attr ?> class="feat-item reveal" style="text-align:center;padding:32px 24px;transition-delay:<?= $i * 0.08 ?>s;">
                        <div class="feat-icon" style="width:52px;height:52px;border-radius:50%;background:rgba(99,102,241,0.1);border:1px solid rgba(99,102,241,0.2);color:var(--primary);display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
                            <?= \App\Helpers\IconHelper::render($card['card_icon'] ?? 'star', ['image' => $card['card_image'] ?? '', 'size' => '22px']) ?>
                        </div>
                        <h3 style="font-size:1rem;font-weight:700;color:var(--text-main);margin-bottom:10px;font-family:var(--font-heading);">
                            <?= htmlspecialchars($card['card_title'] ?? '') ?>
                        </h3>
                        <p style="font-size:0.85rem;line-height:1.65;color:var(--text-sub);">
                            <?= htmlspecialchars($card['card_description'] ?? '') ?>
                        </p>
                    </<?= $tag ?>>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
</section>
