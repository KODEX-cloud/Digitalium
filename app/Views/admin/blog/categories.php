<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;">
    <div>
        <a href="<?= url('/admin/blog') ?>" style="font-size:0.82rem;color:var(--text-muted);text-decoration:none;display:inline-flex;align-items:center;gap:4px;margin-bottom:8px;">
            <i data-lucide="arrow-left" style="width:14px;height:14px;"></i> Blog
        </a>
        <h1 class="page-title">Catégories du blog</h1>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 380px;gap:24px;align-items:start;">
    <!-- Categories list -->
    <div class="card" style="background:var(--bg-surface);border:1px solid var(--border);border-radius:16px;overflow:hidden;">
        <?php if (empty($categories)): ?>
            <div style="text-align:center;padding:40px;color:var(--text-muted);">Aucune catégorie créée.</div>
        <?php else: ?>
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr>
                    <th style="padding:10px 16px;text-align:left;font-size:0.75rem;text-transform:uppercase;letter-spacing:.07em;color:var(--text-muted);border-bottom:1px solid var(--border);">Nom</th>
                    <th style="padding:10px 16px;text-align:left;font-size:0.75rem;text-transform:uppercase;letter-spacing:.07em;color:var(--text-muted);border-bottom:1px solid var(--border);">Slug</th>
                    <th style="padding:10px 16px;text-align:center;font-size:0.75rem;text-transform:uppercase;letter-spacing:.07em;color:var(--text-muted);border-bottom:1px solid var(--border);">Articles</th>
                    <th style="padding:10px 16px;border-bottom:1px solid var(--border);width:80px;"></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($categories as $cat): ?>
                <tr style="border-bottom:1px solid var(--border);">
                    <td style="padding:12px 16px;font-weight:600;"><?= htmlspecialchars($cat['name']) ?></td>
                    <td style="padding:12px 16px;font-size:0.82rem;color:var(--text-muted);"><?= htmlspecialchars($cat['slug']) ?></td>
                    <td style="padding:12px 16px;text-align:center;">
                        <span style="background:rgba(37,99,235,0.1);color:var(--primary);padding:2px 10px;border-radius:50px;font-size:0.78rem;font-weight:700;"><?= (int)($cat['post_count'] ?? 0) ?></span>
                    </td>
                    <td style="padding:12px 16px;">
                        <form method="POST" action="<?= url('/admin/blog/categories/delete/' . $cat['id']) ?>" onsubmit="return confirm('Supprimer cette catégorie ?');">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                            <button type="submit" style="background:none;border:none;cursor:pointer;color:var(--danger);padding:4px;border-radius:6px;display:flex;align-items:center;">
                                <i data-lucide="trash-2" style="width:15px;height:15px;"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <!-- Create form -->
    <div class="card" style="background:var(--bg-surface);border:1px solid var(--border);border-radius:16px;padding:24px;">
        <h3 style="font-size:1rem;font-weight:700;margin-bottom:20px;">Nouvelle catégorie</h3>
        <form method="POST" action="<?= url('/admin/blog/categories/create') ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <div style="margin-bottom:14px;">
                <label class="field-label">Nom *</label>
                <input type="text" name="name" class="field-input" placeholder="Ex: Technologie" required>
            </div>
            <div style="margin-bottom:18px;">
                <label class="field-label">Description</label>
                <textarea name="description" class="field-input" rows="3" placeholder="Description optionnelle"></textarea>
            </div>
            <button type="submit" class="btn-primary" style="width:100%;padding:10px;border-radius:10px;font-size:0.88rem;font-weight:700;">
                Créer la catégorie
            </button>
        </form>
    </div>
</div>

<script>lucide.createIcons();</script>
