<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <i data-lucide="file-text"></i>
            <span>Toutes les pages du site</span>
        </h2>
        <a href="<?= url('/admin/pages/create') ?>" class="btn-primary">
            <i data-lucide="plus-circle"></i>
            <span>Créer une page</span>
        </a>
    </div>

    <?php if (empty($pages)): ?>
        <div style="text-align: center; padding: 40px 20px;">
            <i data-lucide="file" style="width: 48px; height: 48px; color: var(--text-muted); margin-bottom: 16px;"></i>
            <h3 style="margin-bottom: 8px;">Aucune page trouvée</h3>
            <p style="color: var(--text-muted); margin-bottom: 20px; font-size: 0.9rem;">Commencez à créer des pages.</p>
            <a href="<?= url('/admin/pages/create') ?>" class="btn-primary">Ajouter la première page</a>
        </div>
    <?php else: ?>
        <style>
            .pages-list-table {
                width: 100%;
                border-collapse: collapse;
            }
            .pages-list-table th, .pages-list-table td {
                padding: 16px 20px;
                border-bottom: 1px solid var(--border);
            }
            .pages-list-table th {
                font-family: var(--font-headings);
                font-weight: 600;
                color: var(--text-muted);
                text-transform: uppercase;
                font-size: 0.8rem;
                letter-spacing: 0.05em;
            }
            .pages-list-table tr:hover td {
                background-color: rgba(255, 255, 255, 0.01);
            }
            .actions-cell {
                display: flex;
                justify-content: flex-end;
                gap: 8px;
            }
            .badge {
                display: inline-flex;
                align-items: center;
                padding: 4px 10px;
                border-radius: 50px;
                font-size: 0.75rem;
                font-weight: 600;
                text-transform: uppercase;
            }
            .badge-published {
                background-color: rgba(16, 185, 129, 0.15);
                color: #34d399;
                border: 1px solid rgba(16, 185, 129, 0.2);
            }
            .badge-draft {
                background-color: rgba(245, 158, 11, 0.15);
                color: #fbbf24;
                border: 1px solid rgba(245, 158, 11, 0.2);
            }
        </style>

        <table class="pages-list-table">
            <thead>
                <tr>
                    <th>Titre de la page</th>
                    <th>URL Slug</th>
                    <th>Statut</th>
                    <th style="text-align: right; padding-right: 20px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pages as $page): ?>
                    <tr>
                        <td style="font-weight: 600; font-size: 0.95rem;"><?= htmlspecialchars($page['title']) ?></td>
                        <td>
                            <a href="<?= url($page['slug'] === 'home' ? '/' : '/' . htmlspecialchars($page['slug'])) ?>" target="_blank" style="color: var(--primary); text-decoration: none; font-family: monospace; font-size: 0.9rem;">
                                <?= $page['slug'] === 'home' ? '/' : '/' . htmlspecialchars($page['slug']) ?>
                            </a>
                        </td>
                        <td>
                            <span class="badge badge-<?= $page['status'] ?>">
                                <?= $page['status'] === 'published' ? 'Publiée' : 'Brouillon' ?>
                            </span>
                        </td>
                        <td>
                            <div class="actions-cell">
                                <a href="<?= url('/admin/pages/edit/' . $page['id']) ?>" class="btn-secondary" style="padding: 6px 12px; font-size: 0.8rem;">
                                    <i data-lucide="edit-3" style="width: 14px; height: 14px;"></i>
                                    <span>Éditeur Visuel</span>
                                </a>

                                <?php if ($page['slug'] !== 'home'): ?>
                                    <form action="<?= url('/admin/pages/delete/' . $page['id']) ?>" method="POST" onsubmit="return confirm('Êtes-vous sûr ?');" style="display: inline;">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                        <button type="submit" class="btn-danger" style="padding: 6px 12px; font-size: 0.8rem;">
                                            <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
