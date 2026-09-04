<?php
/**
 * Abonnés à la newsletter — liste, filtres et export.
 *
 * Un désabonnement ne supprime pas la ligne : il passe le statut à
 * « Désabonné ». Effacer l'adresse ferait perdre la trace du refus et
 * permettrait de réinscrire quelqu'un qui s'est retiré.
 *
 * L'export reprend exactement les filtres affichés — on exporte ce qu'on voit.
 */
use App\Models\Subscriber;

$f = $filtres ?? ['statut' => '', 'q' => ''];

/** Reconstruit l'URL courante en ne changeant qu'un paramètre. */
$lien = static function (array $remplace = []) use ($f): string {
    $p = array_filter(array_merge($f, $remplace), static fn($v) => (string)$v !== '');
    return url('/admin/newsletter') . ($p ? '?' . http_build_query($p) : '');
};
$lienExport = static function () use ($f): string {
    $p = array_filter($f, static fn($v) => (string)$v !== '');
    return url('/admin/newsletter/export') . ($p ? '?' . http_build_query($p) : '');
};
?>

<style>
.nl-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 22px; }
.nl-stat { background: var(--bg-surface); border: 1px solid var(--border); border-radius: 12px; padding: 18px 20px; }
.nl-stat b { display: block; font-size: 1.6rem; font-weight: 800; color: var(--text-main); line-height: 1.1; }
.nl-stat span { font-size: 0.78rem; color: var(--text-muted); }
.nl-chips { display: flex; gap: 7px; flex-wrap: wrap; margin-bottom: 16px; }
.nl-chip {
    padding: 5px 13px; border-radius: 50px; font-size: 0.78rem; text-decoration: none;
    border: 1px solid var(--border); color: var(--text-muted); background: var(--bg-surface);
}
.nl-chip.on { background: var(--primary); border-color: var(--primary); color: #fff; font-weight: 600; }
.nl-filters { display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end; margin-bottom: 18px; }
.nl-filters input {
    padding: 8px 11px; border: 1px solid var(--border); border-radius: 8px;
    background: var(--bg-surface); color: var(--text-main); font-size: 0.84rem; font-family: inherit;
    min-width: 240px;
}
.nl-state {
    display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 50px;
    font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em;
}
.nl-meta { font-size: 0.78rem; color: var(--text-muted); }
@media (max-width: 900px) { .nl-stats { grid-template-columns: repeat(2, 1fr); } }
</style>

<div class="card-header" style="margin-bottom:18px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
    <h2 class="card-title" style="flex:1;">
        <i data-lucide="mail"></i>
        <span>Abonnés à la newsletter</span>
    </h2>
    <a href="<?= htmlspecialchars($lienExport()) ?>" class="btn-secondary" style="padding:8px 15px;font-size:0.82rem;">
        <i data-lucide="download" style="width:15px;height:15px;"></i> Exporter en CSV
    </a>
</div>

<?php $s = $stats ?? ['total' => 0, 'actifs' => 0, 'desabonnes' => 0, 'mois' => 0]; ?>
<div class="nl-stats">
    <div class="nl-stat"><b><?= (int)$s['actifs'] ?></b><span>Abonnés actifs</span></div>
    <div class="nl-stat"><b><?= (int)$s['mois'] ?></b><span>Inscrits sur 30 jours</span></div>
    <div class="nl-stat"><b><?= (int)$s['desabonnes'] ?></b><span>Désabonnés</span></div>
    <div class="nl-stat"><b><?= (int)$s['total'] ?></b><span>Adresses enregistrées</span></div>
</div>

<div class="nl-chips">
    <a class="nl-chip<?= $f['statut'] === '' ? ' on' : '' ?>" href="<?= htmlspecialchars($lien(['statut' => ''])) ?>">Tous</a>
    <?php foreach (Subscriber::STATUTS as $cle => $libelle): ?>
        <a class="nl-chip<?= $f['statut'] === $cle ? ' on' : '' ?>"
           href="<?= htmlspecialchars($lien(['statut' => $cle])) ?>"><?= htmlspecialchars($libelle) ?></a>
    <?php endforeach; ?>
</div>

<form class="nl-filters" method="get" action="<?= url('/admin/newsletter') ?>">
    <input type="hidden" name="statut" value="<?= htmlspecialchars($f['statut'], ENT_QUOTES) ?>">
    <div>
        <label for="nl-q" style="display:block;font-size:0.72rem;color:var(--text-muted);margin-bottom:5px;">Rechercher</label>
        <input type="search" id="nl-q" name="q" value="<?= htmlspecialchars($f['q'], ENT_QUOTES) ?>"
               placeholder="Adresse email ou page d'origine…">
    </div>
    <button type="submit" class="btn-primary" style="padding:9px 18px;font-size:0.84rem;">Filtrer</button>
    <?php if (array_filter($f)): ?>
        <a href="<?= url('/admin/newsletter') ?>" class="btn-secondary" style="padding:9px 16px;font-size:0.84rem;">Réinitialiser</a>
    <?php endif; ?>
</form>

<?php if (empty($abonnes)): ?>
    <div class="card" style="text-align:center;padding:60px 20px;color:var(--text-muted);">
        <i data-lucide="mail" style="width:48px;height:48px;opacity:0.3;margin-bottom:16px;"></i>
        <p style="font-size:1rem;">
            <?= array_filter($f) ? 'Aucun abonné ne correspond à ces filtres.' : 'Aucun abonné pour le moment.' ?>
        </p>
    </div>
<?php else: ?>
    <div class="card">
        <p class="nl-meta" style="padding:0 4px 10px;">
            <?= count($abonnes) ?> adresse<?= count($abonnes) > 1 ? 's' : '' ?> affichée<?= count($abonnes) > 1 ? 's' : '' ?>.
        </p>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Adresse</th>
                    <th>Statut</th>
                    <th>Origine</th>
                    <th>Inscrit le</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($abonnes as $a):
                    $actif = ($a['status'] ?? '') === 'active';
                ?>
                    <tr>
                        <td>
                            <a href="mailto:<?= htmlspecialchars((string)$a['email'], ENT_QUOTES) ?>"><?= htmlspecialchars((string)$a['email']) ?></a>
                        </td>
                        <td>
                            <span class="nl-state" style="<?= $actif
                                ? 'color:#059669;background:rgba(5,150,105,0.12);border:1px solid rgba(5,150,105,0.25);'
                                : 'color:#6b7280;background:rgba(107,114,128,0.10);border:1px solid rgba(107,114,128,0.25);' ?>">
                                <?= htmlspecialchars(Subscriber::STATUTS[$a['status'] ?? ''] ?? ($a['status'] ?? '')) ?>
                            </span>
                        </td>
                        <td class="nl-meta"><?= htmlspecialchars((string)($a['source'] ?? '')) ?: '—' ?></td>
                        <td class="nl-meta">
                            <?= !empty($a['created_at']) ? htmlspecialchars(date('d/m/Y H:i', strtotime($a['created_at']))) : '—' ?>
                        </td>
                        <td style="text-align:right;white-space:nowrap;">
                            <form method="post" action="<?= url('/admin/newsletter/statut/' . (int)$a['id']) ?>" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES) ?>">
                                <input type="hidden" name="statut" value="<?= $actif ? 'unsubscribed' : 'active' ?>">
                                <button type="submit" class="btn-secondary" style="padding:5px 12px;font-size:0.79rem;">
                                    <?= $actif ? 'Désabonner' : 'Réabonner' ?>
                                </button>
                            </form>
                            <form method="post" action="<?= url('/admin/newsletter/delete/' . (int)$a['id']) ?>"
                                  style="display:inline;"
                                  onsubmit="return confirm('Supprimer définitivement cette adresse ? Préférez « Désabonner » pour garder la trace du refus.');">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES) ?>">
                                <button type="submit" style="background:none;border:none;cursor:pointer;color:#b91c1c;padding:5px;">
                                    <i data-lucide="trash-2" style="width:15px;height:15px;"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
