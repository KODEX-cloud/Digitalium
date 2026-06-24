# PROJECT_STATE — Digitalium Group CMS
> Dernière mise à jour : 2026-06-24 — Gouvernance Enterprise validée

---

## ÉTAT GLOBAL : ⚠️ PRODUCTION-READY PARTIEL

Le CMS est fonctionnel à ~85%. Il manque 3 endpoints critiques et 1 régression UX à corriger avant mise en production.

---

## MODULES TERMINÉS ✅

| Module | Fichiers clés | Statut |
|---|---|---|
| Sécurité P0 — scripts publics supprimés | `database/`, `bin/` | ✅ |
| Migration SQL master | `database/master_migration.php` | ✅ Exécutée |
| Logs SQL production | `app/Services/Database.php` | ✅ |
| Menu système DB-driven | `Menu.php`, `MenuItem.php`, `MenuController.php`, `menus/` views | ✅ |
| Contact Messages inbox | `Message.php`, `MessageController.php`, `messages/` views | ✅ |
| Réalisations publiques | `portfolio_index.php`, `portfolio_show.php`, routes `/realisations` | ✅ |
| Admin projets — nouveaux champs | `Project.php`, `projects/create.php`, `projects/edit.php` | ✅ |
| Dashboard — 5 stat cards | `admin/dashboard.php` | ✅ |
| Hero stats labels dynamiques | `sections/hero.php` | ✅ |
| Sidebar admin enrichie | `admin/layout.php` — Messages badge + Navigation | ✅ |
| Blog — Tag model + sync DB | `Tag.php`, `BlogController.php` | ✅ |
| Blog — Comment model | `Comment.php` | ✅ |
| Blog post frontend — tags chips + section commentaires | `blog_post.php` | ✅ |
| 404 frontend stylée | `frontend/404.php` | ✅ |
| Routes complètes | `routes/web.php` | ✅ |

---

## MODULES INCOMPLETS ⚠️

| Priorité | Module | Manque | Impact |
|---|---|---|---|
| 🔴 P0 | Commentaires blog — endpoint | Route POST `/blog/comment` + `BlogController::submitComment()` | Formulaire frontend = 404 |
| 🔴 P0 | render404() HomeController | Utilise encore du HTML brut | Régression UX sur slug inexistant |
| 🟡 P1 | Modération commentaires admin | Aucune vue ni route `/admin/blog/comments` | Commentaires non modérables |

---

## DETTE TECHNIQUE

| Ref | Description | Fichiers | Sévérité |
|---|---|---|---|
| DT-01 | Duplication `Setting.php` vs `Settings.php` | `app/Models/Settings.php` (doublon inutile) | Faible |
| DT-02 | `blog_posts.tags` (texte) vs `blog_post_tags` (table) — double source | `Post.php`, `Tag.php` | Moyen |
| DT-03 | `contact_email` setting absente du panneau Settings | `admin/settings.php` | Moyen |

---

## RISQUES ACTIFS

| Ref | Risque | Impact | Mitigation |
|---|---|---|---|
| R-01 | Formulaire commentaire → 404 | UX bloquée | Créer endpoint + route |
| R-02 | Email notification contact sans `contact_email` configuré | Mail silencieusement ignoré | Ajouter le champ dans settings |
| R-03 | Slug `/realisations` capturé par `/{slug}` si ordre routes inversé | 404 réalisations | Ordre correct en place ✅ |

---

## GOUVERNANCE

| Fichier | État | Date |
|---|---|---|
| `CLAUDE.md` | ✅ Créé + 8 règles Enterprise ajoutées | 2026-06-24 |
| `PROJECT_STATE.md` | ✅ Actif — mis à jour après chaque tâche | 2026-06-24 |

---

## PROCHAINES ÉTAPES (par priorité)

1. **[P0]** Créer `BlogController::submitComment()` + route POST `/blog/comment`
2. **[P0]** Corriger `HomeController::render404()` → utiliser `frontend/404.php` avec layout
3. **[P1]** Vue + routes admin modération commentaires (`/admin/blog/comments`)
4. **[P1]** Ajouter champ `contact_email` dans le panneau Settings admin
5. **[P2]** Supprimer `app/Models/Settings.php` (doublon de `Setting.php`)

---

## FICHIERS NE PAS TOUCHER SANS ANALYSE

- `app/Services/Router.php` — logique de dispatch critique
- `app/Services/Database.php` — singleton PDO
- `app/Services/CSRF.php` — sécurité globale
- `database/master_migration.php` — idempotent, ne pas re-exécuter sans vérification
- `routes/web.php` — ordre des routes = priorité de matching
