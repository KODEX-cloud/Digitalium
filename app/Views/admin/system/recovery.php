<?php
/** @var array  $diagnostics */
/** @var bool   $maintenance */
/** @var ?array $lastLog */
/** @var array  $rollbacks */
/** @var int    $errors */
/** @var int    $warnings */
/** @var int    $oks */
/** @var int    $total */
/** @var string $csrf_token */

$globalStatus = $errors > 0 ? 'error' : ($warnings > 0 ? 'warning' : 'ok');
$statusLabel  = $errors > 0 ? 'DÉGRADÉ' : ($warnings > 0 ? 'ATTENTION' : 'OPÉRATIONNEL');
$statusPalette = [
    'ok'      => ['bg' => '#d1fae5', 'color' => '#065f46', 'border' => '#6ee7b7', 'dot' => '#22c55e'],
    'warning' => ['bg' => '#fef3c7', 'color' => '#92400e', 'border' => '#fcd34d', 'dot' => '#f59e0b'],
    'error'   => ['bg' => '#fee2e2', 'color' => '#991b1b', 'border' => '#fca5a5', 'dot' => '#ef4444'],
];
$sp = $statusPalette[$globalStatus];
?>

<style>
/* ─── Recovery Center ───────────────────────────────────────────────────── */
.rc-wrap        { max-width:1440px;margin:0 auto;padding:1.5rem 0; }
.rc-header      { display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.75rem;flex-wrap:wrap;gap:1rem; }
.rc-title       { font-size:1.6rem;font-weight:800;color:var(--text-main);margin:0;display:flex;align-items:center;gap:.6rem; }
.rc-subtitle    { font-size:.82rem;color:var(--text-muted);margin:.25rem 0 0; }
.pill           { display:inline-flex;align-items:center;gap:.35rem;padding:.3rem .85rem;border-radius:50px;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em; }
.dot-blink      { width:8px;height:8px;border-radius:50%;animation:blink 1.2s ease-in-out infinite; }
@keyframes blink { 0%,100%{opacity:1;}50%{opacity:.3;} }

/* Grid */
.rc-grid        { display:grid;grid-template-columns:380px 1fr 280px;gap:1.25rem;align-items:start;margin-bottom:1.25rem; }

/* Cards */
.rc-card        { background:var(--bg-surface);border:1px solid var(--border);border-radius:14px;padding:1.25rem; }
.rc-card-title  { font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);margin-bottom:.85rem;display:flex;align-items:center;gap:.4rem; }

