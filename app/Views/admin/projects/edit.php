<style>
    .projects-grid-form {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 28px;
        align-items: start;
    }
    @media (max-width: 1024px) {
        .projects-grid-form {
            grid-template-columns: 1fr;
        }
    }

    .media-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(15, 23, 42, 0.25);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 20px;
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }
    .media-modal.active {
        display: flex;
    }
    .modal-content {
        background-color: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.6);
        border-radius: 24px;
        width: 100%;
        max-width: 800px;
        max-height: 80vh;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        box-shadow: 0 30px 60px -15px rgba(37, 99, 235, 0.15), 0 10px 20px -5px rgba(0, 0, 0, 0.03);
    }
    .modal-header {
        padding: 20px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .modal-body {
        padding: 20px;
        overflow-y: auto;
        flex-grow: 1;
    }
    .modal-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 16px;
    }
    .modal-media-item {
        background-color: rgba(255,255,255,0.4);
        border: 1px solid var(--border);
        border-radius: 12px;
        aspect-ratio: 1;
        overflow: hidden;
        cursor: pointer;
        position: relative;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .modal-media-item:hover {
        border-color: var(--primary);
        transform: translateY(-2px);
        box-shadow: 0 8px 16px -4px rgba(37, 99, 235, 0.2);
    }
    .modal-media-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .image-field-wrapper {
        display: flex;
        gap: 16px;
        align-items: center;
    }
    .image-field-preview {
        width: 70px;
        height: 70px;
        border-radius: 12px;
        border: 1px solid var(--border);
        background-color: rgba(255, 255, 255, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        flex-shrink: 0;
    }
    .image-field-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
</style>

<form action="<?= url('/admin/projects/edit/' . $project['id']) ?>" method="POST">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

    <div class="projects-grid-form">
        <div>
            <div class="card">
                <div class="card-header" style="margin-bottom: 24px;">
                    <h2 class="card-title">
                        <i data-lucide="edit-3"></i>
                        <span>Modifier la Réalisation</span>
                    </h2>
                </div>

                <div class="admin-form-group">
                    <label for="title">Titre du Projet</label>
                    <input type="text" id="title" name="title" class="admin-input" value="<?= htmlspecialchars($project['title']) ?>" required>
                </div>

                <div class="admin-form-group">
                    <label for="category">Catégorie du Projet</label>
                    <select id="category" name="category" class="admin-select" required>
                        <option value="Politique" <?= $project['category'] === 'Politique' ? 'selected' : '' ?>>Politique</option>
                        <option value="Institutionnel" <?= $project['category'] === 'Institutionnel' ? 'selected' : '' ?>>Institutionnel</option>
                        <option value="Médical" <?= $project['category'] === 'Médical' ? 'selected' : '' ?>>Médical</option>
                        <option value="Humanitaire" <?= $project['category'] === 'Humanitaire' ? 'selected' : '' ?>>Humanitaire</option>
                        <option value="Média Digital" <?= $project['category'] === 'Média Digital' ? 'selected' : '' ?>>Média Digital</option>
                        <option value="E-Commerce" <?= $project['category'] === 'E-Commerce' ? 'selected' : '' ?>>E-Commerce</option>
                        <option value="IA & Automatisation" <?= $project['category'] === 'IA & Automatisation' ? 'selected' : '' ?>>IA & Automatisation</option>
                    </select>
                </div>

                <div class="admin-form-group">
                    <label for="technologies">Technologies utilisées (séparées par des virgules)</label>
                    <input type="text" id="technologies" name="technologies" class="admin-input" value="<?= htmlspecialchars($project['technologies'] ?? '') ?>" placeholder="Ex: HTML, CSS, Laravel, MySQL">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="admin-form-group">
                        <label for="client">Client</label>
                        <input type="text" id="client" name="client" class="admin-input" value="<?= htmlspecialchars($project['client'] ?? '') ?>" placeholder="Nom du client">
                    </div>
                    <div class="admin-form-group">
                        <label for="project_date">Date du projet</label>
                        <input type="date" id="project_date" name="project_date" class="admin-input" value="<?= htmlspecialchars($project['project_date'] ?? '') ?>">
                    </div>
                </div>

                <div class="admin-form-group">
                    <label for="slug">Slug URL <small style="color:var(--text-muted);font-weight:400;">(auto-généré si vide)</small></label>
                    <input type="text" id="slug" name="slug" class="admin-input" value="<?= htmlspecialchars($project['slug'] ?? '') ?>" placeholder="mon-projet">
                </div>

                <div class="admin-form-group">
                    <label for="description">Description courte <small style="color:var(--text-muted);font-weight:400;">(résumé public)</small></label>
                    <textarea id="description" name="description" class="admin-textarea" rows="3" placeholder="Résumé du projet affiché dans la liste et les meta..."><?= htmlspecialchars($project['description'] ?? '') ?></textarea>
                </div>

                <div class="admin-form-group">
                    <label for="external_link">Lien externe du projet (URL)</label>
                    <input type="url" id="external_link" name="external_link" class="admin-input" value="<?= htmlspecialchars($project['external_link'] ?? '') ?>" placeholder="https://projet-site.com">
                </div>

                <div class="admin-form-group">
                    <label for="context">Contexte du Projet</label>
                    <textarea id="context" name="context" class="admin-textarea" rows="4" placeholder="Décrivez le besoin du client et le cadre du projet..."><?= htmlspecialchars($project['context'] ?? '') ?></textarea>
                </div>

                <div class="admin-form-group">
                    <label for="impact">Impact / Résultats (ex: Canal direct de communication...)</label>
                    <textarea id="impact" name="impact" class="admin-textarea" rows="4" placeholder="Décrivez l'impact concret du projet pour le client..."><?= htmlspecialchars($project['impact'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <div>
            <div class="card">
                <div class="card-header" style="margin-bottom: 24px;">
                    <h2 class="card-title">
                        <i data-lucide="image"></i>
                        <span>Médias du Projet</span>
                    </h2>
                </div>

                <div class="admin-form-group">
                    <label>Image principale (Mockup Ordinateur)</label>
                    <?= \App\Helpers\MediaHelper::renderField('main_image', $project['main_image'] ?? '', 'main_image') ?>
                </div>

                <div class="admin-form-group">
                    <label>Logo du Projet / Client</label>
                    <?= \App\Helpers\MediaHelper::renderField('logo', $project['logo'] ?? '', 'logo') ?>
                </div>

                <div class="admin-form-group">
                    <label for="sort_order">Ordre d'affichage</label>
                    <input type="number" id="sort_order" name="sort_order" class="admin-input" value="<?= $project['sort_order'] ?>">
                </div>

                <div class="admin-form-group" style="display: flex; align-items: center; gap: 10px; margin-top: 15px;">
                    <input type="checkbox" id="is_featured" name="is_featured" value="1" style="width:18px; height:18px; cursor:pointer;" <?= $project['is_featured'] ? 'checked' : '' ?>>
                    <label for="is_featured" style="margin-bottom:0; cursor:pointer;">Projet Vedette (Featured)</label>
                </div>
            </div>

            <div class="card" style="display: flex; flex-direction: column; gap: 10px;">
                <button type="submit" class="btn-primary" style="justify-content: center; width: 100%; padding: 12px;">
                    <i data-lucide="save"></i>
                    <span>Mettre à jour</span>
                </button>
                <a href="<?= url('/admin/projects') ?>" class="btn-secondary" style="justify-content: center; width: 100%; padding: 12px;">Annuler</a>
            </div>
        </div>
    </div>
</form>


