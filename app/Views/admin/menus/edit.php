<style>
.menu-builder { display: grid; grid-template-columns: 1fr 380px; gap: 24px; align-items: start; }
@media (max-width: 1100px) { .menu-builder { grid-template-columns: 1fr; } }

.menu-item-row {
    background: var(--bg-surface);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 14px 16px;
    margin-bottom: 8px;
    cursor: grab;
    transition: all 0.2s;
    position: relative;
}
.menu-item-row:hover { border-color: rgba(99,102,241,0.3); box-shadow: 0 4px 12px rgba(99,102,241,0.06); }
.menu-item-row.child { margin-left: 36px; border-left: 3px solid rgba(99,102,241,0.3); }
.menu-item-row.dragging { opacity: 0.5; cursor: grabbing; }

.item-header { display: flex; align-items: center; gap: 12px; }
.item-drag-handle { color: var(--text-muted); cursor: grab; flex-shrink: 0; }
.item-label { font-weight: 600; font-size: 0.9rem; flex: 1; }
.item-url-preview { font-family: monospace; font-size: 0.78rem; color: var(--text-muted); }
.item-actions { display: flex; gap: 6px; }

.item-details {
    display: none;
    margin-top: 14px;
    padding-top: 14px;
    border-top: 1px solid var(--border);
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}
.item-details.open { display: grid; }
.item-details .form-group { margin-bottom: 0; }

.add-link-panel { background: var(--bg-surface); border: 1px solid var(--border); border-radius: 16px; padding: 20px; }
.add-link-panel h4 { font-size: 0.95rem; font-weight: 700; margin-bottom: 16px; }
</style>

<div style="display: flex; align-items: center; gap: 16px; margin-bottom: 28px;">
    <a href="<?= url('/admin/menus') ?>" class="btn-secondary" style="padding: 8px 14px;">
        <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
        <span>Retour</span>
    </a>
    <div>
        <h1 style="font-size: 1.4rem; font-weight: 800; margin: 0;"><?= htmlspecialchars($menu['name']) ?></h1>
        <p style="color: var(--text-muted); font-size: 0.85rem; margin: 4px 0 0;">
            Emplacement : <strong><?= htmlspecialchars($menu['location']) ?></strong> —
            Glissez-déposez pour réordonner · Cliquez sur un item pour l'éditer
        </p>
    </div>
</div>

