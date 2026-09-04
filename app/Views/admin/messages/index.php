<?php
/**
 * Demandes reçues — liste, filtres, recherche et export.
 *
 * Les filtres sont des liens GET : une vue filtrée garde une URL partageable
 * et se met en favori. L'export reprend exactement les filtres affichés — on
 * exporte ce qu'on voit, jamais autre chose.
 */
use App\Models\Message;

$f = $filtres ?? ['statut' => '', 'secteur' => '', 'besoin' => '', 'q' => ''];

/** Reconstruit l'URL courante en changeant un seul paramètre. */
$lien = static function (array $remplace = []) use ($f): string {
    $p = array_filter(array_merge($f, $remplace), fn($v) => (string)$v !== '');
    return url('/admin/messages') . ($p ? '?' . http_build_query($p) : '');
};

$couleurs = [
    'nouveau'       => ['#2563eb', 'rgba(37,99,235,0.10)'],
    'a_qualifier'   => ['#7c3aed', 'rgba(124,58,237,0.10)'],
    'contacte'      => ['#0891b2', 'rgba(8,145,178,0.10)'],
    'en_discussion' => ['#d97706', 'rgba(217,119,6,0.10)'],
    'proposition'   => ['#c2410c', 'rgba(194,65,12,0.10)'],
    'gagne'         => ['#059669', 'rgba(5,150,105,0.12)'],
    'perdu'         => ['#b91c1c', 'rgba(185,28,28,0.10)'],
    'archive'       => ['#6b7280', 'rgba(107,114,128,0.10)'],
];
?>

