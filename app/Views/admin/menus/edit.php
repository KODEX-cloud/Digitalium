<?php
/**
 * Éditeur d'un menu de navigation.
 *
 * Les styles et le script vivent dans `_styles.php` et `_script.php` : l'écran
 * tenait auparavant dans un seul fichier de 21 Ko où le balisage, la mise en
 * forme et le comportement étaient mêlés.
 *
 * Variables attendues : $menu, $items, $pages, $emplacements, $estCable, $csrf_token
 *
 * Attention : cette vue est chargée par `require` depuis Controller::render() et
 * s'exécute donc dans le namespace GLOBAL. Tout nom de classe doit être
 * pleinement qualifié — un `MenuItem::` court a déjà provoqué un 500 ici.
 */
require __DIR__ . '/_styles.php';
?>

<div style="display: flex; align-items: center; gap: 16px; margin-bottom: 28px; flex-wrap: wrap;">
    <a href="<?= url('/admin/menus') ?>" class="btn-secondary" style="padding: 8px 14px;">
        <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
        <span>Retour</span>
    </a>
    <div>
        <h1 style="font-size: 1.4rem; font-weight: 800; margin: 0;"><?= htmlspecialchars($menu['name']) ?></h1>
        <p style="color: var(--text-muted); font-size: 0.85rem; margin: 4px 0 0;">
            <?= htmlspecialchars(\App\Models\Menu::libelleEmplacement((string)$menu['location'])) ?>
            — glissez pour réordonner, utilisez les flèches pour créer un sous-menu.
        </p>
    </div>
</div>

<?php if (empty($estCable)): ?>
    <div class="menu-alert">
        <i data-lucide="alert-triangle" style="width:18px;height:18px;flex-shrink:0;margin-top:1px;"></i>
        <div>
            Ce menu porte l'emplacement <strong><?= htmlspecialchars($menu['location']) ?></strong>, qui
            n'est rendu nulle part sur le site. Il reste modifiable, mais il ne s'affichera pas tant
            qu'un emplacement câblé ne lui est pas attribué ci-dessous.
        </div>
    </div>
<?php endif; ?>

