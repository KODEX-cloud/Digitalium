<div style="display: flex; align-items: center; gap: 16px; margin-bottom: 28px;">
    <a href="<?= url('/admin/messages') ?>" class="btn-secondary" style="padding: 8px 14px;">
        <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
        <span>Retour</span>
    </a>
    <h1 style="font-size: 1.3rem; font-weight: 800; margin: 0;">Message de <?= htmlspecialchars($message['nom']) ?></h1>
</div>

<div style="display: grid; grid-template-columns: 1fr 320px; gap: 24px; align-items: start;">

    <div class="card">
        <div style="padding-bottom: 20px; border-bottom: 1px solid var(--border); margin-bottom: 20px;">
            <h2 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 6px;">
                <?= htmlspecialchars($message['sujet'] ?: '(Sans sujet)') ?>
            </h2>
            <p style="font-size: 0.85rem; color: var(--text-muted);">
                Reçu le <?= date('d/m/Y à H:i', strtotime($message['created_at'])) ?>
                <?php if (!empty($message['ip_address'])): ?>
                    · IP : <?= htmlspecialchars($message['ip_address']) ?>
                <?php endif; ?>
            </p>
        </div>
        <div style="font-size: 0.97rem; line-height: 1.85; color: var(--text-main); white-space: pre-wrap;">
            <?= htmlspecialchars($message['message']) ?>
        </div>

        <div style="margin-top: 28px; padding-top: 20px; border-top: 1px solid var(--border);">
            <h3 style="font-size: 0.85rem; font-weight: 700; color: var(--text-muted); margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.08em;">Répondre par email</h3>
            <a href="mailto:<?= htmlspecialchars($message['email']) ?>?subject=Re: <?= rawurlencode($message['sujet'] ?: 'Votre message') ?>"
               class="btn-primary" style="display: inline-flex;">
                <i data-lucide="mail" style="width: 16px; height: 16px;"></i>
                <span>Répondre à <?= htmlspecialchars($message['email']) ?></span>
            </a>
        </div>
    </div>

    <div>
        <div class="card" style="margin-bottom: 16px;">
            <h3 style="font-size: 0.88rem; font-weight: 700; margin-bottom: 16px; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted);">Expéditeur</h3>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <div>
                    <div style="font-size: 0.78rem; color: var(--text-muted); margin-bottom: 2px;">Nom</div>
                    <div style="font-weight: 600;"><?= htmlspecialchars($message['nom']) ?></div>
                </div>
                <div>
                    <div style="font-size: 0.78rem; color: var(--text-muted); margin-bottom: 2px;">Email</div>
                    <a href="mailto:<?= htmlspecialchars($message['email']) ?>" style="color: var(--primary); font-weight: 500;">
                        <?= htmlspecialchars($message['email']) ?>
                    </a>
                </div>
                <?php if (!empty($message['telephone'])): ?>
                <div>
                    <div style="font-size: 0.78rem; color: var(--text-muted); margin-bottom: 2px;">Téléphone</div>
                    <a href="tel:<?= htmlspecialchars($message['telephone']) ?>" style="font-weight: 500;">
                        <?= htmlspecialchars($message['telephone']) ?>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <h3 style="font-size: 0.88rem; font-weight: 700; margin-bottom: 16px; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted);">Actions</h3>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <?php if ($message['statut'] !== 'archivé'): ?>
                <form method="POST" action="<?= url('/admin/messages/archive/' . $message['id']) ?>">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    <button type="submit" class="btn-secondary" style="width: 100%; justify-content: center;">
                        <i data-lucide="archive" style="width: 16px; height: 16px;"></i>
                        <span>Archiver</span>
                    </button>
                </form>
                <?php endif; ?>
                <form method="POST" action="<?= url('/admin/messages/delete/' . $message['id']) ?>" onsubmit="return confirm('Supprimer définitivement ce message ?')">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    <button type="submit" class="btn-secondary" style="width: 100%; justify-content: center; color: var(--danger); border-color: var(--danger);">
                        <i data-lucide="trash-2" style="width: 16px; height: 16px;"></i>
                        <span>Supprimer</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
