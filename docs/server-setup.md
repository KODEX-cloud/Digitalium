# Guide de configuration serveur — Digitalium

## Environnement cible

| Composant | Version |
|-----------|---------|
| OS | Linux (Hostinger shared) |
| PHP | 8.3+ |
| Base de données | MySQL 8 / MariaDB 10.6+ |
| Serveur web | Apache (mod_rewrite requis) |
| Git | 2.x |
| SSH | OpenSSH (port 65002 Hostinger) |

---

## Extensions PHP requises

```
pdo
pdo_mysql
mbstring
json
fileinfo
curl
zip
intl
openssl
```

Vérifier via SSH : `php8.3 -m | grep -E "pdo|mbstring|json|curl"`

---

## Configuration Apache (.htaccess)

Le fichier `public/.htaccess` doit contenir :

```apache
Options -Indexes
RewriteEngine On

# Servir les fichiers statiques directement
RewriteCond %{REQUEST_FILENAME} -f [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^ - [L]

# Toutes les autres requêtes vers index.php
RewriteRule ^ index.php [L]
```

Vérifier que `AllowOverride All` est activé pour le dossier.

---

## Structure de fichiers publics

Seul `public/` doit être le DocumentRoot ou être servi :

```
public_html/
└── public/          ← DocumentRoot ou symlink
    ├── index.php
    ├── assets/
    └── uploads/
```

Si Hostinger utilise `public_html` comme racine web, s'assurer que `public/index.php` reçoit bien toutes les requêtes via le `.htaccess`.

---

## Variables d'environnement / Config

Fichier : `config/config.php`

```php
define('ENVIRONMENT', 'production');  // jamais 'development' en prod
define('DB_HOST',     'localhost');
define('DB_NAME',     'nom_de_la_base');
define('DB_USER',     'utilisateur');
define('DB_PASS',     'mot_de_passe');
define('APP_URL',     'https://digitaliumgroup.com');
define('APP_KEY',     'clé_32_chars_minimum');
```

---

## Permissions recommandées

```bash
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;
chmod -R 777 storage/logs storage/cache storage/sessions
chmod -R 755 public/uploads
chmod 644 config/config.php
```

---

## Cron jobs (optionnel)

Si tu veux automatiser certaines tâches, ajouter dans hPanel → Cron Jobs :

```bash
# Nettoyage cache toutes les 24h
0 3 * * * php8.3 /home/UTILISATEUR/domains/digitaliumgroup.com/public_html/bin/clear-cache.php

# Vérification santé hebdomadaire
0 6 * * 1 php8.3 /home/UTILISATEUR/domains/digitaliumgroup.com/public_html/bin/check_frontend.php
```

---

## Test de santé manuel

```bash
ssh -p 65002 UTILISATEUR@IP
cd /chemin/site
php8.3 bin/check_frontend.php
php8.3 -r "require 'config/config.php'; require 'app/Services/Database.php'; \App\Services\Database::connect(); echo 'DB OK';"
```