<div class="menu-builder">
    <!-- Colonne gauche : les liens -->
    <div>
        <div class="card" style="margin-bottom: 20px;">
            <div class="card-header" style="margin-bottom: 16px;">
                <h3 class="card-title">
                    <i data-lucide="list-ordered"></i>
                    <span>Liens du menu</span>
                </h3>
                <button type="button" id="btnSaveMenu" class="btn-primary" style="padding: 8px 16px; font-size: 0.85rem;">
                    <i data-lucide="save" style="width: 16px; height: 16px;"></i>
                    <span>Enregistrer</span>
                </button>
            </div>

            <div id="menuItemsList">
                <?php foreach ($items as $item):
                    $estEnfant = !empty($item['parent_id']);
                    $actif     = (int)($item['is_active'] ?? 1) === 1;
                ?>
                <div class="menu-item-row<?= $estEnfant ? ' child' : '' ?><?= $actif ? '' : ' inactive' ?>"
                     draggable="true"
                     data-id="<?= (int)$item['id'] ?>"
                     data-parent="<?= $estEnfant ? (int)$item['parent_id'] : '' ?>">
                    <div class="item-header">
                        <span class="item-drag-handle"><i data-lucide="grip-vertical" style="width:18px;height:18px;"></i></span>
                        <div style="flex:1; min-width:0;">
                            <div class="item-label"><?= htmlspecialchars($item['label']) ?></div>
                            <div class="item-url-preview"><?= htmlspecialchars(\App\Models\MenuItem::resolveUrl($item)) ?></div>
                        </div>
                        <div class="item-actions">
                            <span class="item-badge badge-inactive" <?= $actif ? 'hidden' : '' ?>>Masqué</span>
                            <button type="button" class="btn-nest btn-outdent" title="Sortir du sous-menu">&#8592;</button>
                            <button type="button" class="btn-nest btn-indent" title="Mettre en sous-menu du lien au-dessus">&#8594;</button>
                            <button type="button" class="btn-secondary btn-edit-item" style="padding:4px 10px;font-size:0.78rem;" title="Modifier">
                                <i data-lucide="chevron-down" style="width:14px;height:14px;"></i>
                            </button>
                            <button type="button" class="btn-secondary btn-remove-item" style="padding:4px 10px;font-size:0.78rem;color:var(--danger);" title="Retirer">
                                <i data-lucide="x" style="width:14px;height:14px;"></i>
                            </button>
                        </div>
                    </div>
                    <div class="item-details">
                        <input type="hidden" class="field-page-id" value="<?= (int)($item['page_id'] ?? 0) ?>">
                        <div class="form-group">
                            <label class="form-label">Intitulé</label>
                            <input type="text" class="form-input field-label" value="<?= htmlspecialchars($item['label']) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Adresse</label>
                            <input type="text" class="form-input field-url" value="<?= htmlspecialchars(\App\Models\MenuItem::resolveUrl($item)) ?>" placeholder="/contact ou https://...">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Icône Lucide (facultatif)</label>
                            <input type="text" class="form-input field-icon" value="<?= htmlspecialchars($item['icon'] ?? '') ?>" placeholder="ex : home, mail, phone">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ouverture</label>
                            <select class="form-input field-target">
                                <option value="_self"  <?= ($item['target'] ?? '_self') === '_self'  ? 'selected' : '' ?>>Même onglet</option>
                                <option value="_blank" <?= ($item['target'] ?? '')      === '_blank' ? 'selected' : '' ?>>Nouvel onglet</option>
                            </select>
                        </div>
                        <div class="form-group" style="display:flex;align-items:center;gap:10px;padding-top:22px;">
                            <input type="checkbox" class="field-active" id="active_<?= (int)$item['id'] ?>" <?= $actif ? 'checked' : '' ?>>
                            <label for="active_<?= (int)$item['id'] ?>" style="font-size:0.88rem;font-weight:500;">Visible sur le site</label>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <p id="menuEmpty" style="color: var(--text-muted); text-align: center; padding: 24px;" <?= empty($items) ? '' : 'hidden' ?>>
                Aucun lien. Ajoutez-en depuis le panneau de droite.
            </p>
        </div>

        <!-- Paramètres du menu -->
        <div class="card">
            <div class="card-header" style="margin-bottom: 16px;">
                <h3 class="card-title"><i data-lucide="settings"></i><span>Paramètres du menu</span></h3>
            </div>
            <form method="POST" action="<?= url('/admin/menus/update/' . $menu['id']) ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Nom</label>
                        <input type="text" name="name" class="form-input" value="<?= htmlspecialchars($menu['name']) ?>" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Emplacement</label>
                        <select name="location" class="form-input">
                            <?php foreach ($emplacements as $cle => $libelle): ?>
                                <option value="<?= htmlspecialchars($cle) ?>" <?= $menu['location'] === $cle ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($libelle) ?>
                                </option>
                            <?php endforeach; ?>
                            <?php if (!isset($emplacements[$menu['location']])): ?>
                                <option value="<?= htmlspecialchars($menu['location']) ?>" selected>
                                    <?= htmlspecialchars($menu['location']) ?> (non rendu)
                                </option>
                            <?php endif; ?>
                        </select>
                        <p style="font-size:0.74rem;color:var(--text-muted);margin:5px 0 0;">
                            Un seul menu par emplacement : le premier créé est celui qui s'affiche.
                        </p>
                    </div>
                </div>
                <div style="margin-top: 16px;">
                    <button type="submit" class="btn-secondary">
                        <i data-lucide="save" style="width:16px;height:16px;"></i>
                        <span>Mettre à jour</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Colonne droite : ajouter -->
    <div>
        <div class="add-link-panel" style="margin-bottom: 20px;">
            <h4><i data-lucide="link" style="width:16px;height:16px;vertical-align:middle;margin-right:6px;"></i>Lien personnalisé</h4>
            <div class="form-group">
                <label class="form-label">Intitulé *</label>
                <input type="text" id="newLabel" class="form-input" placeholder="ex : Contact, Services...">
            </div>
            <div class="form-group">
                <label class="form-label">Adresse *</label>
                <input type="text" id="newUrl" class="form-input" placeholder="/contact ou https://...">
            </div>
            <div class="form-group">
                <label class="form-label">Ouverture</label>
                <select id="newTarget" class="form-input">
                    <option value="_self">Même onglet</option>
                    <option value="_blank">Nouvel onglet</option>
                </select>
            </div>
            <button type="button" id="btnAddCustom" class="btn-primary" style="width:100%;">
                <i data-lucide="plus-circle"></i>
                <span>Ajouter ce lien</span>
            </button>
        </div>

        <div class="add-link-panel">
            <h4><i data-lucide="file-text" style="width:16px;height:16px;vertical-align:middle;margin-right:6px;"></i>Page du site</h4>
            <?php if (empty($pages)): ?>
                <p style="font-size:0.84rem;color:var(--text-muted);margin:0;">Aucune page publiée.</p>
            <?php else: ?>
            <div id="pagesList" style="display:flex;flex-direction:column;gap:6px;max-height:320px;overflow-y:auto;">
                <?php foreach ($pages as $p): ?>
                <button type="button" class="btn-page-add"
                    data-label="<?= htmlspecialchars($p['title']) ?>"
                    data-url="<?= htmlspecialchars($p['slug'] === 'home' ? '/' : '/' . $p['slug']) ?>"
                    data-page-id="<?= (int)$p['id'] ?>">
                    <i data-lucide="file" style="width:14px;height:14px;color:var(--primary);flex-shrink:0;"></i>
                    <span style="font-size:0.88rem;font-weight:500;"><?= htmlspecialchars($p['title']) ?></span>
                    <span style="font-size:0.75rem;color:var(--text-muted);font-family:monospace;margin-left:auto;">/<?= htmlspecialchars($p['slug']) ?></span>
                </button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/_script.php'; ?>
