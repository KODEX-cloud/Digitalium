<div class="card">
    <div class="card-header" style="margin-bottom: 24px;">
        <h2 class="card-title">
            <i data-lucide="folder-git-2"></i>
            <span>Vos Réalisations Digitales</span>
        </h2>
        <a href="/admin/projects/create" class="btn-primary">
            <i data-lucide="plus-circle"></i>
            <span>Ajouter une réalisation</span>
        </a>
    </div>

    <?php if (empty($projects)): ?>
        <div style="text-align: center; padding: 60px 20px; color: var(--text-muted);">
            <i data-lucide="folder-off" style="width: 48px; height: 48px; stroke-width: 1.5; margin-bottom: 16px;"></i>
            <h3>Aucune réalisation enregistrée</h3>
            <p style="margin-top: 8px;">Commencez par ajouter votre premier projet pour l'afficher sur le site.</p>
        </div>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width: 80px;">Aperçu</th>
                    <th>Titre du Projet</th>
                    <th>Catégorie</th>
                    <th>Ordre</th>
                    <th>Vedette (Featured)</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projects as $proj): ?>
                    <tr>
                        <td>
                            <div style="width: 50px; height: 35px; border-radius: 6px; overflow: hidden; border: 1px solid var(--border); background-color: var(--bg-base);">
                                <img src="<?= htmlspecialchars($proj['main_image'] ?? '/assets/images/hero_3d.png') ?>" alt="Project Thumbnail" style="width:100%; height:100%; object-fit:cover;">
                            </div>
                        </td>
                        <td style="font-weight: 700; color: var(--text-main);"><?= htmlspecialchars($proj['title']) ?></td>
                        <td>
                            <span class="badge" style="background-color: rgba(124, 58, 237, 0.08); color: var(--secondary); border: 1px solid rgba(124, 58, 237, 0.2);">
                                <?= htmlspecialchars($proj['category']) ?>
                            </span>
                        </td>
                        <td style="font-family: monospace; font-weight: 600;"><?= $proj['sort_order'] ?></td>
                        <td>
                            <?php if ($proj['is_featured']): ?>
                                <span class="badge badge-published" style="background-color: rgba(16, 185, 129, 0.08); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2);">
                                    Oui
                                </span>
                            <?php else: ?>
                                <span class="badge badge-draft" style="background-color: rgba(100, 116, 139, 0.08); color: var(--text-muted); border: 1px solid rgba(100, 116, 139, 0.2);">
                                    Non
                                </span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            <div style="display: inline-flex; gap: 8px;">
                                <a href="/admin/projects/edit/<?= $proj['id'] ?>" class="btn-secondary" style="padding: 6px 12px; font-size: 0.8rem;" title="Modifier">
                                    <i data-lucide="edit-3" style="width: 14px; height: 14px;"></i>
                                    <span>Modifier</span>
                                </a>
                                <form action="/admin/projects/delete/<?= $proj['id'] ?>" method="POST" onsubmit="return confirm('Supprimer définitivement cette réalisation ?');" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                    <button type="submit" class="btn-danger" style="padding: 6px 12px; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 4px;">
                                        <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                                        <span>Supprimer</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
