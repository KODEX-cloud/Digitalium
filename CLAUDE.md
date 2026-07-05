# CLAUDE.md — Digitalium Group CMS
> Source de vérité principale. À lire AVANT toute tâche. Ne pas modifier sans validation CTO.
> Dernière mise à jour : 2026-06-24 — Niveau Enterprise

---

## 1. IDENTITÉ DU PROJET

**Nom :** Digitalium Group CMS  
**Type :** CMS PHP natif MVC — sans framework  
**Environnement :** WAMP64 / Windows — PHP 8.3.28  
**Base de données :** MySQL — `digitalium_db`  
**URL locale :** `http://localhost/Digitalium/`  
**URL production :** `https://digitaliumgroup.com`  
**Admin :** `/admin` (auth session)  
**PHP CLI :** `C:\wamp64\bin\php\php8.3.28\php.exe`

---

## 2. ARCHITECTURE MVC

```
Digitalium/
├── app/
│   ├── Controllers/     # Logique métier
│   ├── Models/          # Accès données (PDO)
│   ├── Views/
│   │   ├── admin/       # Backend administrable
│   │   └── frontend/    # Site public
│   ├── Services/        # Router, Database, Auth, Cache, CSRF
│   └── Helpers/         # Validator, Sanitizer, IconHelper
├── public/              # index.php UNIQUEMENT — point d'entrée
├── routes/web.php       # Toutes les routes (ordre = priorité)
├── database/            # Migrations CLI uniquement
├── bin/                 # Scripts utilitaires CLI
├── storage/logs/        # Logs applicatifs
└── config/              # app.php, database.php
```

---

## 3. SERVICES CRITIQUES

| Service | Fichier | Rôle |
|---|---|---|
| Router | `app/Services/Router.php` | first-match-wins — ordre routes = loi |
| Database | `app/Services/Database.php` | Singleton PDO — query/fetch/fetchAll/insert |
| Auth | `app/Services/Auth.php` | Session-based — `Auth::check()` |
| CSRF | `app/Services/CSRF.php` | Double validation : Router (POST global) + Controller |
| Cache | `app/Services/Cache.php` | File-based — `Cache::clear()` après chaque save |

---

## 4. CONVENTIONS OBLIGATOIRES

### Controllers
- Héritent de `Controller` (base)
- Auth : toujours appeler `$this->middlewareAuth()` en première ligne
- CSRF POST : toujours appeler `$this->validateCsrf()`
- Render : `$this->render('view/path', $data, 'layout/path')`
- Redirect : `$this->redirect('/chemin', 'success|error', 'message')`
- JSON : `$this->json($array, $statusCode)`

### Models
- Héritent de `Model` (base)
- Accès DB : uniquement via `Database::query()`, `::fetch()`, `::fetchAll()`, `::insert()`
- Pas de SQL dans les Controllers ni les Views

### Views — Admin
- Layout : `admin/layout` — toujours passer `currentUser`, `csrf_token`, `title`
- Styles : variables CSS `--primary`, `--border`, `--bg-surface`, `--text-muted`, `--text-main`
- Icons : Lucide via CDN (`data-lucide="nom"`)

### Views — Frontend
- Layout : `frontend/layout` — toujours passer `page`, `settings`, `menuPages`, `currentSlug`
- Helpers : `url('/chemin')` pour tous les liens
- `htmlspecialchars()` sur toute sortie utilisateur

### Routes
- Ordre strict : routes spécifiques AVANT les routes avec paramètres
- `/realisations` et `/realisations/{slug}` AVANT `/{slug}`
- `/blog` et `/blog/{slug}` AVANT `/{slug}`
- `/{slug}` est le catch-all — toujours en DERNIER

---

## 5. RÈGLE ABSOLUE — PHILOSOPHIE CMS

**Aucun texte, image, lien, menu, section ou contenu visible sur le frontend ne doit être hardcodé.**

Tout doit être administrable depuis le backend.

---

## 6. SÉCURITÉ

- CSRF : validation globale sur tous les POST (Router) + validation manuelle dans Controller
- Honeypot : champ `website` dans le formulaire contact — ignorer silencieusement si rempli
- Scripts publics dangereux : INTERDITS dans `/public` — uniquement `index.php`
- Logs SQL : désactivés en production (`ENVIRONMENT !== 'development'`)
- Toutes les sorties utilisateur : `htmlspecialchars()` obligatoire

---

## 7. BASE DE DONNÉES — TABLES ACTIVES

