# CHANGELOG — Digitalium CMS Enterprise

Toutes les modifications notables de ce projet sont documentées ici.  
Format : [Sémantique de version](https://semver.org/lang/fr/)

---

## [1.4.0-enterprise-cicd] — 2026-07-05

### Enterprise CI/CD Pipeline — Déploiement Automatisé Complet

#### GitHub Actions
- **`.github/workflows/deploy.yml`** — Pipeline CI/CD complet :
  - Job 1 : PHP Syntax Validation (PHP 8.3, scan `app/`, `database/`, `bin/`)
  - Job 2 : SSH Production Deploy (`appleboy/ssh-action`) → git pull → migrations → `bin/deploy.php`
  - Job 3 : Emergency Rollback (`workflow_dispatch --mode=rollback`)
  - Smoke tests HTTP depuis GitHub (/, /blog, /admin, /sitemap.xml)
  - Notifications Slack (optionnel)
  - `concurrency: production-deploy` — empêche les déploiements simultanés
  - Secrets à configurer : `HOSTINGER_SSH_HOST`, `HOSTINGER_SSH_PORT`, `HOSTINGER_SSH_USER`, `HOSTINGER_SSH_KEY`, `HOSTINGER_SITE_PATH`, `HOSTINGER_PHP_BIN`, `APP_URL`

#### Nouveaux Managers
- **`app/System/RollbackManager`** — Backup SQL + config + restore :
  - `create()` : dump PDO complet (TRUNCATE + INSERT par chunks de 100), copie config.php
  - `restore(id)` : `SET FOREIGN_KEY_CHECKS=0` + statements dans transaction + `FOREIGN_KEY_CHECKS=1`
  - `list()` / `getLatestId()` : inventaire des backups (max 10 conservés)
  - Stockage : `storage/backups/backup_YYYY-MM-DD_HH-II-SS/{database.sql, manifest.json, config.php.bak}`

- **`app/System/DeploymentLog`** — Journal persistant JSON des déploiements :
  - `record(data)` : sauvegarde `{id}.json` dans `storage/deployments/` (max 50 logs)
  - `getAll(limit)` / `getLatest()` / `getById(id)` : lecture chronologique inverse

#### CLI Orchestrateur Enterprise
- **`bin/deploy.php`** — 11 phases, idempotent :
  1. BootCheck (abort si critique)
  2. RollbackManager::create() — point de sauvegarde avant toute modification
  3. Master Migration inline — DDL tables critiques
  4. SyncProductionManager::run() — sync schéma complet
  5. CacheManager::clear() + AssetManager::check()
  6. SelfHealManager::run()
  7. HealthManager::check() (abort si score < 5)
  8. RouteManager::scan() + vérification Settings
  9. Smoke Tests HTTP (/, /admin, /blog, /sitemap.xml)
  10. Auto-rollback si erreur critique (RollbackManager::restore)
  11. DeploymentLog::record() — journalisation
  - Exit codes : 0 = succès, 1 = critique + rollback, 2 = warning
  - Options : `--mode=full|quick|repair|rollback`, `--dry-run`, `--no-rollback`, `--base-url=URL`

#### Intégration Deploy Center
- **`SystemApiController`** — Nouveaux endpoints :
  - `POST /admin/api/system/rollback-latest` → `RollbackManager::restore(latest)`
  - `GET /admin/api/system/deploy-log` → `DeploymentLog::getAll(limit)`
  - `deploy()` enregistre maintenant automatiquement dans `DeploymentLog` (commit, acteur, mode, durée, statut)

- **`/admin/system/deploy-center`** — Améliorations :
  - Section "Historique des déploiements" — tableau des 10 derniers deploys (date, mode, statut, commit, acteur, durée)
  - Section "Rollback d'urgence" — bouton avec confirmation + affichage dernier backup disponible
  - Fonctions JS : `rollbackLatest()`, `reloadDeployLog()` avec refresh AJAX

#### Routes ajoutées
- `POST /admin/api/system/rollback-latest`
- `GET /admin/api/system/deploy-log`

---

## [1.3.0-fault-tolerant] — 2026-07-04

### Enterprise Fault Tolerant — 12 phases de hardening production

#### Modules créés
- **`app/Services/ErrorHandler`** — Handler global exceptions + shutdown (Phase 7)
- **`app/Services/BootCheck`** — 7 checks pré-démarrage isolés (Phase 8)
- **`app/System/SyncProductionManager`** — Sync schéma DB production (inspect/diff/run)
- **`database/sync_production.php`** — CLI wrapper idempotent
- **`app/Controllers/SyncProductionController`** — Dashboard `/admin/system/sync-production`
- **`app/Views/errors/500.php`** / **`503.php`** — Pages d'erreur brandées

#### Modifications critiques
- **`public/index.php`** — Plus aucune stack trace publique, `set_exception_handler` + `register_shutdown_function`
- **`app/Services/Database`** — `fetch()` + `fetchAll()` fault-tolerant : try/catch → log → null/[] en production
- **`app/Controllers/HomeController`** — `renderPage()` wrappé dans try/catch, isolation par section
- **`app/System/DeployPipeline`** — Pre-deploy BootCheck (abort sur critique en mode `production|full`)

#### Root cause incident production
- **Cause exacte** : colonne `menus.location` absente → requête `SELECT * FROM menus WHERE location = :l LIMIT 1` échoue → exception propagée → stack trace publique
- **Fix niveau 1** : `Database::fetch()` swallow en production
- **Fix niveau 2** : `SyncProductionManager` ajoute la colonne idempotement
- **Preuve** : `php database/sync_production.php` → 84 skip / 5 corrections / 0 erreurs

---

## [1.2.0-dsm-os] — 2026-06-27

### DSM Operating System — Cœur Technique Officiel

#### Nouveaux Managers (DSM OS v1.2)
- **`DeployPipeline`** — Orchestrateur multi-modes : Quick / Full / Production / Repair / Audit / Development / Safe / Rollback. Chaque mode = séquence de step-keys avec dispatch unifié.
- **`SelfHealManager`** — Moteur d'auto-réparation : storage dirs, log file, cache corrompu, settings manquants, menus, SEO, uploads orphelins, routes cassées, permissions.
- **`GitManager`** — Opérations Git complètes : `getInfo()`, `commitAndPush()`, `createTag()`, `updateChangelog()`, `healthCheck()`.
- **`PerformanceManager`** — Tests de performance HTTP sur routes clés, audit assets CSS/JS/images avec seuils FAST/SLOW/CRITICAL.

#### API Interne DSM
- **`SystemApiController`** — 13 endpoints `/admin/api/system/*` (JSON, Auth + CSRF + Logs) :
  `deploy`, `migrate`, `cache`, `repair`, `health`, `audit`, `rollback`, `git`, `performance`, `backup`, `status`, `modes`, `heal`

#### CLI Multi-Plateforme
- **`bin/dsm_cli.php`** — Runner CLI compatible Cron, SSH, GitHub Actions, Webhook :
  `deploy [mode]`, `health`, `migrate`, `business-migrate`, `heal`, `backup`, `git:info`, `git:commit`, `git:tag`, `modes`, `--json`

#### Interface Deploy Center
- **`/admin/system/deploy-center`** — Interface Enterprise :
  - Panel système (version, git, branch, commit, score)
  - Sélecteur de mode (8 modes avec description et step count)
  - Bouton 🚀 DEPLOY unique
  - Terminal live-log avec animation step-by-step
  - Health Dashboard avec refresh AJAX
  - Statistiques CMS en temps réel (pages, articles, médias, projets)

#### Routes
- `GET /admin/system/deploy-center` — Deploy Center
- `GET /admin/api/system/modes` — Liste des modes JSON
- 12 endpoints POST `/admin/api/system/*`

#### Sidebar Admin
- Lien "Deploy Center" avec icône Rocket dans la navigation admin

---

## [1.0.0-enterprise] — 2026-06-27

### Ajouté
- **Module Commentaires admin** : vue `/admin/blog/comments` avec modération complète (approuver / rejeter / supprimer)
- **Couleurs admin** : 5 variables CSS (`--primary`, `--accent`, `--text-main`, `--text-muted`, `--bg-base`) configurables depuis Admin → Configuration
- **Scripts admin** : champs `header_scripts`, `footer_scripts`, `custom_css` dans le panneau Configuration
- **Contact form — options DB-driven** : champs bloc `services_primary_list` et `services_extra_list` remplacent les options HTML hardcodées
- **Labels UI admin-éditables** : `whatsapp_btn_label`, `social_section_title`, `social_section_subtitle`, `map_office_label`, `coordonnees_title`
- **Sidebar admin** : badge orange commentaires en attente sur l'item "Blog" + item "Commentaires" dédié
- **Fichiers gouvernance** : `RELEASE_NOTES.md`, `CHANGELOG.md` créés
- **`.env` local** créé (gitignored) pour configuration développement
- **`.env.example`** mis à jour avec procédure Hostinger détaillée

### Corrigé
- **B-01 CRITIQUE** : `onclick="window.location.href='/contact'"` supprimé du bouton submit — ce bug bloquait l'envoi AJAX du formulaire contact
- **B-01** : `fetch('/contact')` corrigé → `fetch('<?= url('/contact') ?>')` — assure la compatibilité sous-répertoire/racine
- **B-04** : `.env` production documenté — `APP_ENV=production` active le cache fichier

### Supprimé
- `app/Models/Settings.php` — doublon de `Setting.php` (DT-01), zéro références, supprimé proprement

### Modifié
- `app/Views/frontend/sections/contact_details.php` — refactorisé complet (B-01 + B-02 + B-02b)
- `app/Views/frontend/layout.php` — injection CSS variables + custom_css + header_scripts + footer_scripts
- `app/Views/admin/settings.php` — ajout sections "Scripts & CSS" et "Couleurs & Thème"
- `app/Views/admin/layout.php` — badge commentaires sidebar + item "Commentaires"
- `app/Controllers/BlogController.php` — méthodes `commentsIndex`, `approveComment`, `rejectComment`, `deleteComment`
- `app/Models/Comment.php` — méthode `delete()` ajoutée
- `routes/web.php` — routes commentaires admin ajoutées
- `.env.example` — documentation Hostinger complète

---

## [0.9.0] — 2026-06-24

### Ajouté
- Module Menus DB-driven (CRUD + drag-drop + multi-level)
- Module Messages Contact (inbox admin avec archivage)
- Module Réalisations public (`/realisations` + `/{slug}`)
- Blog : Tag model + Comment model
- Blog : endpoint `POST /blog/comment` avec honeypot
- Page 404 stylée avec layout complet
- `render404()` avec header/footer frontend
- Dashboard : 5 stat cards dynamiques
- Sidebar admin : Messages badge + Navigation item
- Gouvernance : `CLAUDE.md` (8 règles Enterprise), `PROJECT_STATE.md`

### Corrigé
- Ordre des routes (réalisations AVANT catch-all `/{slug}`)
- Logs SQL désactivés en production

---

## [0.8.0] — 2026-06-23

### Ajouté
- Refactoring identité visuelle complète
- Typographie premium + transitions header
- Hero carousel avec slides administrables
- Pages uniques avec configuration hero per-page

---

## [0.1.0 → 0.7.0] — Antérieur

- MVC PHP natif (Router, Database, Auth, CSRF, Cache, Session)
- Admin login + dashboard
- Pages CMS (CRUD + sections + blocs)
- Blog CRUD
- Projets CRUD
- Médias upload
- Settings globaux
