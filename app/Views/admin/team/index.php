<?php
/**
 * Liste des collaborateurs.
 *
 * La colonne « Rendu sur le site » dit explicitement ce que le visiteur voit,
 * parce que la section bascule d'elle-même : tant qu'aucun collaborateur n'est
 * publié, /a-propos affiche les pôles d'expertise. Sans cette indication,
 * saisir un collaborateur en brouillon et ne rien voir changer sur le site
 * passerait pour une panne.
 */
$membres = $membres ?? [];
$stats   = $stats ?? ['total' => 0, 'publies' => 0];
?>

<div class="card-header" style="margin-bottom: 24px; display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap;">
    <h2 class="card-title" style="margin:0;">
        <i data-lucide="users"></i>
        <span>Équipe — Collaborateurs</span>
    </h2>

    <a href="<?= htmlspecialchars(url('/admin/team/create')) ?>" class="btn-primary" style="padding:9px 18px; font-size:0.85rem;">
        <i data-lucide="plus" style="width:15px;height:15px;"></i>
        <span>Nouveau collaborateur</span>
    </a>
</div>

<!-- État réel de la section publique -->
<div class="card" style="margin-bottom: 24px;">
    <div style="display:flex; align-items:flex-start; gap:14px;">
        <i data-lucide="<?= $stats['publies'] > 0 ? 'check-circle-2' : 'info' ?>"
           style="width:20px;height:20px;flex-shrink:0;margin-top:2px;color:var(--primary);"></i>
        <div>
            <?php if ($stats['publies'] > 0): ?>
                <strong><?= (int)$stats['publies'] ?> collaborateur<?= $stats['publies'] > 1 ? 's' : '' ?> publié<?= $stats['publies'] > 1 ? 's' : '' ?>.</strong>
                <p style="margin:6px 0 0; color:var(--text-muted); font-size:0.86rem;">
                    La section « Notre équipe » de <a href="<?= htmlspecialchars(url('/a-propos#equipe')) ?>" target="_blank" rel="noopener">/a-propos</a>
                    affiche les personnes. Dépublier le dernier collaborateur ramène automatiquement
                    les pôles d'expertise.
                </p>
            <?php else: ?>
                <strong>Aucun collaborateur publié — la page affiche les pôles d'expertise.</strong>
                <p style="margin:6px 0 0; color:var(--text-muted); font-size:0.86rem;">
                    C'est le comportement normal, pas une panne : aucun membre d'équipe n'est inventé
                    par le site. Publier un premier collaborateur fait basculer la section
                    <a href="<?= htmlspecialchars(url('/a-propos#equipe')) ?>" target="_blank" rel="noopener">« Notre équipe »</a>
                    sur la grille des personnes. Les pôles se modifient dans
                    Pages CMS &rsaquo; À propos &rsaquo; section « Notre équipe ».
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="card">
    <?php if (empty($membres)): ?>
        <div style="text-align:center; padding:48px 20px;">
            <i data-lucide="users" style="width:40px;height:40px;color:var(--text-muted);"></i>
            <p style="margin:16px 0 0; color:var(--text-muted);">Aucun collaborateur enregistré.</p>
            <a href="<?= htmlspecialchars(url('/admin/team/create')) ?>" class="btn-primary" style="margin-top:18px; display:inline-flex; padding:9px 18px; font-size:0.85rem;">
                <i data-lucide="plus" style="width:15px;height:15px;"></i>
                <span>Ajouter le premier</span>
            </a>
        </div>
    <?php else: ?>
        <div style="overflow-x:auto;">
            <table class="admin-table" style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr>
                        <th style="text-align:left;">Collaborateur</th>
                        <th style="text-align:left;">Fonction</th>
                        <th style="text-align:left;">Pôle</th>
                        <th style="text-align:left;">Ordre</th>
                        <th style="text-align:left;">Rendu sur le site</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($membres as $m):
                        $publie = ($m['status'] ?? '') === 'published';
                        $pole   = \App\Models\TeamMember::libelleDepartement($m['department'] ?? '');
                    ?>
                        <tr>
                            <td style="display:flex; align-items:center; gap:10px;">
                                <?php if (!empty($m['photo'])): ?>
                                    <img src="<?= htmlspecialchars(url($m['photo'])) ?>" alt=""
                                         style="width:34px;height:34px;border-radius:50%;object-fit:cover;">
                                <?php else: ?>
                                    <span style="width:34px;height:34px;border-radius:50%;background:var(--bg-surface-alt,#eef2f7);display:inline-flex;align-items:center;justify-content:center;">
                                        <i data-lucide="user" style="width:16px;height:16px;color:var(--text-muted);"></i>
                                    </span>
                                <?php endif; ?>
                                <strong><?= htmlspecialchars((string)($m['name'] ?? '')) ?></strong>
                            </td>
                            <td><?= htmlspecialchars((string)($m['role'] ?? '')) ?></td>
                            <td><?= $pole !== '' ? htmlspecialchars($pole) : '<span style="color:var(--text-muted);">—</span>' ?></td>
                            <td><?= (int)($m['sort_order'] ?? 0) ?></td>
                            <td>
                                <?php if ($publie): ?>
                                    <span style="color:var(--primary); font-weight:600;">Oui</span>
                                <?php else: ?>
                                    <span style="color:var(--text-muted);">Non — brouillon</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:right;">
                                <a class="btn-secondary" style="padding:6px 12px; font-size:0.78rem;"
                                   href="<?= htmlspecialchars(url('/admin/team/edit/' . (int)$m['id'])) ?>">
                                    <i data-lucide="edit-3" style="width:13px;height:13px;"></i>
                                    <span>Modifier</span>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
