<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="refresh" content="60">
<title>Maintenance — Digitalium Group</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body {
  font-family: system-ui, -apple-system, 'Segoe UI', sans-serif;
  background: linear-gradient(135deg, #eef2f8 0%, #f6f5fa 40%, #e0e6ff 100%);
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}
.card {
  background: rgba(255,255,255,0.85);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255,255,255,0.6);
  border-radius: 24px;
  padding: 3.5rem 3rem;
  text-align: center;
  box-shadow: 0 20px 60px rgba(79,70,229,0.1), 0 5px 15px rgba(0,0,0,0.03);
  max-width: 540px;
  width: 100%;
}
.logo {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  margin-bottom: 2.5rem;
}
.logo-icon {
  width: 42px;
  height: 42px;
  background: linear-gradient(135deg,#4f46e5,#7c3aed);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-weight: 900;
  font-size: 1.2rem;
}
.logo-text {
  font-size: 1.3rem;
  font-weight: 700;
  background: linear-gradient(135deg,#4f46e5,#7c3aed);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  letter-spacing: -0.025em;
}
.icon-wrap {
  width: 80px;
  height: 80px;
  background: linear-gradient(135deg, #fef3c7, #fde68a);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1.5rem;
  font-size: 2.2rem;
}
h1 {
  font-size: 1.5rem;
  font-weight: 800;
  color: #1e293b;
  margin-bottom: 0.75rem;
  letter-spacing: -0.025em;
}
p {
  color: #64748b;
  line-height: 1.7;
  font-size: 0.95rem;
  margin-bottom: 0.5rem;
}
.eta {
  display: inline-block;
  margin-top: 1.25rem;
  padding: 0.5rem 1.25rem;
  background: rgba(79,70,229,0.08);
  border: 1px solid rgba(79,70,229,0.15);
  border-radius: 50px;
  font-size: 0.82rem;
  color: #4f46e5;
  font-weight: 600;
}
.pulse {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  margin-top: 1.75rem;
  font-size: 0.78rem;
  color: #94a3b8;
}
.pulse-dot {
  width: 8px;
  height: 8px;
  background: #4f46e5;
  border-radius: 50%;
  animation: pulse 1.5s ease-in-out infinite;
}
@keyframes pulse {
  0%,100% { opacity:1; transform:scale(1); }
  50% { opacity:0.4; transform:scale(0.8); }
}
.contact {
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid rgba(0,0,0,0.06);
  font-size: 0.78rem;
  color: #94a3b8;
}
.contact a {
  color: #4f46e5;
  text-decoration: none;
  font-weight: 500;
}
</style>
</head>
<body>
<div class="card">
  <div class="logo">
    <div class="logo-icon">D</div>
    <span class="logo-text">DIGITALIUM GROUP</span>
  </div>

  <div class="icon-wrap">🔧</div>

  <h1>Maintenance en cours</h1>
  <p>Nous mettons à jour notre plateforme pour vous offrir une meilleure expérience.</p>
  <p>Le site sera de retour très bientôt.</p>

  <div class="eta">Retour estimé : dans quelques minutes</div>

  <div class="pulse">
    <span class="pulse-dot"></span>
    Mise à jour en cours — cette page se recharge automatiquement
  </div>

  <div class="contact">
    Questions urgentes ? <a href="mailto:contact@digitaliumgroup.com">contact@digitaliumgroup.com</a>
  </div>
</div>
</body>
</html>
