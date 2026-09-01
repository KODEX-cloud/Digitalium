<style>
.menu-location-badge {
    display: inline-flex; align-items: center; padding: 3px 10px;
    border-radius: 50px; font-size: 0.72rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.05em;
}
.loc-primary   { background: rgba(37,99,235,0.1); color: #2563eb; border: 1px solid rgba(37,99,235,0.2); }
.loc-footer    { background: rgba(16,185,129,0.1); color: #10b981; border: 1px solid rgba(16,185,129,0.2); }
.loc-mobile    { background: rgba(245,158,11,0.1); color: #d97706; border: 1px solid rgba(245,158,11,0.2); }
.loc-secondary { background: rgba(37,99,235,0.1); color: #60a5fa; border: 1px solid rgba(37,99,235,0.2); }
</style>

<div class="card" style="margin-bottom: 28px;">
    <div class="card-header">
        <h2 class="card-title">
            <i data-lucide="menu"></i>
            <span>Menus de navigation</span>
        </h2>
    </div>
    <p style="color: var(--text-muted); margin-bottom: 24px; font-size: 0.95rem;">
        Gérez vos menus de navigation. Chaque menu peut être assigné à un emplacement (header, footer, mobile).
        Cliquez sur <strong>Éditer</strong> pour ajouter, réorganiser et paramétrer les liens.
    </p>

    <?php if (empty($menus)): ?>
        <div style="text-align: center; padding: 40px; color: var(--text-muted);">
            <i data-lucide="menu" style="width: 48px; height: 48px; margin-bottom: 16px; opacity: 0.3;"></i>
            <p>Aucun menu créé. Créez votre premier menu ci-dessous.</p>
        </div>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Nom du menu</th>
                    <th>Emplacement</th>
                    <th>Slug</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($menus as $menu): ?>
                <tr>
                    <td style="font-weight: 600;"><?= htmlspecialchars($menu['name']) ?></td>
                    <td>
                        <span class="menu-location-badge loc-<?= htmlspecialchars($menu['location']) ?>">
                            <?= htmlspecialchars($menu['location']) ?>
                        </span>
                    </td>
                    <td style="font-family: monospace; color: var(--text-muted); font-size: 0.85rem;"><?= htmlspecialchars($menu['slug']) ?></td>
                    <td style="text-align: right; display: flex; gap: 8px; justify-content: flex-end;">
                        <a href="<?= url('/admin/menus/edit/' . $menu['id']) ?>" class="btn-primary" style="padding: 6px 14px; font-size: 0.8rem;">
                            <i data-lucide="edit-3" style="width: 14px; height: 14px;"></i>
                            <span>Éditer</span>
                        </a>
                        <form method="POST" action="<?= url('/admin/menus/delete/' . $menu['id']) ?>" onsubmit="return confirm('Supprimer ce menu et tous ses liens ?')">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                            <button type="submit" class="btn-secondary" style="padding: 6px 14px; font-size: 0.8rem; color: var(--danger); border-color: var(--danger);">
                                <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i data-lucide="plus-circle"></i>
            <span>Créer un nouveau menu</span>
        </h3>
    </div>
    <form method="POST" action="<?= url('/admin/menus/create') ?>">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
        <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 16px; align-items: end;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Nom du menu *</label>
                <input type="text" name="name" class="form-input" placeholder="ex: Navigation Principale" required>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Emplacement</label>
                <select name="location" class="form-input">
                    <option value="primary">Header Principal</option>
                    <option value="footer">Footer</option>
                    <option value="mobile">Mobile uniquement</option>
                    <option value="secondary">Secondaire</option>
                </select>
            </div>
            <button type="submit" class="btn-primary">
                <i data-lucide="plus"></i>
                <span>Créer</span>
            </button>
        </div>
    </form>
</div>
