<style>
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 10px;
    border-radius: 50px;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}
.badge-pending  { background: rgba(245,158,11,0.15); color: #d97706; border: 1px solid rgba(245,158,11,0.3); }
.badge-approved { background: rgba(16,185,129,0.15); color: #059669; border: 1px solid rgba(16,185,129,0.3); }
.badge-rejected { background: rgba(239,68,68,0.15);  color: #dc2626; border: 1px solid rgba(239,68,68,0.3); }
.comment-row { transition: background 0.2s; }
.comment-row:hover { background: rgba(99,102,241,0.03) !important; }
.comment-body { font-size: 0.88rem; color: var(--text-muted); line-height: 1.6; margin-top: 6px; max-width: 480px; }
.filter-tabs { display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap; }
.filter-tab  { padding: 6px 16px; border-radius: 50px; font-size: 0.78rem; font-weight: 600; border: 1px solid var(--border); color: var(--text-muted); text-decoration: none; transition: all 0.2s; }
.filter-tab:hover, .filter-tab.active { background: var(--primary); color: #fff; border-color: var(--primary); }
</style>

<div style="max-width: 1100px; margin: 0 auto;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--text-main); font-family: var(--font-heading); margin: 0;">
                Modération des commentaires
            </h1>
            <p style="color: var(--text-muted); font-size: 0.88rem; margin-top: 4px;">
                <?= $pendingCount ?> commentaire<?= $pendingCount > 1 ? 's' : '' ?> en attente de validation
            </p>
        </div>
        <a href="<?= url('/admin/blog') ?>" style="display: flex; align-items: center; gap: 6px; color: var(--text-muted); font-size: 0.85rem; text-decoration: none;">
            <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
            Retour aux articles
        </a>
    </div>

    <!-- Filter Tabs -->
    <div class="filter-tabs">
        <a href="<?= url('/admin/blog/comments') ?>" class="filter-tab <?= $filterStatus === '' ? 'active' : '' ?>">Tous</a>
        <a href="<?= url('/admin/blog/comments?status=pending') ?>" class="filter-tab <?= $filterStatus === 'pending' ? 'active' : '' ?>">
            En attente <?= $pendingCount > 0 ? "({$pendingCount})" : '' ?>
        </a>
        <a href="<?= url('/admin/blog/comments?status=approved') ?>" class="filter-tab <?= $filterStatus === 'approved' ? 'active' : '' ?>">Approuvés</a>
        <a href="<?= url('/admin/blog/comments?status=rejected') ?>" class="filter-tab <?= $filterStatus === 'rejected' ? 'active' : '' ?>">Rejetés</a>
    </div>

    <?php if (empty($comments)): ?>
    <div class="card" style="text-align: center; padding: 60px 24px; color: var(--text-muted);">
        <i data-lucide="message-circle-off" style="width: 48px; height: 48px; opacity: 0.3; display: block; margin: 0 auto 16px;"></i>
        <p style="font-size: 1.05rem;">Aucun commentaire<?= $filterStatus ? ' avec ce statut' : '' ?>.</p>
    </div>
    <?php else: ?>
    <div class="card" style="padding: 0; overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: rgba(99,102,241,0.06); border-bottom: 1px solid var(--border);">
                    <th style="padding: 12px 20px; text-align: left; font-size: 0.72rem; letter-spacing: 0.1em; text-transform: uppercase; color: var(--text-muted); font-weight: 600;">Auteur</th>
                    <th style="padding: 12px 20px; text-align: left; font-size: 0.72rem; letter-spacing: 0.1em; text-transform: uppercase; color: var(--text-muted); font-weight: 600;">Commentaire</th>
                    <th style="padding: 12px 20px; text-align: left; font-size: 0.72rem; letter-spacing: 0.1em; text-transform: uppercase; color: var(--text-muted); font-weight: 600;">Article</th>
                    <th style="padding: 12px 20px; text-align: left; font-size: 0.72rem; letter-spacing: 0.1em; text-transform: uppercase; color: var(--text-muted); font-weight: 600;">Statut</th>
                    <th style="padding: 12px 20px; text-align: left; font-size: 0.72rem; letter-spacing: 0.1em; text-transform: uppercase; color: var(--text-muted); font-weight: 600;">Date</th>
                    <th style="padding: 12px 20px; text-align: right; font-size: 0.72rem; letter-spacing: 0.1em; text-transform: uppercase; color: var(--text-muted); font-weight: 600;">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($comments as $comment): ?>
                <tr class="comment-row" style="border-bottom: 1px solid var(--border);">
                    <td style="padding: 16px 20px; vertical-align: top;">
                        <div style="font-weight: 700; color: var(--text-main); font-size: 0.88rem;"><?= htmlspecialchars($comment['author_name']) ?></div>
                        <?php if (!empty($comment['author_email'])): ?>
                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">
                            <a href="mailto:<?= htmlspecialchars($comment['author_email']) ?>" style="color: inherit;"><?= htmlspecialchars($comment['author_email']) ?></a>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 16px 20px; vertical-align: top;">
                        <div class="comment-body"><?= htmlspecialchars(mb_substr($comment['content'], 0, 180)) ?><?= mb_strlen($comment['content']) > 180 ? '…' : '' ?></div>
                    </td>
                    <td style="padding: 16px 20px; vertical-align: top;">
                        <?php if (!empty($comment['post_title'])): ?>
                        <span style="font-size: 0.82rem; color: var(--text-muted);"><?= htmlspecialchars(mb_substr($comment['post_title'], 0, 40)) ?></span>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 16px 20px; vertical-align: top;">
                        <?php
                        $statusClass = match($comment['status']) {
                            'approved' => 'badge-approved',
                            'rejected' => 'badge-rejected',
                            default    => 'badge-pending',
                        };
                        $statusLabel = match($comment['status']) {
                            'approved' => 'Approuvé',
                            'rejected' => 'Rejeté',
                            default    => 'En attente',
                        };
                        ?>
                        <span class="status-badge <?= $statusClass ?>"><?= $statusLabel ?></span>
                    </td>
                    <td style="padding: 16px 20px; vertical-align: top; font-size: 0.78rem; color: var(--text-muted); white-space: nowrap;">
                        <?= !empty($comment['created_at']) ? date('d/m/Y H:i', strtotime($comment['created_at'])) : '—' ?>
                    </td>
                    <td style="padding: 16px 20px; vertical-align: top; text-align: right;">
                        <div style="display: flex; gap: 6px; justify-content: flex-end; flex-wrap: wrap;">
                            <?php if ($comment['status'] !== 'approved'): ?>
                            <form method="POST" action="<?= url('/admin/blog/comments/approve/' . $comment['id']) ?>">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                <button type="submit" style="display:inline-flex;align-items:center;gap:5px;padding:6px 12px;background:rgba(16,185,129,0.12);color:#059669;border:1px solid rgba(16,185,129,0.3);border-radius:8px;font-size:0.75rem;font-weight:600;cursor:pointer;" title="Approuver">
                                    <i data-lucide="check" style="width:13px;height:13px;"></i> Approuver
                                </button>
                            </form>
                            <?php endif; ?>
                            <?php if ($comment['status'] !== 'rejected'): ?>
                            <form method="POST" action="<?= url('/admin/blog/comments/reject/' . $comment['id']) ?>">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                <button type="submit" style="display:inline-flex;align-items:center;gap:5px;padding:6px 12px;background:rgba(245,158,11,0.12);color:#d97706;border:1px solid rgba(245,158,11,0.3);border-radius:8px;font-size:0.75rem;font-weight:600;cursor:pointer;" title="Rejeter">
                                    <i data-lucide="x" style="width:13px;height:13px;"></i> Rejeter
                                </button>
                            </form>
                            <?php endif; ?>
                            <form method="POST" action="<?= url('/admin/blog/comments/delete/' . $comment['id']) ?>" onsubmit="return confirm('Supprimer définitivement ce commentaire ?')">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                <button type="submit" style="display:inline-flex;align-items:center;gap:5px;padding:6px 12px;background:rgba(239,68,68,0.1);color:#dc2626;border:1px solid rgba(239,68,68,0.25);border-radius:8px;font-size:0.75rem;font-weight:600;cursor:pointer;" title="Supprimer">
                                    <i data-lucide="trash-2" style="width:13px;height:13px;"></i>
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
</div>
