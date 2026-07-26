<?php
/** @var array $git */
/** @var array $health */
/** @var int $score */
/** @var array $state */
/** @var array $backups */
/** @var array $reports */
/** @var array $modes */
/** @var string $csrf_token */
/** @var string $current_mode */

$globalStatus = 'ok';
foreach ($health as $check) {
    $s = $check['status'] ?? 'ok';
    if ($s === 'error')   { $globalStatus = 'error';   break; }
    if ($s === 'warning') { $globalStatus = 'warning'; }
}
$statusColors = ['ok' => '#22c55e', 'warning' => '#f59e0b', 'error' => '#ef4444'];
$statusBg     = ['ok' => '#d1fae5', 'warning' => '#fef3c7', 'error' => '#fee2e2'];
$statusBorder = ['ok' => '#6ee7b7', 'warning' => '#fcd34d', 'error' => '#fca5a5'];
?>

<style>
/* ─── DSM Deploy Center ─────────────────────────────────────────── */
.dc-wrap        { max-width:1400px;margin:0 auto;padding:1.5rem 0; }
.dc-header      { display:flex;align-items:center;justify-content:space-between;margin-bottom:1.75rem;flex-wrap:wrap;gap:1rem; }
.dc-title       { font-size:1.6rem;font-weight:800;color:var(--text-main);margin:0; }
.dc-subtitle    { font-size:.82rem;color:var(--text-muted);margin:.2rem 0 0; }
.dc-status-pill { display:inline-flex;align-items:center;gap:.4rem;padding:.35rem .9rem;border-radius:99px;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em; }

/* Grid layout */
.dc-grid        { display:grid;grid-template-columns:300px 1fr 260px;gap:1.25rem;margin-bottom:1.25rem; }
.dc-card        { background:var(--bg-surface);border:1px solid var(--border);border-radius:14px;padding:1.25rem; }
.dc-card-title  { font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);margin-bottom:1rem; }

/* Info rows */
.info-row       { display:flex;justify-content:space-between;align-items:center;padding:.4rem 0;border-bottom:1px solid var(--border);font-size:.82rem; }
.info-row:last-child { border-bottom:none; }
.info-key       { color:var(--text-muted);font-size:.78rem; }
.info-val       { font-weight:600;color:var(--text-main);font-family:monospace;font-size:.8rem; }
.dot            { width:8px;height:8px;border-radius:50%;display:inline-block;flex-shrink:0; }

