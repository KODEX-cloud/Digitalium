<?php
/**
 * Tags du blog — renommer, supprimer, voir l'usage réel.
 *
 * Les tags naissent de la saisie libre dans la fiche d'un article : cette page
 * ne sert donc pas à en créer, mais à ranger ceux qui existent. Le compteur
 * porte sur les articles PUBLIÉS — un tag à 0 est soit orphelin, soit
 * uniquement posé sur des brouillons.
 */
?>

<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;">
    <div>
        <a href="<?= url('/admin/blog') ?>" style="font-size:0.82rem;color:var(--text-muted);text-decoration:none;display:inline-flex;align-items:center;gap:4px;margin-bottom:8px;">
            <i data-lucide="arrow-left" style="width:14px;height:14px;"></i> Blog
        </a>
        <h1 class="page-title">Tags du blog</h1>
    </div>
    <a href="<?= url('/admin/blog/categories') ?>" class="btn-secondary" style="padding:9px 16px;border-radius:10px;font-size:0.84rem;text-decoration:none;">
        Gérer les catégories
    </a>
</div>

<p style="font-size:0.86rem;color:var(--text-muted);margin:0 0 20px;max-width:720px;">
    Les tags sont créés depuis la fiche d'un article, dans le champ « Tags ». Ils se gèrent ici :
    renommer un tag met à jour tous les articles qui le portent ; le supprimer le retire des articles
    sans toucher à leur contenu.
</p>

<div class="card" style="background:var(--bg-surface);border:1px solid var(--border);border-radius:16px;overflow:hidden;">
    <?php if (empty($tags)): ?>
        <div style="text-align:center;padding:56px 24px;color:var(--text-muted);">
            <i data-lucide="tags" style="width:42px;height:42px;opacity:0.3;margin-bottom:14px;"></i>
            <p style="margin:0;font-size:1rem;">Aucun tag pour le moment.</p>
            <p style="margin:8px 0 0;font-size:0.85rem;">
                Ajoutez-en depuis le champ « Tags » d'un article.
            </p>
        </div>
    <?php else: ?>
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr>
                    <th style="padding:10px 16px;text-align:left;font-size:0.75rem;text-transform:uppercase;letter-spacing:.07em;color:var(--text-muted);border-bottom:1px solid var(--border);">Nom</th>
                    <th style="padding:10px 16px;text-align:left;font-size:0.75rem;text-transform:uppercase;letter-spacing:.07em;color:var(--text-muted);border-bottom:1px solid var(--border);">Slug</th>
                    <th style="padding:10px 16px;text-align:center;font-size:0.75rem;text-transform:uppercase;letter-spacing:.07em;color:var(--text-muted);border-bottom:1px solid var(--border);width:110px;">Articles</th>
                    <th style="padding:10px 16px;border-bottom:1px solid var(--border);width:70px;"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tags as $t): ?>
                    <tr style="border-bottom:1px solid var(--border);">
                        <td style="padding:10px 16px;">
                            <form method="POST" action="<?= url('/admin/blog/tags/rename/' . (int)$t['id']) ?>"
                                  style="display:flex;gap:8px;align-items:center;">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES) ?>">
                                <input type="text" name="name" value="<?= htmlspecialchars($t['name'], ENT_QUOTES) ?>"
                                       class="field-input" style="max-width:260px;padding:7px 10px;font-size:0.86rem;" required>
                                <button type="submit" class="btn-secondary" style="padding:7px 13px;font-size:0.8rem;border-radius:8px;">
                                    Renommer
                                </button>
                            </form>
                        </td>
                        <td style="padding:10px 16px;font-size:0.82rem;color:var(--text-muted);"><?= htmlspecialchars($t['slug']) ?></td>
                        <td style="padding:10px 16px;text-align:center;">
                            <a href="<?= url('/insights') . '?q=' . urlencode($t['name']) ?>" target="_blank"
                               style="background:color-mix(in srgb, var(--primary) 10%, transparent);color:var(--primary);padding:2px 11px;border-radius:50px;font-size:0.78rem;font-weight:700;text-decoration:none;">
                                <?= (int)($t['post_count'] ?? 0) ?>
                            </a>
                        </td>
                        <td style="padding:10px 16px;">
                            <form method="POST" action="<?= url('/admin/blog/tags/delete/' . (int)$t['id']) ?>"
                                  onsubmit="return confirm('Supprimer le tag « <?= htmlspecialchars(addslashes($t['name']), ENT_QUOTES) ?> » ? Les articles restent intacts.');">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES) ?>">
                                <button type="submit" style="background:none;border:none;cursor:pointer;color:#b91c1c;padding:4px;border-radius:6px;display:flex;align-items:center;">
                                    <i data-lucide="trash-2" style="width:15px;height:15px;"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script>lucide.createIcons();</script>
