<?php
/**
 * Formulaire d'une réalisation — partagé par la création et la modification.
 *
 * Les deux vues déclaraient auparavant leur propre liste de champs : un champ
 * ajouté d'un seul côté n'était éditable qu'à la création ou qu'à la
 * modification. Une seule source désormais (Règle #1).
 *
 * Variables attendues :
 *   $project      tableau de la réalisation (vide à la création)
 *   $formAction   URL de soumission
 *   $submitLabel  libellé du bouton
 *   $csrf_token
 */
$p = $project ?? [];
$v = static fn(string $k, string $default = ''): string => htmlspecialchars((string)($p[$k] ?? $default), ENT_QUOTES, 'UTF-8');
?>

<form action="<?= htmlspecialchars($formAction) ?>" method="POST">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

    <div class="projects-grid-form">
        <div>

            <!-- Identité -->
            <div class="card" style="margin-bottom: 24px;">
                <div class="card-header" style="margin-bottom: 24px;">
                    <h2 class="card-title"><i data-lucide="edit-3"></i><span>Identité du projet</span></h2>
                </div>

                <div class="admin-form-group">
                    <label for="title">Titre du projet *</label>
                    <input type="text" id="title" name="title" class="admin-input" value="<?= $v('title') ?>" required>
                </div>

                <div class="admin-form-group">
                    <label for="slug">Slug URL <small style="color:var(--text-muted);font-weight:400;">(auto-généré si vide)</small></label>
                    <input type="text" id="slug" name="slug" class="admin-input" value="<?= $v('slug') ?>">
                    <p class="field-help">Adresse de l'étude de cas : /realisations/<em>slug</em></p>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="admin-form-group">
                        <label for="category">Catégorie *</label>
                        <input type="text" id="category" name="category" class="admin-input" value="<?= $v('category') ?>" required list="cat-suggestions">
                        <datalist id="cat-suggestions">
                            <option value="Software"></option>
                            <option value="IA &amp; Automatisation"></option>
                            <option value="Data &amp; BI"></option>
                            <option value="Infrastructure"></option>
                            <option value="Web &amp; Digital"></option>
                            <option value="Cybersécurité"></option>
                        </datalist>
                        <p class="field-help">Sert de filtre sur la page Réalisations.</p>
                    </div>
                    <div class="admin-form-group">
                        <label for="sector">Secteur d'activité</label>
                        <input type="text" id="sector" name="sector" class="admin-input" value="<?= $v('sector') ?>">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="admin-form-group">
                        <label for="client">Client</label>
                        <input type="text" id="client" name="client" class="admin-input" value="<?= $v('client') ?>">
                        <p class="field-help">Laisser vide si le nom ne peut pas être communiqué.</p>
                    </div>
                    <div class="admin-form-group">
                        <label for="year">Année</label>
                        <input type="text" id="year" name="year" class="admin-input" value="<?= $v('year') ?>" placeholder="2026">
                    </div>
                </div>

                <div class="admin-form-group">
                    <label for="description">Description courte</label>
                    <textarea id="description" name="description" class="admin-textarea" rows="3"><?= $v('description') ?></textarea>
                    <p class="field-help">Résumé affiché sur la carte de la grille.</p>
                </div>

                <div class="admin-form-group">
                    <label for="external_link">Lien externe du projet</label>
                    <input type="text" id="external_link" name="external_link" class="admin-input" value="<?= $v('external_link') ?>">
                </div>
            </div>

            <!-- Étude de cas -->
            <div class="card" style="margin-bottom: 24px;">
                <div class="card-header" style="margin-bottom: 24px;">
                    <h2 class="card-title"><i data-lucide="file-text"></i><span>Étude de cas</span></h2>
                </div>
                <p class="field-help" style="margin-top:-12px;margin-bottom:18px;">
                    Chaque bloc laissé vide n'apparaît pas sur la page publique : aucune rubrique
                    ne s'affiche sans contenu.
                </p>

                <div class="admin-form-group">
                    <label for="context">Le problème</label>
                    <textarea id="context" name="context" class="admin-textarea" rows="4"><?= $v('context') ?></textarea>
                </div>

                <div class="admin-form-group">
                    <label for="objectives">Les objectifs</label>
                    <textarea id="objectives" name="objectives" class="admin-textarea" rows="4"><?= $v('objectives') ?></textarea>
                    <p class="field-help">Un objectif par ligne.</p>
                </div>

                <div class="admin-form-group">
                    <label for="solution">La solution Digitalium</label>
                    <textarea id="solution" name="solution" class="admin-textarea" rows="5"><?= $v('solution') ?></textarea>
                </div>

                <div class="admin-form-group">
                    <label for="technologies">Technologies utilisées</label>
                    <textarea id="technologies" name="technologies" class="admin-textarea" rows="3"><?= $v('technologies') ?></textarea>
                    <p class="field-help">Une technologie par ligne, ou séparées par « | ».</p>
                </div>

                <div class="admin-form-group">
                    <label for="features">Fonctionnalités principales</label>
                    <textarea id="features" name="features" class="admin-textarea" rows="5"><?= $v('features') ?></textarea>
                    <p class="field-help">Une fonctionnalité par ligne.</p>
                </div>

                <div class="admin-form-group">
                    <label for="impact">Résultats obtenus</label>
                    <textarea id="impact" name="impact" class="admin-textarea" rows="4"><?= $v('impact') ?></textarea>
                    <p class="field-help">Un résultat par ligne. N'inscrire que des résultats réels et vérifiables.</p>
                </div>
            </div>

            <!-- Témoignage -->
            <div class="card" style="margin-bottom: 24px;">
                <div class="card-header" style="margin-bottom: 24px;">
                    <h2 class="card-title"><i data-lucide="quote"></i><span>Témoignage client</span></h2>
                </div>
                <p class="field-help" style="margin-top:-12px;margin-bottom:18px;">
                    Facultatif. Sans citation, aucun bloc témoignage n'apparaît sur la page.
                </p>

                <div class="admin-form-group">
                    <label for="testimonial_quote">Citation</label>
                    <textarea id="testimonial_quote" name="testimonial_quote" class="admin-textarea" rows="3"><?= $v('testimonial_quote') ?></textarea>
                </div>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="admin-form-group">
                        <label for="testimonial_author">Auteur</label>
                        <input type="text" id="testimonial_author" name="testimonial_author" class="admin-input" value="<?= $v('testimonial_author') ?>">
                    </div>
                    <div class="admin-form-group">
                        <label for="testimonial_role">Fonction</label>
                        <input type="text" id="testimonial_role" name="testimonial_role" class="admin-input" value="<?= $v('testimonial_role') ?>">
                    </div>
                </div>
            </div>

            <!-- SEO -->
            <div class="card">
                <div class="card-header" style="margin-bottom: 24px;">
                    <h2 class="card-title"><i data-lucide="search"></i><span>Référencement</span></h2>
                </div>
                <div class="admin-form-group">
                    <label for="meta_title">Meta Title</label>
                    <input type="text" id="meta_title" name="meta_title" class="admin-input" value="<?= $v('meta_title') ?>">
                    <p class="field-help">Vide : le titre du projet est utilisé.</p>
                </div>
                <div class="admin-form-group">
                    <label for="meta_description">Meta Description</label>
                    <textarea id="meta_description" name="meta_description" class="admin-textarea" rows="3"><?= $v('meta_description') ?></textarea>
                    <p class="field-help">Vide : la description courte est utilisée.</p>
                </div>
            </div>
        </div>

        <div>
            <!-- Publication -->
            <div class="card" style="margin-bottom: 24px;">
                <div class="card-header" style="margin-bottom: 24px;">
                    <h2 class="card-title"><i data-lucide="send"></i><span>Publication</span></h2>
                </div>

                <div class="admin-form-group">
                    <label for="status">Statut</label>
                    <select id="status" name="status" class="admin-select admin-input">
                        <option value="draft"     <?= (($p['status'] ?? 'draft') !== 'published') ? 'selected' : '' ?>>Brouillon</option>
                        <option value="published" <?= (($p['status'] ?? '') === 'published') ? 'selected' : '' ?>>Publié</option>
                    </select>
                    <p class="field-help">Un brouillon n'apparaît pas sur le site public.</p>
                </div>

                <div class="admin-form-group">
                    <label for="sort_order">Ordre d'affichage</label>
                    <input type="number" id="sort_order" name="sort_order" class="admin-input" value="<?= htmlspecialchars((string)($p['sort_order'] ?? 0)) ?>">
                </div>

                <div class="admin-form-group" style="display: flex; align-items: center; gap: 10px; margin-top: 15px;">
                    <input type="checkbox" id="is_featured" name="is_featured" value="1" style="width:18px; height:18px; cursor:pointer;" <?= !empty($p['is_featured']) ? 'checked' : '' ?>>
                    <label for="is_featured" style="margin-bottom:0; cursor:pointer;">Projet mis en avant</label>
                </div>
            </div>

            <!-- Médias -->
            <div class="card" style="margin-bottom: 24px;">
                <div class="card-header" style="margin-bottom: 24px;">
                    <h2 class="card-title"><i data-lucide="image"></i><span>Médias</span></h2>
                </div>

                <div class="admin-form-group">
                    <label>Image principale *</label>
                    <?= \App\Helpers\MediaHelper::renderField('main_image', $p['main_image'] ?? '', 'main_image') ?>
                </div>

                <div class="admin-form-group">
                    <label>Logo du projet / client</label>
                    <?= \App\Helpers\MediaHelper::renderField('logo', $p['logo'] ?? '', 'logo') ?>
                </div>

                <div class="admin-form-group">
                    <label for="gallery">Galerie d'images</label>
                    <textarea id="gallery" name="gallery" class="admin-textarea" rows="4"><?= $v('gallery') ?></textarea>
                    <p class="field-help">
                        Un chemin d'image par ligne. Copiez les chemins depuis la
                        <a href="<?= htmlspecialchars(url('/admin/media')) ?>" target="_blank" rel="noopener">Bibliothèque Média</a>.
                    </p>
                </div>
            </div>

            <div class="card" style="display: flex; flex-direction: column; gap: 10px;">
                <button type="submit" class="btn-primary" style="justify-content: center; width: 100%; padding: 12px;">
                    <i data-lucide="save"></i>
                    <span><?= htmlspecialchars($submitLabel) ?></span>
                </button>
                <a href="<?= htmlspecialchars(url('/admin/projects')) ?>" class="btn-secondary" style="justify-content: center; width: 100%; padding: 12px;">Annuler</a>
            </div>
        </div>
    </div>
</form>

<style>
    .projects-grid-form {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 28px;
        align-items: start;
    }
    @media (max-width: 1024px) {
        .projects-grid-form { grid-template-columns: 1fr; }
    }
    .field-help {
        font-size: 0.74rem;
        line-height: 1.45;
        color: var(--text-muted);
        margin: 5px 0 0;
    }
</style>
