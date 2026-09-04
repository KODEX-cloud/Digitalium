<?php
/**
 * Digitalium Labs — liste des produits propriétaires.
 *
 * Deux colonnes de statut distinctes, et c'est volontaire : « Étape » dit où en
 * est le produit (idée → disponible), « Visibilité » dit s'il est en ligne. Les
 * confondre ferait disparaître du site un produit simplement passé en bêta.
 *
 * Variables attendues : $produits, $stats, $csrf_token
 */
$stages = \App\Models\LabProduct::STAGES;
?>

<div class="labs-stats">
    <div class="labs-stat">
        <span class="labs-stat-value"><?= (int)($stats['total'] ?? 0) ?></span>
        <span class="labs-stat-label">Produit<?= ((int)($stats['total'] ?? 0)) > 1 ? 's' : '' ?> enregistré<?= ((int)($stats['total'] ?? 0)) > 1 ? 's' : '' ?></span>
    </div>
    <div class="labs-stat">
        <span class="labs-stat-value"><?= (int)($stats['publies'] ?? 0) ?></span>
        <span class="labs-stat-label">En ligne sur /labs</span>
    </div>
    <div class="labs-stat">
        <span class="labs-stat-value"><?= (int)($stats['avant'] ?? 0) ?></span>
        <span class="labs-stat-label">Mis en avant</span>
    </div>
</div>

<div class="card">
    <div class="card-header" style="margin-bottom: 24px;">
        <h2 class="card-title">
            <i data-lucide="flask-conical"></i>
            <span>Produits Digitalium Labs</span>
        </h2>
        <a href="<?= url('/admin/labs/create') ?>" class="btn-primary">
            <i data-lucide="plus-circle"></i>
            <span>Ajouter un produit</span>
        </a>
    </div>

    <?php if (empty($produits)): ?>
        <div style="text-align: center; padding: 60px 20px; color: var(--text-muted);">
            <i data-lucide="flask-conical-off" style="width: 48px; height: 48px; stroke-width: 1.5; margin-bottom: 16px;"></i>
            <h3>Aucun produit enregistré</h3>
            <p style="margin-top: 8px; max-width: 520px; margin-left:auto; margin-right:auto;">
                La section « Nos produits » de la page Labs reste vide tant qu'aucun produit réel
                n'est saisi ici : rien n'est inventé côté site public.
            </p>
        </div>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width: 70px;">Visuel</th>
                    <th>Produit</th>
                    <th>Secteur</th>
                    <th>Étape</th>
                    <th>Visibilité</th>
                    <th>Ordre</th>
                    <th>Mis en avant</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produits as $prod):
                    $visuel  = trim((string)($prod['logo'] ?? '')) ?: trim((string)($prod['main_image'] ?? ''));
                    $enLigne = ($prod['status'] ?? '') === 'published';
                    $etape   = (string)($prod['stage'] ?? '');
                ?>
                    <tr>
                        <td>
                            <div class="labs-thumb">
                                <?php if ($visuel !== ''): ?>
                                    <img src="<?= htmlspecialchars(url($visuel)) ?>" alt="" style="width:100%; height:100%; object-fit:contain;">
                                <?php else: ?>
                                    <span><?= htmlspecialchars(mb_strtoupper(mb_substr((string)($prod['name'] ?? '?'), 0, 1))) ?></span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 700; color: var(--text-main);"><?= htmlspecialchars((string)$prod['name']) ?></div>
                            <?php if (!empty($prod['tagline'])): ?>
                                <div style="font-size:0.76rem; color:var(--text-muted); margin-top:3px;"><?= htmlspecialchars((string)$prod['tagline']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td style="color: var(--text-muted); font-size: 0.84rem;">
                            <?= $prod['sector'] !== null && $prod['sector'] !== '' ? htmlspecialchars((string)$prod['sector']) : '—' ?>
                        </td>
                        <td>
                            <span class="badge" style="background-color: rgba(8, 145, 178, 0.08); color: var(--secondary); border: 1px solid rgba(8, 145, 178, 0.2);">
                                <?= htmlspecialchars($stages[$etape] ?? 'Idée') ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge" style="<?= $enLigne
                                ? 'background-color: rgba(21,128,61,0.08); color:#15803d; border:1px solid rgba(21,128,61,0.22);'
                                : 'background-color: rgba(180,83,9,0.08); color:#b45309; border:1px solid rgba(180,83,9,0.22);' ?>">
                                <?= $enLigne ? 'Publié' : 'Brouillon' ?>
                            </span>
                        </td>
                        <td style="font-family: monospace; font-weight: 600;"><?= (int)($prod['sort_order'] ?? 0) ?></td>
                        <td>
                            <?php if (!empty($prod['is_featured'])): ?>
                                <span class="badge" style="background-color: rgba(16, 185, 129, 0.08); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2);">Oui</span>
                            <?php else: ?>
                                <span class="badge" style="background-color: rgba(100, 116, 139, 0.08); color: var(--text-muted); border: 1px solid rgba(100, 116, 139, 0.2);">Non</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            <div style="display: inline-flex; gap: 8px;">
                                <a href="<?= url('/admin/labs/edit/' . $prod['id']) ?>" class="btn-secondary" style="padding: 6px 12px; font-size: 0.8rem;" title="Modifier">
                                    <i data-lucide="edit-3" style="width: 14px; height: 14px;"></i>
                                    <span>Modifier</span>
                                </a>
                                <form action="<?= url('/admin/labs/delete/' . $prod['id']) ?>" method="POST" onsubmit="return confirm('Supprimer définitivement ce produit ?');" style="display:inline;">
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

<style>
    .labs-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }
    .labs-stat {
        display: flex;
        flex-direction: column;
        gap: 4px;
        background: var(--bg-surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 18px 20px;
    }
    .labs-stat-value { font-size: 1.7rem; font-weight: 800; color: var(--primary); line-height: 1; }
    .labs-stat-label { font-size: 0.78rem; color: var(--text-muted); }

    .labs-thumb {
        width: 44px; height: 44px;
        display: flex; align-items: center; justify-content: center;
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid var(--border);
        background-color: var(--bg-base);
        font-weight: 800;
        color: var(--primary);
    }

    @media (max-width: 760px) {
        .labs-stats { grid-template-columns: 1fr; }
    }
</style>