/* Diagnostic list */
.diag-list      { display:flex;flex-direction:column;gap:.3rem; }
.diag-row       { display:flex;align-items:center;justify-content:space-between;padding:.42rem .6rem;border-radius:8px;gap:.5rem;transition:.15s; }
.diag-row:hover { background:var(--bg-base); }
.diag-label     { font-size:.8rem;font-weight:600;color:var(--text-main);flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap; }
.diag-msg       { font-size:.72rem;color:var(--text-muted);text-align:right;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap; }
.diag-badge     { display:inline-flex;align-items:center;gap:.2rem;padding:.18rem .5rem;border-radius:5px;font-size:.65rem;font-weight:700;flex-shrink:0; }
.db-ok          { background:#d1fae5;color:#065f46; }
.db-warn        { background:#fef3c7;color:#92400e; }
.db-err         { background:#fee2e2;color:#991b1b; }

/* Control panel */
.ctrl-panel     { display:flex;flex-direction:column;gap:1rem; }
.prog-wrap      { margin:.5rem 0; }
.prog-label     { display:flex;justify-content:space-between;font-size:.75rem;color:var(--text-muted);margin-bottom:.35rem; }
.prog-bar       { height:8px;background:var(--border);border-radius:99px;overflow:hidden; }
.prog-fill      { height:100%;border-radius:99px;transition:width .4s ease,background .4s ease; }

.step-list      { display:flex;flex-direction:column;gap:.2rem;max-height:220px;overflow-y:auto; }
.step-item      { display:flex;align-items:center;gap:.6rem;padding:.3rem .5rem;border-radius:6px;font-size:.78rem; }
.step-item.pending  { color:var(--text-muted); }
.step-item.running  { color:#4f46e5;font-weight:600; animation:pulse 1s infinite; }
.step-item.ok       { color:#065f46;background:#d1fae5; }
.step-item.warning  { color:#92400e;background:#fef3c7; }
.step-item.error    { color:#991b1b;background:#fee2e2; }
@keyframes pulse { 0%,100%{opacity:1;}50%{opacity:.6;} }

.step-icon      { font-size:.9rem;flex-shrink:0;width:18px;text-align:center; }
.step-name      { flex:1;min-width:0; }
.step-ms        { font-size:.68rem;color:var(--text-muted);font-family:monospace;flex-shrink:0; }

/* Recovery button */
.recover-btn    { width:100%;padding:1rem;font-size:.95rem;font-weight:800;border-radius:12px;border:none;cursor:pointer;background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;display:flex;align-items:center;justify-content:center;gap:.6rem;box-shadow:0 4px 14px rgba(79,70,229,.35);transition:.2s;letter-spacing:.02em; }
.recover-btn:hover:not(:disabled) { transform:translateY(-1px);box-shadow:0 6px 20px rgba(79,70,229,.45); }
.recover-btn:disabled { opacity:.6;cursor:not-allowed;transform:none; }

/* Maintenance toggle */
.maint-wrap     { padding:1rem;background:var(--bg-base);border-radius:10px; }
.maint-label    { font-size:.82rem;font-weight:600;margin-bottom:.5rem;color:var(--text-main); }
.toggle-switch  { position:relative;display:inline-block;width:52px;height:28px; }
.toggle-switch input { opacity:0;width:0;height:0; }
.toggle-slider  { position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;background:#cbd5e1;border-radius:99px;transition:.3s; }
.toggle-slider:before { position:absolute;content:"";height:20px;width:20px;left:4px;bottom:4px;background:#fff;border-radius:50%;transition:.3s;box-shadow:0 2px 4px rgba(0,0,0,.15); }
input:checked + .toggle-slider { background:#ef4444; }
input:checked + .toggle-slider:before { transform:translateX(24px); }
.maint-row      { display:flex;align-items:center;justify-content:space-between;gap:.75rem; }

/* Log terminal */
.log-panel      { background:#0b1120;border-radius:14px;border:1px solid #1e293b;overflow:hidden;margin-bottom:1.25rem; }
.log-topbar     { background:#0f172a;padding:.55rem 1rem;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #1e293b; }
.log-title-bar  { font-size:.72rem;font-weight:700;color:#94a3b8;font-family:monospace; }
.log-body       { padding:1rem;font-family:'Fira Code',monospace,monospace;font-size:.76rem;line-height:1.65;color:#e2e8f0;min-height:180px;max-height:380px;overflow-y:auto; }
.log-line       { display:grid;grid-template-columns:80px 1fr auto;gap:.6rem;padding:.15rem 0;border-bottom:1px solid rgba(255,255,255,.03);align-items:start; }
.log-line:last-child { border-bottom:none; }
.log-ok-c   { color:#4ade80; } .log-warn-c { color:#fbbf24; }
.log-err-c  { color:#f87171; } .log-info-c { color:#93c5fd; }
.log-msg-c  { color:#cbd5e1; } .log-ms-c   { color:#475569;font-size:.68rem;text-align:right;font-family:monospace; }
.log-placeholder { color:#334155;text-align:center;padding:2.5rem 0;font-size:.8rem; }

/* Report panel */
.report-panel   { display:none; }
.report-grid    { display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:.75rem;margin-top:1rem; }
.report-card    { background:var(--bg-base);border-radius:10px;padding:.85rem;border:1px solid var(--border); }
.report-stat    { font-size:1.6rem;font-weight:800;line-height:1;margin-bottom:.25rem; }
.report-lbl     { font-size:.68rem;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted); }

/* Smoke results */
.smoke-grid     { display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:.5rem;margin-top:.75rem; }
.smoke-item     { padding:.5rem .75rem;border-radius:8px;font-size:.8rem;display:flex;align-items:center;gap:.4rem;font-weight:600; }
.smoke-ok       { background:#d1fae5;color:#065f46; }
.smoke-fail     { background:#fee2e2;color:#991b1b; }

/* Sidebar quick stats */
.quick-stat     { display:flex;align-items:center;justify-content:space-between;padding:.35rem 0;border-bottom:1px solid var(--border);font-size:.8rem; }
.quick-stat:last-child { border-bottom:none; }
.quick-num      { font-weight:700;font-family:monospace;font-size:.85rem; }

/* Responsive */
@media(max-width:1200px) { .rc-grid { grid-template-columns:1fr 1fr; } }
@media(max-width:720px)  { .rc-grid { grid-template-columns:1fr; } }

/* Spinner */
@keyframes spin { to { transform:rotate(360deg); } }
.spinner { animation:spin .8s linear infinite; }
</style>

<div class="rc-wrap">

  <!-- ─── HEADER ─── -->
  <div class="rc-header">
    <div>
      <h1 class="rc-title">
        <i data-lucide="shield-check" style="width:22px;height:22px;color:#4f46e5;"></i>
        Recovery Center
      </h1>
      <p class="rc-subtitle">Point d'entrée officiel DSM — Restauration complète sans SSH</p>
    </div>
    <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
      <span class="pill" style="background:<?= $sp['bg'] ?>;color:<?= $sp['color'] ?>;border:1px solid <?= $sp['border'] ?>;">
        <span class="dot-blink" style="background:<?= $sp['dot'] ?>;"></span>
        <?= $statusLabel ?>
      </span>
      <span style="font-size:.78rem;color:var(--text-muted);"><?= $errors ?> erreur(s) — <?= $warnings ?> warning(s)</span>
      <button onclick="refreshDiag()" style="font-size:.75rem;padding:.35rem .7rem;border-radius:7px;border:1px solid var(--border);background:none;cursor:pointer;color:var(--text-muted);display:inline-flex;align-items:center;gap:.3rem;" id="refresh-btn">
        <i data-lucide="refresh-cw" style="width:13px;height:13px;" id="refresh-icon"></i> Rafraîchir
      </button>
    </div>
  </div>

  <!-- ─── MAIN GRID ─── -->
  <div class="rc-grid">

    <!-- COL 1 — Diagnostics -->
    <div class="rc-card" id="diag-card">
      <div class="rc-card-title">
        <i data-lucide="activity" style="width:13px;height:13px;"></i>
        Diagnostic système
        <span style="margin-left:auto;font-size:.68rem;color:var(--text-muted);" id="diag-time"><?= date('H:i:s') ?></span>
      </div>
      <div class="diag-list" id="diag-list">
        <?php foreach ($diagnostics as $d):
          $s   = $d['status'] ?? 'ok';
          $cls = $s === 'ok' ? 'db-ok' : ($s === 'warning' ? 'db-warn' : 'db-err');
          $ico = $s === 'ok' ? '✓' : ($s === 'warning' ? '⚠' : '✗');
        ?>
        <div class="diag-row" data-key="<?= htmlspecialchars($d['key'] ?? '') ?>">
          <span class="diag-label"><?= htmlspecialchars($d['label']) ?></span>
          <span class="diag-msg" title="<?= htmlspecialchars($d['message']) ?>"><?= htmlspecialchars($d['message']) ?></span>
          <span class="diag-badge <?= $cls ?>"><?= $ico ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- COL 2 — Control Panel -->
    <div class="ctrl-panel">

      <!-- Steps preview -->
      <div class="rc-card">
        <div class="rc-card-title">
          <i data-lucide="list-checks" style="width:13px;height:13px;"></i>
          Pipeline de restauration
        </div>

        <?php
        $pipelineSteps = [
            ['key'=>'boot',       'label'=>'BootCheck',        'icon'=>'shield'],
            ['key'=>'backup',     'label'=>'Backup SQL',        'icon'=>'database'],
            ['key'=>'migrate',    'label'=>'Master Migration',  'icon'=>'layers'],
            ['key'=>'sync',       'label'=>'Sync Production',   'icon'=>'refresh-cw'],
            ['key'=>'cache',      'label'=>'Cache Clear',       'icon'=>'zap'],
            ['key'=>'assets',     'label'=>'Asset Verify',      'icon'=>'image'],
            ['key'=>'uploads',    'label'=>'Upload Verify',     'icon'=>'upload'],
            ['key'=>'menus',      'label'=>'Menu Rebuild',      'icon'=>'menu'],
            ['key'=>'settings',   'label'=>'Settings Sync',     'icon'=>'settings'],
            ['key'=>'health',     'label'=>'Health Check',      'icon'=>'heart-pulse'],
            ['key'=>'smoke',      'label'=>'Smoke Tests HTTP',  'icon'=>'globe'],
            ['key'=>'rollback',   'label'=>'Auto-Rollback',     'icon'=>'rotate-ccw'],
        ];
        ?>

        <div class="step-list" id="step-list">
          <?php foreach ($pipelineSteps as $i => $step): ?>
          <div class="step-item pending" id="step-<?= $step['key'] ?>">
            <span class="step-icon">○</span>
            <span class="step-name"><?= htmlspecialchars($step['label']) ?></span>
            <span class="step-ms" id="step-ms-<?= $step['key'] ?>"></span>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- Progress -->
        <div class="prog-wrap" style="margin-top:.85rem;">
          <div class="prog-label">
            <span id="prog-label">Prêt à restaurer</span>
            <span id="prog-pct">0%</span>
          </div>
          <div class="prog-bar">
            <div class="prog-fill" id="prog-fill" style="width:0%;background:#4f46e5;"></div>
          </div>
        </div>

        <!-- RESTORE BUTTON -->
        <button class="recover-btn" id="recover-btn" onclick="startRecovery()" style="margin-top:.75rem;">
          <i data-lucide="shield-check" style="width:18px;height:18px;" id="recover-icon"></i>
          <span id="recover-label">🔧 RESTAURER LE SITE</span>
        </button>
      </div>

      <!-- Result banner (hidden until done) -->
      <div id="result-banner" style="display:none;padding:1rem 1.25rem;border-radius:12px;">
        <div style="font-size:.95rem;font-weight:800;" id="result-title">Recovery terminée</div>
        <div style="font-size:.82rem;margin-top:.25rem;" id="result-msg"></div>
        <div class="report-grid" id="result-stats"></div>
      </div>

      <!-- Smoke tests (hidden until done) -->
      <div class="rc-card" id="smoke-panel" style="display:none;">
        <div class="rc-card-title">
          <i data-lucide="globe" style="width:13px;height:13px;"></i>
          Smoke Tests HTTP
        </div>
        <div class="smoke-grid" id="smoke-grid"></div>
      </div>

    </div>

    <!-- COL 3 — Sidebar actions -->
    <div style="display:flex;flex-direction:column;gap:1rem;">

      <!-- Maintenance toggle -->
      <div class="rc-card">
        <div class="rc-card-title">
          <i data-lucide="toggle-left" style="width:13px;height:13px;"></i>
          Mode Maintenance
        </div>
        <div class="maint-wrap">
          <div class="maint-row">
            <div>
              <div class="maint-label" id="maint-label"><?= $maintenance ? 'Activé — Site hors ligne' : 'Désactivé — Site en ligne' ?></div>
              <div style="font-size:.72rem;color:var(--text-muted);">Les visiteurs voient une page 503</div>
            </div>
            <label class="toggle-switch">
              <input type="checkbox" id="maint-toggle" <?= $maintenance ? 'checked' : '' ?> onchange="toggleMaintenance(this.checked)">
              <span class="toggle-slider"></span>
            </label>
          </div>
          <div id="maint-status" style="margin-top:.5rem;font-size:.72rem;color:var(--text-muted);"></div>
        </div>
      </div>

      <!-- Stats rapides -->
      <div class="rc-card">
        <div class="rc-card-title">
          <i data-lucide="bar-chart-2" style="width:13px;height:13px;"></i>
          Statut rapide
        </div>
        <?php
        $quickStats = [
            ['OK',      $oks,      '#22c55e'],
            ['Warning', $warnings, '#f59e0b'],
            ['Erreur',  $errors,   '#ef4444'],
            ['Total',   $total,    '#4f46e5'],
        ];
        ?>
        <?php foreach ($quickStats as [$lbl, $num, $clr]): ?>
        <div class="quick-stat">
          <span><?= $lbl ?></span>
          <span class="quick-num" style="color:<?= $clr ?>;"><?= $num ?></span>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Dernier log -->
      <?php if ($lastLog): ?>
      <div class="rc-card">
        <div class="rc-card-title">
          <i data-lucide="clock" style="width:13px;height:13px;"></i>
          Dernier déploiement
        </div>
        <?php
        $ls = $lastLog['status'] ?? 'ok';
        $lb = $ls === 'ok' ? '#d1fae5' : ($ls === 'error' ? '#fee2e2' : '#fef3c7');
        $lc = $ls === 'ok' ? '#065f46' : ($ls === 'error' ? '#991b1b' : '#92400e');
        ?>
        <div style="font-size:.75rem;color:var(--text-muted);"><?= htmlspecialchars(substr($lastLog['recorded_at'] ?? '', 0, 16)) ?></div>
        <div style="font-size:.8rem;font-weight:600;margin-top:.2rem;"><?= htmlspecialchars($lastLog['mode'] ?? '') ?> — <span style="background:<?= $lb ?>;color:<?= $lc ?>;padding:.1rem .4rem;border-radius:4px;font-size:.7rem;font-weight:700;"><?= strtoupper($ls) ?></span></div>
        <?php if (!empty($lastLog['duration_ms'])): ?>
        <div style="font-size:.72rem;color:var(--text-muted);margin-top:.15rem;"><?= round($lastLog['duration_ms']) ?>ms</div>
        <?php endif; ?>
      </div>
      <?php endif; ?>

      <!-- Rollback -->
      <?php
        $rollbackList = is_array($rollbacks) && isset($rollbacks['data']) ? $rollbacks['data'] : $rollbacks;
        $latestBackup = is_array($rollbackList) ? ($rollbackList[0] ?? null) : null;
      ?>
      <?php if ($latestBackup): ?>
      <div class="rc-card">
        <div class="rc-card-title">
          <i data-lucide="rotate-ccw" style="width:13px;height:13px;"></i>
          Rollback d'urgence
        </div>
        <div style="font-size:.72rem;color:var(--text-muted);margin-bottom:.5rem;"><?= htmlspecialchars($latestBackup['id'] ?? '—') ?></div>
        <div style="font-size:.7rem;color:var(--text-muted);margin-bottom:.6rem;"><?= htmlspecialchars($latestBackup['created_at'] ?? '') ?></div>
        <button onclick="rollbackLatest()" id="rollback-btn" style="width:100%;padding:.5rem;font-size:.75rem;font-weight:700;border-radius:8px;border:2px solid #ef4444;background:none;color:#ef4444;cursor:pointer;transition:.2s;" onmouseover="this.style.background='rgba(239,68,68,.08)'" onmouseout="this.style.background='none'">
          ⬅ Rollback maintenant
        </button>
      </div>
      <?php endif; ?>

    </div>

  </div><!-- /rc-grid -->

  <!-- ─── LOG TERMINAL ─── -->
  <div class="log-panel">
    <div class="log-topbar">
      <div style="display:flex;gap:.35rem;align-items:center;">
        <span style="width:10px;height:10px;border-radius:50%;background:#ef4444;display:inline-block;"></span>
        <span style="width:10px;height:10px;border-radius:50%;background:#f59e0b;display:inline-block;"></span>
        <span style="width:10px;height:10px;border-radius:50%;background:#22c55e;display:inline-block;"></span>
      </div>
      <span class="log-title-bar" id="log-title">dsm@digitalium ~ Recovery Center — prêt</span>
      <button onclick="clearLog()" style="background:none;border:none;color:#475569;font-size:.7rem;cursor:pointer;">clear</button>
    </div>
    <div class="log-body" id="log-body">
      <div class="log-placeholder">
        <i data-lucide="terminal" style="width:20px;height:20px;display:block;margin:0 auto .4rem;color:#334155;"></i>
        Cliquez sur <strong style="color:#93c5fd;">RESTAURER LE SITE</strong> pour lancer la restauration complète.
      </div>
    </div>
  </div>

  <!-- ─── RAPPORT FINAL (apparaît après restauration) ─── -->
  <div class="rc-card report-panel" id="final-report">
    <div class="rc-card-title" style="margin-bottom:1rem;">
      <i data-lucide="file-check" style="width:13px;height:13px;"></i>
      Rapport de restauration
    </div>
    <div id="report-content"><!-- rempli par JS --></div>
  </div>

</div><!-- /rc-wrap -->

<script>
// ─── État ─────────────────────────────────────────────────────────────────────
const CSRF    = <?= json_encode($csrf_token) ?>;
const API     = '<?= url('/admin/api/system') ?>';
let   running = false;

const PIPELINE = [
  {key:'boot',     label:'BootCheck',       icon:'🔐'},
  {key:'backup',   label:'Backup SQL',      icon:'💾'},
  {key:'migrate',  label:'Migration SQL',   icon:'🗄'},
  {key:'sync',     label:'Sync Production', icon:'🔄'},
  {key:'cache',    label:'Cache Clear',     icon:'⚡'},
  {key:'assets',   label:'Asset Verify',    icon:'🖼'},
  {key:'uploads',  label:'Upload Verify',   icon:'📁'},
  {key:'menus',    label:'Menu Rebuild',    icon:'📋'},
  {key:'settings', label:'Settings Sync',   icon:'⚙'},
  {key:'health',   label:'Health Check',    icon:'❤'},
  {key:'smoke',    label:'Smoke Tests',     icon:'🌐'},
  {key:'rollback', label:'Auto-Rollback',   icon:'↩'},
];

// Mapping step labels → pipeline keys (approximate match)
function mapStepToKey(label) {
  const map = {
    'bootcheck':'boot', 'boot check':'boot', 'boot':'boot',
    'backup':'backup', 'rollback point':'backup',
    'master migration':'migrate', 'migration':'migrate',
    'sync production':'sync', 'sync db':'sync', 'synchronisation':'sync',
    'cache clear':'cache', 'cache':'cache',
    'asset verify':'assets', 'assets':'assets',
    'upload verify':'uploads', 'uploads':'uploads',
    'menu rebuild':'menus', 'menus':'menus',
    'settings sync':'settings', 'settings':'settings',
    'health check':'health', 'santé':'health',
    'smoke tests':'smoke', 'smoke':'smoke',
    'auto-rollback':'rollback', 'rollback':'rollback',
  };
  const l = (label||'').toLowerCase();
  for (const [k,v] of Object.entries(map)) {
    if (l.includes(k)) return v;
  }
  return null;
}

// ─── Démarrer la restauration ──────────────────────────────────────────────────
async function startRecovery() {
  if (running) return;
  if (!confirm('⚠️ Lancer la restauration complète ?\n\nLe pipeline exécutera 11 phases de correction automatique.\n\nContinuer ?')) return;

  running = true;
  setUI('running');
  clearLog();
  resetSteps();

  document.getElementById('log-title').textContent = 'dsm@digitalium ~ recovery --mode=full …';
  document.getElementById('prog-label').textContent  = 'Initialisation…';

  appendLog('info', 'Recovery Center', 'Pipeline démarré — ' + new Date().toLocaleTimeString());
  setProgress(2, 'Envoi de la requête…');

  // Indiquer les étapes en attente
  PIPELINE.forEach((s,i) => setTimeout(() => markStep(s.key,'pending'), i * 20));

  try {
    const fd = new FormData();
    fd.append('_csrf', CSRF);

    const r    = await fetch(API + '/recovery-run', { method:'POST', body:fd });
    const data = await r.json();

    // Animer les étapes reçues
    const steps = data.steps || [];
    let   pipeIdx = 0;

    for (let i=0; i < steps.length; i++) {
      const step = steps[i];
      await delay(120);
      const mapped = mapStepToKey(step.label);
      if (mapped) markStep(mapped, step.status, step.duration_ms);
      appendLog(step.status, step.label, step.message, step.duration_ms);
      setProgress(((i+1)/Math.max(steps.length,1))*95, step.label);
    }

    setProgress(100, data.status==='ok' ? '✓ Restauration terminée' : '⚠ Terminée avec alertes');
    showBanner(data);
    showReport(data);
    if (data.smoke) showSmoke(data.smoke);
    document.getElementById('log-title').textContent = 'dsm@digitalium ~ recovery [' + data.status + '] ' + Math.round(data.duration_ms||0) + 'ms';

    // Rafraîchir les diagnostics
    await delay(1000);
    await refreshDiag();

  } catch(e) {
    appendLog('error', 'Erreur réseau', e.message);
    setProgress(100, '✗ Erreur');
    document.getElementById('log-title').textContent = 'dsm@digitalium ~ recovery [ERREUR RÉSEAU]';
  } finally {
    running = false;
    setUI('idle');
  }
}

// ─── Maintenance toggle ────────────────────────────────────────────────────────
async function toggleMaintenance(enabled) {
  const label    = document.getElementById('maint-label');
  const status   = document.getElementById('maint-status');
  label.textContent   = 'Mise à jour…';
  status.textContent  = '';

  try {
    const fd = new FormData();
    fd.append('_csrf', CSRF);
    fd.append('action', enabled ? 'enable' : 'disable');

    const r    = await fetch(API + '/recovery-maintenance', { method:'POST', body:fd });
    const data = await r.json();

    if (data.status === 'ok') {
      label.textContent  = enabled ? 'Activé — Site hors ligne (HTTP 503)' : 'Désactivé — Site en ligne';
      label.style.color  = enabled ? '#ef4444' : 'var(--text-main)';
      status.textContent = data.message;
      status.style.color = data.status === 'ok' ? '#22c55e' : '#f59e0b';
      appendLog(data.status, 'Maintenance', data.message);
    } else {
      status.textContent = '✗ ' + (data.message || 'Erreur');
      status.style.color = '#ef4444';
      document.getElementById('maint-toggle').checked = !enabled;
    }
  } catch(e) {
    status.textContent = '✗ Erreur réseau';
    document.getElementById('maint-toggle').checked = !enabled;
  }
}

// ─── Rollback ─────────────────────────────────────────────────────────────────
async function rollbackLatest() {
  if (!confirm('↩ Rollback d\'urgence\n\nRestaurer la base de données au dernier backup ?\nCette action est irréversible.\n\nContinuer ?')) return;
  const btn = document.getElementById('rollback-btn');
  if (btn) { btn.disabled=true; btn.textContent='Rollback en cours…'; }
  appendLog('info', 'Rollback', 'Démarrage…');
  try {
    const fd = new FormData(); fd.append('_csrf', CSRF);
    const r  = await fetch(API + '/rollback-latest', { method:'POST', body:fd });
    const d  = await r.json();
    appendLog(d.status, 'Rollback', d.message, d.duration_ms);
  } catch(e) { appendLog('error','Rollback',e.message); }
  finally { if(btn){btn.disabled=false;btn.textContent='↩ Rollback maintenant';} }
}

// ─── Rafraîchir diagnostics ───────────────────────────────────────────────────
async function refreshDiag() {
  const icon = document.getElementById('refresh-icon');
  icon.classList.add('spinner');
  try {
    const r    = await fetch(API + '/recovery-diagnostic');
    const data = await r.json();
    const list = document.getElementById('diag-list');
    if (!list || !data.diagnostics) return;

    const statusBadge = { ok: 'db-ok', warning: 'db-warn', error: 'db-err' };
    const statusIco   = { ok: '✓', warning: '⚠', error: '✗' };
    list.innerHTML = '';
    data.diagnostics.forEach(d => {
      const s = d.status || 'ok';
      list.innerHTML += `<div class="diag-row" data-key="${escH(d.key||'')}">
        <span class="diag-label">${escH(d.label)}</span>
        <span class="diag-msg" title="${escH(d.message)}">${escH(d.message)}</span>
        <span class="diag-badge ${statusBadge[s]||'db-ok'}">${statusIco[s]||'?'}</span>
      </div>`;
    });
    document.getElementById('diag-time').textContent = new Date().toLocaleTimeString();

    // Mise à jour toggle maintenance
    document.getElementById('maint-toggle').checked = data.maintenance;
  } catch(e) { console.error('Diag refresh error:', e); }
  finally { icon.classList.remove('spinner'); }
}

// ─── Helpers UI ───────────────────────────────────────────────────────────────
function setUI(state) {
  const btn   = document.getElementById('recover-btn');
  const icon  = document.getElementById('recover-icon');
  const label = document.getElementById('recover-label');
  if (state === 'running') {
    btn.disabled = true;
    icon.setAttribute('data-lucide','loader-2');
    icon.classList.add('spinner');
    label.textContent = 'Restauration en cours…';
  } else {
    btn.disabled = false;
    icon.setAttribute('data-lucide','shield-check');
    icon.classList.remove('spinner');
    label.textContent = '🔧 RESTAURER LE SITE';
  }
  lucide.createIcons();
}

function resetSteps() {
  PIPELINE.forEach(s => {
    const el = document.getElementById('step-'+s.key);
    if (el) { el.className = 'step-item pending'; el.querySelector('.step-icon').textContent = '○'; }
    const ms = document.getElementById('step-ms-'+s.key);
    if (ms) ms.textContent = '';
  });
}

function markStep(key, status, ms) {
  const el  = document.getElementById('step-'+key);
  if (!el) return;
  const ico  = { ok:'✓', warning:'⚠', error:'✗', running:'⟳', pending:'○' };
  el.className = 'step-item ' + (status||'pending');
  el.querySelector('.step-icon').textContent = ico[status] || '○';
  const msEl = document.getElementById('step-ms-'+key);
  if (msEl && ms) msEl.textContent = Math.round(ms)+'ms';
}

function setProgress(pct, label) {
  const fill = document.getElementById('prog-fill');
  fill.style.width = pct + '%';
  fill.style.background = pct < 100 ? '#4f46e5' : '#22c55e';
  document.getElementById('prog-pct').textContent   = Math.round(pct) + '%';
  document.getElementById('prog-label').textContent  = label || '';
}

function appendLog(status, label, msg, ms) {
  const body = document.getElementById('log-body');
  const cls  = {ok:'log-ok-c',warning:'log-warn-c',error:'log-err-c',info:'log-info-c'}[status]||'log-info-c';
  const ico  = {ok:'[✓ OK  ]',warning:'[⚠ WARN]',error:'[✗ ERR ]',info:'[› INFO]'}[status]||'[› INFO]';
  const msStr= ms ? Math.round(ms)+'ms' : '';
  const div  = document.createElement('div');
  div.className = 'log-line';
  div.innerHTML = `<span class="${cls}">${escH(ico)}</span><span class="log-msg-c"><span style="color:#93c5fd;">${escH(label||'')} </span>${escH(msg||'')}</span><span class="log-ms-c">${msStr}</span>`;
  body.appendChild(div);
  body.scrollTop = body.scrollHeight;
}

function clearLog() {
  document.getElementById('log-body').innerHTML = '<div class="log-placeholder">Log vidé.</div>';
}

function showBanner(data) {
  const banner = document.getElementById('result-banner');
  const colors = {ok:'#d1fae5',warning:'#fef3c7',error:'#fee2e2'};
  const borders= {ok:'#6ee7b7',warning:'#fcd34d',error:'#fca5a5'};
  const s = data.status || 'ok';
  banner.style.display   = 'block';
  banner.style.background= colors[s]||'#f1f5f9';
  banner.style.border    = `1px solid ${borders[s]||'#e5e7eb'}`;
  document.getElementById('result-title').textContent = 'Recovery [' + s.toUpperCase() + ']';
  document.getElementById('result-msg').textContent   = (data.message||'') + ' — ' + Math.round(data.duration_ms||0) + 'ms';
  document.getElementById('result-stats').innerHTML = [
    [data.ok||0,      'OK',      '#22c55e'],
    [data.warning||0, 'Warning', '#f59e0b'],
    [data.error||0,   'Erreur',  '#ef4444'],
    [data.total||0,   'Total',   '#4f46e5'],
  ].map(([n,l,c]) => `<div class="report-card"><div class="report-stat" style="color:${c};">${n}</div><div class="report-lbl">${l}</div></div>`).join('');
}

function showSmoke(smoke) {
  const panel = document.getElementById('smoke-panel');
  const grid  = document.getElementById('smoke-grid');
  panel.style.display = 'block';
  grid.innerHTML = smoke.map(s =>
    `<div class="smoke-item ${s.ok?'smoke-ok':'smoke-fail'}">
       ${s.ok?'✓':'✗'} <span>${escH(s.label)}</span>
       <span style="margin-left:auto;font-size:.7rem;font-family:monospace;">HTTP ${s.code||'—'}</span>
     </div>`
  ).join('');
}

function showReport(data) {
  const panel = document.getElementById('final-report');
  const cont  = document.getElementById('report-content');
  panel.style.display = 'block';

  const stepsOk   = (data.steps||[]).filter(s=>s.status==='ok');
  const stepsWarn = (data.steps||[]).filter(s=>s.status==='warning');
  const stepsErr  = (data.steps||[]).filter(s=>s.status==='error');

  let html = `<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:.6rem;margin-bottom:1rem;">`;
  [[stepsOk.length,'Étapes OK','#22c55e'],[stepsWarn.length,'Warnings','#f59e0b'],[stepsErr.length,'Erreurs','#ef4444'],[Math.round(data.duration_ms||0)+'ms','Durée','#4f46e5']].forEach(([v,l,c])=>{
    html+=`<div class="report-card"><div class="report-stat" style="color:${c};">${v}</div><div class="report-lbl">${l}</div></div>`;
  });
  html += '</div>';

  // Steps list
  html += '<table style="width:100%;border-collapse:collapse;font-size:.8rem;">';
  html += '<thead><tr style="background:var(--bg-base);"><th style="padding:.4rem .6rem;text-align:left;font-size:.68rem;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);">Étape</th><th style="padding:.4rem .6rem;text-align:left;font-size:.68rem;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);">Statut</th><th style="padding:.4rem .6rem;text-align:left;font-size:.68rem;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);">Message</th><th style="padding:.4rem .6rem;text-align:right;font-size:.68rem;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);">Durée</th></tr></thead><tbody>';
  (data.steps||[]).forEach(step => {
    const s  = step.status||'ok';
    const bg = s==='ok'?'#d1fae5':s==='error'?'#fee2e2':'#fef3c7';
    const cl = s==='ok'?'#065f46':s==='error'?'#991b1b':'#92400e';
    const ic = s==='ok'?'✓':s==='error'?'✗':'⚠';
    html += `<tr style="border-bottom:1px solid var(--border);">
      <td style="padding:.4rem .6rem;font-weight:600;">${escH(step.label||'')}</td>
      <td style="padding:.4rem .6rem;"><span style="background:${bg};color:${cl};padding:.15rem .5rem;border-radius:5px;font-size:.68rem;font-weight:700;">${ic} ${s.toUpperCase()}</span></td>
      <td style="padding:.4rem .6rem;color:var(--text-muted);">${escH(step.message||'')}</td>
      <td style="padding:.4rem .6rem;text-align:right;font-family:monospace;color:var(--text-muted);">${step.duration_ms?Math.round(step.duration_ms)+'ms':'—'}</td>
    </tr>`;
  });
  html += '</tbody></table>';

  if (data.rollback_done) {
    html += '<div style="margin-top:1rem;padding:.75rem 1rem;background:#fef3c7;border:1px solid #fcd34d;border-radius:10px;font-size:.82rem;color:#92400e;font-weight:600;">⚠ Auto-Rollback effectué — la base de données a été restaurée au dernier backup suite aux erreurs critiques.</div>';
  }

  cont.innerHTML = html;
}

// ─── Utils ────────────────────────────────────────────────────────────────────
const delay = ms => new Promise(r => setTimeout(r, ms));
const escH  = s  => String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');

document.addEventListener('DOMContentLoaded', () => { lucide.createIcons(); });
</script>