<div class="menu-builder">
    <!-- Left: items list -->
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
                <?php if (empty($items)): ?>
                    <p style="color: var(--text-muted); text-align: center; padding: 24px;">
                        Aucun lien. Ajoutez des liens depuis le panneau à droite.
                    </p>
                <?php else: ?>
                    <?php foreach ($items as $item): ?>
                    <div class="menu-item-row <?= $item['parent_id'] ? 'child' : '' ?>"
                         data-id="<?= $item['id'] ?>"
                         data-parent="<?= $item['parent_id'] ?? '' ?>">
                        <div class="item-header">
                            <span class="item-drag-handle"><i data-lucide="grip-vertical" style="width:18px;height:18px;"></i></span>
                            <div style="flex:1; min-width:0;">
                                <div class="item-label"><?= htmlspecialchars($item['label']) ?></div>
                                <div class="item-url-preview"><?= htmlspecialchars(MenuItem::resolveUrl($item)) ?></div>
                            </div>
                            <div class="item-actions">
                                <?php if (!$item['is_active']): ?>
                                    <span style="font-size:0.72rem;color:var(--text-muted);padding:2px 8px;background:var(--border);border-radius:4px;">Masqué</span>
                                <?php endif; ?>
                                <button type="button" class="btn-secondary btn-edit-item" style="padding:4px 10px;font-size:0.78rem;">
                                    <i data-lucide="chevron-down" style="width:14px;height:14px;"></i>
                                </button>
                                <button type="button" class="btn-secondary btn-remove-item" style="padding:4px 10px;font-size:0.78rem;color:var(--danger);">
                                    <i data-lucide="x" style="width:14px;height:14px;"></i>
                                </button>
                            </div>
                        </div>
                        <div class="item-details">
                            <div class="form-group">
                                <label class="form-label">Label</label>
                                <input type="text" class="form-input field-label" value="<?= htmlspecialchars($item['label']) ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">URL</label>
                                <input type="text" class="form-input field-url" value="<?= htmlspecialchars(MenuItem::resolveUrl($item)) ?>" placeholder="/contact ou https://...">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Icône Lucide (optionnel)</label>
                                <input type="text" class="form-input field-icon" value="<?= htmlspecialchars($item['icon'] ?? '') ?>" placeholder="ex: home, mail, phone">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Ouverture</label>
                                <select class="form-input field-target">
                                    <option value="_self" <?= ($item['target'] ?? '_self') === '_self' ? 'selected' : '' ?>>Même onglet</option>
                                    <option value="_blank" <?= ($item['target'] ?? '') === '_blank' ? 'selected' : '' ?>>Nouvel onglet</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Sous-menu de</label>
                                <select class="form-input field-parent">
                                    <option value="">— Racine —</option>
                                    <!-- populated by JS -->
                                </select>
                            </div>
                            <div class="form-group" style="display:flex;align-items:center;gap:10px;padding-top:22px;">
                                <input type="checkbox" class="field-active" id="active_<?= $item['id'] ?>" <?= $item['is_active'] ? 'checked' : '' ?>>
                                <label for="active_<?= $item['id'] ?>" style="font-size:0.88rem;font-weight:500;">Actif (visible)</label>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Menu settings -->
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
                            <option value="primary"   <?= $menu['location']==='primary'   ? 'selected':'' ?>>Header Principal</option>
                            <option value="footer"    <?= $menu['location']==='footer'    ? 'selected':'' ?>>Footer</option>
                            <option value="mobile"    <?= $menu['location']==='mobile'    ? 'selected':'' ?>>Mobile uniquement</option>
                            <option value="secondary" <?= $menu['location']==='secondary' ? 'selected':'' ?>>Secondaire</option>
                        </select>
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

    <!-- Right: add links -->
    <div>
        <div class="add-link-panel" style="margin-bottom: 20px;">
            <h4><i data-lucide="link" style="width:16px;height:16px;vertical-align:middle;margin-right:6px;"></i>Ajouter un lien personnalisé</h4>
            <div class="form-group">
                <label class="form-label">Label *</label>
                <input type="text" id="newLabel" class="form-input" placeholder="ex: Contact, Services...">
            </div>
            <div class="form-group">
                <label class="form-label">URL *</label>
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
            <h4><i data-lucide="file-text" style="width:16px;height:16px;vertical-align:middle;margin-right:6px;"></i>Ajouter une page CMS</h4>
            <div id="pagesList" style="display:flex;flex-direction:column;gap:6px;max-height:320px;overflow-y:auto;">
                <?php foreach ($pages as $p): ?>
                <button type="button" class="btn-page-add"
                    data-label="<?= htmlspecialchars($p['title']) ?>"
                    data-url="<?= htmlspecialchars($p['slug'] === 'home' ? '/' : '/'.$p['slug']) ?>"
                    data-page-id="<?= $p['id'] ?>"
                    style="display:flex;align-items:center;gap:10px;padding:8px 12px;background:var(--bg-surface);border:1px solid var(--border);border-radius:8px;cursor:pointer;width:100%;text-align:left;transition:all 0.2s;">
                    <i data-lucide="file" style="width:14px;height:14px;color:var(--primary);flex-shrink:0;"></i>
                    <span style="font-size:0.88rem;font-weight:500;"><?= htmlspecialchars($p['title']) ?></span>
                    <span style="font-size:0.75rem;color:var(--text-muted);font-family:monospace;margin-left:auto;">/<?= htmlspecialchars($p['slug']) ?></span>
                </button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
const menuId   = <?= $menu['id'] ?>;
const saveUrl  = '<?= url('/admin/menus/' . $menu['id'] . '/items/save') ?>';
const csrfToken = '<?= htmlspecialchars($csrf_token) ?>';

let itemCounter = 1000;

function getItems() {
    return [...document.querySelectorAll('#menuItemsList .menu-item-row')];
}

