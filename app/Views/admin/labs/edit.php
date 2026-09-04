<?php
/**
 * Modification d'un produit Digitalium Labs.
 *
 * Le formulaire vit dans `_form.php`, partagé avec la création. Le sélecteur de
 * média (modale + JS) est fourni par `admin/layout` : le dupliquer ici créerait
 * deux modales concurrentes dans la page.
 *
 * Le lien « Voir sur le site » pointe vers l'ancre du produit dans la grille de
 * /labs, pas vers une fiche : il n'existe pas de page publique par produit.
 */
$formAction  = url('/admin/labs/edit/' . $produit['id']);
$submitLabel = 'Mettre à jour';
?>

<div class="card-header" style="margin-bottom: 24px; display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap;">
    <h2 class="card-title" style="margin:0;">
        <i data-lucide="edit-3"></i>
        <span>Modifier : <?= htmlspecialchars($produit['name']) ?></span>
    </h2>

    <div style="display:flex; gap:10px; flex-wrap:wrap;">
        <?php if (!empty($produit['slug']) && ($produit['status'] ?? '') === 'published'): ?>
            <a class="btn-secondary" style="padding:8px 16px; font-size:0.82rem;"
               href="<?= htmlspecialchars(url('/labs#produit-' . $produit['slug'])) ?>" target="_blank" rel="noopener">
                <i data-lucide="external-link" style="width:14px;height:14px;"></i>
                <span>Voir sur le site</span>
            </a>
        <?php endif; ?>

        <form action="<?= htmlspecialchars(url('/admin/labs/delete/' . $produit['id'])) ?>" method="POST"
              onsubmit="return confirm('Supprimer définitivement ce produit ?');" style="margin:0;">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <button type="submit" class="btn-danger" style="padding:8px 16px; font-size:0.82rem; display:inline-flex; align-items:center; gap:6px;">
                <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                <span>Supprimer</span>
            </button>
        </form>
    </div>
</div>

<?php require __DIR__ . '/_form.php'; ?>
