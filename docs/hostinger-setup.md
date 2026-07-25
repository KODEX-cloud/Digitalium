# Préparation du serveur Hostinger — Digitalium

## Prérequis Hostinger

- Plan Hostinger **Business** ou supérieur (accès SSH requis)
- Domaine `digitaliumgroup.com` pointant vers l'hébergement
- PHP 8.3 activé dans hPanel

---

## Étape 1 — Activer SSH

1. hPanel → **SSH Access** → activer SSH
2. Générer ou importer une clé publique SSH
3. Noter : IP, port (65002), nom d'utilisateur

---

## Étape 2 — Configurer PHP 8.3

1. hPanel → **PHP Configuration**
2. Sélectionner PHP 8.3
3. Activer les extensions :
   - `pdo_mysql`
   - `mbstring`
   - `json`
   - `fileinfo`
   - `curl`
   - `zip`

---

## Étape 3 — Créer le dossier site

Via hPanel File Manager ou SSH :

```bash
# Si le dossier public_html est le site principal
ls /home/UTILISATEUR/domains/digitaliumgroup.com/public_html/

# Sinon créer un sous-dossier
mkdir -p /home/UTILISATEUR/domains/digitaliumgroup.com/public_html
```

---

## Étape 4 — Base de données

1. hPanel → **Databases** → **MySQL Databases**
2. Créer une base : `digitalium_db` (ou adapter le nom)
3. Créer un utilisateur MySQL avec tous les droits sur cette base
4. Noter : host, nom BDD, utilisateur, mot de passe

### Fichier .env ou config/database.php

Le CMS lit sa configuration depuis `config/config.php`. S'assurer que les valeurs correspondent à la base Hostinger :

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'u839163661_digitalium');
define('DB_USER', 'u839163661_user');
define('DB_PASS', 'MOT_DE_PASSE');
```

> Sur Hostinger, le nom de la base est souvent préfixé par l'identifiant utilisateur.

---

## Étape 5 — Premier déploiement

Une fois les secrets GitHub configurés (voir `docs/github-secrets.md`) :

1. https://github.com/KODEX-cloud/Digitalium/actions
2. **"Run workflow"** → mode `full`
3. Le pipeline clone automatiquement le dépôt sur le serveur

---

## Vérification post-déploiement

```bash
# Via SSH sur Hostinger
cd /home/UTILISATEUR/domains/digitaliumgroup.com/public_html
ls -la
php8.3 -v
php8.3 bin/deploy.php --mode=quick
```

---

## Structure attendue sur le serveur

```
public_html/
├── .git/                   # Dépôt Git (géré automatiquement)
├── app/
├── bin/
├── config/
├── database/
├── docs/
├── public/
│   ├── index.php           # SEUL point d'entrée public
│   ├── assets/
│   └── uploads/
├── routes/
├── storage/
│   ├── cache/              # chmod 777
│   ├── logs/               # chmod 777
│   └── sessions/           # chmod 777
└── .github/
```

---

## Maintenance — Mode maintenance

Activer via Recovery Center : `/admin/system/recovery`
Ou créer le fichier : `storage/maintenance.lock`
Désactiver en supprimant ce fichier.