function getParentOptions(excludeId = null) {
    return getItems()
        .filter(el => !el.classList.contains('child') && el.dataset.id !== String(excludeId))
        .map(el => {
            const label = el.querySelector('.item-label')?.textContent || el.querySelector('.field-label')?.value || '';
            return `<option value="${el.dataset.id}">${label}</option>`;
        }).join('');
}

function refreshParentSelects() {
    getItems().forEach(row => {
        const sel = row.querySelector('.field-parent');
        if (!sel) return;
        const current = row.dataset.parent || '';
        sel.innerHTML = '<option value="">— Racine —</option>' + getParentOptions(row.dataset.id);
        sel.value = current;
    });
}

function syncRowFromFields(row) {
    const label  = row.querySelector('.field-label')?.value || '';
    const url    = row.querySelector('.field-url')?.value || '';
    const active = row.querySelector('.field-active')?.checked ?? true;
    const parent = row.querySelector('.field-parent')?.value || '';

    row.querySelector('.item-label').textContent = label || '(sans label)';
    row.querySelector('.item-url-preview').textContent = url || '#';
    row.dataset.parent = parent;
    row.classList.toggle('child', !!parent);

    const badge = row.querySelector('.item-actions span');
    if (badge) badge.style.display = active ? 'none' : 'inline';
}

