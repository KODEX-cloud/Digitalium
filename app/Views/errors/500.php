<?php
// DIAGNOSTIC TEMPORAIRE — À supprimer après identification de l'erreur
if (($_GET['_dg_diag'] ?? '') === 'dg2026secret') {
    $root    = defined('ROOT_PATH') ? ROOT_PATH : dirname(dirname(dirname(__FILE__)));
    $logPath = $root . '/storage/logs/errors.log';
    header('Content-Type: text/plain; charset=UTF-8');
    if (file_exists($logPath)) {
        $lines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $last  = array_slice($lines, -30);
        echo "=== LAST 30 LINES OF errors.log ===\n\n";
        echo implode("\n", $last);
    } else {
        echo "errors.log NOT FOUND at: " . $logPath;
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Service temporairement indisponible — Digitalium Group</title>
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family: system-ui, -apple-system, sans-serif; background: linear-gradient(135deg,#eef2f8,#e0e6ff); min-height:100vh; display:flex; align-items:center; justify-content:center; }
  .card { background:#fff; border-radius:20px; padding:3rem 2.5rem; text-align:center; box-shadow:0 8px 32px rgba(79,70,229,.12); max-width:520px; width:90%; }
  .logo { font-size:2.5rem; margin-bottom:1rem; }
  .code { font-size:5rem; font-weight:900; background:linear-gradient(135deg,#4f46e5,#7c3aed); -webkit-background-clip:text; -webkit-text-fill-color:transparent; line-height:1; }
  h1 { font-size:1.3rem; color:#1e293b; margin:.75rem 0 .5rem; font-weight:700; }
  p  { color:#64748b; line-height:1.7; font-size:.95rem; }
  .btn { display:inline-block; margin-top:1.75rem; padding:.75rem 1.75rem; background:#4f46e5; color:#fff; text-decoration:none; border-radius:10px; font-weight:600; transition:.2s; }
  .btn:hover { background:#7c3aed; }
  .ref { margin-top:1.25rem; font-size:.72rem; color:#94a3b8; font-family:monospace; }
</style>
</head>
<body>
<div class="card">
  <div class="logo">⚡</div>
  <div class="code">500</div>
  <h1>Service temporairement indisponible</h1>
  <p>Nous rencontrons une difficulté technique. Notre équipe est notifiée automatiquement et travaille à rétablir le service.</p>
  <p style="margin-top:.75rem;">Merci de réessayer dans quelques instants.</p>
  <a href="/" class="btn">Retourner à l'accueil</a>
  <div class="ref">Ref: <?= date('YmdHis') ?></div>
</div>
</body>
</html>
