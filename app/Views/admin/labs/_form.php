<?php
/**
 * Formulaire d'un produit Digitalium Labs — partagé par la création et la
 * modification.
 *
 * Une seule source de champs pour les deux écrans (Règle #1) : c'est la leçon
 * du formulaire des Réalisations, où un champ ajouté d'un seul côté n'était
 * éditable qu'à la création ou qu'à la modification.
 *
 * Variables attendues :
 *   $produit      tableau du produit (vide à la création)
 *   $formAction   URL de soumission
 *   $submitLabel  libellé du bouton
 *   $csrf_token
 */
$p = $produit ?? [];
$v = static fn(string $k, string $default = ''): string => htmlspecialchars((string)($p[$k] ?? $default), ENT_QUOTES, 'UTF-8');
$stageActuel = (string)($p['stage'] ?? 'idee');
?>

<form action="<?= htmlspecialchars($formAction) ?>" method="POST">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

    <div class="labs-grid-form">
        <div>

            <!-- Identité -->
            <div class="card" style="margin-bottom: 24px;">
                <div class="card-header" style="margin-bottom: 24px;">
                    <h2 class="card-title"><i data-lucide="box"></i><span>Identité du produit</span></h2>
                </div>

                <div class="admin-form-group">
                    <label for="name">Nom du produit *</label>
                    <input type="text" id="name" name="name" class="admin-input" value="<?= $v('name') ?>" required>
                </div>

                <div class="admin-form-group">
                    <label for="slug">Slug <small style="color:var(--text-muted);font-weight:400;">(auto-généré si vide)</small></label>
                    <input type="text" id="slug" name="slug" class="admin-input" value="<?= $v('slug') ?>">
                    <p class="field-help">
                        Sert d'ancre stable dans la grille de la page Labs : #produit-<em>slug</em>.
                        Il n'y a pas de fiche produit séparée — chaque produit pointe vers son propre lien.
                    </p>
                </div>

                <div class="admin-form-group">
                    <label for="tagline">Accroche</label>
                    <input type="text" id="tagline" name="tagline" class="admin-input" value="<?= $v('tagline') ?>">
                    <p class="field-help">Une phrase courte affichée sous le nom, sur la carte.</p>
                </div>

                <div class="admin-form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="admin-textarea" rows="5"><?= $v('description') ?></textarea>
                    <p class="field-help">Ce que fait le produit, à qui il s'adresse.</p>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="admin-form-group">
                        <label for="sector">Secteur</label>
                        <input type="text" id="sector" name="sector" class="admin-input" value="<?= $v('sector') ?>" list="labs-secteurs">
                        <datalist id="labs-secteurs">
                            <option value="Intelligence Artificielle"></option>
                            <option value="SaaS B2B"></option>
                            <option value="FinTech &amp; Paiements"></option>
                            <option value="GovTech &amp; CivicTech"></option>
                            <option value="Education Technology"></option>
                            <option value="Business Productivity"></option>
                        </datalist>
                    </div>
                    <div class="admin-form-group">
                        <label for="availability">Disponibilité</label>
                        <input type="text" id="availability" name="availability" class="admin-input" value="<?= $v('availability') ?>">
                        <p class="field-help">Exemple : Sur demande, Web &amp; mobile.</p>
                    </div>
                </div>

                <div class="admin-form-group">
                    <label for="technologies">Technologies</label>
                    <input type="text" id="technologies" name="technologies" class="admin-input" value="<?= $v('technologies') ?>">
                    <p class="field-help">Séparées par une virgule ou une barre verticale. Affichées en étiquettes sur la carte.</p>
                </div>

                <div class="admin-form-group">
                    <label for="external_link">Lien du produit</label>
                    <input type="text" id="external_link" name="external_link" class="admin-input" value="<?= $v('external_link') ?>" placeholder="https://">
                    <p class="field-help">Sans lien, la carte est affichée sans bouton : rien ne renvoie vers une page inexistante.</p>
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
                    <p class="field-help">Vide : le nom du produit est utilisé.</p>
                </div>
                <div class="admin-form-group">
                    <label for="meta_description">Meta Description</label>
                    <textarea id="meta_description" name="meta_description" class="admin-textarea" rows="3"><?= $v('meta_description') ?></textarea>
                </div>
            </div>
        </div>

        <div>
            <!-- Cycle de vie et publication -->
            <div class="card" style="margin-bottom: 24px;">
                <div class="card-header" style="margin-bottom: 24px;">
                    <h2 class="card-title"><i data-lucide="git-branch"></i><span>Cycle de vie</span></h2>
                </div>

                <div class="admin-form-group">
                    <label for="stage">Étape du produit</label>
                    <select id="stage" name="stage" class="admin-select admin-input">
                        <?php foreach (\App\Models\LabProduct::STAGES as $cle => $libelle): ?>
                            <option value="<?= htmlspecialchars($cle) ?>" <?= $stageActuel === $cle ? 'selected' : '' ?>>
                                <?= htmlspecialchars($libelle) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="field-help">Où en est le produit. C'est l'étiquette affichée sur sa carte.</p>
                </div>

                <div class="admin-form-group">
                    <label for="status">Visibilité</label>
                    <select id="status" name="status" class="admin-select admin-input">
                        <option value="draft"     <?= (($p['status'] ?? 'draft') !== 'published') ? 'selected' : '' ?>>Brouillon</option>
                        <option value="published" <?= (($p['status'] ?? '') === 'published') ? 'selected' : '' ?>>Publié</option>
                    </select>
                    <p class="field-help">
                        Distinct de l'étape : un produit peut être « Disponible » et rester hors ligne,
                        ou n'être qu'une « Idée » déjà annoncée.
                    </p>
                </div>

                <div class="admin-form-group">
                    <label for="sort_order">Ordre d'affichage</label>
                    <input type="number" id="sort_order" name="sort_order" class="admin-input" value="<?= htmlspecialchars((string)($p['sort_order'] ?? 0)) ?>">
                    <p class="field-help">Le plus petit nombre apparaît en premier.</p>
                </div>

                <div class="admin-form-group" style="display: flex; align-items: center; gap: 10px; margin-top: 15px;">
                    <input type="checkbox" id="is_featured" name="is_featured" value="1" style="width:18px; height:18px; cursor:pointer;" <?= !empty($p['is_featured']) ? 'checked' : '' ?>>
                    <label for="is_featured" style="margin-bottom:0; cursor:pointer;">Produit mis en avant</label>
                </div>
            </div>

            <!-- Médias -->
            <div class="card" style="margin-bottom: 24px;">
                <div class="card-header" style="margin-bottom: 24px;">
                    <h2 class="card-title"><i data-lucide="image"></i><span>Médias</span></h2>
                </div>

                <div class="admin-form-group">
                    <label>Logo du produit</label>
                    <?= \App\Helpers\MediaHelper::renderField('logo', $p['logo'] ?? '', 'logo') ?>
                </div>

                <div class="admin-form-group">
                    <label>Capture / visuel principal</label>
                    <?= \App\Helpers\MediaHelper::renderField('main_image', $p['main_image'] ?? '', 'main_image') ?>
                    <p class="field-help">Sans visuel, la carte affiche le logo, puis à défaut l'initiale du produit.</p>
                </div>
            </div>

            <div class="card" style="display: flex; flex-direction: column; gap: 10px;">
                <button type="submit" class="btn-primary" style="justify-content: center; width: 100%; padding: 12px;">
                    <i data-lucide="save"></i>
                    <span><?= htmlspecialchars($submitLabel) ?></span>
                </button>
                <a href="<?= htmlspecialchars(url('/admin/labs')) ?>" class="btn-secondary" style="justify-content: center; width: 100%; padding: 12px;">Annuler</a>
            </div>
        </div>
    </div>
</form>

<style>
    .labs-grid-form {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 28px;
        align-items: start;
    }
    @media (max-width: 1024px) {
        .labs-grid-form { grid-template-columns: 1fr; }
    }
    .field-help {
        font-size: 0.74rem;
        line-height: 1.45;
        color: var(--text-muted);
        margin: 5px 0 0;
    }
</style>
