<style>
.blog-table { width: 100%; border-collapse: collapse; }
.blog-table th { padding: 10px 14px; text-align: left; font-size: 0.75rem; text-transform: uppercase; letter-spacing: .08em; color: var(--text-muted); border-bottom: 1px solid var(--border); }
.blog-table td { padding: 12px 14px; border-bottom: 1px solid var(--border); font-size: 0.9rem; vertical-align: middle; }
.blog-table tr:hover td { background: rgba(37,99,235,0.03); }
.status-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 50px; font-size: 0.72rem; font-weight: 700; }
.status-published { background: rgba(34,197,94,0.12); color: #16a34a; }
.status-draft { background: rgba(148,163,184,0.12); color: #64748b; }
.featured-star { color: #f5b800; }
</style>

<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;">
    <div>
        <h1 class="page-title">Insights</h1>
        <p style="color:var(--text-muted);font-size:0.9rem;margin-top:4px;">
            <?= count($posts) ?> article(s) au total &middot;
            <a href="<?= url('/insights') ?>" target="_blank" style="color:var(--primary);text-decoration:none;">voir la page publique</a>
        </p>
    </div>
    <div style="display:flex;gap:10px;">
        <a href="<?= url('/admin/blog/categories') ?>" class="btn-secondary" style="display:inline-flex;align-items:center;gap:8px;padding:10px 18px;border-radius:10px;font-size:0.85rem;font-weight:600;border:1px solid var(--border);color:var(--text-main);text-decoration:none;">
            <i data-lucide="tag" style="width:16px;height:16px;"></i> Catégories
        </a>
        <a href="<?= url('/admin/blog/tags') ?>" class="btn-secondary" style="display:inline-flex;align-items:center;gap:8px;padding:10px 18px;border-radius:10px;font-size:0.85rem;font-weight:600;border:1px solid var(--border);color:var(--text-main);text-decoration:none;">
            <i data-lucide="tags" style="width:16px;height:16px;"></i> Tags
        </a>
        <a href="<?= url('/admin/blog/create') ?>" class="btn-primary" style="display:inline-flex;align-items:center;gap:8px;padding:10px 20px;border-radius:10px;font-size:0.85rem;font-weight:700;text-decoration:none;">
            <i data-lucide="plus" style="width:16px;height:16px;"></i> Nouvel article
        </a>
    </div>
</div>

<?php if (empty($posts)): ?>
    <div style="text-align:center;padding:60px 20px;color:var(--text-muted);">
        <i data-lucide="file-text" style="width:48px;height:48px;margin-bottom:16px;opacity:0.3;"></i>
        <p style="font-size:1.1rem;font-weight:600;">Aucun article pour le moment</p>
        <a href="<?= url('/admin/blog/create') ?>" style="margin-top:16px;display:inline-block;" class="btn-primary">Créer le premier article</a>
    </div>
<?php else: ?>
<div class="card" style="background:var(--bg-surface);border:1px solid var(--border);border-radius:16px;overflow:hidden;">
    <table class="blog-table">
        <thead>
            <tr>
                <th>Titre</th>
                <th>Catégorie</th>
                <th>Auteur</th>
                <th>Statut</th>
                <th>Date</th>
                <th style="width:120px;">Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($posts as $post): ?>
            <tr>
                <td>
                    <?php if ($post['is_featured']): ?><i data-lucide="star" style="width:14px;height:14px;color:#f5b800;vertical-align:middle;margin-right:4px;"></i><?php endif; ?>
                    <strong><?= htmlspecialchars($post['title']) ?></strong>
                    <div style="font-size:0.75rem;color:var(--text-muted);margin-top:2px;">/blog/<?= htmlspecialchars($post['slug']) ?></div>
                </td>
                <td><?= htmlspecialchars($post['category'] ?? '—') ?></td>
                <td><?= htmlspecialchars($post['author'] ?? '—') ?></td>
                <td>
                    <span class="status-badge status-<?= $post['status'] ?>">
                        <i data-lucide="<?= $post['status'] === 'published' ? 'check-circle' : 'clock' ?>" style="width:12px;height:12px;"></i>
                        <?= $post['status'] === 'published' ? 'Publié' : 'Brouillon' ?>
                    </span>
                </td>
                <td style="font-size:0.8rem;color:var(--text-muted);">
                    <?= $post['published_at'] ? date('d/m/Y', strtotime($post['published_at'])) : date('d/m/Y', strtotime($post['created_at'])) ?>
                </td>
                <td>
                    <div style="display:flex;gap:6px;align-items:center;">
                        <a href="<?= url('/insights/' . $post['slug']) ?>" target="_blank" title="Voir" style="padding:6px;color:var(--text-muted);border-radius:6px;display:flex;" title="Voir sur le site">
                            <i data-lucide="eye" style="width:16px;height:16px;"></i>
                        </a>
                        <a href="<?= url('/admin/blog/edit/' . $post['id']) ?>" title="Modifier" style="padding:6px;color:var(--primary);border-radius:6px;display:flex;">
                            <i data-lucide="edit" style="width:16px;height:16px;"></i>
                        </a>
                        <form method="POST" action="<?= url('/admin/blog/delete/' . $post['id']) ?>" onsubmit="return confirm('Supprimer cet article ?');" style="display:inline;">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                            <button type="submit" title="Supprimer" style="padding:6px;color:var(--danger);background:none;border:none;cursor:pointer;border-radius:6px;display:flex;">
                                <i data-lucide="trash-2" style="width:16px;height:16px;"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<script>lucide.createIcons();</script>
