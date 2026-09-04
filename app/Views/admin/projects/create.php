<?php
/**
 * Création d'une réalisation.
 *
 * Le formulaire vit dans `_form.php`, partagé avec la modification, afin que
 * tout champ ajouté soit éditable des deux côtés.
 */
$project     = [];
$formAction  = url('/admin/projects/create');
$submitLabel = 'Créer la réalisation';
?>

<div class="card-header" style="margin-bottom: 24px;">
    <h2 class="card-title" style="margin:0;">
        <i data-lucide="plus-circle"></i>
        <span>Ajouter une réalisation</span>
    </h2>
</div>

<?php require __DIR__ . '/_form.php'; ?>
