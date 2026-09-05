<?php
/**
 * Création d'un collaborateur.
 *
 * Le formulaire vit dans `_form.php`, partagé avec la modification, afin que
 * tout champ ajouté soit éditable des deux côtés.
 */
$membre      = [];
$formAction  = url('/admin/team/create');
$submitLabel = 'Créer le collaborateur';
?>

<div class="card-header" style="margin-bottom: 24px;">
    <h2 class="card-title" style="margin:0;">
        <i data-lucide="user-plus"></i>
        <span>Nouveau collaborateur</span>
    </h2>
</div>

<?php require __DIR__ . '/_form.php'; ?>
