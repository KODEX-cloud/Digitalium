<?php
/**
 * Fiche d'une demande — informations, statut, notes internes et historique.
 *
 * Les champs absents ne sont pas affichés : une fiche pleine de tirets se lit
 * moins bien qu'une fiche courte. Les demandes venues de l'ancien formulaire
 * simple n'ont que nom, email et message — elles restent lisibles ici.
 */
use App\Models\Message;

$m  = $message;
$id = (int)($m['id'] ?? 0);

$champs = [
    'Entreprise'        => $m['entreprise'] ?? null,
    'Secteur'           => $m['secteur'] ?? null,
    'Pays'              => $m['pays'] ?? null,
    'Téléphone'         => $m['telephone'] ?? null,
    'Type de besoin'    => $m['besoin'] ?? null,
    'Objectif principal'=> $m['objectif'] ?? null,
    'Urgence'           => $m['urgence'] ?? null,
    'Budget indicatif'  => $m['budget'] ?? null,
    'Source'            => $m['source'] ?? null,
    'Adresse IP'        => $m['ip_address'] ?? null,
];
$champs = array_filter($champs, fn($v) => trim((string)$v) !== '');
?>

<style>
.lead-wrap { display: grid; grid-template-columns: minmax(0, 1fr) 320px; gap: 22px; align-items: start; }
.lead-dl { display: grid; grid-template-columns: minmax(130px, auto) 1fr; gap: 9px 18px; margin: 0; }
.lead-dl dt { font-size: 0.78rem; color: var(--text-muted); font-weight: 600; }
.lead-dl dd { margin: 0; font-size: 0.9rem; color: var(--text-main); overflow-wrap: anywhere; }
.lead-msg {
    white-space: pre-wrap; line-height: 1.7; font-size: 0.94rem; color: var(--text-main);
    background: var(--bg-surface-alt, var(--bg-surface)); border: 1px solid var(--border);
    border-radius: 10px; padding: 18px 20px; margin-top: 8px;
}
.lead-hist { list-style: none; padding: 0; margin: 0; }
.lead-hist li { padding: 11px 0; border-bottom: 1px solid var(--border); font-size: 0.84rem; }
.lead-hist li:last-child { border-bottom: 0; }
.lead-hist time { display: block; font-size: 0.72rem; color: var(--text-muted); margin-bottom: 3px; }
.lead-hist .note { white-space: pre-wrap; color: var(--text-main); }
.lead-side .card { margin-bottom: 18px; }
.lead-side select, .lead-side textarea {
    width: 100%; box-sizing: border-box; padding: 9px 11px; font-family: inherit;
    border: 1px solid var(--border); border-radius: 8px;
    background: var(--bg-surface); color: var(--text-main); font-size: 0.85rem;
}
.lead-side textarea { min-height: 90px; resize: vertical; }
@media (max-width: 1050px) { .lead-wrap { grid-template-columns: 1fr; } }
</style>

<div class="card-header" style="margin-bottom: 18px; display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
    <a href="<?= url('/admin/messages') ?>" class="btn-secondary" style="padding: 7px 13px; font-size: 0.82rem;">
        <i data-lucide="arrow-left" style="width:15px;height:15px;"></i> Retour
    </a>
    <h2 class="card-title" style="flex:1; margin:0;">
        <span><?= htmlspecialchars((string)($m['nom'] ?? 'Demande')) ?></span>
    </h2>
    <span class="lead-meta" style="font-size: 0.8rem; color: var(--text-muted);">
        Reçue le <?= htmlspecialchars(date('d/m/Y à H:i', strtotime((string)($m['created_at'] ?? 'now')))) ?>
    </span>
</div>

