<?php
/**
 * Frontend Blog Listing Page
 */
require APP_PATH . '/Views/frontend/partials/hero.php';
?>

<section style="padding: 80px 0; background: var(--bg-base);">
    <div class="container">

        <!-- Header -->
        <div class="reveal" style="text-align: center; margin-bottom: 60px;">
            <span style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.2em; font-weight: 700; color: var(--primary);">Blog &amp; Insights</span>
            <h2 style="font-size: clamp(1.8rem, 3.5vw, 2.8rem); font-weight: 800; color: var(--text-main); margin: 12px 0 16px; font-family: var(--font-heading);">
                Actualités &amp; Expertise
            </h2>
            <p style="color: var(--text-muted); font-size: 1.05rem; max-width: 520px; margin: 0 auto;">
                Découvrez nos articles sur la transformation digitale, le développement logiciel et les tendances tech.
            </p>
        </div>

        <!-- Categories filter -->
        <?php if (!empty($categories)): ?>
        <div style="display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; margin-bottom: 48px;">
            <a href="<?= url('/blog') ?>" style="padding: 6px 18px; border-radius: 50px; font-size: 0.82rem; font-weight: 600; background: var(--primary); color: white; text-decoration: none;">Tous</a>
            <?php foreach ($categories as $cat): ?>
            <a href="<?= url('/blog?cat=' . urlencode($cat['slug'])) ?>" style="padding: 6px 18px; border-radius: 50px; font-size: 0.82rem; font-weight: 600; background: var(--bg-surface); border: 1px solid var(--border); color: var(--text-main); text-decoration: none;">
                <?= htmlspecialchars($cat['name']) ?>
                <span style="color: var(--text-muted); font-weight: 500;">(<?= (int)$cat['post_count'] ?>)</span>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Featured post -->
        <?php if ($featured): ?>
        <div class="reveal" style="margin-bottom: 56px;">
            <a href="<?= url('/blog/' . $featured['slug']) ?>" style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 0; background: var(--bg-surface); border: 1px solid var(--border); border-radius: 20px; overflow: hidden; text-decoration: none; transition: box-shadow 0.3s; box-shadow: 0 4px 20px rgba(0,0,0,0.03);">
                <div style="padding: 40px 44px; display: flex; flex-direction: column; justify-content: center;">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px;">
                        <span style="background: rgba(37,99,235,0.12); color: var(--primary); padding: 4px 14px; border-radius: 50px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em;">À la une</span>
                        <?php if ($featured['category']): ?>
                        <span style="color: var(--text-muted); font-size: 0.78rem;"><?= htmlspecialchars($featured['category']) ?></span>
                        <?php endif; ?>
                    </div>
                    <h2 style="font-size: clamp(1.4rem, 2.5vw, 2rem); font-weight: 800; color: var(--text-main); line-height: 1.2; margin-bottom: 16px; font-family: var(--font-heading);"><?= htmlspecialchars($featured['title']) ?></h2>
                    <?php if ($featured['excerpt']): ?>
                    <p style="color: var(--text-muted); font-size: 1rem; line-height: 1.7; margin-bottom: 28px;"><?= htmlspecialchars($featured['excerpt']) ?></p>
                    <?php endif; ?>
                    <div style="display: flex; align-items: center; gap: 16px; font-size: 0.82rem; color: var(--text-muted);">
                        <span><?= htmlspecialchars($featured['author'] ?? 'Équipe Digitalium') ?></span>
                        <span>&bull;</span>
                        <span><?= $featured['published_at'] ? date('d M Y', strtotime($featured['published_at'])) : '' ?></span>
                    </div>
                </div>
                <?php if ($featured['featured_image']): ?>
                <div style="background: url('<?= htmlspecialchars(url($featured['featured_image'])) ?>') center/cover no-repeat; min-height: 280px;"></div>
                <?php else: ?>
                <div style="background: linear-gradient(135deg, #1e1b4b 0%, #0b0f19 100%); min-height: 280px; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="file-text" style="width: 64px; height: 64px; color: rgba(255,255,255,0.15);"></i>
                </div>
                <?php endif; ?>
            </a>
        </div>
        <?php endif; ?>

        <!-- Posts grid -->
        <?php if (!empty($posts)): ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 28px; margin-bottom: 56px;">
            <?php foreach ($posts as $p):
                if ($featured && $p['id'] == $featured['id']) continue;
            ?>
            <article class="reveal" style="background: var(--bg-surface); border: 1px solid var(--border); border-radius: 18px; overflow: hidden; transition: box-shadow 0.3s, transform 0.3s; display: flex; flex-direction: column;">
                <a href="<?= url('/blog/' . $p['slug']) ?>" style="text-decoration: none; display: flex; flex-direction: column; flex: 1;">
                    <?php if ($p['featured_image']): ?>
                    <div style="height: 200px; background: url('<?= htmlspecialchars(url($p['featured_image'])) ?>') center/cover no-repeat; flex-shrink: 0;"></div>
                    <?php else: ?>
                    <div style="height: 140px; background: linear-gradient(135deg, rgba(37,99,235,0.1) 0%, rgba(8,145,178,0.08) 100%); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i data-lucide="file-text" style="width: 36px; height: 36px; color: var(--primary); opacity: 0.4;"></i>
                    </div>
                    <?php endif; ?>
                    <div style="padding: 24px; flex: 1; display: flex; flex-direction: column;">
                        <?php if ($p['category']): ?>
                        <span style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .12em; color: var(--primary); margin-bottom: 10px;"><?= htmlspecialchars($p['category']) ?></span>
                        <?php endif; ?>
                        <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--text-main); line-height: 1.35; margin-bottom: 10px; font-family: var(--font-heading);"><?= htmlspecialchars($p['title']) ?></h3>
                        <?php if ($p['excerpt']): ?>
                        <p style="color: var(--text-muted); font-size: 0.88rem; line-height: 1.65; margin-bottom: 20px; flex: 1;"><?= htmlspecialchars(mb_substr($p['excerpt'], 0, 120)) ?>...</p>
                        <?php endif; ?>
                        <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.78rem; color: var(--text-muted); margin-top: auto; padding-top: 16px; border-top: 1px solid var(--border);">
                            <span><?= $p['published_at'] ? date('d M Y', strtotime($p['published_at'])) : '' ?></span>
                            <span style="color: var(--primary); font-weight: 600; display: flex; align-items: center; gap: 4px;">
                                Lire <i data-lucide="arrow-right" style="width: 14px; height: 14px;"></i>
                            </span>
                        </div>
                    </div>
                </a>
            </article>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div style="text-align: center; padding: 60px 20px; color: var(--text-muted);">
            <i data-lucide="file-text" style="width: 48px; height: 48px; margin-bottom: 16px; opacity: 0.3;"></i>
            <p style="font-size: 1.1rem;">Aucun article publié pour le moment.</p>
        </div>
        <?php endif; ?>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div style="display: flex; justify-content: center; gap: 8px; align-items: center;">
            <?php if ($page_num > 1): ?>
            <a href="<?= url('/blog?page=' . ($page_num - 1)) ?>" style="padding: 8px 16px; border: 1px solid var(--border); border-radius: 8px; color: var(--text-main); text-decoration: none; font-size: 0.88rem;">← Précédent</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="<?= url('/blog?page=' . $i) ?>" style="padding: 8px 14px; border: 1px solid <?= $i === $page_num ? 'var(--primary)' : 'var(--border)' ?>; border-radius: 8px; background: <?= $i === $page_num ? 'var(--primary)' : 'transparent' ?>; color: <?= $i === $page_num ? 'white' : 'var(--text-main)' ?>; text-decoration: none; font-size: 0.88rem; font-weight: <?= $i === $page_num ? '700' : '500' ?>;"><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($page_num < $totalPages): ?>
            <a href="<?= url('/blog?page=' . ($page_num + 1)) ?>" style="padding: 8px 16px; border: 1px solid var(--border); border-radius: 8px; color: var(--text-main); text-decoration: none; font-size: 0.88rem;">Suivant →</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    </div>
</section>
