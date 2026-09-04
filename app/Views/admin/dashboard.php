<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 24px;
        margin-bottom: 32px;
    }
    .stat-card {
        background: var(--bg-surface);
        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);
        border: 1px solid var(--border-highlight);
        border-radius: 20px;
        padding: 24px;
        display: flex;
        align-items: center;
        gap: 20px;
        box-shadow: 0 20px 40px -15px rgba(37, 99, 235, 0.05), 0 5px 15px -5px rgba(0, 0, 0, 0.02);
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 25px 50px -12px rgba(37, 99, 235, 0.08);
        border-color: rgba(37, 99, 235, 0.2);
    }
    .stat-icon {
        background-color: rgba(37, 99, 235, 0.08);
        color: var(--primary);
        padding: 16px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .stat-details {
        display: flex;
        flex-direction: column;
    }
    .stat-number {
        font-family: var(--font-headings);
        font-size: 2rem;
        font-weight: 800;
        line-height: 1.2;
    }
    .stat-label {
        color: var(--text-muted);
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .dashboard-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 24px;
    }

    @media (max-width: 1024px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
        }
    }

    .admin-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }
    .admin-table th, .admin-table td {
        padding: 14px 16px;
        border-bottom: 1px solid var(--border);
    }
    .admin-table th {
        font-family: var(--font-headings);
        font-weight: 700;
        color: var(--text-muted);
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .admin-table tr:last-child td {
        border-bottom: none;
    }
    .badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }
    .badge-published {
        background-color: rgba(16, 185, 129, 0.08);
        color: #10b981;
        border: 1px solid rgba(16, 185, 129, 0.2);
    }
    .badge-draft {
        background-color: rgba(245, 158, 11, 0.08);
        color: #d97706;
        border: 1px solid rgba(245, 158, 11, 0.2);
    }

    .media-thumb-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(70px, 1fr));
        gap: 12px;
    }
    .media-thumb-item {
        background-color: rgba(255, 255, 255, 0.5);
        border: 1px solid var(--border);
        border-radius: 10px;
        aspect-ratio: 1;
        overflow: hidden;
        position: relative;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .media-thumb-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .media-thumb-item img:hover {
        transform: scale(1.08);
    }
</style>

<?php
/* Pipeline commercial en tête de tableau de bord : ce sont les chiffres qui
   demandent une action aujourd'hui, avant l'inventaire du contenu. Chaque
   carte mène à la liste déjà filtrée. */
?>
<div class="stats-grid">
    <a class="stat-card" href="<?= url('/admin/messages?statut=nouveau') ?>" style="text-decoration:none;">
        <div class="stat-icon"><i data-lucide="inbox" style="width: 28px; height: 28px;"></i></div>
        <div class="stat-details">
            <span class="stat-number"><?= (int)($stats['leads_nouveaux'] ?? 0) ?></span>
            <span class="stat-label">Nouveaux leads</span>
        </div>
    </a>
    <a class="stat-card" href="<?= url('/admin/messages?statut=en_discussion') ?>" style="text-decoration:none;">
        <div class="stat-icon"><i data-lucide="activity" style="width: 28px; height: 28px;"></i></div>
        <div class="stat-details">
            <span class="stat-number"><?= (int)($stats['leads_en_cours'] ?? 0) ?></span>
            <span class="stat-label">Leads en cours</span>
        </div>
    </a>
    <a class="stat-card" href="<?= url('/admin/messages') ?>" style="text-decoration:none;">
        <div class="stat-icon"><i data-lucide="calendar" style="width: 28px; height: 28px;"></i></div>
        <div class="stat-details">
            <span class="stat-number"><?= (int)($stats['leads_semaine'] ?? 0) ?></span>
            <span class="stat-label">Demandes cette semaine</span>
        </div>
    </a>
    <a class="stat-card" href="<?= url('/admin/messages?statut=gagne') ?>" style="text-decoration:none;">
        <div class="stat-icon"><i data-lucide="trophy" style="width: 28px; height: 28px;"></i></div>
        <div class="stat-details">
            <span class="stat-number"><?= (int)($stats['leads_gagnes'] ?? 0) ?></span>
            <span class="stat-label">Projets gagnés</span>
        </div>
    </a>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">
            <i data-lucide="file-text" style="width: 28px; height: 28px;"></i>
        </div>
        <div class="stat-details">
            <span class="stat-number"><?= $stats['pages_count'] ?></span>
            <span class="stat-label">Pages actives</span>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <i data-lucide="layout" style="width: 28px; height: 28px;"></i>
        </div>
        <div class="stat-details">
            <span class="stat-number"><?= $stats['sections_count'] ?></span>
            <span class="stat-label">Sections éditables</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <i data-lucide="image" style="width: 28px; height: 28px;"></i>
        </div>
        <div class="stat-details">
            <span class="stat-number"><?= $stats['media_count'] ?></span>
            <span class="stat-label">Fichiers Média</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background-color: rgba(16, 185, 129, 0.08); color: #10b981;">
            <i data-lucide="newspaper" style="width: 28px; height: 28px;"></i>
        </div>
        <div class="stat-details">
            <span class="stat-number"><?= $stats['blog_count'] ?? 0 ?></span>
            <span class="stat-label">Articles Blog</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background-color: rgba(245, 158, 11, 0.08); color: #d97706;">
            <i data-lucide="briefcase" style="width: 28px; height: 28px;"></i>
        </div>
        <div class="stat-details">
            <span class="stat-number"><?= $stats['project_count'] ?? 0 ?></span>
            <span class="stat-label">Réalisations</span>
        </div>
    </div>
</div>

<div class="dashboard-grid">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">
                <i data-lucide="clock"></i>
                <span>Pages modifiées récemment</span>
            </h2>
            <a href="<?= url('/admin/pages') ?>" class="btn-secondary" style="padding: 6px 12px; font-size: 0.8rem;">Tout voir</a>
        </div>
        <?php if (empty($recentPages)): ?>
            <p style="color: var(--text-muted); text-align: center; padding: 20px;">Aucune page créée pour le moment.</p>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Chemin URL (slug)</th>
                        <th>Statut</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentPages as $page): ?>
                        <tr>
                            <td style="font-weight: 500;"><?= htmlspecialchars($page['title']) ?></td>
                            <td style="font-family: monospace; color: var(--text-muted)">/<?= htmlspecialchars($page['slug']) ?></td>
                            <td>
                                <span class="badge badge-<?= $page['status'] ?>">
                                    <?= $page['status'] === 'published' ? 'Publiée' : 'Brouillon' ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <a href="<?= url('/admin/pages/edit/' . $page['id']) ?>" class="btn-primary" style="padding: 6px 12px; font-size: 0.8rem;">
                                    <i data-lucide="edit-3" style="width: 14px; height: 14px;"></i>
                                    <span>Gérer</span>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div>
        <div class="card">
            <div class="card-header" style="margin-bottom: 15px; padding-bottom: 8px;">
                <h3 class="card-title" style="font-size: 1rem;">Actions Rapides</h3>
            </div>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <a href="<?= url('/admin/pages/create') ?>" class="btn-primary" style="justify-content: center; width: 100%;">
                    <i data-lucide="plus-circle"></i>
                    <span>Ajouter une page</span>
                </a>
                <a href="<?= url('/admin/media') ?>" class="btn-secondary" style="justify-content: center; width: 100%;">
                    <i data-lucide="image"></i>
                    <span>Importer un média</span>
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-header" style="margin-bottom: 15px; padding-bottom: 8px;">
                <h3 class="card-title" style="font-size: 1rem;">Médias Récents</h3>
            </div>
            <?php if (empty($recentMedia)): ?>
                <p style="color: var(--text-muted); font-size: 0.85rem; text-align: center; padding: 10px 0;">Aucun fichier.</p>
            <?php else: ?>
                <div class="media-thumb-grid">
                    <?php foreach ($recentMedia as $media): ?>
                        <div class="media-thumb-item" title="<?= htmlspecialchars($media['original_name']) ?>">
                            <a href="<?= url('/admin/media') ?>">
                                <img src="<?= htmlspecialchars(url($media['filepath'])) ?>" alt="<?= htmlspecialchars($media['original_name']) ?>">
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
