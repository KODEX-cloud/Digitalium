# TECHNICAL_AUDIT — Digitalium Group CMS
> Produit le 2026-06-24 — audit de l'état réel du code, sans hypothèses.

---

## LÉGENDE

- 🔴 **CRITIQUE** — bloque en production / sécurité
- 🟠 **ÉLEVÉ** — fonctionnalité cassée ou données manquantes
- 🟡 **MOYEN** — UX dégradé ou dette technique
- 🟢 **FAIBLE** — amélioration, non bloquant

---

## SECTION 1 : SÉCURITÉ

### 🔴 SEC-01 — Fichiers dangereux accessibles publiquement

**Fichiers concernés :**
- `public/change_admin_prod.php` — modifie l'admin en production
- `public/seed_prod.php` — seed la base de données en production
- `public/run_blog_migration.php` — exécute la migration blog
- `public/run_pages_migration.php` — exécute la migration pages
- `public/read_logs.php` — expose les logs serveur (emails, IPs, messages contact)

**Impact :** N'importe qui peut accéder à `https://digitaliumgroup.com/read_logs.php` et lire tous les logs. `change_admin_prod.php` permet de modifier l'accès admin.

**Action requise :** Suppression immédiate ou déplacement hors du dossier public.

---

### 🔴 SEC-02 — Double validation CSRF (redondance à risque)

**Fichiers :** `app/Services/Router.php:36-65` + chaque `Controller::validateCsrf()`

Le Router valide CSRF sur **tous** les POST globalement. Ensuite chaque contrôleur appelle `$this->validateCsrf()` une deuxième fois. Le token est valide les deux fois (pas de single-use enforcement), donc pas de bug actuel, mais c'est de la redondance qui peut créer une confusion sur qui est responsable de la validation.

**Action requise :** Documenter la décision ou supprimer la validation dans les contrôleurs si le Router couvre tout.

---

### 🟡 SEC-03 — Logging de tous les paramètres SQL en clair

**Fichier :** `app/Services/Database.php:51` et `app/Models/Page.php:37-38`

Chaque requête SQL est loggée dans `storage/logs/app.log` avec **tous les paramètres en clair**. En production, cela inclut des données personnelles (emails contacts, mots de passe hashés).

**Action requise :** Désactiver le logging SQL en mode `production`.

---

## SECTION 2 : BUGS FONCTIONNELS

### 🔴 BUG-01 — Migration blog non exécutée

**Fichier :** `database/add_blog_and_hero_features.php`

Les tables `blog_posts` et `blog_categories` n'existent pas encore en base (si la migration n'a pas été exécutée). Toutes ces routes retournent HTTP 500 :
- `GET /blog`
- `GET /blog/{slug}`
- `GET /admin/blog`
- `GET /admin/blog/create`
- etc.

**Proof :** `Post::create()`, `Category::getAllWithCount()` font `SELECT * FROM blog_posts` sans `checkTableExists()` — pas de protection automatique contrairement à `Project` et `HeroSlide`.

**Action requise :** Exécuter `public/run_blog_migration.php` UNE FOIS, puis supprimer ce fichier.

---

### 🟠 BUG-02 — Stats labels hardcodés dans `sections/hero.php`

**Fichier :** `app/Views/frontend/sections/hero.php:68,72,76`

```php
// LIGNE 68 — hardcodé
<div class="hstat-l">Expérience</div>
// LIGNE 72 — hardcodé
<div class="hstat-l">Clients</div>
// LIGNE 76 — hardcodé
<div class="hstat-l">Satisfaction</div>
```

Le seeder dans `PageController::seedDefaultSectionBlocks()` crée bien les blocs `stats_label_years`, `stats_label_clients`, `stats_label_satisfaction`, mais la vue ne les lit pas. **Les nouvelles sections lisent les valeurs DB ; les anciennes sections ET la vue affichent toujours les labels hardcodés.**

**Action requise :** Modifier les 3 lignes dans `sections/hero.php` pour lire `$single['stats_label_years']` avec fallback hardcodé.

