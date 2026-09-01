<style>
.msg-status { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 50px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; }
.msg-nouveau  { background: rgba(37,99,235,0.1); color: #2563eb; border: 1px solid rgba(37,99,235,0.25); }
.msg-lu       { background: rgba(16,185,129,0.08); color: #10b981; border: 1px solid rgba(16,185,129,0.2); }
.msg-archive  { background: rgba(107,114,128,0.08); color: #6b7280; border: 1px solid rgba(107,114,128,0.2); }
.msg-row-new  { background: rgba(37,99,235,0.03); }
</style>

<div class="card-header" style="margin-bottom: 20px; display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
    <h2 class="card-title" style="flex:1;">
        <i data-lucide="inbox"></i>
        <span>Messages reçus</span>
        <?php if ($newCount > 0): ?>
            <span style="background: #2563eb; color: white; font-size: 0.72rem; padding: 2px 8px; border-radius: 50px; margin-left: 8px; font-weight: 700;"><?= $newCount ?> nouveau<?= $newCount > 1 ? 'x' : '' ?></span>
        <?php endif; ?>
    </h2>
    <div style="display: flex; gap: 8px;">
        <?php foreach (['all' => 'Tous', 'nouveau' => 'Nouveaux', 'lu' => 'Lus', 'archivé' => 'Archivés'] as $key => $label): ?>
        <a href="<?= url('/admin/messages?filter=' . $key) ?>"
           class="btn-secondary"
           style="padding: 6px 14px; font-size: 0.8rem; <?= $filter === $key ? 'background: var(--primary); color: white;' : '' ?>">
            <?= $label ?>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<?php if (empty($messages)): ?>
<div class="card" style="text-align: center; padding: 60px 20px; color: var(--text-muted);">
    <i data-lucide="inbox" style="width: 48px; height: 48px; opacity: 0.3; margin-bottom: 16px;"></i>
    <p style="font-size: 1rem;">Aucun message dans cette catégorie.</p>
</div>
<?php else: ?>
<div class="card">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Statut</th>
                <th>Expéditeur</th>
                <th>Sujet</th>
                <th>Date</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($messages as $msg): ?>
            <tr class="<?= $msg['statut'] === 'nouveau' ? 'msg-row-new' : '' ?>">
                <td>
                    <span class="msg-status msg-<?= $msg['statut'] === 'archivé' ? 'archive' : htmlspecialchars($msg['statut']) ?>">
                        <?= htmlspecialchars($msg['statut']) ?>
                    </span>
                </td>
                <td>
                    <div style="font-weight: <?= $msg['statut'] === 'nouveau' ? '700' : '500' ?>;">
                        <?= htmlspecialchars($msg['nom']) ?>
                    </div>
                    <div style="font-size: 0.82rem; color: var(--text-muted);">
                        <?= htmlspecialchars($msg['email']) ?>
                    </div>
                </td>
                <td style="max-width: 280px;">
                    <div style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 0.9rem;">
                        <?= htmlspecialchars($msg['sujet'] ?: '(Sans sujet)') ?>
                    </div>
                    <div style="font-size: 0.78rem; color: var(--text-muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 260px;">
                        <?= htmlspecialchars(mb_substr($msg['message'], 0, 80)) ?>...
                    </div>
                </td>
                <td style="font-size: 0.82rem; color: var(--text-muted); white-space: nowrap;">
                    <?= date('d/m/Y H:i', strtotime($msg['created_at'])) ?>
                </td>
                <td style="text-align: right;">
                    <div style="display: flex; gap: 6px; justify-content: flex-end;">
                        <a href="<?= url('/admin/messages/' . $msg['id']) ?>" class="btn-primary" style="padding: 5px 12px; font-size: 0.78rem;">
                            <i data-lucide="eye" style="width: 13px; height: 13px;"></i>
                            <span>Lire</span>
                        </a>
                        <?php if ($msg['statut'] !== 'archivé'): ?>
                        <form method="POST" action="<?= url('/admin/messages/archive/' . $msg['id']) ?>" style="display:inline;">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                            <button type="submit" class="btn-secondary" style="padding: 5px 10px; font-size: 0.78rem;" title="Archiver">
                                <i data-lucide="archive" style="width: 13px; height: 13px;"></i>
                            </button>
                        </form>
                        <?php endif; ?>
                        <form method="POST" action="<?= url('/admin/messages/delete/' . $msg['id']) ?>" style="display:inline;" onsubmit="return confirm('Supprimer ce message définitivement ?')">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                            <button type="submit" class="btn-secondary" style="padding: 5px 10px; font-size: 0.78rem; color: var(--danger);" title="Supprimer">
                                <i data-lucide="trash-2" style="width: 13px; height: 13px;"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