<style>
.lead-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 22px; }
.lead-stat { background: var(--bg-surface); border: 1px solid var(--border); border-radius: 12px; padding: 18px 20px; }
.lead-stat b { display: block; font-size: 1.6rem; font-weight: 800; color: var(--text-main); line-height: 1.1; }
.lead-stat span { font-size: 0.78rem; color: var(--text-muted); }
.lead-filters { display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end; margin-bottom: 18px; }
.lead-filters label { display: block; font-size: 0.72rem; color: var(--text-muted); margin-bottom: 5px; }
.lead-filters select, .lead-filters input {
    padding: 8px 11px; border: 1px solid var(--border); border-radius: 8px;
    background: var(--bg-surface); color: var(--text-main); font-size: 0.84rem; font-family: inherit;
}
.lead-chips { display: flex; gap: 7px; flex-wrap: wrap; margin-bottom: 16px; }
.lead-chip {
    padding: 5px 13px; border-radius: 50px; font-size: 0.78rem; text-decoration: none;
    border: 1px solid var(--border); color: var(--text-muted); background: var(--bg-surface);
}
.lead-chip.on { background: var(--primary); border-color: var(--primary); color: #fff; font-weight: 600; }
.msg-status {
    display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 50px;
    font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em;
    white-space: nowrap;
}
.msg-row-new { background: rgba(37,99,235,0.03); }
.lead-meta { font-size: 0.76rem; color: var(--text-muted); }
@media (max-width: 900px) { .lead-stats { grid-template-columns: repeat(2, 1fr); } }
</style>

<div class="card-header" style="margin-bottom: 18px; display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
    <h2 class="card-title" style="flex:1;">
        <i data-lucide="inbox"></i>
        <span>Demandes reçues</span>
        <?php if (($newCount ?? 0) > 0): ?>
            <span style="background: #2563eb; color: white; font-size: 0.72rem; padding: 2px 8px; border-radius: 50px; margin-left: 8px; font-weight: 700;"><?= (int)$newCount ?> nouveau<?= $newCount > 1 ? 'x' : '' ?></span>
        <?php endif; ?>
    </h2>
    <a href="<?= htmlspecialchars(str_replace('/admin/messages', '/admin/messages/export', $lien())) ?>"
       class="btn-secondary" style="padding: 8px 15px; font-size: 0.82rem;">
        <i data-lucide="download" style="width:15px;height:15px;"></i> Exporter en CSV
    </a>
</div>

<?php $s = $stats ?? ['nouveaux' => 0, 'en_cours' => 0, 'semaine' => 0, 'gagnes' => 0]; ?>
<div class="lead-stats">
    <div class="lead-stat"><b><?= (int)$s['nouveaux'] ?></b><span>Nouveaux leads</span></div>
    <div class="lead-stat"><b><?= (int)$s['en_cours'] ?></b><span>En cours de traitement</span></div>
    <div class="lead-stat"><b><?= (int)$s['semaine'] ?></b><span>Demandes des 7 derniers jours</span></div>
    <div class="lead-stat"><b><?= (int)$s['gagnes'] ?></b><span>Projets gagnés</span></div>
</div>

<div class="lead-chips">
    <a class="lead-chip<?= $f['statut'] === '' ? ' on' : '' ?>" href="<?= htmlspecialchars($lien(['statut' => ''])) ?>">Tous</a>
    <?php foreach (Message::STATUTS as $cle => $libelle): ?>
        <a class="lead-chip<?= $f['statut'] === $cle ? ' on' : '' ?>"
           href="<?= htmlspecialchars($lien(['statut' => $cle])) ?>"><?= htmlspecialchars($libelle) ?></a>
    <?php endforeach; ?>
</div>

<form class="lead-filters" method="get" action="<?= url('/admin/messages') ?>">
    <input type="hidden" name="statut" value="<?= htmlspecialchars($f['statut'], ENT_QUOTES) ?>">
    <div>
        <label for="lf-q">Rechercher</label>
        <input type="search" id="lf-q" name="q" value="<?= htmlspecialchars($f['q'], ENT_QUOTES) ?>"
               placeholder="Nom, email, entreprise…" style="min-width: 230px;">
    </div>
    <div>
        <label for="lf-secteur">Secteur</label>
        <select id="lf-secteur" name="secteur">
            <option value="">Tous</option>
            <?php foreach (($secteurs ?? []) as $sec): ?>
                <option value="<?= htmlspecialchars($sec, ENT_QUOTES) ?>" <?= $f['secteur'] === $sec ? 'selected' : '' ?>>
                    <?= htmlspecialchars($sec) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label for="lf-besoin">Type de besoin</label>
        <select id="lf-besoin" name="besoin">
            <option value="">Tous</option>
            <?php foreach (($besoins ?? []) as $b): ?>
                <option value="<?= htmlspecialchars($b, ENT_QUOTES) ?>" <?= $f['besoin'] === $b ? 'selected' : '' ?>>
                    <?= htmlspecialchars($b) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="btn-primary" style="padding: 9px 18px; font-size: 0.84rem;">Filtrer</button>
    <?php if (array_filter($f)): ?>
        <a href="<?= url('/admin/messages') ?>" class="btn-secondary" style="padding: 9px 16px; font-size: 0.84rem;">Réinitialiser</a>
    <?php endif; ?>
</form>

<?php if (empty($messages)): ?>
    <div class="card" style="text-align: center; padding: 60px 20px; color: var(--text-muted);">
        <i data-lucide="inbox" style="width: 48px; height: 48px; opacity: 0.3; margin-bottom: 16px;"></i>
        <p style="font-size: 1rem;">
            <?= array_filter($f) ? 'Aucune demande ne correspond à ces filtres.' : 'Aucune demande pour le moment.' ?>
        </p>
    </div>
<?php else: ?>
    <div class="card">
        <p class="lead-meta" style="padding: 0 4px 10px;"><?= count($messages) ?> demande<?= count($messages) > 1 ? 's' : '' ?> affichée<?= count($messages) > 1 ? 's' : '' ?>.</p>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Statut</th>
                    <th>Prospect</th>
                    <th>Besoin</th>
                    <th>Secteur</th>
                    <th>Date</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $msg):
                    $st = (string)($msg['statut'] ?? 'nouveau');
                    [$couleur, $fond] = $couleurs[$st] ?? ['#6b7280', 'rgba(107,114,128,0.10)'];
                ?>
                    <tr class="<?= $st === 'nouveau' ? 'msg-row-new' : '' ?>">
                        <td>
                            <span class="msg-status" style="color: <?= $couleur ?>; background: <?= $fond ?>; border: 1px solid <?= $couleur ?>33;">
                                <?= htmlspecialchars(Message::libelleStatut($st)) ?>
                            </span>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars((string)($msg['nom'] ?? '')) ?></strong>
                            <?php if (!empty($msg['entreprise'])): ?>
                                <div class="lead-meta"><?= htmlspecialchars((string)$msg['entreprise']) ?></div>
                            <?php endif; ?>
                            <div class="lead-meta"><?= htmlspecialchars((string)($msg['email'] ?? '')) ?></div>
                        </td>
                        <td><?= htmlspecialchars((string)($msg['besoin'] ?? $msg['sujet'] ?? '—')) ?></td>
                        <td><?= htmlspecialchars((string)($msg['secteur'] ?? '—')) ?></td>
                        <td class="lead-meta"><?= htmlspecialchars(date('d/m/Y H:i', strtotime((string)($msg['created_at'] ?? 'now')))) ?></td>
                        <td style="text-align: right; white-space: nowrap;">
                            <?php if (!empty($msg['piece_jointe'])): ?>
                                <a href="<?= url('/admin/messages/' . (int)$msg['id'] . '/piece-jointe') ?>"
                                   class="btn-secondary" style="padding: 5px 9px;" title="Pièce jointe">
                                    <i data-lucide="paperclip" style="width:14px;height:14px;"></i>
                                </a>
                            <?php endif; ?>
                            <a href="<?= url('/admin/messages/' . (int)$msg['id']) ?>"
                               class="btn-primary" style="padding: 6px 13px; font-size: 0.8rem;">Ouvrir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
