# IMPLEMENTATION_PLAN — Digitalium Group CMS
> Produit le 2026-06-24 — plan d'exécution priorisé, approuvé à obtenir avant toute modification.

---

## PRIORITÉS

| Code | Libellé | Critère |
|---|---|---|
| P0 | Bloquant/Sécurité | À corriger immédiatement, sans validation |
| P1 | CMS — fonctionnalité manquante ou cassée | Bloque l'usage normal |
| P2 | Frontend/UX | Dégradé mais non bloquant |
| P3 | Optimisations | Performance, SEO |

---

## PHASE P0 — SÉCURITÉ ET STABILITÉ CRITIQUE

> Ces actions ne modifient pas le comportement fonctionnel. Elles stoppent des risques immédiats.

### P0-01 : Supprimer les fichiers publics dangereux
**Fichiers :**
- `public/change_admin_prod.php` → DELETE
- `public/seed_prod.php` → DELETE
- `public/run_blog_migration.php` → DELETE après exécution
- `public/run_pages_migration.php` → DELETE après vérification
- `public/read_logs.php` → DELETE (déplacer si outil de debug voulu)
- `public/check_frontend.php` → DELETE

**Risque si on ne le fait pas :** Accès admin non autorisé, exposition des logs.

---

### P0-02 : Exécuter la migration blog
**Action :** Accéder à `http://localhost/Digitalium/public/run_blog_migration.php`
**Résultat attendu :** Tables `blog_posts` et `blog_categories` créées, colonnes `hero_features` et `hero_articles` ajoutées à `pages`.
**Puis :** Supprimer `run_blog_migration.php`.

---

### P0-03 : Désactiver les logs SQL en production
**Fichier :** `app/Services/Database.php`
**Modification :** Conditionner les `error_log` à `ENVIRONMENT === 'development'`.
**Impact :** Aucun impact fonctionnel. Élimine le risque de disque plein et l'exposition de données.

---

## PHASE P1 — CMS FONCTIONNEL COMPLET

### P1-01 : Stats labels dynamiques dans `sections/hero.php`
**Fichier :** `app/Views/frontend/sections/hero.php` lignes 68, 72, 76
**Modification :** Remplacer "Expérience", "Clients", "Satisfaction" par `$single['stats_label_years'] ?? 'Expérience'` etc.
**Test :** Éditer une page → section hero → modifier "Expérience" → sauvegarder → vérifier frontend.

---

### P1-02 : Dashboard — afficher stats blog et projets
**Fichier :** `app/Views/admin/dashboard.php`
**Modification :** Ajouter 2 stat cards (blog_count et project_count) dans `.stats-grid`.
**Test :** `/admin/dashboard` → vérifier 5 cards visibles.

---

### P1-03 : Page publique Réalisations
**Besoin :** Route + vue frontend pour afficher les projets (`/realisations`).
**Fichiers à créer/modifier :**
- `routes/web.php` → ajouter `GET /realisations → ProjectController@publicIndex`
- `app/Controllers/ProjectController.php` → ajouter méthode `publicIndex()`
- `app/Views/frontend/portfolio.php` → vue listing projets
**Test :** `/realisations` → HTTP 200, grille de projets visible.

---

### P1-04 : Page 404 avec layout
**Fichiers :**
- `app/Views/frontend/404.php` → créer la vue 404 dans le layout
- `app/Controllers/HomeController.php` → modifier `render404()` pour utiliser le layout
- `app/Controllers/BlogController.php:227` → modifier le 404 blog de même
**Test :** Accéder à `/slug-inexistant` → page 404 avec header/footer intacts.

---

## PHASE P2 — FRONTEND ET UX

### P2-01 : Envoi email formulaire de contact
**Fichier :** `app/Controllers/HomeController.php:109-129`
**Options :**
- Option A : PHP `mail()` natif (dépend de la configuration Hostinger)
- Option B : Implémenter PHPMailer via SMTP (recommandé pour fiabilité)
- Option C : Stocker en base + notification dashboard (rapide, sans dépendance SMTP)
**Recommandation :** Option C pour démarrer (stockage DB) + Option B en suivi.
**Test :** Soumettre le formulaire → vérifier réception email / entrée DB.

---

### P2-02 : Menu manager admin
**Besoin :** Interface pour ajouter des liens externes au menu, réordonner indépendamment du sort_order des pages, masquer/afficher des entrées.
**Approche recommandée :** Nouvelle table `menu_items` (id, label, url, sort_order, is_visible, page_id nullable) + admin vue CRUD avec drag-and-drop.
**Complexité :** Moyenne — 1 table, 1 contrôleur, 1 vue admin, modifier layout.php pour lire menu_items.

---

### P2-03 : Domaine site depuis settings
**Fichier :** `app/Controllers/HomeController.php:148`
**Modification :** Ajouter clé `site_url` dans settings → lire depuis `$settings['site_url']` avec fallback `https://digitaliumgroup.com`.
**Test :** Modifier `site_url` en admin → vérifier `/sitemap.xml`.

