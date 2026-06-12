<div class="card" style="max-width: 600px; margin: 0 auto 24px auto;">
    <div class="card-header">
        <h2 class="card-title">
            <i data-lucide="plus-circle"></i>
            <span>Créer une nouvelle page</span>
        </h2>
        <a href="<?= url('/admin/pages') ?>" class="btn-secondary" style="padding: 6px 12px; font-size: 0.8rem;">Retour</a>
    </div>

    <form action="<?= url('/admin/pages/create') ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

        <div class="admin-form-group">
            <label for="title">Titre de la page</label>
            <input type="text" id="title" name="title" class="admin-input" placeholder="Ex: Notre Agence" required autofocus oninput="generateSlug(this.value)">
        </div>

        <div class="admin-form-group">
            <label for="slug">URL Slug</label>
            <input type="text" id="slug" name="slug" class="admin-input" placeholder="Ex: notre-agence" required>
            <small style="color: var(--text-muted); font-size: 0.75rem; margin-top: 4px; display: block;">L'URL finale sera : <strong>digitaliumgroup.com/<span id="slug-preview">notre-agence</span></strong></small>
        </div>

        <div class="admin-form-group">
            <label for="status">Statut de publication</label>
            <select id="status" name="status" class="admin-select">
                <option value="draft">Brouillon</option>
                <option value="published">Publiée</option>
            </select>
        </div>

        <hr style="border: 0; border-top: 1px solid var(--border); margin: 24px 0;">

        <h3 style="font-family: var(--font-headings); font-size: 1rem; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="search" style="width: 18px; height: 18px; color: var(--primary);"></i>
            <span>Optimisation SEO</span>
        </h3>

        <div class="admin-form-group">
            <label for="meta_title">Balise Titre SEO (Meta Title)</label>
            <input type="text" id="meta_title" name="meta_title" class="admin-input" placeholder="Laisser vide">
        </div>

        <div class="admin-form-group">
            <label for="meta_description">Description SEO (Meta Description)</label>
            <textarea id="meta_description" name="meta_description" class="admin-textarea" rows="3" placeholder="Saisissez une description..."></textarea>
        </div>

        <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 24px;">
            <a href="<?= url('/admin/pages') ?>" class="btn-secondary">Annuler</a>
            <button type="submit" class="btn-primary">
                <i data-lucide="save"></i>
                <span>Créer la page</span>
            </button>
        </div>
    </form>
</div>

<script>
    function generateSlug(text) {
        let slug = text.toString().toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/\s+/g, '-')
            .replace(/[^\w\-]+/g, '')
            .replace(/\-\-+/g, '-')
            .replace(/^-+/, '')
            .replace(/-+$/, '');
        
        document.getElementById('slug').value = slug;
        document.getElementById('slug-preview').innerText = slug ? slug : 'notre-agence';
    }

    document.getElementById('slug').addEventListener('input', function() {
        document.getElementById('slug-preview').innerText = this.value ? this.value : 'notre-agence';
    });
</script>
