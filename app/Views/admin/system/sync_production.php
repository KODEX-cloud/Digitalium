<?php /** @var array $diff @var array $inspect @var array $expectedTables @var array $boot @var array $health @var string $csrf_token */ ?>

<style>
.sync-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:24px; }
.sync-card { background:var(--bg-surface,#fff); border:1px solid var(--border,#e2e8f0); border-radius:12px; padding:20px; }
.sync-card h3 { font-size:.85rem; text-transform:uppercase; letter-spacing:.08em; color:var(--text-muted,#64748b); margin:0 0 14px; }
.badge { display:inline-block; padding:2px 8px; border-radius:4px; font-size:.72rem; font-weight:600; }
.badge-ok    { background:#dcfce7; color:#166534; }
.badge-warn  { background:#fef9c3; color:#854d0e; }
.badge-error { background:#fee2e2; color:#991b1b; }
.badge-skip  { background:#f1f5f9; color:#64748b; }
.diff-item { display:flex; align-items:center; gap:8px; padding:6px 10px; border-radius:6px; margin-bottom:4px; font-size:.82rem; }
.diff-missing { background:#fee2e2; color:#991b1b; }
.diff-ok      { background:#dcfce7; color:#166534; }
.table-list   { display:flex; flex-wrap:wrap; gap:6px; }
.table-tag    { padding:3px 10px; border-radius:4px; font-size:.75rem; font-weight:500; }
.tag-present  { background:#e0f2fe; color:#0369a1; }
.tag-missing  { background:#fee2e2; color:#991b1b; }
.run-btn { width:100%; padding:14px; font-size:1rem; font-weight:700; border-radius:10px; border:none; cursor:pointer; background:linear-gradient(135deg,#2563eb,#1d4ed8); color:#fff; transition:.2s; }
.run-btn:hover { opacity:.9; }
.run-btn:disabled { opacity:.5; cursor:not-allowed; }
#syncLog { background:#0f172a; color:#e2e8f0; border-radius:10px; padding:16px; font-family:monospace; font-size:.8rem; min-height:120px; max-height:400px; overflow-y:auto; white-space:pre-wrap; margin-top:16px; display:none; }
.check-row { display:flex; justify-content:space-between; align-items:center; padding:6px 0; border-bottom:1px solid var(--border,#e2e8f0); font-size:.83rem; }
.check-row:last-child { border:none; }
.incident-box { background:#fff1f2; border:1px solid #fecaca; border-radius:10px; padding:16px; margin-bottom:24px; }
.incident-box h3 { color:#991b1b; margin:0 0 10px; font-size:.9rem; }
.incident-sql { background:#1e293b; color:#f87171; padding:8px 12px; border-radius:6px; font-family:monospace; font-size:.8rem; margin:6px 0; }
.section-title { font-size:1rem; font-weight:700; color:var(--text-main,#1e293b); margin:0 0 16px; display:flex; align-items:center; gap:8px; }
</style>

<!-- INCIDENT ROOT CAUSE -->
<div class="incident-box">
    <h3>⚠ Incident Production 2026-07-04 — Cause Identifiée</h3>
    <div style="font-size:.83rem; color:#7f1d1d; line-height:1.7;">
        <strong>Requête échouée :</strong>
        <div class="incident-sql">SELECT * FROM menus WHERE location = :l LIMIT 1</div>
        <strong>Colonne manquante :</strong> <code>menus.location</code> (VARCHAR 50 DEFAULT 'primary')<br>
        <strong>Modèle :</strong> <code>App\Models\Menu::findByLocation()</code> — <code>Menu.php:14</code><br>
        <strong>Appelé depuis :</strong> <code>app/Views/frontend/layout.php:313</code><br>
        <strong>Cause racine :</strong> <code>master_migration.php</code> utilise <code>CREATE TABLE IF NOT EXISTS</code>. La table <code>menus</code> existait sur Hostinger sans la colonne <code>location</code> → le <code>IF NOT EXISTS</code> a skipé la création → colonne jamais ajoutée.<br>
        <strong>Correction :</strong> <code>ALTER TABLE menus ADD COLUMN location VARCHAR(50) DEFAULT 'primary'</code> — idempotente.
    </div>
</div>

<!-- DIFF SCHEMA -->
<div class="sync-grid">
    <!-- Tables présentes vs attendues -->
    <div class="sync-card">
        <h3>Tables DB — État Actuel</h3>
        <div class="table-list">
            <?php foreach ($expectedTables as $tbl): ?>
                <?php $present = array_key_exists($tbl, $inspect); ?>
                <span class="table-tag <?= $present ? 'tag-present' : 'tag-missing' ?>">
                    <?= $present ? '✓' : '✗' ?> <?= htmlspecialchars($tbl) ?>
                </span>
            <?php endforeach; ?>
        </div>
        <?php if (!empty($diff['tables'])): ?>
            <div style="margin-top:12px; color:#991b1b; font-size:.8rem;">
                ⚠ <?= count($diff['tables']) ?> table(s) manquante(s)
            </div>
        <?php else: ?>
            <div style="margin-top:12px; color:#166534; font-size:.8rem;">✓ Toutes les tables requises sont présentes</div>
        <?php endif; ?>
    </div>

    <!-- Colonnes manquantes -->
    <div class="sync-card">
        <h3>Colonnes Manquantes</h3>
        <?php if (empty($diff['columns'])): ?>
            <div class="diff-item diff-ok">✓ Aucune colonne manquante détectée</div>
        <?php else: ?>
            <?php foreach ($diff['columns'] as $col): ?>
                <div class="diff-item diff-missing">
                    ✗ <code><?= htmlspecialchars($col['table']) ?>.<?= htmlspecialchars($col['column']) ?></code>
                    <span style="margin-left:auto;font-size:.7rem;opacity:.7;"><?= htmlspecialchars($col['definition']) ?></span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- BOOT CHECK + HEALTH -->
<div class="sync-grid">
    <div class="sync-card">
        <h3>Boot Check — <?= $boot['ok'] ? '<span class="badge badge-ok">OK</span>' : '<span class="badge badge-error">ERREUR</span>' ?></h3>
        <?php foreach ($boot['checks'] as $key => $check): ?>
            <div class="check-row">
                <span><?= htmlspecialchars($check['label']) ?></span>
                <span class="badge badge-<?= $check['status'] === 'ok' ? 'ok' : ($check['status'] === 'warning' ? 'warn' : 'error') ?>">
                    <?= htmlspecialchars($check['status']) ?>
                </span>
            </div>
        <?php endforeach; ?>
        <div style="margin-top:10px; font-size:.78rem; color:var(--text-muted);"><?= htmlspecialchars($boot['summary']) ?></div>
    </div>

    <div class="sync-card">
        <h3>Health Check</h3>
        <?php if (empty($health)): ?>
            <div style="color:var(--text-muted); font-size:.83rem;">Health check indisponible</div>
        <?php else: ?>
            <?php foreach ($health as $key => $check): ?>
                <?php $st = is_array($check) ? ($check['status'] ?? 'ok') : 'ok'; ?>
                <div class="check-row">
                    <span><?= htmlspecialchars(is_array($check) ? ($check['label'] ?? $key) : $key) ?></span>
                    <span class="badge badge-<?= $st === 'ok' ? 'ok' : ($st === 'warning' ? 'warn' : 'error') ?>">
                        <?= htmlspecialchars($st) ?>
                    </span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- ACTION -->
<div class="sync-card" style="margin-bottom:24px;">
    <div class="section-title">
        <i data-lucide="zap" style="width:18px;height:18px;color:#2563eb;"></i>
        Lancer la Synchronisation Production
    </div>
    <p style="font-size:.83rem; color:var(--text-muted); margin:0 0 16px;">
        Cette action va : corriger les colonnes manquantes · créer les tables manquantes · régénérer le menu · régénérer le cache · vérifier les routes · sonder HTTP 200 · vérifier les settings, uploads, assets et permissions.
        <strong>Aucune donnée existante n'est modifiée ou supprimée.</strong>
    </p>
    <form id="syncForm">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
        <button type="submit" class="run-btn" id="syncBtn">
            <i data-lucide="play" style="width:16px;height:16px;vertical-align:middle;margin-right:6px;"></i>
            Lancer Sync Production
        </button>
    </form>
    <pre id="syncLog"></pre>
</div>

<script>
document.getElementById('syncForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('syncBtn');
    const log = document.getElementById('syncLog');
    btn.disabled = true;
    btn.textContent = '⏳ Synchronisation en cours...';
    log.style.display = 'block';
    log.textContent = '> Lancement sync production...\n';

    try {
        const fd = new FormData(this);
        const resp = await fetch('/admin/api/system/sync-db', { method: 'POST', body: fd });
        const data = await resp.json();

        log.textContent += `\n> Statut global : ${data.status.toUpperCase()} — ${data.message}\n`;
        log.textContent += `> Durée : ${data.duration_ms}ms\n\n`;

        if (data.steps) {
            Object.entries(data.steps).forEach(([key, step]) => {
                const icon = step.status === 'ok' ? '✓' : step.status === 'error' ? '✗' : '·';
                log.textContent += `  ${icon} [${step.status?.toUpperCase() ?? '?'}] ${step.label ?? key} — ${step.message ?? ''}\n`;

                // Détails DB sync
                if (key === 'db_sync' && step.data?.results) {
                    step.data.results.forEach(r => {
                        const i2 = r.status === 'ok' ? '✓' : r.status === 'error' ? '✗' : '·';
                        log.textContent += `      ${i2} ${r.action}${r.detail ? ' — ' + r.detail : ''}\n`;
                    });
                }

                // HTTP probes
                if (key === 'http' && step.data) {
                    Object.entries(step.data).forEach(([name, probe]) => {
                        if (typeof probe === 'object' && probe.url) {
                            const i2 = probe.status === 'ok' ? '✓' : '✗';
                            log.textContent += `      ${i2} ${name} ${probe.url} → HTTP ${probe.code}\n`;
                        }
                    });
                }
            });
        }

        log.textContent += `\n> ${data.status === 'ok' ? '✅ SYNCHRONISATION RÉUSSIE' : '❌ ERREURS DÉTECTÉES — Voir les détails ci-dessus'}\n`;
        log.textContent += `> Timestamp : ${data.timestamp}\n`;

        btn.textContent = data.status === 'ok' ? '✅ Synchronisation réussie' : '❌ Erreurs détectées';
        btn.style.background = data.status === 'ok' ? '#166534' : '#991b1b';
        btn.disabled = false;

        // Reload après 3s si succès
        if (data.status === 'ok') {
            log.textContent += '\n> Rechargement dans 3 secondes...\n';
            setTimeout(() => location.reload(), 3000);
        }

    } catch (err) {
        log.textContent += `\n> ERREUR RÉSEAU : ${err.message}\n`;
        btn.textContent = '↩ Réessayer';
        btn.disabled = false;
    }

    log.scrollTop = log.scrollHeight;
});
</script>
<?php if (isset($lucideInit) && $lucideInit): ?>
<script>lucide.createIcons();</script>
<?php else: ?>
<script>if(typeof lucide !== 'undefined') lucide.createIcons();</script>
<?php endif; ?>
