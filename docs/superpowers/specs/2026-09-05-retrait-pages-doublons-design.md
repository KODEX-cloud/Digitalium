# Retrait des pages en doublon — `/service` et `/blog`

> Règle #3 : audit, risques et plan **avant** le code.
> Règle #4 : toutes les dépendances recherchées **avant** toute suppression.

---

## 1. TECHNICAL_AUDIT

### 1.1 `/blog` est déjà traité

| Vérification | Résultat |
|---|---|
| `GET /blog` | **301 → `/insights`** |
| `GET /blog/seo-reseaux-sociaux-ia-visibilite` | **301 → `/insights/seo-…`** |
| `GET /public/blog` | **301 → `/public/insights`** |
| `/blog` dans le sitemap | non |
| Liens internes vers `/blog` sur les 8 pages publiques | **aucun** |

`routes/web.php:25-26` → `BlogController@legacyIndex` / `@legacyPost`, deux `header(..., 301)`.
`HomeController::sitemap()` ignore déjà explicitement le slug `blog`.

**Il reste néanmoins un objet à retirer** : ce filtre du sitemap n'existe que parce qu'une **page CMS
`blog` est encore présente et publiée**. Invisible (la route l'intercepte), mais listée dans
`/admin/pages` — exactement le doublon que le CTO demande de supprimer.

### 1.2 `/service` n'est pas traité du tout

| Vérification | Résultat |
|---|---|
| `GET /service` | **200** — page CMS servie normalement |
| `/service` dans le sitemap | **OUI** |
| Liens internes | **présent dans le pied de page de TOUTES les pages** (« Services ») |
| Enfants `/service/{x}` | aucun — les cinq slugs de Solutions y renvoient 404 |
| Titre | « Nos Prestations \| Des expertises digitales haut de gamme » |

Comparaison du contenu :

| `/service` (4 sections) | `/solutions` (8 sections) |
|---|---|
| `hero_media_cards`, `process_timeline`, `testimonials_carousel`, `cta` | `hero_media_cards`, `sectors_grid`, `process_strip`, `capabilities_grid`, `needs_router`, `sectors_grid`, `projects_cms`, `cta` |

`/solutions` est un sur-ensemble fonctionnel et porte les cinq pages filles. La désignation de
Solutions comme référence est cohérente avec l'état réel du site.

### 1.3 Le préfixe `/public` est déjà absorbé par le routeur

`GET /public/blog` répond **301 → `/public/insights`** : le routeur normalise l'URI avant de router.
Une seule route couvre donc `/service` **et** `/public/service` — inutile d'en déclarer deux (DT-05
reste par ailleurs ouvert, non traité ici).

### 1.4 Cascade de suppression — vérifiée, pas supposée

`database/database.sql:105` et `:119` :

```
sections.page_id   → pages(id)     ON DELETE CASCADE
blocks.section_id  → sections(id)  ON DELETE CASCADE
menu_items.page_id → pages(id)     ON DELETE SET NULL     (master_migration.php:154)
```

Supprimer une page emporte donc ses sections et ses blocs. **Mais pas ses entrées de menu** : elles
survivent avec `page_id = NULL` et leur `url` intacte. Les entrées doivent être supprimées
explicitement, sinon un lien « Services » resterait au pied de page.

### 1.5 CE QUI NE DOIT SURTOUT PAS ÊTRE SUPPRIMÉ

`/insights/{slug}` est servi par **`BlogController@frontendPost`**, et les articles vivent dans
**`blog_posts`**. Le « blog » n'est pas un module concurrent d'Insights : **c'est le moteur
d'Insights**. Supprimer `BlogController`, `blog_posts`, `blog_categories` ou `/admin/blog`
détruirait la vitrine éditoriale que le CTO demande justement de conserver.

Ce qui disparaît est donc l'**adresse publique** `/blog` (déjà fait) et la **page CMS résiduelle**,
pas le moteur.

De même, les routes `/blog` et `/blog/{slug}` doivent **rester déclarées** : ce sont elles qui
portent la redirection 301. Les retirer transformerait une redirection en 404 et ferait perdre
l'indexation existante — l'inverse de la demande.

---

## 2. RISK_ANALYSIS

| # | Risque | Probabilité | Impact | Prévention |
|---|---|---|---|---|
| R1 | Suppression de `BlogController` → `/insights` tombe | — | **Critique** | Explicitement hors périmètre (§1.5) ; assertion de banc dédiée |
| R2 | Retrait des routes `/blog` → 404 au lieu de 301 | Moyenne | Élevé | Les routes restent ; banc vérifiant qu'elles sont toujours déclarées |
| R3 | La page supprimée mais le lien de pied de page subsiste | **Certaine si non traité** | Moyen | Suppression explicite des `menu_items` (FK en SET NULL, pas CASCADE) |
| R4 | Le sitemap continue d'annoncer une URL qui redirige | Moyenne | Moyen | Liste de slugs retirés dans `sitemap()`, indépendante de la suppression |
| R5 | La suppression rejouée à chaque déploiement efface une page recréée volontairement | Certaine si non gardée | Élevé | Opération **unique**, drapeau `settings` par page |
| R6 | La redirection n'existe pas encore quand la page disparaît | Faible | Élevé | Le `git pull` précède les migrations : la route est en place avant la suppression |
| R7 | Perte définitive du contenu de `/service` | Certaine — c'est la demande | Moyen | Sauvegarde SQL créée par `RollbackManager` **avant chaque déploiement** ; suppression donc récupérable |
| R8 | Une étape annexe fait échouer tout le script | Déjà survenu (`/insights`) | Élevé | Chaque étape dans son propre `try/catch` |

---

## 3. IMPLEMENTATION_PLAN

### Étape 1 — Redirections 301

`routes/web.php`, **avant** le catch-all, à côté du bloc `/blog` existant :

```php
$router->get('/service',        'HomeController@legacyService');
$router->get('/service/{slug}', 'HomeController@legacyService');
```

`HomeController::legacyService()` calque `BlogController::legacyIndex()` : `header(Location, 301)`.
Aucun enfant n'existant, un `/service/{x}` hérité pointe vers `/solutions` plutôt que vers un 404.

### Étape 2 — Sitemap

Remplacer le test `if ($slug === 'blog')` par une **liste** de slugs retirés (`blog`, `service`) :
une adresse qui redirige n'a rien à faire dans un sitemap, que la page existe encore ou non.

### Étape 3 — Migration `database/retire_legacy_pages.php`

Par page, opération **unique** gardée par un drapeau :

1. supprimer les `menu_items` pointant vers l'URL ou vers la page (tous emplacements) ;
2. supprimer les `blocks` puis les `sections` de la page, puis la page ;
3. poser le drapeau.

Étapes isolées ; l'échec de l'une n'empêche pas les autres.

### Étape 4 — Pipeline

Étape non bloquante dans `deploy.yml`, après les scripts de construction : les pages doivent exister
avant qu'on en retire d'autres.

### Étape 5 — Bancs

| Banc | Portée |
|---|---|
| `h_retire_legacy.php` | Suppression, idempotence, entrées de menu, drapeaux, dégradations |
| `h_routes_legacy.php` | 301 en place, routes `/blog` conservées, `/insights` et `/admin/blog` intacts |
