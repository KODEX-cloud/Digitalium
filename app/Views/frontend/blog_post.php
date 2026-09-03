<?php
/**
 * Frontend Blog Single Post
 */
?>

<!-- Article Hero -->
<section style="padding: 140px 0 60px; background: var(--bg-base); border-bottom: 1px solid var(--border);">
    <div class="container" style="max-width: 820px;">
        <?php if ($post['category']): ?>
        <div style="margin-bottom: 16px;">
            <a href="<?= url('/blog') ?>" style="font-size: 0.78rem; color: var(--text-muted); text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                <i data-lucide="arrow-left" style="width: 14px; height: 14px;"></i> Blog
            </a>
            <span style="margin: 0 8px; color: var(--border);">/</span>
            <span style="font-size: 0.78rem; color: var(--primary); font-weight: 700; text-transform: uppercase; letter-spacing: .1em;"><?= htmlspecialchars($post['category']) ?></span>
        </div>
        <?php endif; ?>

        <h1 style="font-size: clamp(1.8rem, 4vw, 2.8rem); font-weight: 900; color: var(--text-main); line-height: 1.18; letter-spacing: -0.02em; margin-bottom: 20px; font-family: var(--font-heading);">
            <?= htmlspecialchars($post['title']) ?>
        </h1>

        <?php if ($post['excerpt']): ?>
        <p style="font-size: 1.15rem; color: var(--text-muted); line-height: 1.7; margin-bottom: 28px;">
            <?= htmlspecialchars($post['excerpt']) ?>
        </p>
        <?php endif; ?>

        <div style="display: flex; align-items: center; gap: 20px; font-size: 0.85rem; color: var(--text-muted); padding-top: 20px; border-top: 1px solid var(--border);">
            <div style="display: flex; align-items: center; gap: 8px;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--secondary)); display: flex; align-items: center; justify-content: center; color: white; font-size: 0.72rem; font-weight: 700;">
                    <?= mb_strtoupper(mb_substr($post['author'] ?? 'D', 0, 1)) ?>
                </div>
                <span style="font-weight: 600; color: var(--text-main);"><?= htmlspecialchars($post['author'] ?? 'Équipe Digitalium') ?></span>
            </div>
            <?php if ($post['published_at']): ?>
            <span>&bull;</span>
            <span><?= date('d M Y', strtotime($post['published_at'])) ?></span>
            <?php endif; ?>
            <?php if (!empty($postTags)): ?>
            <span>&bull;</span>
            <span style="display:inline-flex;gap:6px;flex-wrap:wrap;">
                <?php foreach ($postTags as $t): ?>
                <span style="background:color-mix(in srgb, var(--primary) 10%, transparent);border:1px solid color-mix(in srgb, var(--primary) 20%, transparent);color:var(--primary);padding:1px 9px;border-radius:50px;font-size:0.7rem;font-weight:700;"><?= htmlspecialchars($t['name']) ?></span>
                <?php endforeach; ?>
            </span>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Featured Image -->
<?php if ($post['featured_image']): ?>
<div style="background: var(--bg-base); padding: 0 20px;">
    <div class="container" style="max-width: 820px; padding-top: 40px;">
        <div style="border-radius: 16px; overflow: hidden; aspect-ratio: 16/7; background: var(--bg-surface);">
            <img src="<?= htmlspecialchars(url($post['featured_image'])) ?>" alt="<?= htmlspecialchars($post['title']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Article Content -->
<section style="padding: 56px 0 80px; background: var(--bg-base);">
    <div class="container" style="max-width: 820px;">
        <div class="blog-content" style="font-size: 1.05rem; line-height: 1.85; color: var(--text-main);">
            <?= $post['content'] ?>
        </div>
    </div>
</section>

<!-- Tag cloud in content area -->
<?php if (!empty($postTags)): ?>
<div style="background: var(--bg-base); padding: 0 20px 40px;">
    <div class="container" style="max-width: 820px;">
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            <span style="font-size:0.78rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.1em;">Tags:</span>
            <?php foreach ($postTags as $t): ?>
            <span style="background:color-mix(in srgb, var(--primary) 7%, transparent);border:1px solid color-mix(in srgb, var(--primary) 18%, transparent);color:var(--primary);padding:4px 12px;border-radius:50px;font-size:0.75rem;font-weight:700;">
                <?= htmlspecialchars($t['name']) ?>
            </span>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Comments section -->