/* Modes */
.mode-grid      { display:grid;grid-template-columns:1fr 1fr;gap:.5rem;margin-bottom:1rem; }
.mode-btn       { background:transparent;border:1.5px solid var(--border);border-radius:10px;padding:.6rem .75rem;cursor:pointer;text-align:left;transition:.2s;position:relative; }
.mode-btn:hover { border-color:var(--primary);background:rgba(79,70,229,.04); }
.mode-btn.active{ border-color:var(--primary);background:rgba(79,70,229,.08); }
.mode-btn .mode-name  { font-size:.8rem;font-weight:700;color:var(--text-main); }
.mode-btn .mode-desc  { font-size:.68rem;color:var(--text-muted);margin-top:.15rem;line-height:1.3; }
.mode-btn .mode-steps { font-size:.65rem;color:var(--primary);margin-top:.2rem;font-weight:600; }
.mode-badge     { position:absolute;top:.4rem;right:.5rem;font-size:.6rem;padding:.1rem .4rem;border-radius:4px;font-weight:700; }
.badge-prod     { background:#fef3c7;color:#92400e; }
.badge-safe     { background:#d1fae5;color:#065f46; }
.badge-danger   { background:#fee2e2;color:#991b1b; }

/* Deploy button */
.deploy-main-btn { width:100%;padding:1.1rem;font-size:1rem;font-weight:800;border-radius:12px;border:none;cursor:pointer;background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;display:flex;align-items:center;justify-content:center;gap:.6rem;transition:.2s;letter-spacing:.02em;box-shadow:0 4px 14px rgba(79,70,229,.35); }
.deploy-main-btn:hover:not(:disabled) { transform:translateY(-1px);box-shadow:0 6px 20px rgba(79,70,229,.45); }
.deploy-main-btn:disabled { opacity:.6;cursor:not-allowed;transform:none; }

/* Stats */
.stat-grid      { display:grid;grid-template-columns:1fr 1fr;gap:.75rem; }
.stat-card      { background:var(--bg-surface);border:1px solid var(--border);border-radius:10px;padding:.75rem;text-align:center; }
.stat-num       { font-size:1.5rem;font-weight:800;line-height:1; }
.stat-lbl       { font-size:.67rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;margin-top:.2rem; }

/* Log area */
.log-panel      { background:#0b1120;border-radius:14px;border:1px solid #1e293b;overflow:hidden;margin-bottom:1.25rem; }
.log-topbar     { background:#0f172a;padding:.6rem 1rem;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #1e293b; }
.log-title-bar  { font-size:.75rem;font-weight:700;color:#94a3b8;font-family:monospace; }
.log-controls   { display:flex;gap:.5rem; }
.log-dot-r      { width:10px;height:10px;border-radius:50%;background:#ef4444; }
.log-dot-y      { width:10px;height:10px;border-radius:50%;background:#f59e0b; }
.log-dot-g      { width:10px;height:10px;border-radius:50%;background:#22c55e; }
.log-body       { padding:1rem;font-family:'Fira Code',monospace,monospace;font-size:.78rem;line-height:1.65;color:#e2e8f0;min-height:200px;max-height:440px;overflow-y:auto; }
.log-line       { display:grid;grid-template-columns:70px 200px 1fr auto;gap:.75rem;padding:.18rem 0;align-items:start;border-bottom:1px solid rgba(255,255,255,.03); }
.log-line:last-child { border-bottom:none; }
.log-status     { font-weight:700;font-size:.72rem; }
.log-ok-c       { color:#4ade80; }
.log-warn-c     { color:#fbbf24; }
.log-err-c      { color:#f87171; }
.log-label-c    { color:#93c5fd; }
.log-msg-c      { color:#cbd5e1; }
.log-time-c     { color:#475569;font-size:.7rem;text-align:right; }
.log-placeholder{ color:#334155;text-align:center;padding:2rem 0; }

/* Health dashboard */
.health-table   { width:100%;border-collapse:collapse; }
.health-table th{ font-size:.7rem;text-transform:uppercase;letter-spacing:.07em;color:var(--text-muted);padding:.5rem .75rem;text-align:left;background:var(--bg-base);border-bottom:1px solid var(--border); }
.health-table td{ padding:.5rem .75rem;border-bottom:1px solid var(--border);font-size:.82rem; }
.health-table tr:last-child td { border-bottom:none; }
.hc-badge       { display:inline-flex;align-items:center;gap:.3rem;padding:.2rem .55rem;border-radius:6px;font-size:.68rem;font-weight:700; }
.hc-ok          { background:#d1fae5;color:#065f46; }
.hc-warn        { background:#fef3c7;color:#92400e; }
.hc-err         { background:#fee2e2;color:#991b1b; }

/* Progress bar */
.prog-wrap      { margin:1rem 0; }
.prog-label     { display:flex;justify-content:space-between;font-size:.75rem;color:var(--text-muted);margin-bottom:.35rem; }
.prog-bar       { height:6px;background:var(--border);border-radius:99px;overflow:hidden; }
.prog-fill      { height:100%;border-radius:99px;transition:width .4s ease; }

/* Animations */
@keyframes spin  { to { transform:rotate(360deg); } }
@keyframes pulse { 0%,100% { opacity:1; } 50% { opacity:.4; } }
.spinner        { animation:spin .8s linear infinite; }
.pulsing        { animation:pulse 1.5s ease-in-out infinite; }

/* Result banner */
.result-banner  { border-radius:12px;padding:1rem 1.25rem;display:none;margin-bottom:1rem; }
.result-grid    { display:grid;grid-template-columns:repeat(4,1fr);gap:.75rem;margin-top:.75rem; }
.result-stat    { text-align:center;padding:.5rem;background:rgba(255,255,255,.5);border-radius:8px; }
.result-stat-n  { font-size:1.4rem;font-weight:800; }
.result-stat-l  { font-size:.67rem;text-transform:uppercase;color:var(--text-muted); }

/* Responsive */
@media(max-width:1100px) { .dc-grid { grid-template-columns:1fr 1fr; } }
@media(max-width:700px)  { .dc-grid { grid-template-columns:1fr; } }
</style>

<div class="dc-wrap">

  <!-- ─── HEADER ─── -->
  <div class="dc-header">
    <div>
      <h1 class="dc-title">
        <i data-lucide="rocket" style="width:22px;height:22px;vertical-align:middle;margin-right:.4rem;color:#4f46e5;"></i>
        Deploy Center
      </h1>
      <p class="dc-subtitle">Digitalium System Manager — Operating System du CMS Enterprise</p>
    </div>
    <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
      <span class="dc-status-pill" style="background:<?= $statusBg[$globalStatus] ?>;color:<?= $statusColors[$globalStatus] ?>;border:1px solid <?= $statusBorder[$globalStatus] ?>;">
        <span class="dot" style="background:<?= $statusColors[$globalStatus] ?>;"></span>
        <?= strtoupper($globalStatus) ?>
      </span>
      <span style="font-size:.8rem;color:var(--text-muted);">
        <?= htmlspecialchars($git['branch'] ?? 'main') ?> /
        <code><?= htmlspecialchars($git['commit'] ?? '—') ?></code>
      </span>
      <a href="<?= url('/admin/system/status') ?>" style="font-size:.78rem;color:var(--primary);text-decoration:none;">
        ← Tableau de bord DSM
      </a>
    </div>
  </div>

  <!-- ─── RESULT BANNER ─── -->
  <div class="result-banner" id="result-banner">
    <strong id="result-title">Deploy terminé</strong> — <span id="result-msg"></span>
    <div class="result-grid" id="result-grid"></div>
  </div>

  <!-- ─── MAIN GRID ─── -->
  <div class="dc-grid">

    <!-- COL 1 — Infos système -->
    <div>
      <div class="dc-card" style="margin-bottom:1.25rem;">
        <div class="dc-card-title">Système</div>
        <?php
        $infoRows = [
            ['CMS Version', 'v1.1.0 Enterprise'],
            ['PHP',         PHP_VERSION],
            ['Environnement', ENVIRONMENT],
            ['Branch Git',  $git['branch'] ?? '—'],
            ['Commit',      $git['commit'] ?? '—'],
            ['Tag',         $git['tag'] ?: '—'],
            ['Statut Git',  ($git['dirty'] ?? false) ? '⚠ Dirty' : '✓ Clean'],
            ['Dernier deploy', $state['last_update'] ?? '—'],
            ['Score santé', $score . '/10'],
        ];
        foreach ($infoRows as [$k, $v]):
        ?>
          <div class="info-row">
            <span class="info-key"><?= htmlspecialchars($k) ?></span>
            <span class="info-val"><?= htmlspecialchars($v) ?></span>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="dc-card">
        <div class="dc-card-title">Commits récents</div>
        <div style="font-size:.75rem;color:var(--text-muted);line-height:1.6;">
          <?php if (!empty($git['message'])): ?>
            <div style="font-family:monospace;padding:.35rem .5rem;background:var(--bg-base);border-radius:6px;margin-bottom:.5rem;">
              <span style="color:#4f46e5;font-weight:600;"><?= htmlspecialchars($git['commit'] ?? '') ?></span>
              <?= htmlspecialchars($git['message'] ?? '') ?>
            </div>
            <div style="color:var(--text-muted);">par <?= htmlspecialchars($git['author'] ?? '') ?> — <?= htmlspecialchars($git['date'] ?? '') ?></div>
          <?php else: ?>
            <span style="color:var(--text-muted);">Git indisponible</span>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- COL 2 — Deploy Control -->
    <div>
      <div class="dc-card">
        <div class="dc-card-title">Mode de déploiement</div>

        <!-- Mode selector -->
        <div class="mode-grid" id="mode-grid">
          <?php foreach ($modes as $m): ?>
          <?php
            $badges = [
                'production' => ['badge-prod',   'PROD'],
                'safe'       => ['badge-safe',   'SAFE'],
                'rollback'   => ['badge-danger',  'ROLL'],
                'repair'     => ['badge-warn',    'FIX'],
            ];
            $badge = $badges[$m['key']] ?? null;
          ?>
          <button class="mode-btn <?= $m['key'] === ($current_mode ?? 'full') ? 'active' : '' ?>"
            onclick="selectMode('<?= htmlspecialchars($m['key']) ?>', this)"
            data-mode="<?= htmlspecialchars($m['key']) ?>">
            <?php if ($badge): ?>
              <span class="mode-badge <?= $badge[0] ?>"><?= $badge[1] ?></span>
            <?php endif; ?>
            <div class="mode-name"><?= htmlspecialchars($m['label']) ?></div>
            <div class="mode-desc"><?= htmlspecialchars($m['description']) ?></div>
            <div class="mode-steps"><?= count($m['steps']) ?> étape<?= count($m['steps']) > 1 ? 's' : '' ?></div>
          </button>
          <?php endforeach; ?>
        </div>

        <!-- Progress bar -->
        <div class="prog-wrap">
          <div class="prog-label">
            <span id="prog-label">Prêt</span>
            <span id="prog-pct">0%</span>
          </div>
          <div class="prog-bar">
            <div class="prog-fill" id="prog-fill" style="width:0%;background:#4f46e5;"></div>
          </div>
        </div>

        <!-- DEPLOY BUTTON -->
        <button class="deploy-main-btn" id="deploy-btn" onclick="startDeploy()">
          <i data-lucide="rocket" style="width:20px;height:20px;" id="deploy-icon"></i>
          <span id="deploy-btn-label">🚀 DEPLOY</span>
        </button>

        <!-- Mode description -->
        <div id="mode-info" style="margin-top:.75rem;padding:.65rem .85rem;background:var(--bg-base);border-radius:8px;font-size:.78rem;color:var(--text-muted);">
          <strong style="color:var(--text-main);">Full Deploy</strong> — Pipeline complet avec migration, sync CMS, cache, santé.
        </div>
      </div>
    </div>

    <!-- COL 3 — Stats rapides -->
    <div>
      <div class="dc-card" style="margin-bottom:1.25rem;">
        <div class="dc-card-title">Statistiques</div>
        <?php
        try {
            $pagesCount   = \App\Services\Database::fetch("SELECT COUNT(*) as n FROM pages")['n'] ?? 0;
            $postsCount   = \App\Services\Database::fetch("SELECT COUNT(*) as n FROM blog_posts WHERE status='published'")['n'] ?? 0;
            $mediaCount   = \App\Services\Database::fetch("SELECT COUNT(*) as n FROM media")['n'] ?? 0;
            $projCount    = \App\Services\Database::fetch("SELECT COUNT(*) as n FROM projects")['n'] ?? 0;
            $settingsCount= \App\Services\Database::fetch("SELECT COUNT(*) as n FROM settings")['n'] ?? 0;
            $messagesCount= \App\Services\Database::fetch("SELECT COUNT(*) as n FROM contact_messages WHERE statut='new'")['n'] ?? 0;
        } catch (\Throwable $e) { $pagesCount=$postsCount=$mediaCount=$projCount=$settingsCount=$messagesCount=0; }
        $statsGrid = [
            [$pagesCount,    'Pages',    '#4f46e5'],
            [$postsCount,    'Articles', '#0ea5e9'],
            [$mediaCount,    'Médias',   '#8b5cf6'],
            [$projCount,     'Projets',  '#06b6d4'],
            [$settingsCount, 'Settings', '#10b981'],
            [$messagesCount, 'Messages', '#f59e0b'],
        ];
        ?>
        <div class="stat-grid">
          <?php foreach ($statsGrid as [$num, $lbl, $col]): ?>
          <div class="stat-card">
            <div class="stat-num" style="color:<?= $col ?>;"><?= $num ?></div>
            <div class="stat-lbl"><?= $lbl ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="dc-card">
        <div class="dc-card-title">Derniers backups</div>
        <?php if (empty($backups)): ?>
          <p style="font-size:.78rem;color:var(--text-muted);">Aucun backup disponible.</p>
          <button onclick="runQuickAction('backup')" style="margin-top:.5rem;font-size:.75rem;padding:.4rem .75rem;border-radius:7px;border:1px solid var(--border);background:none;cursor:pointer;color:var(--primary);">
            Créer un backup
          </button>
        <?php else: ?>
          <?php foreach (array_slice($backups, 0, 4) as $b): ?>
          <div class="info-row">
            <span style="font-size:.72rem;font-family:monospace;color:var(--text-muted);"><?= htmlspecialchars(basename($b['path'] ?? (is_string($b) ? $b : ''))) ?></span>
            <span style="font-size:.7rem;color:var(--text-muted);"><?= isset($b['size']) ? round($b['size']/1024/1024, 1).'MB' : '' ?></span>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- ─── LOG TERMINAL ─── -->
  <div class="log-panel">
    <div class="log-topbar">
      <div style="display:flex;gap:.4rem;align-items:center;">
        <span class="log-dot-r"></span>
        <span class="log-dot-y"></span>
        <span class="log-dot-g"></span>
      </div>
      <span class="log-title-bar" id="log-title">dsm@digitalium ~ Deploy Center — prêt</span>
      <button onclick="clearLog()" style="background:none;border:none;color:#475569;font-size:.7rem;cursor:pointer;">clear</button>
    </div>
    <div class="log-body" id="log-body">
      <div class="log-placeholder">
        <i data-lucide="terminal" style="width:24px;height:24px;margin-bottom:.5rem;display:block;margin:0 auto .5rem;"></i>
        Sélectionnez un mode et cliquez sur DEPLOY pour lancer le pipeline.
      </div>
    </div>
  </div>

  <!-- ─── DEPLOYMENT HISTORY + ROLLBACK ─── -->
  <div style="display:grid;grid-template-columns:1fr 320px;gap:1.25rem;margin-bottom:1.25rem;">

    <!-- Historique des déploiements -->
    <div class="dc-card" style="margin-bottom:0;">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.75rem;">
        <div class="dc-card-title" style="margin:0;">Historique des déploiements</div>
        <button onclick="reloadDeployLog()" style="background:none;border:1px solid var(--border);border-radius:7px;padding:.3rem .6rem;font-size:.72rem;cursor:pointer;color:var(--text-muted);">
          <i data-lucide="refresh-cw" style="width:12px;height:12px;"></i> Refresh
        </button>
      </div>
      <?php if (empty($deploy_logs)): ?>
        <p style="font-size:.78rem;color:var(--text-muted);padding:.5rem 0;">Aucun déploiement enregistré — le premier deploy sera loggué ici.</p>
      <?php else: ?>
        <div style="overflow-x:auto;">
          <table style="width:100%;border-collapse:collapse;font-size:.78rem;" id="deploy-log-table">
            <thead>
              <tr style="background:var(--bg-base);">
                <th style="padding:.4rem .65rem;text-align:left;font-size:.68rem;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);border-bottom:1px solid var(--border);">Date</th>
                <th style="padding:.4rem .65rem;text-align:left;font-size:.68rem;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);border-bottom:1px solid var(--border);">Mode</th>
                <th style="padding:.4rem .65rem;text-align:left;font-size:.68rem;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);border-bottom:1px solid var(--border);">Statut</th>
                <th style="padding:.4rem .65rem;text-align:left;font-size:.68rem;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);border-bottom:1px solid var(--border);">Commit</th>
                <th style="padding:.4rem .65rem;text-align:left;font-size:.68rem;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);border-bottom:1px solid var(--border);">Acteur</th>
                <th style="padding:.4rem .65rem;text-align:right;font-size:.68rem;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);border-bottom:1px solid var(--border);">Durée</th>
              </tr>
            </thead>
            <tbody id="deploy-log-tbody">
              <?php foreach ($deploy_logs as $log):
                $ls = $log['status'] ?? 'ok';
                $lc = $ls === 'ok' ? '#22c55e' : ($ls === 'error' ? '#ef4444' : '#f59e0b');
                $lb = $ls === 'ok' ? '#d1fae5' : ($ls === 'error' ? '#fee2e2' : '#fef3c7');
              ?>
              <tr style="border-bottom:1px solid var(--border);">
                <td style="padding:.45rem .65rem;font-family:monospace;font-size:.75rem;color:var(--text-muted);"><?= htmlspecialchars(substr($log['recorded_at'] ?? '—', 0, 16)) ?></td>
                <td style="padding:.45rem .65rem;font-weight:600;"><?= htmlspecialchars($log['mode'] ?? '—') ?></td>
                <td style="padding:.45rem .65rem;">
                  <span style="display:inline-flex;align-items:center;gap:.25rem;padding:.15rem .5rem;border-radius:5px;font-size:.68rem;font-weight:700;background:<?= $lb ?>;color:<?= $lc ?>;">
                    <?= $ls === 'ok' ? '✓ OK' : ($ls === 'error' ? '✗ ERROR' : '⚠ WARN') ?>
                  </span>
                </td>
                <td style="padding:.45rem .65rem;font-family:monospace;font-size:.72rem;color:var(--text-muted);"><?= htmlspecialchars(substr($log['commit'] ?? '—', 0, 7)) ?></td>
                <td style="padding:.45rem .65rem;color:var(--text-muted);"><?= htmlspecialchars($log['actor'] ?? '—') ?></td>
                <td style="padding:.45rem .65rem;text-align:right;font-family:monospace;color:var(--text-muted);"><?= isset($log['duration_ms']) ? round($log['duration_ms']).'ms' : '—' ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

    <!-- Rollback d'urgence -->
    <div class="dc-card" style="margin-bottom:0;">
      <div class="dc-card-title">Rollback d'urgence</div>
      <?php
        $rbList = $rollback_ids['data'] ?? $rollback_ids;
        $latestBackup = is_array($rbList) && !empty($rbList) ? $rbList[0] : null;
      ?>
      <?php if ($latestBackup): ?>
        <div style="padding:.6rem .75rem;background:var(--bg-base);border-radius:8px;margin-bottom:.75rem;">
          <div style="font-size:.72rem;color:var(--text-muted);margin-bottom:.2rem;">Dernier point de sauvegarde</div>
          <div style="font-family:monospace;font-size:.75rem;font-weight:600;color:var(--text-main);"><?= htmlspecialchars($latestBackup['id'] ?? '—') ?></div>
          <div style="font-size:.7rem;color:var(--text-muted);"><?= htmlspecialchars($latestBackup['created_at'] ?? '') ?></div>
          <div style="font-size:.7rem;color:var(--text-muted);"><?= isset($latestBackup['size_bytes']) ? round($latestBackup['size_bytes']/1024, 1).'KB' : '' ?></div>
        </div>
        <button onclick="rollbackLatest()" id="rollback-btn" style="width:100%;padding:.75rem;font-size:.82rem;font-weight:700;border-radius:10px;border:2px solid #ef4444;background:none;color:#ef4444;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.5rem;transition:.2s;" onmouseover="this.style.background='rgba(239,68,68,.08)'" onmouseout="this.style.background='none'">
          <i data-lucide="rotate-ccw" style="width:15px;height:15px;"></i>
          Rollback maintenant
        </button>
        <p style="font-size:.68rem;color:var(--text-muted);margin-top:.5rem;text-align:center;">Restaure la base de données au dernier point de sauvegarde.</p>
      <?php else: ?>
        <p style="font-size:.78rem;color:var(--text-muted);padding:.5rem 0;">Aucun point de rollback disponible.</p>
        <p style="font-size:.7rem;color:var(--text-muted);">Un backup est créé automatiquement avant chaque deploy via le pipeline.</p>
      <?php endif; ?>

      <div style="margin-top:1rem;padding-top:.75rem;border-top:1px solid var(--border);">
        <div class="dc-card-title" style="margin-bottom:.5rem;">Actions rapides</div>
        <button onclick="runQuickAction('backup')" style="width:100%;padding:.5rem;font-size:.75rem;font-weight:600;border-radius:8px;border:1px solid var(--border);background:none;color:var(--primary);cursor:pointer;margin-bottom:.4rem;">
          <i data-lucide="database" style="width:12px;height:12px;"></i> Créer backup maintenant
        </button>
        <button onclick="runQuickAction('cache')" style="width:100%;padding:.5rem;font-size:.75rem;font-weight:600;border-radius:8px;border:1px solid var(--border);background:none;color:var(--text-muted);cursor:pointer;">
          <i data-lucide="zap" style="width:12px;height:12px;"></i> Vider le cache
        </button>
      </div>
    </div>
  </div>

  <!-- ─── HEALTH DASHBOARD ─── -->
  <div class="dc-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
      <div class="dc-card-title" style="margin:0;">Health Dashboard</div>
      <div style="display:flex;align-items:center;gap:.5rem;">
        <span style="font-size:1.4rem;font-weight:800;color:<?= $score >= 8 ? '#22c55e' : ($score >= 5 ? '#f59e0b' : '#ef4444') ?>;"><?= $score ?></span>
        <span style="font-size:.75rem;color:var(--text-muted);">/10</span>
        <button onclick="refreshHealth()" style="background:none;border:1px solid var(--border);border-radius:7px;padding:.3rem .6rem;font-size:.72rem;cursor:pointer;color:var(--text-muted);">
          <i data-lucide="refresh-cw" style="width:12px;height:12px;"></i> Refresh
        </button>
      </div>
    </div>

    <table class="health-table" id="health-table">
      <thead>
        <tr>
          <th>Composant</th>
          <th>Statut</th>
          <th>Message</th>
          <th>Durée</th>
        </tr>
      </thead>
      <tbody id="health-tbody">
        <?php foreach ($health as $check): ?>
        <?php $s = $check['status'] ?? 'ok'; ?>
        <tr>
          <td><strong><?= htmlspecialchars($check['label'] ?? '—') ?></strong></td>
          <td>
            <span class="hc-badge <?= $s === 'ok' ? 'hc-ok' : ($s === 'warning' ? 'hc-warn' : 'hc-err') ?>">
              <?= $s === 'ok' ? '✓ OK' : ($s === 'warning' ? '⚠ WARNING' : '✗ ERROR') ?>
            </span>
          </td>
          <td style="color:var(--text-muted);font-size:.8rem;"><?= htmlspecialchars($check['message'] ?? '') ?></td>
          <td style="color:var(--text-muted);font-size:.75rem;"><?= isset($check['duration_ms']) ? round($check['duration_ms']).'ms' : '—' ?></td>
        </tr>
        <?php endforeach; ?>

        <!-- Git check -->
        <?php if (!empty($git['commit'])): ?>
        <?php $gs = ($git['dirty'] ?? false) ? 'warning' : 'ok'; ?>
        <tr>
          <td><strong>Git</strong></td>
          <td>
            <span class="hc-badge <?= $gs === 'ok' ? 'hc-ok' : 'hc-warn' ?>">
              <?= $gs === 'ok' ? '✓ Clean' : '⚠ Dirty' ?>
            </span>
          </td>
          <td style="color:var(--text-muted);font-size:.8rem;">
            <?= htmlspecialchars($git['branch'] ?? '') ?> — <?= htmlspecialchars($git['commit'] ?? '') ?>
          </td>
          <td>—</td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

</div><!-- /dc-wrap -->

<script>
// ─── State ───────────────────────────────────────────────────────────────────
const CSRF        = <?= json_encode($csrf_token) ?>;
const BASE_API    = '<?= url('/admin/api/system') ?>';
let   currentMode = '<?= htmlspecialchars($current_mode ?? 'full') ?>';
let   deploying   = false;

const modeInfos = <?= json_encode(array_reduce($modes, function($carry, $m) {
    $carry[$m['key']] = [
        'label'       => $m['label'],
        'description' => $m['description'],
        'steps'       => $m['steps'],
        'step_count'  => $m['step_count'],
    ];
    return $carry;
}, [])) ?>;

// ─── Mode selection ───────────────────────────────────────────────────────────
function selectMode(mode, btn) {
    currentMode = mode;
    document.querySelectorAll('.mode-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const info = modeInfos[mode] || {};
    document.getElementById('mode-info').innerHTML =
        `<strong style="color:var(--text-main);">${escH(info.label||mode)}</strong> — ${escH(info.description||'')}` +
        `<br><span style="color:#4f46e5;font-size:.72rem;">${(info.steps||[]).join(' → ')}</span>`;
}

// ─── Deploy ───────────────────────────────────────────────────────────────────
async function startDeploy() {
    if (deploying) return;
    deploying = true;

    const btn     = document.getElementById('deploy-btn');
    const icon    = document.getElementById('deploy-icon');
    const label   = document.getElementById('deploy-btn-label');
    const logBody = document.getElementById('log-body');
    const banner  = document.getElementById('result-banner');
    const logTitle= document.getElementById('log-title');

    btn.disabled = true;
    icon.setAttribute('data-lucide', 'loader-2');
    icon.classList.add('spinner');
    label.textContent = 'Déploiement en cours…';
    lucide.createIcons();

    logBody.innerHTML = '';
    banner.style.display = 'none';
    logTitle.textContent = `dsm@digitalium ~ deploy --mode=${currentMode} …`;

    setProgress(0, 'Initialisation…');
    appendLog('info', 'DSM', `Pipeline ${currentMode} démarré — ${new Date().toLocaleTimeString()}`, null);

    try {
        const fd = new FormData();
        fd.append('_csrf', CSRF);
        fd.append('mode', currentMode);

        const r    = await fetch(BASE_API + '/deploy', { method: 'POST', body: fd });
        const data = await r.json();

        // Animate steps
        const steps = data.steps || [];
        for (let i = 0; i < steps.length; i++) {
            const step = steps[i];
            await delay(60);
            appendLog(step.status, step.label, step.message, step.duration_ms);
            setProgress(((i+1)/steps.length)*100, step.label);
        }

        setProgress(100, data.status === 'ok' ? '✓ Terminé' : '⚠ Terminé avec avertissements');
        showBanner(data);
        logTitle.textContent = `dsm@digitalium ~ deploy --mode=${currentMode} [${data.status}] ${data.duration_ms}ms`;

    } catch (e) {
        appendLog('error', 'Réseau', e.message, null);
        logTitle.textContent = 'dsm@digitalium ~ deploy [ERREUR RÉSEAU]';
    } finally {
        deploying = false;
        btn.disabled = false;
        icon.setAttribute('data-lucide', 'rocket');
        icon.classList.remove('spinner');
        label.textContent = '🚀 DEPLOY';
        lucide.createIcons();
    }
}

// ─── Log helpers ─────────────────────────────────────────────────────────────
function appendLog(status, label, msg, ms) {
    const logBody = document.getElementById('log-body');
    const cls     = status === 'ok' ? 'log-ok-c' : (status === 'error' ? 'log-err-c' : status === 'info' ? 'log-label-c' : 'log-warn-c');
    const icon    = status === 'ok' ? '✓' : (status === 'error' ? '✗' : status === 'info' ? '›' : '⚠');
    const msStr   = ms ? `${Math.round(ms)}ms` : '—';

    const div = document.createElement('div');
    div.className = 'log-line';
    div.innerHTML =
        `<span class="log-status ${cls}">[${icon} ${(status||'info').toUpperCase().slice(0,4)}]</span>` +
        `<span class="log-label-c">${escH(label||'')}</span>` +
        `<span class="log-msg-c">${escH(msg||'')}</span>` +
        `<span class="log-time-c">${msStr}</span>`;
    logBody.appendChild(div);
    logBody.scrollTop = logBody.scrollHeight;
}

function clearLog() {
    document.getElementById('log-body').innerHTML =
        '<div class="log-placeholder">Log vidé.</div>';
}

// ─── Progress bar ─────────────────────────────────────────────────────────────
function setProgress(pct, label) {
    document.getElementById('prog-fill').style.width = pct + '%';
    document.getElementById('prog-pct').textContent   = Math.round(pct) + '%';
    document.getElementById('prog-label').textContent  = label || '';
    const color = pct === 100
        ? (document.getElementById('result-banner').style.background?.includes('d1fae5') ? '#22c55e' : '#f59e0b')
        : '#4f46e5';
    document.getElementById('prog-fill').style.background = color;
}

// ─── Result banner ────────────────────────────────────────────────────────────
function showBanner(data) {
    const banner = document.getElementById('result-banner');
    const colors = { ok: '#d1fae5', warning: '#fef3c7', error: '#fee2e2' };
    const borders= { ok: '#6ee7b7', warning: '#fcd34d', error: '#fca5a5' };
    const s = data.status || 'ok';
    banner.style.display   = 'block';
    banner.style.background= colors[s] || '#f1f5f9';
    banner.style.border    = `1px solid ${borders[s] || '#e5e7eb'}`;
    document.getElementById('result-title').textContent = `${data.label||'Deploy'} [${s.toUpperCase()}]`;
    document.getElementById('result-msg').textContent   = `${data.message||''} — ${data.duration_ms||0}ms`;

    const grid = document.getElementById('result-grid');
    grid.innerHTML = [
        [data.ok||0,      'OK',      '#22c55e'],
        [data.warning||0, 'Warning', '#f59e0b'],
        [data.error||0,   'Error',   '#ef4444'],
        [data.total||0,   'Total',   '#4f46e5'],
    ].map(([n,l,c]) =>
        `<div class="result-stat"><div class="result-stat-n" style="color:${c};">${n}</div><div class="result-stat-l">${l}</div></div>`
    ).join('');
}

// ─── Health refresh ───────────────────────────────────────────────────────────
async function refreshHealth() {
    const fd = new FormData();
    fd.append('_csrf', CSRF);
    try {
        const r    = await fetch(BASE_API + '/health', { method: 'POST', body: fd });
        const data = await r.json();
        const tbody= document.getElementById('health-tbody');
        tbody.innerHTML = '';
        const checks = data.checks || [];
        (Array.isArray(checks) ? checks : Object.values(checks)).forEach(c => {
            const s   = c.status || 'ok';
            const cls = s === 'ok' ? 'hc-ok' : (s === 'warning' ? 'hc-warn' : 'hc-err');
            const ico = s === 'ok' ? '✓ OK' : (s === 'warning' ? '⚠ WARNING' : '✗ ERROR');
            tbody.innerHTML +=
                `<tr>
                  <td><strong>${escH(c.label||'')}</strong></td>
                  <td><span class="hc-badge ${cls}">${ico}</span></td>
                  <td style="color:var(--text-muted);font-size:.8rem;">${escH(c.message||'')}</td>
                  <td style="color:var(--text-muted);font-size:.75rem;">${c.duration_ms ? Math.round(c.duration_ms)+'ms' : '—'}</td>
                </tr>`;
        });
    } catch (e) {
        console.error('Health refresh error:', e);
    }
}

// ─── Rollback latest ─────────────────────────────────────────────────────────
async function rollbackLatest() {
    if (!confirm('⚠️ Rollback d\'urgence — Restaurer la base de données au dernier backup ?\n\nCette action est irréversible. Continuer ?')) return;
    const btn = document.getElementById('rollback-btn');
    if (btn) { btn.disabled = true; btn.textContent = 'Rollback en cours…'; }
    appendLog('info', 'Rollback', 'Démarrage du rollback au dernier backup…', null);
    const fd = new FormData();
    fd.append('_csrf', CSRF);
    try {
        const r    = await fetch(BASE_API + '/rollback-latest', { method: 'POST', body: fd });
        const data = await r.json();
        appendLog(data.status, data.label || 'Rollback', data.message || '', data.duration_ms);
        if (data.status === 'ok') {
            appendLog('ok', 'Rollback', 'Base de données restaurée avec succès.', null);
        }
    } catch (e) {
        appendLog('error', 'Rollback', e.message, null);
    } finally {
        if (btn) { btn.disabled = false; btn.innerHTML = '<i data-lucide="rotate-ccw" style="width:15px;height:15px;"></i> Rollback maintenant'; lucide.createIcons(); }
    }
}

// ─── Reload deploy log ────────────────────────────────────────────────────────
async function reloadDeployLog() {
    try {
        const r    = await fetch(BASE_API + '/deploy-log?limit=10');
        const data = await r.json();
        const logs = data.data || [];
        const tbody= document.getElementById('deploy-log-tbody');
        if (!tbody) return;
        tbody.innerHTML = logs.length === 0
            ? '<tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:1rem;">Aucun déploiement enregistré.</td></tr>'
            : logs.map(log => {
                const ls = log.status || 'ok';
                const lc = ls === 'ok' ? '#22c55e' : (ls === 'error' ? '#ef4444' : '#f59e0b');
                const lb = ls === 'ok' ? '#d1fae5' : (ls === 'error' ? '#fee2e2' : '#fef3c7');
                const ico= ls === 'ok' ? '✓ OK' : (ls === 'error' ? '✗ ERROR' : '⚠ WARN');
                const date = (log.recorded_at||'—').slice(0,16);
                const commit = (log.commit||'—').slice(0,7);
                const ms = log.duration_ms ? Math.round(log.duration_ms)+'ms' : '—';
                return `<tr style="border-bottom:1px solid var(--border);">
                  <td style="padding:.45rem .65rem;font-family:monospace;font-size:.75rem;color:var(--text-muted);">${escH(date)}</td>
                  <td style="padding:.45rem .65rem;font-weight:600;">${escH(log.mode||'—')}</td>
                  <td style="padding:.45rem .65rem;"><span style="display:inline-flex;align-items:center;gap:.25rem;padding:.15rem .5rem;border-radius:5px;font-size:.68rem;font-weight:700;background:${lb};color:${lc};">${ico}</span></td>
                  <td style="padding:.45rem .65rem;font-family:monospace;font-size:.72rem;color:var(--text-muted);">${escH(commit)}</td>
                  <td style="padding:.45rem .65rem;color:var(--text-muted);">${escH(log.actor||'—')}</td>
                  <td style="padding:.45rem .65rem;text-align:right;font-family:monospace;color:var(--text-muted);">${ms}</td>
                </tr>`;
            }).join('');
    } catch (e) {
        console.error('Deploy log reload error:', e);
    }
}

// ─── Quick actions ────────────────────────────────────────────────────────────
async function runQuickAction(action) {
    const fd = new FormData();
    fd.append('_csrf', CSRF);
    appendLog('info', 'DSM', `Action: ${action}…`, null);
    try {
        const r    = await fetch(`${BASE_API}/${action}`, { method: 'POST', body: fd });
        const data = await r.json();
        appendLog(data.status, data.label||action, data.message||'', data.duration_ms);
    } catch (e) {
        appendLog('error', action, e.message, null);
    }
}

// ─── Utils ────────────────────────────────────────────────────────────────────
const delay   = ms => new Promise(r => setTimeout(r, ms));
const escH    = s => String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');

// Init Lucide icons on load
document.addEventListener('DOMContentLoaded', () => {
    lucide.createIcons();
    // Init mode info display
    const info = modeInfos[currentMode] || {};
    document.getElementById('mode-info').innerHTML =
        `<strong style="color:var(--text-main);">${escH(info.label||currentMode)}</strong> — ${escH(info.description||'')}` +
        `<br><span style="color:#4f46e5;font-size:.72rem;">${(info.steps||[]).join(' → ')}</span>`;
});
</script>