function addItem(label, url, pageId = '', target = '_self', parentId = '', icon = '') {
    const id = 'new_' + (++itemCounter);
    const div = document.createElement('div');
    div.className = 'menu-item-row';
    div.dataset.id = id;
    div.dataset.parent = parentId;
    div.innerHTML = `
        <div class="item-header">
            <span class="item-drag-handle"><i data-lucide="grip-vertical" style="width:18px;height:18px;"></i></span>
            <div style="flex:1;min-width:0;">
                <div class="item-label">${label}</div>
                <div class="item-url-preview">${url || '#'}</div>
            </div>
            <div class="item-actions">
                <button type="button" class="btn-secondary btn-edit-item" style="padding:4px 10px;font-size:0.78rem;">
                    <i data-lucide="chevron-down" style="width:14px;height:14px;"></i></button>
                <button type="button" class="btn-secondary btn-remove-item" style="padding:4px 10px;font-size:0.78rem;color:var(--danger);">
                    <i data-lucide="x" style="width:14px;height:14px;"></i></button>
            </div>
        </div>
        <div class="item-details">
            <div class="form-group">
                <label class="form-label">Label</label>
                <input type="text" class="form-input field-label" value="${label}">
            </div>
            <div class="form-group">
                <label class="form-label">URL</label>
                <input type="text" class="form-input field-url" value="${url}" placeholder="/contact ou https://...">
            </div>
            <div class="form-group">
                <label class="form-label">Icône Lucide</label>
                <input type="text" class="form-input field-icon" value="${icon}" placeholder="ex: home, mail">
            </div>
            <div class="form-group">
                <label class="form-label">Ouverture</label>
                <select class="form-input field-target">
                    <option value="_self" ${target === '_self' ? 'selected' : ''}>Même onglet</option>
                    <option value="_blank" ${target === '_blank' ? 'selected' : ''}>Nouvel onglet</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Sous-menu de</label>
                <select class="form-input field-parent">
                    <option value="">— Racine —</option>
                </select>
            </div>
            <div class="form-group" style="display:flex;align-items:center;gap:10px;padding-top:22px;">
                <input type="checkbox" class="field-active" id="active_${id}" checked>
                <label for="active_${id}" style="font-size:0.88rem;font-weight:500;">Actif (visible)</label>
            </div>
            <input type="hidden" class="field-page-id" value="${pageId}">
        </div>`;
    div.dataset.pageId = pageId;

    const list = document.getElementById('menuItemsList');
    const empty = list.querySelector('p');
    if (empty) empty.remove();
    list.appendChild(div);

    bindRowEvents(div);
    refreshParentSelects();
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function bindRowEvents(row) {
    row.querySelector('.btn-edit-item').addEventListener('click', () => {
        const details = row.querySelector('.item-details');
        details.classList.toggle('open');
        refreshParentSelects();
    });
    row.querySelector('.btn-remove-item').addEventListener('click', () => {
        row.remove();
        refreshParentSelects();
    });
    row.querySelectorAll('.field-label, .field-url, .field-active, .field-parent').forEach(f => {
        f.addEventListener('input', () => syncRowFromFields(row));
        f.addEventListener('change', () => syncRowFromFields(row));
    });
}

// Bind existing rows
document.querySelectorAll('.menu-item-row').forEach(row => bindRowEvents(row));
refreshParentSelects();

// Add custom link
document.getElementById('btnAddCustom').addEventListener('click', () => {
    const label  = document.getElementById('newLabel').value.trim();
    const url    = document.getElementById('newUrl').value.trim();
    const target = document.getElementById('newTarget').value;
    if (!label) { alert('Le label est obligatoire.'); return; }
    addItem(label, url, '', target);
    document.getElementById('newLabel').value = '';
    document.getElementById('newUrl').value = '';
});

// Add page
document.querySelectorAll('.btn-page-add').forEach(btn => {
    btn.addEventListener('click', () => {
        addItem(btn.dataset.label, btn.dataset.url, btn.dataset.pageId);
    });
});

// Save
document.getElementById('btnSaveMenu').addEventListener('click', async () => {
    const rows = getItems();
    const items = rows.map((row, idx) => ({
        label:      row.querySelector('.field-label')?.value || '',
        url:        row.querySelector('.field-url')?.value || '',
        page_id:    row.querySelector('.field-page-id')?.value || '',
        target:     row.querySelector('.field-target')?.value || '_self',
        icon:       row.querySelector('.field-icon')?.value || '',
        parent_id:  row.querySelector('.field-parent')?.value || '',
        is_active:  (row.querySelector('.field-active')?.checked ? 1 : 0),
        sort_order: idx,
    }));

    const btn = document.getElementById('btnSaveMenu');
    btn.disabled = true;
    btn.innerHTML = '<i data-lucide="loader" style="width:16px;height:16px;"></i><span>Enregistrement...</span>';

    try {
        const body = new URLSearchParams();
        body.append('csrf_token', csrfToken);
        items.forEach((item, i) => {
            Object.entries(item).forEach(([k, v]) => body.append(`items[${i}][${k}]`, v));
        });
        const res  = await fetch(saveUrl, { method: 'POST', body });
        const json = await res.json();
        if (json.success) {
            btn.innerHTML = '<i data-lucide="check" style="width:16px;height:16px;"></i><span>Enregistré !</span>';
            btn.style.background = '#10b981';
            setTimeout(() => {
                btn.disabled = false;
                btn.innerHTML = '<i data-lucide="save" style="width:16px;height:16px;"></i><span>Enregistrer</span>';
                btn.style.background = '';
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }, 2000);
        } else {
            alert('Erreur : ' + (json.error || 'Inconnue'));
            btn.disabled = false;
            btn.innerHTML = '<i data-lucide="save" style="width:16px;height:16px;"></i><span>Enregistrer</span>';
        }
    } catch (e) {
        alert('Erreur réseau.');
        btn.disabled = false;
        btn.innerHTML = '<i data-lucide="save" style="width:16px;height:16px;"></i><span>Enregistrer</span>';
    }
    if (typeof lucide !== 'undefined') lucide.createIcons();
});

// Simple drag-and-drop reorder
let dragged = null;
document.addEventListener('dragstart', e => {
    const row = e.target.closest('.menu-item-row');
    if (row) { dragged = row; row.classList.add('dragging'); }
});
document.addEventListener('dragend', e => {
    if (dragged) { dragged.classList.remove('dragging'); dragged = null; }
});
document.addEventListener('dragover', e => {
    e.preventDefault();
    const row = e.target.closest('.menu-item-row');
    if (row && dragged && row !== dragged) {
        const list = document.getElementById('menuItemsList');
        const rows = [...list.querySelectorAll('.menu-item-row')];
        const di = rows.indexOf(dragged);
        const ri = rows.indexOf(row);
        if (di < ri) list.insertBefore(dragged, row.nextSibling);
        else list.insertBefore(dragged, row);
    }
});
document.querySelectorAll('.menu-item-row').forEach(r => r.setAttribute('draggable', 'true'));
document.addEventListener('DOMNodeInserted', e => {
    if (e.target.classList?.contains('menu-item-row')) {
        e.target.setAttribute('draggable', 'true');
    }
});
</script>
