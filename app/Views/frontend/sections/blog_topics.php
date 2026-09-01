<section class="topics section-padding" id="topics" style="background: var(--bg-surface-alt); border-top: 1px solid var(--border); border-bottom: 1px solid var(--border);">
    <div class="container">
        
        <div class="reveal">
            <span class="section-tag" style="display: inline-flex; align-items: center; gap: 8px; font-size: 0.68rem; letter-spacing: 0.3em; text-transform: uppercase; color: var(--primary); margin-bottom: 1rem;"><?= htmlspecialchars($single['tag'] ?? 'Catégories') ?></span>
            <h2 style="font-size: 1.6rem; font-weight: 700; color: var(--text-main); font-family: var(--font-heading); margin-bottom: 3rem;"><?= htmlspecialchars($single['title'] ?? 'Nos grandes thématiques') ?></h2>
        </div>

        <div class="topics-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
            <?php if (!empty($groups)): ?>
                <?php foreach ($groups as $topic): ?>
                    <div class="topic-card reveal" style="text-align: center; padding: 2rem 1.2rem; border: 1px solid var(--border); border-radius: 12px; background: var(--bg-surface); transition: var(--transition);">
                        <div class="topic-icon" style="width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.2rem auto; border: 1px solid rgba(255,255,255,0.06); background: var(--primary-glow); color: var(--primary);">
                            <i data-lucide="<?= htmlspecialchars($topic['topic_icon'] ?? 'tag') ?>" style="width: 20px; height: 20px;"></i>
                        </div>
                        <h3 class="topic-title" style="font-size: 0.88rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.4rem; font-family: var(--font-heading);"><?= htmlspecialchars($topic['topic_title'] ?? '') ?></h3>
                        <div class="topic-count" style="font-size: 0.72rem; color: var(--text-muted); font-family: monospace;"><?= htmlspecialchars($topic['topic_count'] ?? '0 article') ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
</section>

<style>
.topic-card:hover {
    border-color: rgba(37, 99, 235, 0.45) !important;
    transform: translateY(-3px);
}
</style>
