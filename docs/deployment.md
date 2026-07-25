# Pipeline de Déploiement — Digitalium Enterprise v2.0

## Vue d'ensemble

```
git push main
    ↓
GitHub Actions — Pre-flight (secrets OK ?)
    ↓
PHP Syntax Check (toujours)
    ↓
SSH → Hostinger
    ├─ [1] Git sync (clone ou pull)
    ├─ [2] Permissions fichiers
    ├─ [3] Master migration + sync_production
    ├─ [4] Cache clear
    ├─ [5] bin/deploy.php (pipeline PHP interne)
    ├─ [6] Vérification intégrité fichiers
    ├─ [7] Smoke tests HTTP (/, /blog, /realisations, /sitemap.xml)
    └─ [8] Résumé + notification
```

---

## Jobs GitHub Actions

### Job 0 — Pre-flight
- Vérifie que les 4 secrets SSH sont configurés
- Si manquants → affiche la liste exacte + lien de configuration
- Bloque tout le reste si un secret est absent

### Job 1 — PHP Syntax Check
- Lance `php -l` sur tous les fichiers `app/`, `database/`, `bin/`
- Bloque le déploiement si une erreur de syntaxe est détectée
- Tournant sur ubuntu-latest avec PHP 8.3

### Job 2 — Production Deploy (SSH)
8 étapes séquentielles :

| # | Étape | Comportement si erreur |
|---|-------|------------------------|
| 1 | Git sync | Fatal — abort |
| 2 | Permissions | Warning — continue |
| 3 | Migrations | Warning — continue |
| 4 | Cache clear | Warning — continue |
| 5 | bin/deploy.php | Warning — continue |
| 6 | Intégrité fichiers | Warning — continue |
| 7 | Smoke tests HTTP | ≥3 échecs → Fatal |
| 8 | Résumé | - |

### Job 3 — Emergency Rollback
- Déclenché uniquement via `workflow_dispatch --mode=rollback`
- Lance `bin/deploy.php --mode=rollback` sur le serveur

---

## Modes de déploiement

| Mode | Description |
|------|-------------|
| `full` | Déploiement complet (défaut) |
| `quick` | Git pull + cache clear uniquement |
| `repair` | Migration + cache + self-heal |
| `rollback` | Restaure le dernier backup SQL |

---

## Premier déploiement (site vide sur Hostinger)

Le pipeline détecte automatiquement si `.git` est absent dans `HOSTINGER_SITE_PATH` :
1. `git init` dans le dossier
2. `git remote add origin` le dépôt GitHub
3. `git fetch` + `git reset --hard origin/main`

Le dossier doit exister et être accessible (créé par Hostinger hPanel ou SSH).

---

## Déploiements suivants

Détecte le `.git` existant et fait :
1. `git stash` des modifications locales éventuelles
2. `git fetch origin main`
3. `git reset --hard origin/main`

---

## Fichiers ignorés par le pipeline (pas de déploiement déclenchant)

```
docs/**
PROJECT_STATE.md
CHANGELOG.md
IMPLEMENTATION_PLAN.md
TECHNICAL_AUDIT.md
RISK_ANALYSIS.md
CMS_MASTER_ARCHITECTURE.md
PROJECT_MAP.md
```

---

## Notifications

### Slack (optionnel)
Configurer le secret `SLACK_WEBHOOK_URL` pour recevoir :
- ✅ Succès : commit, acteur, URL
- ❌ Échec : commit, acteur, lien vers les logs Actions

---

## Logs et journaux

- **GitHub Actions** : https://github.com/KODEX-cloud/Digitalium/actions
- **Logs applicatifs** : `storage/logs/` sur le serveur
- **Historique déploiements** : `/admin/system/deploy-center`

---

## Sécurité

- Aucune information sensible dans les logs
- Secrets GitHub jamais affichés dans les outputs
- SSH key-based authentication uniquement
- `storage/`, `public/uploads/` : permissions strictes
