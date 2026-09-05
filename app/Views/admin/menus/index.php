<?php
/**
 * Liste des menus de navigation.
 *
 * La colonne « Rendu » est ce qui évite de chercher longtemps pourquoi un menu
 * « ne s'affiche pas » : un menu dont l'emplacement n'est câblé nulle part
 * reste modifiable, mais n'apparaît sur aucune page.
 *
 * Variables attendues : $menus (avec nb_liens, est_cable, emplacement_libelle),
 * $emplacements, $csrf_token
 */
?>
<style>
.menu-location-badge {
    display: inline-flex; align-items: center; padding: 3px 10px;
    border-radius: 50px; font-size: 0.72rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.05em;
    background: rgba(37,99,235,0.1); color: #2563eb; border: 1px solid rgba(37,99,235,0.2);
}
.menu-location-badge.orphelin {
    background: rgba(180,83,9,0.08); color: #b45309; border-color: rgba(180,83,9,0.22);
}
.menu-rendu { font-size: 0.8rem; }
.menu-rendu.oui { color: #15803d; }
.menu-rendu.non { color: #b45309; }
</style>

<div class="card" style="margin-bottom: 28px;">
    <div class="card-header">
        <h2 class="card-title">
            <i data-lucide="menu"></i>
            <span>Menus de navigation</span>
        </h2>
    </div>
    <p style="color: var(--text-muted); margin-bottom: 24px; font-size: 0.95rem;">
        Chaque menu occupe un <strong>emplacement</strong> du site. Le frontend demande un emplacement,
        jamais un menu précis : vous pouvez donc renommer ou recréer un menu sans rien casser.
        Cliquez sur <strong>Éditer</strong> pour ajouter, réorganiser, imbriquer ou retirer des liens.
    </p>

    <?php if (empty($menus)): ?>
        <div style="text-align: center; padding: 40px; color: var(--text-muted);">
            <i data-lucide="menu" style="width: 48px; height: 48px; margin-bottom: 16px; opacity: 0.3;"></i>
            <p>Aucun menu. Créez le premier ci-dessous.</p>
        </div>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Nom du menu</th>
                    <th>Emplacement</th>
                    <th>Liens</th>
                    <th>Rendu sur le site</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($menus as $menu):
                    $cable = !empty($menu['est_cable']);
                ?>
                <tr>
                    <td style="font-weight: 600;"><?= htmlspecialchars($menu['name']) ?></td>
                    <td>
                        <span class="menu-location-badge<?= $cable ? '' : ' orphelin' ?>">
                            <?= htmlspecialchars($menu['location']) ?>
                        </span>
                        <div style="font-size:0.76rem;color:var(--text-muted);margin-top:4px;">
                            <?= htmlspecialchars($menu['emplacement_libelle'] ?? '') ?>
                        </div>
                    </td>
                    <td style="font-family: monospace; font-weight: 600;"><?= (int)($menu['nb_liens'] ?? 0) ?></td>
                    <td>
                        <?php if ($cable && (int)($menu['nb_liens'] ?? 0) > 0): ?>
                            <span class="menu-rendu oui">Oui</span>
                        <?php elseif ($cable): ?>
                            <span class="menu-rendu non">Vide — repli automatique</span>
                        <?php else: ?>
                            <span class="menu-rendu non">Non — emplacement non câblé</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align: right; display: flex; gap: 8px; justify-content: flex-end;">
                        <a href="<?= url('/admin/menus/edit/' . $menu['id']) ?>" class="btn-primary" style="padding: 6px 14px; font-size: 0.8rem;">
                            <i data-lucide="edit-3" style="width: 14px; height: 14px;"></i>
                            <span>Éditer</span>
                        </a>
                        <form method="POST" action="<?= url('/admin/menus/delete/' . $menu['id']) ?>"
                              onsubmit="return confirm('Supprimer ce menu et ses <?= (int)($menu['nb_liens'] ?? 0) ?> lien(s) ?');">
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
        <p style="font-size:0.8rem;color:var(--text-muted);margin:16px 0 0;">
            Un emplacement câblé mais vide n'efface rien : le site retombe sur son comportement
            automatique (pages publiées pour la navigation, section Services de l'accueil pour la
            colonne Services).
        </p>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i data-lucide="plus-circle"></i>
            <span>Créer un menu</span>
        </h3>
    </div>
    <form method="POST" action="<?= url('/admin/menus/create') ?>">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
        <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 16px; align-items: end;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Nom du menu *</label>
                <input type="text" name="name" class="form-input" placeholder="ex : Navigation principale" required>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Emplacement</label>
                <select name="location" class="form-input">
                    <?php foreach ($emplacements as $cle => $libelle): ?>
                        <option value="<?= htmlspecialchars($cle) ?>"><?= htmlspecialchars($libelle) ?></option>
                    <?php endforeach; ?>
                    <option value="secondary">Menu secondaire (non rendu)</option>
                </select>
            </div>
            <button type="submit" class="btn-primary">
                <i data-lucide="plus"></i>
                <span>Créer</span>
            </button>
        </div>
    </form>
</div>