<section style="padding: 56px 0 80px; background: var(--bg-base); border-top: 1px solid var(--border);">
    <div class="container" style="max-width: 820px;">
        <h2 style="font-size: 1.3rem; font-weight: 800; margin-bottom: 32px;">
            <?= count($comments ?? []) ?> Commentaire<?= count($comments ?? []) !== 1 ? 's' : '' ?>
        </h2>

        <?php if (!empty($comments)): ?>
        <div style="display:flex;flex-direction:column;gap:20px;margin-bottom:40px;">
            <?php foreach ($comments as $c): ?>
            <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:14px;padding:20px;">
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:10px;">
                    <div style="width:36px;height:36px;border-radius:50%;background:color-mix(in srgb, var(--primary) 15%, transparent);display:flex;align-items:center;justify-content:center;font-weight:700;color:var(--primary);font-size:0.9rem;">
                        <?= strtoupper(mb_substr($c['author_name'], 0, 1)) ?>
                    </div>
                    <div>
                        <div style="font-weight:700;font-size:0.9rem;"><?= htmlspecialchars($c['author_name']) ?></div>
                        <div style="font-size:0.75rem;color:var(--text-muted);"><?= date('d/m/Y', strtotime($c['created_at'])) ?></div>
                    </div>
                </div>
                <p style="font-size:0.93rem;line-height:1.75;color:var(--text-muted);margin:0;"><?= nl2br(htmlspecialchars($c['content'])) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Comment form -->
        <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:16px;padding:28px;">
            <h3 style="font-size:1rem;font-weight:700;margin-bottom:20px;">Laisser un commentaire</h3>
            <form id="commentForm" data-post-id="<?= (int)$post['id'] ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;">
                    <div>
                        <label style="font-size:0.8rem;font-weight:600;display:block;margin-bottom:5px;">Nom *</label>
                        <input type="text" name="author_name" required class="admin-input" style="width:100%;box-sizing:border-box;" placeholder="Votre nom">
                    </div>
                    <div>
                        <label style="font-size:0.8rem;font-weight:600;display:block;margin-bottom:5px;">Email</label>
                        <input type="email" name="author_email" class="admin-input" style="width:100%;box-sizing:border-box;" placeholder="votre@email.com">
                    </div>
                </div>
                <div style="margin-bottom:16px;">
                    <label style="font-size:0.8rem;font-weight:600;display:block;margin-bottom:5px;">Commentaire *</label>
                    <textarea name="content" required rows="5" class="admin-input" style="width:100%;box-sizing:border-box;resize:vertical;" placeholder="Votre commentaire..."></textarea>
                </div>
                <button type="submit" class="btn-primary">
                    <i data-lucide="send" style="width:15px;height:15px;"></i>
                    <span>Envoyer le commentaire</span>
                </button>
                <div id="commentResult" style="margin-top:12px;display:none;"></div>
            </form>
        </div>
    </div>
</section>

<script>
document.getElementById('commentForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('button[type=submit]');
    const result = document.getElementById('commentResult');
    btn.disabled = true;
    const data = new URLSearchParams(new FormData(this));
    data.append('post_id', this.dataset.postId);
    try {
        const r = await fetch('<?= url('/blog/comment') ?>', { method: 'POST', body: data });
        const json = await r.json();
        result.style.display = 'block';
        result.style.color = json.success ? 'var(--primary)' : 'var(--danger)';
        result.textContent = json.message;
        if (json.success) this.reset();
    } catch {
        result.style.display = 'block';
        result.textContent = 'Erreur. Réessayez plus tard.';
    }
    btn.disabled = false;
});
</script>

<!-- Related posts -->
<?php if (!empty($related)): ?>
<section style="padding: 60px 0; background: var(--bg-surface); border-top: 1px solid var(--border);">
    <div class="container">
        <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--text-main); margin-bottom: 32px; font-family: var(--font-heading);">Articles similaires</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 24px;">
            <?php foreach ($related as $r): ?>
            <a href="<?= url('/blog/' . $r['slug']) ?>" style="text-decoration: none; background: var(--bg-base); border: 1px solid var(--border); border-radius: 14px; overflow: hidden; display: flex; flex-direction: column; transition: box-shadow 0.3s, transform 0.3s;">
                <?php if ($r['featured_image']): ?>
                <div style="height: 160px; background: url('<?= htmlspecialchars(url($r['featured_image'])) ?>') center/cover no-repeat;"></div>
                <?php endif; ?>
                <div style="padding: 20px;">
                    <?php if ($r['category']): ?><span style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .12em; color: var(--primary);"><?= htmlspecialchars($r['category']) ?></span><?php endif; ?>
                    <h3 style="font-size: 0.95rem; font-weight: 700; color: var(--text-main); margin-top: 8px; line-height: 1.4;"><?= htmlspecialchars($r['title']) ?></h3>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<style>
.blog-content h1, .blog-content h2, .blog-content h3 {
    font-family: var(--font-heading);
    font-weight: 800;
    color: var(--text-main);
    margin: 2rem 0 1rem;
    line-height: 1.25;
}
.blog-content h2 { font-size: 1.5rem; }
.blog-content h3 { font-size: 1.2rem; }
.blog-content p { margin-bottom: 1.4rem; }
.blog-content a { color: var(--primary); text-decoration: underline; }
.blog-content ul, .blog-content ol { margin: 1rem 0 1.4rem 2rem; }
.blog-content li { margin-bottom: 0.4rem; }
.blog-content blockquote { border-left: 4px solid var(--primary); padding: 12px 24px; margin: 1.5rem 0; background: color-mix(in srgb, var(--primary) 5%, transparent); border-radius: 0 8px 8px 0; font-style: italic; color: var(--text-muted); }
.blog-content img { max-width: 100%; border-radius: 12px; margin: 1rem 0; }
.blog-content code { background: color-mix(in srgb, var(--primary) 8%, transparent); padding: 2px 8px; border-radius: 4px; font-size: 0.88em; font-family: monospace; }
.blog-content pre { background: var(--surface-dark); color: #e2e8f0; padding: 20px 24px; border-radius: 12px; overflow-x: auto; margin: 1.5rem 0; }
.blog-content pre code { background: none; padding: 0; font-size: 0.9rem; }
</style>