| Table | Usage |
|---|---|
| `users` | Authentification admin |
| `pages` | CMS pages + hero engine |
| `sections` | Sections de pages |
| `blocks` | Blocs de contenu (key/value + groups) |
| `settings` | Paramètres globaux clé/valeur |
| `media` | Bibliothèque fichiers uploadés |
| `hero_slides` | Slides du hero carousel |
| `projects` | Réalisations portfolio |
| `blog_posts` | Articles de blog |
| `blog_categories` | Catégories blog |
| `blog_tags` | Tags blog |
| `blog_post_tags` | Pivot posts ↔ tags |
| `blog_comments` | Commentaires blog (modération) |
| `contact_messages` | Messages du formulaire contact |
| `menus` | Menus de navigation (location-based) |
| `menu_items` | Items de menu (arbre parent_id) |

**Migration de référence :** `database/master_migration.php` (idempotente — CLI uniquement)

---

## 8. ROUTES ACTIVES

### Frontend public
```
GET  /                         → HomeController@index
GET  /sitemap.xml              → HomeController@sitemap
POST /contact                  → HomeController@contactSubmit
GET  /blog                     → BlogController@frontendIndex
GET  /blog/{slug}              → BlogController@frontendPost
POST /blog/comment             → BlogController@submitComment
GET  /realisations             → ProjectController@publicIndex
GET  /realisations/{slug}      → ProjectController@publicShow
GET  /{slug}                   → HomeController@renderPage          (catch-all)
```

### Admin
```
/admin, /admin/dashboard, /admin/login, /admin/logout
/admin/settings
/admin/pages + CRUD + sections/blocks AJAX
/admin/projects + CRUD
/admin/blog + CRUD + /categories
/admin/blog/comments
/admin/media
/admin/messages + show/archive/delete
/admin/menus + edit + items/save
/admin/system/deploy-center       → Deploy Center + historique + rollback
/admin/system/sync-production     → SyncProductionManager + BootCheck
/admin/api/system/*               → 15 endpoints JSON (deploy, health, rollback-latest, deploy-log, …)
```

---

## 8b. PIPELINE CI/CD (v1.4)

```
Git push main
  → GitHub Actions (syntax check PHP)
  → SSH Hostinger (appleboy/ssh-action)
    → git pull origin main
    → php database/master_migration.php
    → php database/sync_production.php
    → php bin/deploy.php --mode=full
        ├── BootCheck (abort si critique)
        ├── RollbackManager::create() (backup SQL)
        ├── SyncProductionManager::run()
        ├── CacheManager::clear()
        ├── HealthManager::check() (abort si score < 5 → rollback)
        ├── Smoke Tests HTTP
        └── DeploymentLog::record()
  → Notification Slack (optionnel)
```

**Secrets GitHub requis :**
- `HOSTINGER_SSH_HOST`, `HOSTINGER_SSH_PORT`, `HOSTINGER_SSH_USER`, `HOSTINGER_SSH_KEY`
- `HOSTINGER_SITE_PATH`, `HOSTINGER_PHP_BIN`, `APP_URL`

**Rollback d'urgence :** `workflow_dispatch --mode=rollback` ou bouton dans `/admin/system/deploy-center`

---

## 9. DETTE TECHNIQUE ACTIVE

| Ref | Description | Priorité |
|---|---|---|
| DT-01 | `Models/Settings.php` doublon de `Models/Setting.php` | Faible |
| DT-02 | `blog_posts.tags` (texte) vs `blog_post_tags` (table) — double source | Moyen |
| DT-03 | `contact_email` absente du panneau Settings admin | Moyen |

---

## 10. WORKFLOW OBLIGATOIRE CTO

### Avant toute tâche
1. Lire CLAUDE.md (ce fichier)
2. Lire PROJECT_STATE.md
3. Identifier les fichiers concernés
4. Analyser les dépendances et risques

### Pendant la tâche
- Une modification à la fois
- Pas de restructuration massive
- Préserver l'existant

### Après chaque tâche importante
- Mettre à jour PROJECT_STATE.md

### Sous-agents : UNIQUEMENT pour
- Audit global complet
- Refactoring massif multi-fichiers
- Architecture système complexe
- Analyse de sécurité
- Migration majeure de données

---

## 11. RÈGLES ENTERPRISE — NIVEAU CTO

---

### RÈGLE #1 — ARCHITECTURE AVANT CODE

Avant toute modification, vérifier dans cet ordre :

1. Lire `CLAUDE.md`
2. Lire `PROJECT_STATE.md`
3. Vérifier les routes existantes dans `routes/web.php`
4. Vérifier les contrôleurs existants dans `app/Controllers/`
5. Vérifier les modèles existants dans `app/Models/`
6. Vérifier les tables SQL existantes (section §7 de ce fichier)
7. Vérifier les vues existantes dans `app/Views/`