---

### 🟠 BUG-03 — Dashboard manque les stats blog et projets

**Fichier :** `app/Views/admin/dashboard.php`

Le contrôleur (`AdminController::dashboard()`) passe :
```php
'blog_count'    => $blogCount,
'project_count' => $projectCount,
```

La vue affiche seulement 3 cards (`pages_count`, `sections_count`, `media_count`). Les 2 nouvelles stats sont ignorées — la variable existe mais aucun HTML ne la rend.

**Action requise :** Ajouter 2 stat cards dans `dashboard.php`.

---

### 🟠 BUG-04 — Pas de page publique pour les réalisations

**Contrôleur :** `ProjectController` — uniquement méthodes admin (CRUD).

Il n'existe aucune route ni méthode pour afficher les projets publiquement. La section `portfolio` dans le builder affiche des items statiques de blocks, pas les données de la table `projects`. Il n'y a pas de `/realisations` ou `/portfolio` en GET public.

**Action requise :** Créer une route + méthode publique dans `ProjectController` ou `HomeController`.

---

### 🟡 BUG-05 — 404 pages sans layout

**Fichier :** `app/Controllers/HomeController.php:168-174`

La méthode `render404()` génère un HTML minimal inline sans le layout frontend. De même dans `BlogController::frontendPost()`.

**Impact :** UX dégradé — le visiteur perd le header/footer/menu.

**Action requise :** Créer une vue `frontend/404.php` dans le layout.

---

### 🟡 BUG-06 — Section renderer skips silencieusement types `*_hero`

**Fichier :** `app/Views/frontend/section_renderer.php:16`

```php
if (in_array($type, ['hero', 'about_hero', 'services_hero', 'blog_hero', 'contact_hero']) || str_contains($type, '_hero')) {
    continue;
}
```

Si un admin ajoute une section de type `about_hero` depuis le page builder, elle est **silencieusement ignorée** dans le rendu frontend. L'admin ne reçoit aucune erreur.

**Note :** Comportement intentionnel (le hero est géré par `partials/hero.php`) mais il devrait être documenté et visible dans l'interface admin.

---

### 🟡 BUG-07 — Contact form → log seulement, pas d'email

**Fichier :** `app/Controllers/HomeController.php:109-129`

Le formulaire de contact écrit dans `storage/logs/contacts.log`. Il n'y a aucun appel à `mail()` ou PHPMailer. La réponse JSON dit "Un architecte conseil... prendra contact sous 24 heures" mais aucun email n'est envoyé. Le propriétaire ne sait jamais qu'un message est arrivé.

---

### 🟡 BUG-08 — Domaine sitemap.xml hardcodé

**Fichier :** `app/Controllers/HomeController.php:148`

```php
$loc = "https://digitaliumgroup.com" . ...
```

En local, le sitemap génère des URLs de production. En production avec un nom de domaine différent, le sitemap sera faux.

**Action requise :** Lire le domaine depuis `$settings['site_url']` (à créer) ou `$_SERVER['HTTP_HOST']`.

---

### 🟡 BUG-09 — OG image hardcodée dans layout.php

**Fichier :** `app/Views/frontend/layout.php:14`

```php
<meta property="og:image" content="https://digitaliumgroup.com/assets/images/og-image.jpg">
```

L'image de partage social est la même pour toutes les pages. Devrait lire `$page['og_image']` ou `$page['hero_image']`.

---

## SECTION 3 : DETTE TECHNIQUE

### 🟡 TECH-01 — `Settings.php` alias redondant

**Fichier :** `app/Models/Settings.php`

```php
class Settings extends Setting { }
```

C'est un alias pur. Partout dans le code, seule la classe `Setting` est utilisée. Ce fichier est là pour compatibilité mais crée de la confusion (`Setting` vs `Settings`).

**Action :** Conserver pour compatibilité, documenter.

---

### 🟡 TECH-02 — Migrations laissées dans `/database`

