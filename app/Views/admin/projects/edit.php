<?php
/**
 * Modification d'une réalisation.
 *
 * Le formulaire vit dans `_form.php`, partagé avec la création. Le sélecteur
 * de média (modale + JS) est fourni par `admin/layout` : le dupliquer ici
 * créait deux modales concurrentes dans la page.
 */
$formAction  = url('/admin/projects/edit/' . $project['id']);
$submitLabel = 'Mettre à jour';
?>

<div class="card-header" style="margin-bottom: 24px; display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap;">
    <h2 class="card-title" style="margin:0;">
        <i data-lucide="edit-3"></i>
        <span>Modifier : <?= htmlspecialchars($project['title']) ?></span>
    </h2>
    <?php if (!empty($project['slug'])): ?>
        <a class="btn-secondary" style="padding:8px 16px; font-size:0.82rem;"
           href="<?= htmlspecialchars(url('/realisations/' . $project['slug'])) ?>" target="_blank" rel="noopener">
            <i data-lucide="external-link" style="width:14px;height:14px;"></i>
            <span>Voir l'étude de cas</span>
        </a>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/_form.php'; ?>
