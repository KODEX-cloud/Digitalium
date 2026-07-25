# Recovery Center — Digitalium

## Accès

URL : https://digitaliumgroup.com/admin/system/recovery
Authentification admin requise.

---

## Quand utiliser le Recovery Center

- Site inaccessible après déploiement
- Base de données incohérente (tables manquantes, données corrompues)
- Cache corrompu
- Menus disparus
- Settings réinitialisés
- Uploads inaccessibles

---

## Pipeline de restauration (11 phases)

| # | Phase | Action |
|---|-------|--------|
| 1 | BootCheck | Vérifie PHP, constantes, .env, connexion DB |
| 2 | Backup SQL | Crée un backup avant toute modification |
| 3 | Master Migration | Recrée les tables manquantes (idempotent) |
| 4 | Sync Production | Synchronise les données de production |
| 5 | Cache Clear | Vide intégralement le cache |
| 6 | Asset Verify | Vérifie les assets CSS/JS |
| 7 | Upload Verify | Vérifie le dossier uploads |
| 8 | Menu Rebuild | Reconstruit l'arbre des menus |
| 9 | Settings Sync | Synchronise les settings globaux |
| 10 | Health Check | Score de santé minimum 5/10 requis |
| 11 | Smoke Tests | Teste /, /blog, /realisations, /sitemap.xml |

En cas d'erreur critique : **rollback SQL automatique** vers le backup créé en phase 2.

---

## Diagnostics temps réel (16 checks)

Accessibles via le bouton **"Diagnostic"** avant de lancer la restauration :

1. Version PHP
2. Constantes applicatives
3. Fichier .env / config
4. Connexion SQL
5. Tables (10 vérifications)
6. menus.location
7. Nombre de routes
8. Handler index.php
9. Assets CSS/JS
10. Dossier uploads
11. Cache
12. Menus
13. Settings
14. Hero slides
15. Permissions
16. Autoloader

---

## Mode Maintenance

Le Recovery Center permet d'activer/désactiver le mode maintenance :
- **Activer** : crée `storage/maintenance.lock` — affiche une page de maintenance
- **Désactiver** : supprime `storage/maintenance.lock`

---

## Version CLI (backup si navigateur inaccessible)

```bash
ssh -p 65002 UTILISATEUR@IP_HOSTINGER
cd /home/UTILISATEUR/domains/digitaliumgroup.com/public_html
php8.3 bin/recover-production.php
```

---

## API Recovery (JSON)

```
GET  /admin/api/system/recovery-diagnostic  → 16 diagnostics
POST /admin/api/system/recovery-run         → pipeline complet
POST /admin/api/system/recovery-maintenance → toggle maintenance.lock
```
