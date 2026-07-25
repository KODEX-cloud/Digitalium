# Configuration des Secrets GitHub — Digitalium

## URL directe

https://github.com/KODEX-cloud/Digitalium/settings/secrets/actions

## Secrets requis

| Secret | Description | Exemple | Obligatoire |
|--------|-------------|---------|-------------|
| `HOSTINGER_SSH_HOST` | IP ou hostname SSH de ton hébergement | `145.14.112.53` | ✅ |
| `HOSTINGER_SSH_PORT` | Port SSH Hostinger | `65002` | ✅ |
| `HOSTINGER_SSH_USER` | Utilisateur SSH | `u839163661` | ✅ |
| `HOSTINGER_SSH_KEY` | Clé privée SSH complète | `-----BEGIN OPENSSH...` | ✅ |
| `HOSTINGER_SITE_PATH` | Chemin absolu du site sur le serveur | `/home/u839163661/domains/digitaliumgroup.com/public_html` | ✅ |
| `HOSTINGER_PHP_BIN` | Binaire PHP sur le serveur | `php8.3` ou `/usr/local/bin/php` | ✅ |
| `APP_URL` | URL publique sans slash final | `https://digitaliumgroup.com` | ✅ |
| `SLACK_WEBHOOK_URL` | Webhook Slack pour notifications | `https://hooks.slack.com/...` | ❌ optionnel |

---

## Comment trouver chaque valeur

### HOSTINGER_SSH_HOST
1. Hostinger hPanel → **SSH Access** (menu de gauche)
2. Copier l'**IP** ou le **hostname** affiché

### HOSTINGER_SSH_PORT
- Hostinger utilise le port **65002** par défaut (pas 22)
- Confirmer dans hPanel → SSH Access

### HOSTINGER_SSH_USER
- Visible dans hPanel → SSH Access
- Format habituel : `u839163661` (ton identifiant Hostinger)

### HOSTINGER_SSH_KEY
1. Sur ta machine locale, générer une clé si tu n'en as pas :
   ```bash
   ssh-keygen -t ed25519 -C "github-deploy-digitalium" -f ~/.ssh/digitalium_deploy
   ```
2. Copier la **clé publique** (`digitalium_deploy.pub`) dans Hostinger hPanel → SSH Keys → Add SSH Key
3. Copier la **clé privée complète** (`digitalium_deploy`) dans ce secret GitHub
   - Elle commence par `-----BEGIN OPENSSH PRIVATE KEY-----`
   - Elle se termine par `-----END OPENSSH PRIVATE KEY-----`
   - Inclure TOUT le contenu avec les lignes de début et fin

### HOSTINGER_SITE_PATH
- Format : `/home/UTILISATEUR/domains/digitaliumgroup.com/public_html`
- Ou via SSH : `pwd` depuis le dossier du site
- Hostinger hPanel → File Manager → naviguer jusqu'au dossier du site → noter le chemin

### HOSTINGER_PHP_BIN
- Via SSH : `which php8.3` ou `which php`
- Hostinger : généralement `php8.3` ou `/usr/local/bin/php8.3`
- Tester : `php8.3 -v` pour confirmer la version

### APP_URL
- `https://digitaliumgroup.com` (sans slash final)

---

## Procédure d'ajout dans GitHub

1. Aller sur : https://github.com/KODEX-cloud/Digitalium/settings/secrets/actions
2. Cliquer **"New repository secret"**
3. Remplir **Name** (ex: `HOSTINGER_SSH_HOST`) et **Value** (la valeur réelle)
4. Cliquer **"Add secret"**
5. Répéter pour chaque secret

---

## Test de connexion SSH

Avant de configurer les secrets, tester la connexion SSH manuellement :

```bash
ssh -i ~/.ssh/digitalium_deploy -p 65002 u839163661@IP_HOSTINGER
```

Si la connexion fonctionne, la clé est correcte et peut être ajoutée comme secret.

---

## Déclencher le premier déploiement après config

1. Aller sur : https://github.com/KODEX-cloud/Digitalium/actions
2. Cliquer **"Deploy — Digitalium Group CMS"**
3. Cliquer **"Run workflow"** → branche `main` → mode `full`
4. Observer les logs en temps réel

---

## Rollback d'urgence

En cas de problème sur le site :
1. Aller sur : https://github.com/KODEX-cloud/Digitalium/actions
2. **"Run workflow"** → mode `rollback`

Ou depuis l'admin : `/admin/system/deploy-center` → bouton Rollback