Plus de 10 fichiers de migration dans `/database/`. Certains sont anciens, d'autres récents. Pas d'historique de migration ni de numérotation. En production, difficile de savoir lesquels ont été exécutés.

**Action requise :** Créer un fichier `database/migrations.log` documentant ce qui a été appliqué.

---

### 🟡 TECH-03 — Blog ne bénéficie pas du cache

**Fichier :** `app/Controllers/BlogController.php:194-218`

`frontendIndex()` et `frontendPost()` font des requêtes DB à chaque requête. `HomeController::renderPage()` utilise `Cache::get/set`. Le blog devrait avoir le même traitement.

---

### 🟢 TECH-04 — `public/check_frontend.php`, `public/read_logs.php`

Outils de diagnostic laissés en production. Non critiques mais à déplacer ou supprimer.

---

### 🟢 TECH-05 — Pas de gestion des slugs de pages déjà existantes

Si quelqu'un renomme la page "Accueil" avec slug `home` → `accueil`, toutes les routes `/` → `renderPage('home')` retourneront 404 car `Page::findBySlug('home')` ne trouve rien.

---

## SECTION 4 : ROUTES ET VUES

### Vues présentes vs types de sections connus

| Type section | Vue existante |
|---|---|
| `hero` | ✅ `sections/hero.php` |
| `services` | ✅ `sections/services.php` |
| `services_grid` | ✅ `sections/services_grid.php` |
| `services_hero` | ✅ mais **skippé** |
| `portfolio` | ✅ `sections/portfolio.php` |
| `team` | ✅ `sections/team.php` |
| `team_roles` | ✅ `sections/team_roles.php` |
| `testimonials` | ✅ `sections/testimonials.php` |
| `testimonials_grid` | ✅ `sections/testimonials_grid.php` |
| `faq` | ✅ `sections/faq.php` |
| `blog` | ✅ `sections/blog.php` |
| `blog_grid` | ✅ `sections/blog_grid.php` |
| `contact` | ✅ `sections/contact.php` |
| `contact_details` | ✅ `sections/contact_details.php` |
| `features` | ✅ `sections/features.php` |
| `process` | ✅ `sections/process.php` |
| `process_strip` | ✅ `sections/process_strip.php` |
| `about` | ✅ `sections/about.php` |
| `cta` | ✅ `sections/cta.php` |
| `newsletter` | ✅ `sections/newsletter.php` |
| `mission` | ✅ `sections/mission.php` |
| `values` | ✅ `sections/values.php` |
| `blog_topics` | ✅ `sections/blog_topics.php` |
| `services_strip` | ✅ `sections/services_strip.php` |
| `about_hero` `blog_hero` `contact_hero` `services_hero` | ✅ vues existent mais **toutes skippées par renderer** |

### 🟠 Section types in seeder but NOT in renderer's skip list
Le seeder dans `PageController` permet de créer les types : `hero`, `services`, `portfolio`, `team`, `testimonials`, `faq`, `blog`, `contact`. Tous fonctionnent.

Types présents dans les vues mais **pas dans le seeder** (peuvent être ajoutés via UI mais sans blocs par défaut) : `services_grid`, `blog_grid`, `features`, `process`, `process_strip`, `team_roles`, `about`, `cta`, `newsletter`, `mission`, `values`.

**Impact :** Une section `features` ajoutée depuis l'admin aura des blocs vides — l'admin voit le builder mais aucun champ n'est pré-rempli.

---

## RÉSUMÉ COMPTABLE

| Priorité | Nombre | Catégories |
|---|---|---|
| 🔴 CRITIQUE | 3 | SEC-01, BUG-01, + migration non exécutée |
| 🟠 ÉLEVÉ | 3 | BUG-02, BUG-03, BUG-04 |
| 🟡 MOYEN | 8 | SEC-02, SEC-03, BUG-05 à 09, TECH-01 à 03 |
| 🟢 FAIBLE | 3 | TECH-04, TECH-05, types non seedés |
