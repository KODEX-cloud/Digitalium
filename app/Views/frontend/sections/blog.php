<?php
/**
 * Section : blog — liste d'articles saisis dans la section (ancien gabarit).
 *
 * Sans article saisi, la section n'est pas rendue. Elle affichait auparavant
 * trois articles de démonstration, datés et catégorisés, qui se présentaient
 * comme publiés alors qu'ils n'existaient pas (Règle #2). Pour les vrais
 * articles, le module Insights fait autorité.
 */
if (empty($groups)) {
    return;
}
?>
<section class="section-padding" id="blog">
    <div class="container">

        <?php if (!empty($single['badge']) || !empty($single['title']) || !empty($single['subtitle'])): ?>
        <div class="section-header reveal">
            <?php if (!empty($single['badge'])): ?>
                <span class="section-badge"><?= htmlspecialchars($single['badge']) ?></span>
            <?php endif; ?>
            <?php if (!empty($single['title'])): ?>
                <h2 class="section-title"><?= htmlspecialchars($single['title']) ?></h2>
            <?php endif; ?>
            <?php if (!empty($single['subtitle'])): ?>
                <p class="section-subtitle"><?= htmlspecialchars($single['subtitle']) ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="blog-grid">
            <?php foreach ($groups as $i => $post): ?>
                <div class="blog-card reveal" style="transition-delay:<?= $i * 0.1 ?>s;">
                    <div class="blog-img-box">
                        <?php if (!empty($post['post_image'])): ?>
                            <img src="<?= htmlspecialchars(url($post['post_image'])) ?>" alt="<?= htmlspecialchars($post['post_title'] ?? '') ?>" loading="lazy">
                        <?php else: ?>
                            <div style="width:100%;height:100%;background:linear-gradient(135deg,color-mix(in srgb, var(--primary) 8%, transparent) 0%,color-mix(in srgb, var(--secondary) 5%, transparent) 100%);display:flex;align-items:center;justify-content:center;">
                                <i data-lucide="book-open" style="width:40px;height:40px;color:color-mix(in srgb, var(--primary) 30%, transparent);"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="blog-content">
                        <?php if (!empty($post['post_category']) || !empty($post['post_date'])): ?>
                            <div class="blog-meta">
                                <?php if (!empty($post['post_category'])): ?><span><?= htmlspecialchars($post['post_category']) ?></span><?php endif; ?>
                                <?php if (!empty($post['post_date'])): ?><span><?= htmlspecialchars($post['post_date']) ?></span><?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($post['post_title'])): ?>
                            <h3 class="blog-title"><?= htmlspecialchars($post['post_title']) ?></h3>
                        <?php endif; ?>
                        <?php if (!empty($post['post_summary'])): ?>
                            <p class="blog-summary"><?= htmlspecialchars($post['post_summary']) ?></p>
                        <?php endif; ?>
                        <?php $lien = trim((string)($post['post_url'] ?? $post['post_link'] ?? '')); ?>
                        <?php if ($lien !== ''): ?>
                            <a href="<?= htmlspecialchars(url($lien)) ?>" class="blog-readmore">
                                <span><?= htmlspecialchars(($single['read_more'] ?? '') !== '' ? $single['read_more'] : 'Lire la suite') ?></span>
                                <i data-lucide="arrow-right" style="width:14px;height:14px;"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>
