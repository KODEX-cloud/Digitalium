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
                    <div class="bcard reveal" data-tag="<?= htmlspecialchars($category) ?>" style="background: var(--bg-surface); border: 1px solid var(--border); border-radius: 16px; overflow: hidden; transition: var(--transition); display: flex; flex-direction: column; height: 100%;">
                        
                        <!-- Header / Image Box with dynamic cover image support -->
                        <div class="bcard-img" style="height: 180px; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden; background: <?= !empty($post['post_image']) ? "url('" . htmlspecialchars($post['post_image']) . "') center center / cover" : "linear-gradient(135deg, var(--primary-glow), rgba(13,34,71,0.5))" ?>;">
                            <?php if (!empty($post['post_image'])): ?>
                                <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.25); z-index: 1;"></div>
                            <?php endif; ?>
                            <div class="bcard-icon" style="width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(255,255,255,0.15); background: rgba(255, 255, 255, 0.9); color: var(--primary); z-index: 2; overflow: hidden; padding: 10px;">
                                <?= \App\Helpers\IconHelper::render($post['post_icon'] ?? 'book-open', ['size' => '20px']) ?>
                            </div>
                        </div>

                        <!-- Card Content Body -->
                        <div class="bcard-body" style="padding: 1.8rem; display: flex; flex-direction: column; flex-grow: 1;">
                            <div class="bcard-meta" style="display: flex; gap: 10px; align-items: center; margin-bottom: 1rem; flex-wrap: wrap;">
                                <span class="pill" style="padding: 4px 12px; border-radius: 8px; font-size: 0.65rem; letter-spacing: 0.05em; text-transform: uppercase; font-weight: 700; background: color-mix(in srgb, var(--secondary) 8%, transparent); color: var(--secondary); border: 1px solid color-mix(in srgb, var(--secondary) 15%, transparent);">
                                    <?= htmlspecialchars($category) ?>
                                </span>
                                <span class="bcard-date" style="font-size: 0.72rem; color: var(--text-muted); font-family: monospace;">
                                    <?= htmlspecialchars($post['post_date'] ?? date('d.m.Y')) ?>
                                </span>
                            </div>

                            <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--text-main); line-height: 1.4; margin-bottom: 0.8rem; font-family: var(--font-heading);">
                                <a href="<?= htmlspecialchars($post['post_link'] ?? '/insights') ?>" class="bcard-title-link">
                                    <?= htmlspecialchars($post['post_title'] ?? 'Article sans titre') ?>
                                </a>
                            </h3>
                            
                            <p style="font-size: 0.88rem; line-height: 1.6; color: var(--text-muted); margin-bottom: 1.8rem; flex-grow: 1;">
                                <?= htmlspecialchars($post['post_summary'] ?? '') ?>
                            </p>

                            <div class="bcard-footer" style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border); padding-top: 1.2rem; margin-top: auto;">
                                <div class="bcard-author" style="display: flex; align-items: center; gap: 8px;">
                                    <div class="author-av" style="width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.68rem; font-weight: 700; background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); color: white;">
                                        DG
                                    </div>
                                    <span class="author-name" style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">Équipe DG</span>
                                </div>
                                <span class="bcard-read" style="font-size: 0.72rem; color: var(--text-muted); font-family: monospace;">5 min de lecture</span>
                            </div>
                        </div>

                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div style="text-align: center; margin-top: 4rem;" class="reveal">
            <button class="btn-hero-secondary" style="padding: 14px 32px; font-weight: 600; cursor: pointer;" onclick="window.location.href='<?= url('/contact') ?>'">Charger plus d'articles</button>
        </div>

    </div>
</div>

<style>
.bcard {
    box-shadow: 0 12px 30px -10px rgba(30, 58, 138, 0.04), inset 0 1px 1px rgba(255,255,255,0.9) !important;
}
.bcard:hover {
    border-color: color-mix(in srgb, var(--secondary) 35%, transparent) !important;
    transform: translateY(-5px);
    box-shadow: 0 24px 48px -12px color-mix(in srgb, var(--secondary) 12%, transparent), inset 0 1px 2px rgba(255,255,255,1) !important;
}
.bcard-title-link {
    transition: var(--transition);
}
.bcard-title-link:hover {
    color: var(--secondary) !important;
}
</style>
