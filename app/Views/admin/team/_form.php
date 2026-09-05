<?php
/**
 * Formulaire d'un collaborateur — partagé par la création et la modification.
 *
 * Une seule source de champs pour les deux écrans (Règle #1) : c'est la leçon
 * du formulaire des Réalisations, où un champ ajouté d'un seul côté n'était
 * éditable qu'à la création ou qu'à la modification.
 *
 * Variables attendues :
 *   $membre        tableau du collaborateur (vide à la création)
 *   $departements  liste des pôles d'expertise
 *   $formAction    URL de soumission
 *   $submitLabel   libellé du bouton
 *   $csrf_token
 */
$m = $membre ?? [];
$v = static fn(string $k, string $default = ''): string => htmlspecialchars((string)($m[$k] ?? $default), ENT_QUOTES, 'UTF-8');
$depActuel = (string)($m['department'] ?? '');
?>

<form action="<?= htmlspecialchars($formAction) ?>" method="POST">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

    <div class="team-grid-form">
        <div>

            <!-- Identité -->
            <div class="card" style="margin-bottom: 24px;">
                <div class="card-header" style="margin-bottom: 24px;">
                    <h2 class="card-title"><i data-lucide="user"></i><span>Identité</span></h2>
                </div>

                <div class="admin-form-group">
                    <label for="name">Nom et prénom *</label>
                    <input type="text" id="name" name="name" class="admin-input" value="<?= $v('name') ?>" required>
                </div>

                <div class="admin-form-group">
                    <label for="role">Fonction</label>
                    <input type="text" id="role" name="role" class="admin-input" value="<?= $v('role') ?>">
                    <p class="field-help">Intitulé affiché sous le nom, sur la carte.</p>
                </div>

                <div class="admin-form-group">
                    <label for="bio">Courte biographie</label>
                    <textarea id="bio" name="bio" class="admin-textarea" rows="4"><?= $v('bio') ?></textarea>
                    <p class="field-help">Deux à trois phrases. Laisser vide masque simplement ce texte.</p>
                </div>
            </div>

            <!-- Contact -->
            <div class="card" style="margin-bottom: 24px;">
                <div class="card-header" style="margin-bottom: 24px;">
                    <h2 class="card-title"><i data-lucide="link"></i><span>Liens</span></h2>
                </div>

                <div class="admin-form-group">
                    <label for="linkedin">Profil LinkedIn</label>
                    <input type="text" id="linkedin" name="linkedin" class="admin-input" value="<?= $v('linkedin') ?>" placeholder="https://www.linkedin.com/in/...">
                    <p class="field-help">
                        Facultatif. Une adresse saisie sans « https:// » est complétée à l'enregistrement :
                        sans cela le lien pointerait vers ce site au lieu de LinkedIn.
                    </p>
                </div>

                <div class="admin-form-group">
                    <label for="email">Adresse e-mail</label>
                    <input type="email" id="email" name="email" class="admin-input" value="<?= $v('email') ?>">
                    <p class="field-help">
                        Usage interne uniquement — elle n'est pas affichée sur le site public,
                        pour ne pas l'exposer aux robots de collecte.
                    </p>
                </div>
            </div>
        </div>

        <div>
            <!-- Publication -->
            <div class="card" style="margin-bottom: 24px;">
                <div class="card-header" style="margin-bottom: 24px;">
                    <h2 class="card-title"><i data-lucide="settings"></i><span>Publication</span></h2>
                </div>

                <div class="admin-form-group">
                    <label for="department">Pôle d'expertise</label>
                    <select id="department" name="department" class="admin-select admin-input">
                        <option value="">— Aucun —</option>
                        <?php foreach (($departements ?? []) as $cle => $libelle): ?>
                            <option value="<?= htmlspecialchars($cle) ?>" <?= $depActuel === $cle ? 'selected' : '' ?>>
                                <?= htmlspecialchars($libelle) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="field-help">
                        Les mêmes pôles que ceux affichés sur /a-propos tant qu'aucun collaborateur
                        n'est publié.
                    </p>
                </div>

                <div class="admin-form-group">
                    <label for="status">Visibilité</label>
                    <select id="status" name="status" class="admin-select admin-input">
                        <option value="draft"     <?= (($m['status'] ?? 'draft') !== 'published') ? 'selected' : '' ?>>Brouillon</option>
                        <option value="published" <?= (($m['status'] ?? '') === 'published') ? 'selected' : '' ?>>Publié</option>
                    </select>
                    <p class="field-help">
                        Tant qu'AUCUN collaborateur n'est publié, la page /a-propos affiche les pôles
                        d'expertise. Le premier collaborateur publié fait basculer la section.
                    </p>
                </div>

                <div class="admin-form-group">
                    <label for="sort_order">Ordre d'affichage</label>
                    <input type="number" id="sort_order" name="sort_order" class="admin-input" value="<?= htmlspecialchars((string)($m['sort_order'] ?? 0)) ?>">
                    <p class="field-help">Le plus petit nombre apparaît en premier.</p>
                </div>
            </div>

            <!-- Photo -->
            <div class="card" style="margin-bottom: 24px;">
                <div class="card-header" style="margin-bottom: 24px;">
                    <h2 class="card-title"><i data-lucide="image"></i><span>Photo</span></h2>
                </div>

                <div class="admin-form-group">
                    <label>Portrait</label>
                    <?= \App\Helpers\MediaHelper::renderField('photo', $m['photo'] ?? '', 'photo') ?>
                    <p class="field-help">Sans portrait, la carte affiche les initiales — jamais un trou.</p>
                </div>
            </div>

            <div class="card" style="display: flex; flex-direction: column; gap: 10px;">
                <button type="submit" class="btn-primary" style="justify-content: center; width: 100%; padding: 12px;">
                    <i data-lucide="save"></i>
                    <span><?= htmlspecialchars($submitLabel) ?></span>
                </button>
                <a href="<?= htmlspecialchars(url('/admin/team')) ?>" class="btn-secondary" style="justify-content: center; width: 100%; padding: 12px;">Annuler</a>
            </div>
        </div>
    </div>
</form>

<style>
    .team-grid-form {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 28px;
        align-items: start;
    }
    @media (max-width: 1024px) {
        .team-grid-form { grid-template-columns: 1fr; }
    }
    .field-help {
        font-size: 0.74rem;
        line-height: 1.45;
        color: var(--text-muted);
        margin: 5px 0 0;
    }
</style>