<div class="lead-wrap">

    <div>
        <div class="card" style="margin-bottom: 18px;">
            <h3 style="font-size: 0.95rem; margin: 0 0 16px;">Coordonnées et contexte</h3>
            <dl class="lead-dl">
                <dt>Email</dt>
                <dd><a href="mailto:<?= htmlspecialchars((string)($m['email'] ?? ''), ENT_QUOTES) ?>"><?= htmlspecialchars((string)($m['email'] ?? '')) ?></a></dd>
                <?php foreach ($champs as $libelle => $valeur): ?>
                    <dt><?= htmlspecialchars($libelle) ?></dt>
                    <dd><?= htmlspecialchars((string)$valeur) ?></dd>
                <?php endforeach; ?>
            </dl>
        </div>

        <div class="card" style="margin-bottom: 18px;">
            <h3 style="font-size: 0.95rem; margin: 0 0 4px;">Description du besoin</h3>
            <div class="lead-msg"><?= htmlspecialchars((string)($m['message'] ?? '')) ?></div>
        </div>

        <?php if (!empty($m['piece_jointe'])): ?>
            <div class="card" style="margin-bottom: 18px;">
                <h3 style="font-size: 0.95rem; margin: 0 0 12px;">Pièce jointe</h3>
                <a href="<?= url('/admin/messages/' . $id . '/piece-jointe') ?>" class="btn-primary" style="padding: 9px 17px; font-size: 0.85rem;">
                    <i data-lucide="download" style="width:15px;height:15px;"></i>
                    <?= htmlspecialchars((string)($m['piece_jointe_nom'] ?? 'Télécharger le document')) ?>
                </a>
                <p style="font-size: 0.76rem; color: var(--text-muted); margin: 10px 0 0;">
                    Le fichier est stocké hors de la racine web : il n'a pas d'adresse publique et ne se télécharge que depuis ici.
                </p>
            </div>
        <?php endif; ?>

        <div class="card">
            <h3 style="font-size: 0.95rem; margin: 0 0 12px;">Historique</h3>
            <?php if (empty($historique)): ?>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0;">Aucun événement enregistré pour cette demande.</p>
            <?php else: ?>
                <ul class="lead-hist">
                    <?php foreach (array_reverse($historique) as $ev): ?>
                        <li>
                            <time><?= htmlspecialchars(date('d/m/Y H:i', strtotime((string)($ev['created_at'] ?? 'now')))) ?>
                                <?= !empty($ev['auteur']) ? ' · ' . htmlspecialchars((string)$ev['auteur']) : '' ?>
                            </time>
                            <?php if (($ev['type'] ?? '') === 'statut'): ?>
                                Statut : <strong><?= htmlspecialchars(Message::libelleStatut($ev['ancien'] ?? '')) ?></strong>
                                → <strong><?= htmlspecialchars(Message::libelleStatut($ev['nouveau'] ?? '')) ?></strong>
                            <?php elseif (($ev['type'] ?? '') === 'note'): ?>
                                <span class="note"><?= htmlspecialchars((string)($ev['note'] ?? '')) ?></span>
                            <?php else: ?>
                                Demande reçue.
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <aside class="lead-side">
        <div class="card">
            <h3 style="font-size: 0.95rem; margin: 0 0 12px;">Statut</h3>
            <form method="post" action="<?= url('/admin/messages/statut/' . $id) ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES) ?>">
                <select name="statut" aria-label="Statut de la demande">
                    <?php foreach (Message::STATUTS as $cle => $libelle): ?>
                        <option value="<?= htmlspecialchars($cle, ENT_QUOTES) ?>"
                            <?= ($m['statut'] ?? '') === $cle ? 'selected' : '' ?>>
                            <?= htmlspecialchars($libelle) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn-primary" style="width: 100%; margin-top: 10px; padding: 10px; font-size: 0.85rem;">
                    Mettre à jour
                </button>
            </form>
        </div>

        <div class="card">
            <h3 style="font-size: 0.95rem; margin: 0 0 12px;">Note interne</h3>
            <form method="post" action="<?= url('/admin/messages/note/' . $id) ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES) ?>">
                <textarea name="note" placeholder="Ce qui a été dit, décidé, ou reste à faire…" maxlength="2000"></textarea>
                <button type="submit" class="btn-secondary" style="width: 100%; margin-top: 10px; padding: 10px; font-size: 0.85rem;">
                    Ajouter la note
                </button>
            </form>
            <p style="font-size: 0.74rem; color: var(--text-muted); margin: 10px 0 0;">
                Les notes ne sont visibles que dans l'administration ; elles apparaissent dans l'historique ci-contre.
            </p>
        </div>

        <div class="card">
            <h3 style="font-size: 0.95rem; margin: 0 0 12px;">Actions</h3>
            <a href="mailto:<?= htmlspecialchars((string)($m['email'] ?? ''), ENT_QUOTES) ?>"
               class="btn-secondary" style="display:block; text-align:center; padding: 9px; font-size: 0.84rem; margin-bottom: 9px;">
                Répondre par email
            </a>
            <form method="post" action="<?= url('/admin/messages/delete/' . $id) ?>"
                  onsubmit="return confirm('Supprimer définitivement cette demande ? La pièce jointe sera également supprimée.');">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES) ?>">
                <button type="submit" class="btn-secondary" style="width: 100%; padding: 9px; font-size: 0.84rem; color: #b91c1c;">
                    Supprimer la demande
                </button>
            </form>
        </div>
    </aside>

</div>
