<div class="blog-section section-padding" style="padding-top: 1rem;">
    <div class="container">
        
        <div class="blog-header reveal" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 3rem;">
            <div>
                <span class="section-tag" style="display: inline-flex; align-items: center; gap: 8px; font-size: 0.68rem; letter-spacing: 0.3em; text-transform: uppercase; color: var(--primary); margin-bottom: 1rem;"><?= htmlspecialchars($single['tag'] ?? 'Derniers articles') ?></span>
                <h2 style="font-size: 1.6rem; font-weight: 700; color: var(--text-main); font-family: var(--font-heading);"><?= htmlspecialchars($single['title'] ?? 'Tous nos contenus') ?></h2>
            </div>
            
            <select style="background: var(--bg-surface); border: 1px solid var(--border); color: var(--text-muted); padding: 0.45rem 1rem; border-radius: 6px; font-size: 0.8rem; outline: none; font-weight: 600;">
                <option>Plus récents</option>
                <option>Plus populaires</option>
            </select>
        </div>

        <div class="blog-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 30px;">
            <?php if (!empty($groups)): ?>
                <?php foreach ($groups as $post): 
                    $category = $post['post_category'] ?? 'Intelligence Artificielle';
                ?>
                    <div class="bcard reveal" data-tag="<?= htmlspecialchars($category) ?>" style="background: var(--bg-surface); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; transition: var(--transition);">
                        
                        <!-- Header / Image Box with dynamic cover image support -->
                        <div class="bcard-img" style="height: 140px; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden; background: <?= !empty($post['post_image']) ? "url('" . htmlspecialchars($post['post_image']) . "') center center / cover" : "linear-gradient(135deg, var(--primary-glow), rgba(13,34,71,0.5))" ?>;">
                            <?php if (!empty($post['post_image'])): ?>
                                <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.45); z-index: 1;"></div>
                            <?php endif; ?>
                            <div class="bcard-icon" style="width: 56px; height: 56px; border-radius: 12px; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(255,255,255,0.06); background: var(--bg-surface-alt); color: var(--primary); z-index: 2; overflow: hidden; padding: 12px;">
                                <?= \App\Helpers\IconHelper::render($post['post_icon'] ?? 'book-open', ['size' => '24px']) ?>
                            </div>
                        </div>

                        <!-- Card Content Body -->
                        <div class="bcard-body" style="padding: 1.5rem;">
                            <div class="bcard-meta" style="display: flex; gap: 8px; align-items: center; margin-bottom: 0.8rem; flex-wrap: wrap;">
                                <span class="pill" style="padding: 0.22rem 0.75rem; border-radius: 50px; font-size: 0.65rem; letter-spacing: 0.05em; text-transform: uppercase; font-weight: 700; background: var(--primary-glow); color: var(--accent);">
                                    <?= htmlspecialchars($category) ?>
                                </span>
                                <span class="bcard-date" style="font-size: 0.72rem; color: var(--text-muted); font-family: monospace;">
                                    <?= htmlspecialchars($post['post_date'] ?? date('d.m.Y')) ?>
                                </span>
                            </div>

                            <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--text-main); line-height: 1.4; margin-bottom: 0.8rem; font-family: var(--font-heading);">
                                <a href="<?= htmlspecialchars($post['post_link'] ?? '/blog') ?>" style="transition: var(--transition); hover: color: var(--primary);">
                                    <?= htmlspecialchars($post['post_title'] ?? 'Article sans titre') ?>
                                </a>
                            </h3>
                            
                            <p style="font-size: 0.82rem; line-height: 1.6; color: var(--text-muted); margin-bottom: 1.5rem;">
                                <?= htmlspecialchars($post['post_summary'] ?? '') ?>
                            </p>

                            <div class="bcard-footer" style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border); padding-top: 1rem;">
                                <div class="bcard-author" style="display: flex; align-items: center; gap: 8px;">
                                    <div class="author-av" style="width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.65rem; font-weight: 700; background: var(--primary-glow); color: var(--accent); border: 1px solid var(--border);">
                                        DG
                                    </div>
                                    <span class="author-name" style="font-size: 0.72rem; color: var(--text-muted);">Équipe DG</span>
                                </div>
                                <span class="bcard-read" style="font-size: 0.72rem; color: var(--text-muted); font-family: monospace;">5 min de lecture</span>
                            </div>
                        </div>

                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div style="text-align: center; margin-top: 3.5rem;" class="reveal">
            <button class="btn-hero-secondary" style="padding: 12px 28px; font-weight: 600; border-radius: 8px; cursor: pointer;">Charger plus d'articles</button>
        </div>

    </div>
</div>

<style>
.bcard:hover {
    border-color: rgba(99, 102, 241, 0.45) !important;
    transform: translateY(-4px);
}
</style>
