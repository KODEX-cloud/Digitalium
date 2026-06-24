# RISK_ANALYSIS — Digitalium Group CMS
> Produit le 2026-06-24 — identification des risques avant toute modification.

---

## MATRICE DE RISQUES

| Risque | Probabilité | Impact | Sévérité | Mitigation |
|---|---|---|---|---|
| Exécution publique de `change_admin_prod.php` | Élevée | Critique | 🔴 CRITIQUE | Supprimer immédiatement |
| Lecture de `read_logs.php` par un tiers | Élevée | Critique | 🔴 CRITIQUE | Supprimer immédiatement |
| HTTP 500 sur `/blog` si migration non faite | Certaine | Élevée | 🔴 CRITIQUE | Exécuter migration |
| Migration destructrice (`DROP TABLE`) | Faible | Critique | 🟠 ÉLEVÉ | Les migrations actuelles utilisent `CREATE IF NOT EXISTS` et `ALTER ADD COLUMN` — pas de DROP |
| Perte de données lors d'un `ALTER TABLE` | Faible | Élevée | 🟠 ÉLEVÉ | `ADD COLUMN` seulement, pas de modification de colonnes existantes |
| Corruption SQL lors d'update `Page::updatePage` | Très faible | Élevée | 🟡 MOYEN | SQL paramétré PDO, pas d'injection possible |
| Perte d'images uploadées après déploiement | Moyenne | Élevée | 🟡 MOYEN | `public/assets/uploads/` doit être exclu du `.gitignore` ou copié manuellement |
| Route cassée après renommage de slug | Moyenne | Élevée | 🟡 MOYEN | Pas de redirect automatique slug → nouveau slug |
| Cache page obsolète après modification | Faible | Faible | 🟢 FAIBLE | `Cache::clear()` appelé après chaque sauvegarde |
| Session expirée côté admin (2h) | Normale | Faible | 🟢 FAIBLE | Redirection vers login, données de formulaire non sauvegardées |

---

## RISQUES DÉTAILLÉS

### 🔴 RISQUE-01 — Fichiers manipulation production accessibles publiquement

**Fichiers :**
- `public/change_admin_prod.php`
- `public/seed_prod.php`
- `public/run_blog_migration.php`
- `public/run_pages_migration.php`
- `public/read_logs.php`

**Scénario :** Un concurrent, robot ou attaquant accède à `https://digitaliumgroup.com/change_admin_prod.php`. Sans authentification, il peut modifier les credentials admin ou déclencher un seed qui écrase des données.

**Probabilité actuelle :** Élevée (indexable par les moteurs).

**Action immédiate :** Supprimer ou déplacer hors de `/public` AVANT toute autre modification.

---

### 🔴 RISQUE-02 — Tables blog inexistantes → HTTP 500 sur toutes les routes blog

**Scénario :** La migration `add_blog_and_hero_features.php` n'a pas été exécutée. Les tables `blog_posts` et `blog_categories` n'existent pas. Contrairement à `Project` et `HeroSlide` qui ont un `checkTableExists()`, les modèles `Post` et `Category` font des requêtes directes.

**Routes affectées :**
- `GET /blog` → `Post::countPublished()` → PDO Exception → HTTP 500
- `GET /blog/{slug}` → `Post::findBySlug()` → HTTP 500
- `GET /admin/blog` → `Post::all()` → HTTP 500

**Vérification :** Requête SQL `SHOW TABLES LIKE 'blog_posts'` ou accès à `/blog`.

**Action :** Exécuter `run_blog_migration.php` puis le supprimer.

---

### 🟠 RISQUE-03 — Perte d'images lors de déploiement

**Scénario :** Les images sont uploadées dans `public/assets/uploads/`. Si le déploiement en production consiste à synchroniser le repo git (sans les uploads), toutes les images disparaissent.

**Note :** Le dossier `uploads/` est probablement dans `.gitignore` (vérifier).

**Action :** S'assurer qu'une procédure de sauvegarde/restauration des uploads existe. En Hostinger, utiliser FTP ou SSH pour copier le dossier.

---

### 🟠 RISQUE-04 — Modification `pages.slug` d'une page existante

**Scénario :** L'admin renomme le slug de la page "Accueil" de `home` vers `accueil`. La route `HomeController@index` appelle `renderPage(['slug' => 'home'])` qui fait `Page::findBySlug('home')` → retourne null → `render404()`.

**Impact :** Page d'accueil inaccessible.

**Protection actuelle :** La page `home` est protégée contre la suppression mais pas contre le changement de slug.

**Mitigation proposée :** Ajouter une validation dans `PageController::editSubmit()` qui interdit de changer le slug des pages système (`home`).

---

### 🟡 RISQUE-05 — Migration `ALTER TABLE` sur serveur production

**Scénario :** Exécution de `run_blog_migration.php` sur un serveur avec des milliers de lignes dans `pages`. L'`ALTER TABLE pages ADD COLUMN hero_features` est une opération qui peut verrouiller la table sur MySQL < 8.0 (MariaDB < 10.3).

**Hostinger utilise :** MySQL 8.0 ou MariaDB 10.x — opérations ADD COLUMN sont généralement en ligne (online DDL).

**Mitigation :** Exécuter en heures creuses ou en maintenance.

---

### 🟡 RISQUE-06 — Logs disque plein en production

**Scénario :** `Database::query()` log chaque SQL avec paramètres dans `storage/logs/app.log`. Une page avec 20 sections × 10 blocs = 200+ requêtes par affichage. En production haute charge, le fichier grossit à plusieurs GB/jour.

**Mitigation :** Désactiver les logs SQL en `ENVIRONMENT === 'production'`.

---

### 🟡 RISQUE-07 — Conflit slug `/blog` vs page CMS slug `blog`

**Scénario :** Si une page CMS avec slug `blog` existe dans la table `pages`, la route `GET /blog → BlogController@frontendIndex` est définie AVANT `GET /{slug} → HomeController@renderPage`. La route blog prend la priorité. Si l'admin supprime la route mais garde la page CMS, la page CMS rendrait le blog, ce qui peut créer une double rendering situation.

**État actuel :** Ordre des routes correct — `/blog` est déclaré avant `/{slug}`. Pas de risque immédiat.

---

## RÈGLES DE SÉCURITÉ AVANT MODIFICATION

Avant chaque modification de code ou de base de données :

1. **Sauvegarder la DB** : `mysqldump digitalium_db > backup_YYYY-MM-DD.sql`
2. **Sauvegarder uploads** : Copier `public/assets/uploads/`
3. **Vérifier l'environnement** : Local d'abord, production ensuite
4. **Ne jamais modifier** : `slug = 'home'` dans la table pages
5. **Ne jamais supprimer** : Une section sans vérifier ses blocs associés
6. **Ne jamais ALTER** sans sauvegarder la table concernée

---

## FICHIERS À NE JAMAIS MODIFIER SANS ANALYSE COMPLÈTE

| Fichier | Raison |
|---|---|
| `app/Services/Router.php` | Toute modification peut casser le dispatch de toutes les routes |
| `app/Services/Database.php` | Singleton PDO — modification affecte toutes les requêtes |
| `config/config.php` | Définit les constantes globales — toute erreur = HTTP 500 sur tout le site |
| `public/index.php` | Bootstrap principal — toute erreur = site down |
| `routes/web.php` | Ordre des routes critique (catch-all `/{slug}` DOIT être en dernier) |
| Table `pages` slug `home` | Renommer = page d'accueil inaccessible |