**Interdiction absolue de créer un doublon si l'élément existe déjà.**

---

### RÈGLE #2 — ZERO HARDCODE

Tout élément visible sur le frontend doit être administrable depuis le backend.

**Interdit :**
- Textes statiques (titres, descriptions, CTA)
- Menus codés en dur
- Images en dur dans les vues
- Statistiques fixes
- Coordonnées, adresses, emails
- Réseaux sociaux
- Couleurs de marque (si non gérées via Settings)

**Flux obligatoire :** `Backend → Table SQL → Frontend`

---

### RÈGLE #3 — AVANT TOUTE CORRECTION

Pour toute correction non triviale, produire **avant** de toucher au code :

- **TECHNICAL_AUDIT** : identification précise du problème, fichier + ligne
- **RISK_ANALYSIS** : impacts sur les autres modules, régressions possibles
- **IMPLEMENTATION_PLAN** : étapes numérotées, fichiers à modifier, ordre d'exécution

Exception : corrections triviales (typo, CSS mineur) → action directe autorisée.

---

### RÈGLE #4 — AVANT TOUTE SUPPRESSION

Rechercher **toutes les dépendances** avant toute suppression.

Ne jamais supprimer sans analyse d'impact :
- Un fichier PHP (contrôleur, modèle, vue)
- Une table SQL ou une colonne
- Une route déclarée
- Un paramètre de `settings`

**Procédure :** Grep du nom dans tout le projet → lister les références → valider l'absence d'usage → supprimer.

---

### RÈGLE #5 — OBLIGATION DE PREUVE

Toute correction déclarée comme "faite" doit produire :

- **Fichier modifié** : chemin exact
- **Ligne modifiée** : numéro ou extrait avant/après
- **Test exécuté** : commande ou URL vérifiée
- **Résultat obtenu** : réponse HTTP, output CLI, ou comportement observé

**Les affirmations sans preuve sont interdites.**

---

### RÈGLE #6 — PROJECT_STATE obligatoire

Après chaque tâche importante, mettre à jour `PROJECT_STATE.md` avec :

- Routes ajoutées/modifiées
- Tables SQL créées/modifiées
- Modules ajoutés/complétés
- Bugs corrigés (ref + description)
- Dette technique restante (ref + statut)

---

### RÈGLE #7 — OBJECTIF CMS ENTERPRISE

Mission permanente : transformer Digitalium en CMS Enterprise complet.

Toutes les fonctionnalités doivent être :
- **Administrables** depuis le backend
- **Éditables** sans toucher au code
- **Configurables** via le panneau Settings
- **Extensibles** sans régression sur l'existant

---

### RÈGLE #8 — PRÉVENTION DES ERREURS HISTORIQUES

Les situations suivantes sont des incidents critiques à éviter absolument :

| Situation | Prévention |
|---|---|
| Route 404 inattendue | Vérifier l'ordre dans `routes/web.php` avant tout ajout |
| Routes dupliquées | Grep avant création |
| Tables dupliquées | Vérifier §7 de ce fichier avant migration |
| Modèles dupliqués | Glob `app/Models/` avant création |
| Page admin cassée | Toujours tester la vue après modification du Controller |
| Paramètre sauvegardé mais non affiché | Vérifier que la vue lit bien la clé `$settings['clé']` |
| Frontend déconnecté du backend | Tracer le flux : Settings → Controller → View pour chaque donnée |
| Upload perdu | Ne jamais modifier `MediaController` sans test d'upload complet |

---

## 12. IDENTITÉ CTO

```
Rôle     : CTO Principal Digitalium Group
Profil   : Architecte Logiciel Senior — 15+ ans
Domaines : PHP MVC natif · CMS Enterprise · DevOps · UX/UI · Vibe Coding
Mission  : Construire une plateforme Enterprise stable, maintenable, totalement administrable
Posture  : Construire — pas corriger. Architecturer — pas patcher.
```

---

## 14. FICHIERS DE GOUVERNANCE

| Fichier | Rôle |
|---|---|
| `CLAUDE.md` | Ce fichier — source de vérité |
| `PROJECT_STATE.md` | Mémoire technique permanente |
| `CMS_MASTER_ARCHITECTURE.md` | Architecture SQL + routes cible |
| `IMPLEMENTATION_PLAN.md` | Plan P0/P1/P2/P3 priorisé |
| `TECHNICAL_AUDIT.md` | Audit bugs et risques identifiés |
| `PROJECT_MAP.md` | Arborescence + mapping controllers/routes |
| `RISK_ANALYSIS.md` | Matrice des risques |
