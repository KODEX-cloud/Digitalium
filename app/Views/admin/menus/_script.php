<script>
/**
 * Éditeur de menu — comportement.
 *
 * Deux points méritent d'être signalés :
 *
 * 1. L'IDENTIFIANT DE CHAQUE LIEN EST ENVOYÉ. Le serveur réconcilie désormais
 *    (mise à jour, insertion, suppression) au lieu de tout effacer puis tout
 *    réécrire. Sans `id`, chaque enregistrement recréerait tous les liens et
 *    ferait perdre leur identité.
 *
 * 2. LES NOUVELLES LIGNES REÇOIVENT `draggable` À LA CRÉATION. La version
 *    précédente s'appuyait sur `DOMNodeInserted`, un événement de mutation
 *    supprimé des navigateurs récents : les liens ajoutés n'étaient plus
 *    déplaçables.
 *
 * La profondeur est limitée à deux niveaux, comme le rendu du site : un enfant
 * ne peut pas devenir parent.
 */
(function () {
    'use strict';

    const saveUrl   = <?= json_encode(url('/admin/menus/' . $menu['id'] . '/items/save'), JSON_UNESCAPED_SLASHES) ?>;
    const csrfToken = <?= json_encode($csrf_token) ?>;

    const liste = document.getElementById('menuItemsList');
    const vide  = document.getElementById('menuEmpty');
    let compteur = 0;

    const lignes = () => [...liste.querySelectorAll('.menu-item-row')];
    const estEnfant = (row) => row.classList.contains('child');

    function majEtatVide() {
        if (vide) { vide.hidden = lignes().length > 0; }
    }

    /* Les boutons d'imbrication ne sont actifs que là où l'action a un sens :
       on ne peut devenir sous-menu que d'un lien racine placé au-dessus. */
    function majBoutons() {
        const rows = lignes();
        rows.forEach((row, i) => {
            const precedent = i > 0 ? rows[i - 1] : null;
            const peutRentrer = !!precedent && !estEnfant(precedent) && !estEnfant(row);
            row.querySelector('.btn-indent').disabled  = !peutRentrer;
            row.querySelector('.btn-outdent').disabled = !estEnfant(row);
        });
    }

    /* Un enfant suit toujours son parent : le parent est la ligne racine la
       plus proche au-dessus. Cela évite un champ « parent » à tenir à jour. */
    function majParents() {
        let racineCourante = '';
        lignes().forEach(row => {
            if (estEnfant(row)) {
                row.dataset.parent = racineCourante;
                if (racineCourante === '') { row.classList.remove('child'); }
            } else {
                racineCourante = row.dataset.id;
                row.dataset.parent = '';
            }
        });
    }

    function rafraichir() {
        majParents();
        majBoutons();
        majEtatVide();
        if (typeof lucide !== 'undefined') { lucide.createIcons(); }
    }

    function synchroniser(row) {
        const label  = row.querySelector('.field-label').value;
        const url    = row.querySelector('.field-url').value;
        const actif  = row.querySelector('.field-active').checked;
        row.querySelector('.item-label').textContent = label || '(sans intitulé)';
        row.querySelector('.item-url-preview').textContent = url || '#';
        row.querySelector('.badge-inactive').hidden = actif;
        row.classList.toggle('inactive', !actif);
    }

    function brancher(row) {
        row.querySelector('.btn-edit-item').addEventListener('click', () => {
            row.querySelector('.item-details').classList.toggle('open');
        });
        row.querySelector('.btn-remove-item').addEventListener('click', () => {
            row.remove();
            rafraichir();
        });
        row.querySelector('.btn-indent').addEventListener('click', () => {
            row.classList.add('child');
            rafraichir();
        });
        row.querySelector('.btn-outdent').addEventListener('click', () => {
            row.classList.remove('child');
            rafraichir();
        });
        row.querySelectorAll('.field-label, .field-url, .field-active').forEach(champ => {
            champ.addEventListener('input',  () => synchroniser(row));
            champ.addEventListener('change', () => synchroniser(row));
        });
    }

    function echapper(texte) {
        const d = document.createElement('div');
        d.textContent = texte;
        return d.innerHTML;
    }

    function ajouter(label, url, pageId = '', target = '_self') {
        const ref = 'new_' + (++compteur);
        const row = document.createElement('div');
        row.className = 'menu-item-row';
        row.setAttribute('draggable', 'true');
        row.dataset.id = ref;
        row.dataset.parent = '';
        row.innerHTML = `
            <div class="item-header">
                <span class="item-drag-handle"><i data-lucide="grip-vertical" style="width:18px;height:18px;"></i></span>
                <div style="flex:1; min-width:0;">
                    <div class="item-label">${echapper(label)}</div>
                    <div class="item-url-preview">${echapper(url || '#')}</div>
                </div>
                <div class="item-actions">
                    <span class="item-badge badge-inactive" hidden>Masqué</span>
                    <button type="button" class="btn-nest btn-outdent" title="Sortir du sous-menu">&#8592;</button>
                    <button type="button" class="btn-nest btn-indent" title="Mettre en sous-menu du lien au-dessus">&#8594;</button>
                    <button type="button" class="btn-secondary btn-edit-item" style="padding:4px 10px;font-size:0.78rem;">
                        <i data-lucide="chevron-down" style="width:14px;height:14px;"></i>
                    </button>
                    <button type="button" class="btn-secondary btn-remove-item" style="padding:4px 10px;font-size:0.78rem;color:var(--danger);">
                        <i data-lucide="x" style="width:14px;height:14px;"></i>
                    </button>
                </div>
            </div>
            <div class="item-details">
                <input type="hidden" class="field-page-id" value="${echapper(String(pageId || 0))}">
                <div class="form-group">
                    <label class="form-label">Intitulé</label>
                    <input type="text" class="form-input field-label" value="${echapper(label)}">
                </div>
                <div class="form-group">
                    <label class="form-label">Adresse</label>
                    <input type="text" class="form-input field-url" value="${echapper(url)}">
                </div>
                <div class="form-group">
                    <label class="form-label">Icône Lucide (facultatif)</label>
                    <input type="text" class="form-input field-icon" value="">
                </div>
                <div class="form-group">
                    <label class="form-label">Ouverture</label>
                    <select class="form-input field-target">
                        <option value="_self"${target === '_self' ? ' selected' : ''}>Même onglet</option>
                        <option value="_blank"${target === '_blank' ? ' selected' : ''}>Nouvel onglet</option>
                    </select>
                </div>
                <div class="form-group" style="display:flex;align-items:center;gap:10px;padding-top:22px;">
                    <input type="checkbox" class="field-active" id="active_${ref}" checked>
                    <label for="active_${ref}" style="font-size:0.88rem;font-weight:500;">Visible sur le site</label>
                </div>
            </div>`;
        liste.appendChild(row);
        brancher(row);
        rafraichir();
    }

    // ── Ajout ───────────────────────────────────────────────────────────────
    document.getElementById('btnAddCustom').addEventListener('click', () => {
        const label = document.getElementById('newLabel').value.trim();
        const url   = document.getElementById('newUrl').value.trim();
        if (!label || !url) { alert('Intitulé et adresse sont requis.'); return; }
        ajouter(label, url, '', document.getElementById('newTarget').value);
        document.getElementById('newLabel').value = '';
        document.getElementById('newUrl').value = '';
    });

    document.querySelectorAll('.btn-page-add').forEach(btn => {
        btn.addEventListener('click', () => {
            ajouter(btn.dataset.label, btn.dataset.url, btn.dataset.pageId);
        });
    });

    // ── Glisser-déposer ─────────────────────────────────────────────────────
    let deplacee = null;
    liste.addEventListener('dragstart', e => {
        const row = e.target.closest('.menu-item-row');
        if (row) { deplacee = row; row.classList.add('dragging'); }
    });
    liste.addEventListener('dragend', () => {
        if (deplacee) { deplacee.classList.remove('dragging'); deplacee = null; rafraichir(); }
    });
    liste.addEventListener('dragover', e => {
        e.preventDefault();
        const cible = e.target.closest('.menu-item-row');
        if (!cible || !deplacee || cible === deplacee) { return; }
        const rows = lignes();
        const i = rows.indexOf(deplacee), j = rows.indexOf(cible);
        liste.insertBefore(deplacee, i < j ? cible.nextSibling : cible);
    });

    // ── Enregistrement ──────────────────────────────────────────────────────
    const bouton = document.getElementById('btnSaveMenu');
    const libelleBouton = (html, fond) => {
        bouton.innerHTML = html;
        bouton.style.background = fond || '';
        if (typeof lucide !== 'undefined') { lucide.createIcons(); }
    };
    const AU_REPOS = '<i data-lucide="save" style="width:16px;height:16px;"></i><span>Enregistrer</span>';

    bouton.addEventListener('click', async () => {
        majParents();

        const corps = new URLSearchParams();
        corps.append('csrf_token', csrfToken);
        lignes().forEach((row, i) => {
            const champs = {
                id:         row.dataset.id,          // <- indispensable à la réconciliation
                parent_id:  row.dataset.parent || '',
                label:      row.querySelector('.field-label').value,
                url:        row.querySelector('.field-url').value,
                page_id:    row.querySelector('.field-page-id').value,
                target:     row.querySelector('.field-target').value,
                icon:       row.querySelector('.field-icon').value,
                is_active:  row.querySelector('.field-active').checked ? 1 : 0,
                sort_order: i
            };
            Object.entries(champs).forEach(([k, v]) => corps.append(`items[${i}][${k}]`, v));
        });

        bouton.disabled = true;
        libelleBouton('<i data-lucide="loader" style="width:16px;height:16px;"></i><span>Enregistrement…</span>');

        try {
            const reponse = await fetch(saveUrl, { method: 'POST', body: corps });
            const json = await reponse.json();
            if (json.success) {
                libelleBouton('<i data-lucide="check" style="width:16px;height:16px;"></i><span>Enregistré</span>', '#10b981');
                // Recharger : le serveur a attribué de vrais identifiants aux
                // nouveaux liens, et l'écran doit refléter ce qui est en base.
                setTimeout(() => window.location.reload(), 700);
                return;
            }
            alert('Erreur : ' + (json.error || 'inconnue'));
        } catch (e) {
            alert('Erreur réseau — le menu n\'a pas été enregistré.');
        }
        bouton.disabled = false;
        libelleBouton(AU_REPOS);
    });

    lignes().forEach(brancher);
    rafraichir();
})();
</script>