---

### P2-04 : OG image dynamique par page
**Fichier :** `app/Views/frontend/layout.php:14`
**Modification :** Lire `$page['hero_image']` comme OG image avec fallback vers image par défaut.
**Test :** Vérifier balise OG image sur plusieurs pages.

---

## PHASE P3 — OPTIMISATIONS

### P3-01 : Cache sur les routes blog
**Fichier :** `app/Controllers/BlogController.php`
**Modification :** Ajouter `Cache::get/set` dans `frontendIndex()` et `frontendPost()`.

---

### P3-02 : Seeder pour tous les types de sections
**Fichier :** `app/Controllers/PageController.php::seedDefaultSectionBlocks()`
**Modification :** Ajouter les cas manquants : `services_grid`, `blog_grid`, `features`, `process`, `process_strip`, `team_roles`, `about`, `cta`, `newsletter`.

---

### P3-03 : Nettoyage dossier `database/`
**Action :** Créer `database/MIGRATIONS_LOG.md` documentant chaque migration exécutée avec sa date.

---

## ORDRE D'EXÉCUTION RECOMMANDÉ

```
Semaine 1 :
  [x] P0-01 : Supprimer fichiers publics dangereux
  [x] P0-02 : Exécuter migration blog
  [x] P0-03 : Désactiver logs SQL en production
  [x] P1-01 : Stats labels dynamiques
  [x] P1-02 : Dashboard stats blog + projets

Semaine 2 :
  [ ] P1-03 : Page publique Réalisations
  [ ] P1-04 : Page 404 avec layout
  [ ] P2-03 : Domaine sitemap depuis settings
  [ ] P2-04 : OG image dynamique

Semaine 3 :
  [ ] P2-01 : Email contact (stockage DB)
  [ ] P2-02 : Menu manager admin
  [ ] P3-01 : Cache blog
  [ ] P3-02 : Compléter seeders sections
  [ ] P3-03 : Nettoyage migrations
```

---

## TESTS OBLIGATOIRES APRÈS CHAQUE PHASE

### Frontend
| URL | HTTP attendu | Vérification |
|---|---|---|
| `/` | 200 | Page d'accueil, hero, sections visibles |
| `/about` ou page équivalente | 200 | Page statique OK |
| `/blog` | 200 | Listing articles, pagination |
| `/blog/{slug-existant}` | 200 | Article complet, articles liés |
| `/blog/{slug-inexistant}` | 404 | Page 404 dans le layout |
| `/realisations` | 200 | Grille projets |
| `/slug-inexistant` | 404 | Page 404 dans le layout |
| `/sitemap.xml` | 200 | XML valide |

### Backend Admin
| URL | HTTP attendu | Vérification |
|---|---|---|
| `/admin` | 200 (si connecté) ou 302→login | Dashboard avec 5 stats |
| `/admin/blog` | 200 | Liste articles |
| `/admin/blog/create` | 200 | Formulaire création article |
| `/admin/pages` | 200 | Liste pages |
| `/admin/pages/edit/{id}` | 200 | Éditeur complet |
| `/admin/projects` | 200 | Liste réalisations |
| `/admin/media` | 200 | Bibliothèque média |
| `/admin/settings` | 200 | Formulaire settings |
| POST `/admin/settings` | 302→settings | Flash success |

### Vérifications qualité
- [ ] Aucune erreur PHP dans les logs
- [ ] Aucune erreur JS dans la console navigateur
- [ ] Navigation mobile : menu toggle fonctionne (hamburger → ouverture → fermeture)
- [ ] Upload d'une image → visible dans la bibliothèque → sélectionnable depuis le page editor
- [ ] Création article blog → publication → visible sur `/blog`

---

## CE QUI NE SERA PAS MODIFIÉ (hors périmètre)

- Architecture MVC : pas de migration vers un framework
- Schema existant des tables déjà en production (pas de DROP, pas de RENAME de colonnes)
- Autoloader custom : pas de migration vers Composer/PSR-4
- CSS `/assets/css/index.css` : pas de refonte design sans validation

---

## VALIDATION REQUISE AVANT DÉMARRAGE

**Je soumets ce plan pour validation. Merci de confirmer :**

1. Dois-je exécuter P0-01 (suppression fichiers dangereux) immédiatement ?
2. La migration blog (P0-02) a-t-elle déjà été exécutée sur le serveur local ?
3. La page `/realisations` (P1-03) doit-elle utiliser le même système hero que les autres pages ?
4. Pour le formulaire contact (P2-01) : préférence entre email PHP natif, SMTP, ou stockage DB ?
5. Le menu manager (P2-02) : prioritaire ou à reporter ?

Une fois validé, je commence par P0 et descends dans l'ordre sans dévier.
