<?php
/**
 * Création d'un produit Digitalium Labs.
 *
 * Le formulaire vit dans `_form.php`, partagé avec la modification, afin que
 * tout champ ajouté soit éditable des deux côtés.
 */
$produit     = [];
$formAction  = url('/admin/labs/create');
$submitLabel = 'Créer le produit';
?>

<div class="card-header" style="margin-bottom: 24px;">
    <h2 class="card-title" style="margin:0;">
        <i data-lucide="plus-circle"></i>
        <span>Nouveau produit Labs</span>
    </h2>
</div>

<?php require __DIR__ . '/_form.php'; ?>
