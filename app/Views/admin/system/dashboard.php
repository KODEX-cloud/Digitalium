<?php
/** @var array $health */
/** @var int $score */
/** @var array $state */
/** @var array $reports */
/** @var array $backups */
/** @var array $routes */
/** @var array $migrations */
/** @var string $csrf_token */

$statusColor = function (string $status): string {
    return match ($status) {
        'ok'      => 'var(--success, #22c55e)',
        'warning' => 'var(--warning, #f59e0b)',
        'error'   => 'var(--danger,  #ef4444)',
        default   => 'var(--text-muted)',
    };
};
$statusBadge = function (string $status): string {
    return match ($status) {
        'ok'      => '<span class="badge badge-success">OK</span>',
        'warning' => '<span class="badge badge-warning">WARNING</span>',
        'error'   => '<span class="badge badge-danger">ERROR</span>',
        default   => '<span class="badge">—</span>',
    };
};

$globalStatus = 'ok';
foreach ($health as $check) {
    if (($check['status'] ?? '') === 'error')   { $globalStatus = 'error';   break; }
    if (($check['status'] ?? '') === 'warning') { $globalStatus = 'warning'; }
}
?>

<style>
/* ─── DSM Design System ─── */
.dsm-grid       { display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); gap:1.5rem; margin-bottom:1.5rem; }
.dsm-card       { background:var(--bg-surface,#fff); border:1px solid var(--border,#e5e7eb); border-radius:12px; padding:1.25rem; }
.dsm-card-title { font-size:.75rem; font-weight:600; text-transform:uppercase; letter-spacing:.06em; color:var(--text-muted,#64748b); margin-bottom:.75rem; }
.dsm-score      { font-size:3rem; font-weight:800; line-height:1; }
.health-row     { display:flex; align-items:center; justify-content:space-between; padding:.5rem 0; border-bottom:1px solid var(--border,#e5e7eb); font-size:.875rem; }
.health-row:last-child { border-bottom:none; }
.health-label   { display:flex; align-items:center; gap:.5rem; color:var(--text-main,#0f172a); }
.health-dot     { width:8px; height:8px; border-radius:50%; flex-shrink:0; }
.badge          { display:inline-flex; align-items:center; padding:.2rem .5rem; border-radius:6px; font-size:.7rem; font-weight:600; text-transform:uppercase; }
.badge-success  { background:#d1fae5; color:#065f46; }
.badge-warning  { background:#fef3c7; color:#92400e; }
.badge-danger   { background:#fee2e2; color:#991b1b; }

.btn-dsm        { display:inline-flex; align-items:center; gap:.4rem; padding:.55rem 1.1rem; border-radius:8px; font-size:.8rem; font-weight:600; border:none; cursor:pointer; transition:.2s; white-space:nowrap; }
.btn-primary    { background:var(--primary,#4f46e5); color:#fff; }
.btn-primary:hover { opacity:.88; }
.btn-secondary  { background:var(--bg-surface,#fff); color:var(--text-main); border:1px solid var(--border); }
.btn-secondary:hover { background:var(--border); }
.btn-danger     { background:#fee2e2; color:#991b1b; border:1px solid #fca5a5; }
.btn-warning    { background:#fef3c7; color:#92400e; border:1px solid #fcd34d; }
.btn-success    { background:#d1fae5; color:#065f46; border:1px solid #6ee7b7; }

.deploy-btn     { padding:.75rem 2rem; font-size:.95rem; }

.action-grid    { display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:.75rem; }
.progress-bar   { height:6px; background:var(--border,#e5e7eb); border-radius:99px; overflow:hidden; margin-top:.5rem; }
.progress-fill  { height:100%; border-radius:99px; transition:width .5s; }

.log-area       { background:#0f172a; color:#e2e8f0; border-radius:10px; padding:1rem; font-family:monospace; font-size:.78rem; line-height:1.6; max-height:400px; overflow-y:auto; display:none; margin-top:1rem; }
.log-line       { padding:.15rem 0; border-bottom:1px solid rgba(255,255,255,.04); display:flex; gap:.75rem; }
.log-ok         { color:#4ade80; }
.log-warn       { color:#fbbf24; }
.log-err        { color:#f87171; }
.log-step       { color:#94a3b8; flex-shrink:0; min-width:60px; }

.spinner        { animation:spin .8s linear infinite; }
@keyframes spin { to { transform:rotate(360deg); } }

.mig-table      { width:100%; border-collapse:collapse; font-size:.82rem; }
.mig-table th   { text-align:left; padding:.4rem .75rem; background:var(--border); font-weight:600; }
.mig-table td   { padding:.4rem .75rem; border-bottom:1px solid var(--border); }

#deploy-status  { margin-top:1rem; padding:1rem; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; display:none; }
</style>

<div style="max-width:1200px;margin:0 auto;padding:1.5rem 0;">

  <!-- ─── HEADER ─── -->
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:2rem;flex-wrap:wrap;gap:1rem;">
    <div>
      <h1 style="font-size:1.5rem;font-weight:800;margin:0;color:var(--text-main);">
        <i data-lucide="zap" style="width:20px;height:20px;vertical-align:middle;margin-right:.4rem;color:var(--primary,#4f46e5);"></i>
        Digitalium System Manager
      </h1>
      <p style="margin:.25rem 0 0;color:var(--text-muted);font-size:.85rem;">
        <?= htmlspecialchars($state['last_update'] ?? 'Aucune mise à jour enregistrée') ?>
        — Santé système :
        <strong style="color:<?= $statusColor($globalStatus) ?>">
          <?= strtoupper($globalStatus) ?>
        </strong>
      </p>
    </div>
    <button class="btn-dsm btn-primary deploy-btn" onclick="runDeploy()">
      <i data-lucide="rocket" style="width:16px;height:16px;" id="deploy-icon"></i>
      <span id="deploy-label">Deploy Complet (22 étapes)</span>
    </button>
  </div>

  <!-- ─── SCORE + HEALTH ─── -->
  <div class="dsm-grid" style="grid-template-columns:200px 1fr;">

    <!-- Score -->
    <div class="dsm-card" style="display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;">
      <div class="dsm-card-title">Score Santé</div>
      <div class="dsm-score" style="color:<?= $score >= 8 ? '#22c55e' : ($score >= 5 ? '#f59e0b' : '#ef4444') ?>;">
        <?= $score ?>
        <span style="font-size:1.2rem;color:var(--text-muted);font-weight:400;">/10</span>
      </div>
      <div class="progress-bar" style="width:100%;margin-top:.75rem;">
        <div class="progress-fill" style="width:<?= $score * 10 ?>%;background:<?= $score >= 8 ? '#22c55e' : ($score >= 5 ? '#f59e0b' : '#ef4444') ?>;"></div>
      </div>
      <p style="margin:.5rem 0 0;font-size:.75rem;color:var(--text-muted);"><?= count($health) ?> composants vérifiés</p>
    </div>

    <!-- Health checks -->
    <div class="dsm-card">
      <div class="dsm-card-title">Composants Système</div>
      <?php foreach ($health as $check): ?>
        <?php $s = $check['status'] ?? 'ok'; ?>
        <div class="health-row">
          <div class="health-label">
            <div class="health-dot" style="background:<?= $statusColor($s) ?>;"></div>
            <?= htmlspecialchars($check['label'] ?? 'Inconnu') ?>
          </div>
          <div style="display:flex;align-items:center;gap:.75rem;">
            <span style="font-size:.78rem;color:var(--text-muted);max-width:300px;text-align:right;">
              <?= htmlspecialchars($check['message'] ?? '') ?>
            </span>
            <?= $statusBadge($s) ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- ─── ACTIONS RAPIDES ─── -->
  <div class="dsm-card" style="margin-bottom:1.5rem;">
    <div class="dsm-card-title">Actions Rapides</div>
    <div class="action-grid">
      <button class="btn-dsm btn-secondary" onclick="runAction('cache','clear')">
        <i data-lucide="trash-2" style="width:14px;height:14px;"></i> Vider Cache
      </button>
      <button class="btn-dsm btn-secondary" onclick="runAction('cache','warm')">
        <i data-lucide="flame" style="width:14px;height:14px;"></i> Warm Cache
      </button>
      <button class="btn-dsm btn-secondary" onclick="runAction('migrate')">
        <i data-lucide="database" style="width:14px;height:14px;"></i> Migration SQL
      </button>
      <button class="btn-dsm btn-secondary" onclick="runAction('business-migrate')">
        <i data-lucide="layers" style="width:14px;height:14px;"></i> Migrations Métier
      </button>
      <button class="btn-dsm btn-secondary" onclick="runAction('verify')">
        <i data-lucide="shield-check" style="width:14px;height:14px;"></i> Vérif. Intégrité
      </button>
      <button class="btn-dsm btn-secondary" onclick="runAction('audit')">
        <i data-lucide="lock" style="width:14px;height:14px;"></i> Audit Sécurité
      </button>
      <button class="btn-dsm btn-secondary" onclick="runAction('seo')">
        <i data-lucide="search" style="width:14px;height:14px;"></i> Audit SEO
      </button>
      <button class="btn-dsm btn-secondary" onclick="runAction('assets')">
        <i data-lucide="image" style="width:14px;height:14px;"></i> Audit Assets
      </button>
      <button class="btn-dsm btn-secondary" onclick="runAction('routes')">
        <i data-lucide="route" style="width:14px;height:14px;"></i> Scan Routes
      </button>
      <button class="btn-dsm btn-secondary" onclick="runAction('uploads')">
        <i data-lucide="upload-cloud" style="width:14px;height:14px;"></i> Audit Uploads
      </button>
      <button class="btn-dsm btn-warning" onclick="runAction('rebuild')">
        <i data-lucide="refresh-cw" style="width:14px;height:14px;"></i> Rebuild
      </button>
      <button class="btn-dsm btn-danger" onclick="runAction('backup')">
        <i data-lucide="archive" style="width:14px;height:14px;"></i> Backup DB
      </button>
    </div>
    <div id="action-result" style="display:none;margin-top:1rem;padding:.75rem 1rem;border-radius:8px;font-size:.83rem;"></div>
  </div>

  <!-- ─── DEPLOY LOG ─── -->
  <div id="deploy-status"></div>
  <div class="log-area" id="deploy-log"></div>

  <!-- ─── MIGRATIONS MÉTIER ─── -->
  <div class="dsm-card" style="margin-bottom:1.5rem;">
    <div class="dsm-card-title">Migrations Métier Disponibles</div>
    <table class="mig-table">
      <thead><tr><th>Nom</th><th>Description</th><th>Action</th></tr></thead>
      <tbody>
        <?php foreach ($migrations as $mig): ?>
        <tr>
          <td><code><?= htmlspecialchars($mig['name']) ?></code></td>
          <td><?= htmlspecialchars($mig['description']) ?></td>
          <td>
            <button class="btn-dsm btn-secondary" style="padding:.25rem .75rem;font-size:.75rem;"
              onclick="runMigration('<?= htmlspecialchars($mig['name']) ?>')">
              Exécuter
            </button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- ─── BACKUPS + REPORTS ─── -->
  <div class="dsm-grid">
    <div class="dsm-card">
      <div class="dsm-card-title">Sauvegardes Récentes</div>
      <?php if (empty($backups)): ?>
        <p style="color:var(--text-muted);font-size:.83rem;">Aucun backup disponible.</p>
      <?php else: ?>
        <?php foreach (array_slice($backups, 0, 5) as $b): ?>
          <div class="health-row">
            <span style="font-size:.8rem;font-family:monospace;"><?= htmlspecialchars(basename($b['path'] ?? $b)) ?></span>
            <span style="font-size:.75rem;color:var(--text-muted);">
              <?= isset($b['size']) ? round($b['size'] / 1024 / 1024, 2) . ' MB' : '' ?>
            </span>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <div class="dsm-card">
      <div class="dsm-card-title">Rapports Récents</div>
      <?php $rList = $reports['data']['reports'] ?? (is_array($reports) && isset($reports[0]) ? $reports : []); ?>
      <?php if (empty($rList)): ?>
        <p style="color:var(--text-muted);font-size:.83rem;">Aucun rapport disponible.</p>
      <?php else: ?>
        <?php foreach (array_slice($rList, 0, 5) as $r): ?>
          <div class="health-row">
            <span style="font-size:.8rem;font-family:monospace;"><?= htmlspecialchars(basename($r['path'] ?? $r)) ?></span>
            <span style="font-size:.75rem;color:var(--text-muted);">
              <?= isset($r['date']) ? $r['date'] : '' ?>
            </span>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

</div><!-- /max-width -->

<script>
const CSRF = <?= json_encode($csrf_token) ?>;

async function post(url, body = {}) {
  body._csrf = CSRF;
  const fd = new FormData();
  for (const [k, v] of Object.entries(body)) fd.append(k, v);
  const r = await fetch(url, { method: 'POST', body: fd });
  return r.json();
}

function showActionResult(data) {
  const el = document.getElementById('action-result');
  const colors = { ok: '#d1fae5', warning: '#fef3c7', error: '#fee2e2' };
  el.style.display = 'block';
  el.style.background = colors[data.status] || '#f1f5f9';
  el.innerHTML = `<strong>${data.label || data.status?.toUpperCase()}</strong> — ${escapeHtml(data.message || JSON.stringify(data))}`;
}

function escapeHtml(s) {
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

async function runAction(action, extra = null) {
  const el = document.getElementById('action-result');
  el.style.display = 'block';
  el.style.background = '#f1f5f9';
  el.innerHTML = '<i data-lucide="loader-2" class="spinner" style="width:14px;height:14px;"></i> En cours…';
  lucide.createIcons();

  const body = {};
  if (extra) body.action = extra;

  try {
    const data = await post(`<?= url('/admin/system/') ?>${action}`, body);
    showActionResult(data);
  } catch (e) {
    el.style.background = '#fee2e2';
    el.innerHTML = '<strong>Erreur réseau</strong> — ' + e.message;
  }
}

async function runMigration(name) {
  const el = document.getElementById('action-result');
  el.style.display = 'block';
  el.style.background = '#f1f5f9';
  el.innerHTML = `<i data-lucide="loader-2" class="spinner" style="width:14px;height:14px;"></i> Migration <strong>${name}</strong> en cours…`;
  lucide.createIcons();

  try {
    const data = await post('<?= url('/admin/system/business-migrate') ?>', { migration: name });
    showActionResult(data);
  } catch (e) {
    el.style.background = '#fee2e2';
    el.innerHTML = '<strong>Erreur réseau</strong> — ' + e.message;
  }
}

async function runDeploy() {
  const btn    = document.querySelector('.deploy-btn');
  const icon   = document.getElementById('deploy-icon');
  const label  = document.getElementById('deploy-label');
  const log    = document.getElementById('deploy-log');
  const status = document.getElementById('deploy-status');

  btn.disabled = true;
  icon.setAttribute('data-lucide', 'loader-2');
  icon.classList.add('spinner');
  label.textContent = 'Deploy en cours…';
  lucide.createIcons();

  log.style.display = 'block';
  log.innerHTML = '';
  status.style.display = 'none';

  const appendLog = (line) => {
    const s = line.status || 'ok';
    const cls = s === 'ok' ? 'log-ok' : (s === 'error' ? 'log-err' : 'log-warn');
    log.innerHTML += `<div class="log-line ${cls}">
      <span class="log-step">[${escapeHtml(s.toUpperCase())}]</span>
      <span>${escapeHtml(line.label || '')} — ${escapeHtml(line.message || '')}</span>
    </div>`;
    log.scrollTop = log.scrollHeight;
  };

  try {
    const data = await post('<?= url('/admin/system/deploy') ?>');

    // Display each step
    if (data.steps) {
      data.steps.forEach(step => appendLog(step));
    }

    // Final status banner
    const colors = { ok: '#d1fae5', warning: '#fef3c7', error: '#fee2e2' };
    const borders = { ok: '#bbf7d0', warning: '#fcd34d', error: '#fca5a5' };
    const s = data.status || 'ok';
    status.style.display = 'block';
    status.style.background = colors[s] || '#f1f5f9';
    status.style.border = `1px solid ${borders[s] || '#e5e7eb'}`;
    status.innerHTML = `
      <strong>${s.toUpperCase()} — Deploy terminé</strong><br>
      ${escapeHtml(data.message || '')}
      — <em>${data.duration_ms}ms</em>
    `;

  } catch (e) {
    appendLog({ status: 'error', label: 'Réseau', message: e.message });
  } finally {
    btn.disabled = false;
    icon.setAttribute('data-lucide', 'rocket');
    icon.classList.remove('spinner');
    label.textContent = 'Deploy Complet (22 étapes)';
    lucide.createIcons();
  }
}
</script>
