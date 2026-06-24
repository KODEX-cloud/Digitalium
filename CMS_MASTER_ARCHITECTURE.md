# CMS_MASTER_ARCHITECTURE — Digitalium Group
> Produit le 2026-06-24 — Document de référence CTO. Ne pas modifier sans validation.

---

## 1. ARCHITECTURE ACTUELLE → CIBLE

### Actuel
```
CMS basique : pages + sections/blocks + hero + media + settings + projets (admin only) + blog (tables manquantes) + menu hardcodé
```

### Cible
```
CMS complet administrable :
├── Navigation dynamique (menus + menu_items)
├── Pages (hero engine + sections/blocks + SEO)
├── Blog (articles + catégories + tags + commentaires)
├── Réalisations (portfolio public + admin CRUD)
├── Messages contact (base + email + admin inbox)
├── Média (upload + bibliothèque)
├── Settings globaux (header/footer/SEO/socials/logo)
└── Sécurité (CSRF + RBAC futur)
```

---

## 2. SCHÉMA SQL COMPLET — ÉTAT CIBLE

### Tables existantes (à conserver)
```sql
users           (id, username, password_hash, email, created_at)
pages           (id, title, slug, meta_*, status, sort_order, in_navigation, hero_*, header_*, 
                 logo_*, responsive_settings, hero_features, hero_articles, created_at, updated_at)
sections        (id, page_id, name, type, sort_order, status)
blocks          (id, section_id, block_key, type, value, group_id, sort_order)
settings        (id, setting_key, setting_value)
media           (id, filename, filepath, original_name, file_size, mime_type, created_at)
hero_slides     (id, page_id, title, subtitle, badge, image, cta_text, cta_url, sort_order)
```

### Tables existantes (à modifier)
```sql
-- Ajouter slug, client, project_date, description à projects
projects (
  id, title, slug, category, client, project_date,
  logo, main_image, gallery,
  description,         -- remplace context (plus explicite)
  context,             -- conservé pour compatibilité
  impact,
  technologies, external_link,
  sort_order, is_featured, created_at, updated_at
)

-- Ajouter category_id FK si non existant
blog_posts (
  id, title, slug, excerpt, content,
  featured_image, category, category_id,
  author, status, is_featured,
  meta_title, meta_description, tags,
  published_at, created_at, updated_at
)
```

### Nouvelles tables
```sql
-- BLOG : Tags
blog_tags (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(100) NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)

-- BLOG : Pivot posts ↔ tags
blog_post_tags (
  post_id INT NOT NULL,
  tag_id  INT NOT NULL,
  PRIMARY KEY (post_id, tag_id),
  FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
  FOREIGN KEY (tag_id)  REFERENCES blog_tags(id)  ON DELETE CASCADE
)

-- BLOG : Commentaires
blog_comments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  post_id      INT NOT NULL,
  author_name  VARCHAR(100) NOT NULL,
  author_email VARCHAR(255) NOT NULL,
  content      TEXT NOT NULL,
  status       ENUM('pending','approved','spam') DEFAULT 'pending',
  ip_address   VARCHAR(45) NULL,
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE
)

-- CONTACT : Messages reçus
contact_messages (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  nom        VARCHAR(150) NOT NULL,
  email      VARCHAR(255) NOT NULL,
  telephone  VARCHAR(30)  NULL,
  sujet      VARCHAR(255) NULL,
  message    TEXT NOT NULL,
  ip_address VARCHAR(45)  NULL,
  statut     ENUM('nouveau','lu','archivé') DEFAULT 'nouveau',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)

-- MENUS : Groupes de navigation
menus (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100) NOT NULL,
  slug       VARCHAR(100) NOT NULL UNIQUE,
  location   VARCHAR(50)  DEFAULT 'primary',   -- primary | footer | mobile
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)

-- MENUS : Entrées individuelles
menu_items (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  menu_id    INT NOT NULL,
  parent_id  INT NULL,                         -- NULL = niveau racine
  page_id    INT NULL,                         -- lien vers page CMS
  label      VARCHAR(150) NOT NULL,
  url        VARCHAR(500) NULL,                -- URL externe ou chemin interne
  target     VARCHAR(20)  DEFAULT '_self',     -- _self | _blank
  icon       VARCHAR(50)  NULL,               -- icône Lucide optionnelle
  sort_order INT DEFAULT 0,
  is_active  TINYINT DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (menu_id)   REFERENCES menus(id)      ON DELETE CASCADE,
  FOREIGN KEY (parent_id) REFERENCES menu_items(id) ON DELETE SET NULL,
  FOREIGN KEY (page_id)   REFERENCES pages(id)      ON DELETE SET NULL
)
```

---

## 3. ROUTES COMPLÈTES — ÉTAT CIBLE

### Frontend public
| Méthode | URL | Handler | Statut |
|---|---|---|---|
| GET | `/` | HomeController@index | ✅ Existant |
| GET | `/sitemap.xml` | HomeController@sitemap | ✅ Existant |
| POST | `/contact` | HomeController@contactSubmit | ✅ Existant → modifier |
| GET | `/blog` | BlogController@frontendIndex | ✅ Existant |
| GET | `/blog/{slug}` | BlogController@frontendPost | ✅ Existant |
| GET | `/realisations` | ProjectController@publicIndex | 🆕 À créer |
| GET | `/realisations/{slug}` | ProjectController@publicShow | 🆕 À créer |
| GET | `/{slug}` | HomeController@renderPage | ✅ Existant (catch-all) |

### Admin
| Méthode | URL | Handler | Statut |
|---|---|---|---|
| GET/POST | `/admin/login` | AdminController | ✅ |
| GET | `/admin/logout` | AdminController | ✅ |
| GET | `/admin` `/admin/dashboard` | AdminController@dashboard | ✅ |
| GET/POST | `/admin/settings` | AdminController@settingsForm/Submit | ✅ |
| GET | `/admin/media` | MediaController@index | ✅ |
| POST | `/admin/media/upload` | MediaController@upload | ✅ |
| POST | `/admin/media/delete` | MediaController@delete | ✅ |
| GET | `/admin/pages` | PageController@index | ✅ |
| GET/POST | `/admin/pages/create` | PageController@createForm/Submit | ✅ |
| GET/POST | `/admin/pages/edit/{id}` | PageController@editForm/Submit | ✅ |
| POST | `/admin/pages/delete/{id}` | PageController@deletePage | ✅ |
| POST | `/admin/pages/sections/*` | PageController@addSection etc. | ✅ |
| POST | `/admin/pages/blocks/*` | PageController@updateBlocks etc. | ✅ |
| POST | `/admin/pages/slides/*` | PageController@addSlide etc. | ✅ |
| GET | `/admin/projects` | ProjectController@index | ✅ |
| GET/POST | `/admin/projects/create` | ProjectController@createForm/Submit | ✅ |
| GET/POST | `/admin/projects/edit/{id}` | ProjectController@editForm/Submit | ✅ → modifier |
| POST | `/admin/projects/delete/{id}` | ProjectController@delete | ✅ |
| GET | `/admin/blog` | BlogController@index | ✅ |
| GET/POST | `/admin/blog/create` | BlogController@createForm/Submit | ✅ |
| GET/POST | `/admin/blog/edit/{id}` | BlogController@editForm/Submit | ✅ |
| POST | `/admin/blog/delete/{id}` | BlogController@delete | ✅ |
| GET | `/admin/blog/categories` | BlogController@categories | ✅ |
| POST | `/admin/blog/categories/create` | BlogController@createCategory | ✅ |
| POST | `/admin/blog/categories/delete/{id}` | BlogController@deleteCategory | ✅ |
| GET | `/admin/messages` | MessageController@index | 🆕 À créer |
| GET | `/admin/messages/{id}` | MessageController@show | 🆕 À créer |
| POST | `/admin/messages/delete/{id}` | MessageController@delete | 🆕 À créer |
| POST | `/admin/messages/mark-read/{id}` | MessageController@markRead | 🆕 À créer |
| GET | `/admin/menus` | MenuController@index | 🆕 À créer |
| POST | `/admin/menus/create` | MenuController@create | 🆕 À créer |
| GET | `/admin/menus/edit/{id}` | MenuController@editForm | 🆕 À créer |
| POST | `/admin/menus/edit/{id}` | MenuController@editSubmit | 🆕 À créer |
| POST | `/admin/menus/delete/{id}` | MenuController@delete | 🆕 À créer |
| POST | `/admin/menus/{id}/items/save` | MenuController@saveItems | 🆕 À créer |

---

## 4. CONTRÔLEURS — ÉTAT CIBLE

| Contrôleur | Responsabilité | Statut |
|---|---|---|
| Controller (abstract) | render, redirect, json, CSRF | ✅ |
| AdminController | dashboard, login, settings | ✅ |
| HomeController | pages frontend, contact, sitemap | ✅ → contact modifier |
| PageController | CRUD pages, sections, blocks, slides | ✅ |
| ProjectController | CRUD admin + **frontend public** | ✅ → ajouter public |
| MediaController | upload, delete, library | ✅ |
| BlogController | CRUD blog admin + frontend | ✅ |
| **MessageController** | inbox messages contact | 🆕 |
| **MenuController** | CRUD menus + items (drag & drop AJAX) | 🆕 |

---

## 5. MODÈLES — ÉTAT CIBLE

| Modèle | Table | Statut |
|---|---|---|
| Model (abstract) | — | ✅ |
| Page | pages | ✅ |
| Section | sections | ✅ |
| Block | blocks | ✅ |
| Setting | settings | ✅ |
| Media | media | ✅ |
| User | users | ✅ |
| Project | projects | ✅ → ajouter findBySlug, slug |
| HeroSlide | hero_slides | ✅ |
| Post | blog_posts | ✅ |
| Category | blog_categories | ✅ |
| **Tag** | blog_tags | 🆕 |
| **Comment** | blog_comments | 🆕 |
| **Message** | contact_messages | 🆕 |
| **Menu** | menus | 🆕 |
| **MenuItem** | menu_items | 🆕 |

---

## 6. VUES — ÉTAT CIBLE

### Admin
| Vue | Statut |
|---|---|
| admin/layout.php | ✅ → ajouter items sidebar |
| admin/dashboard.php | ✅ → ajouter stats |
| admin/settings.php | ✅ |
| admin/pages/* | ✅ |
| admin/projects/* | ✅ |
| admin/blog/* | ✅ |
| admin/media/index.php | ✅ |
| **admin/messages/index.php** | 🆕 |
| **admin/messages/show.php** | 🆕 |
| **admin/menus/index.php** | 🆕 |
| **admin/menus/edit.php** | 🆕 |

### Frontend
| Vue | Statut |
|---|---|
| frontend/layout.php | ✅ → menu dynamique |
| frontend/page.php | ✅ |
| frontend/section_renderer.php | ✅ |
| frontend/partials/hero.php | ✅ |
| frontend/sections/* | ✅ |
| frontend/blog_index.php | ✅ |
| frontend/blog_post.php | ✅ |
| **frontend/portfolio_index.php** | 🆕 |
| **frontend/portfolio_show.php** | 🆕 |
| **frontend/404.php** | 🆕 |

---

## 7. RELATIONS ENTRE MODULES

```
pages ─────────── sections (1→N)
                      └── blocks (1→N)
pages ─────────── hero_slides (1→N)

blog_posts ─────── blog_categories (N→1)
blog_posts ─────── blog_tags (N→N via blog_post_tags)
blog_posts ─────── blog_comments (1→N)

menus ──────────── menu_items (1→N)
menu_items ──────── menu_items (parent_id, self-ref sous-menus)
menu_items ──────── pages (page_id, optionnel)

contact_messages ── (autonome)

projects ────────── media (main_image, gallery, logo — chemins string)
media ───────────── (utilisé par tous les modules comme sélecteur)
settings ────────── (lu par tous les contrôleurs via Controller::render())
```

---

## 8. RÈGLES DE DÉVELOPPEMENT

1. **Toujours utiliser PDO paramétré** — jamais d'interpolation SQL directe
2. **Toujours valider CSRF** sur les POST
3. **Toujours appeler Cache::clear()** après modification de contenu
4. **Toujours utiliser `url()`** pour les chemins internes
5. **Toujours htmlspecialchars()** sur les données affichées
6. **Migrations idempotentes** — CREATE IF NOT EXISTS, ALTER ADD IF NOT EXISTS
7. **checkTableExists()** sur les modèles de tables créées par migration dynamique
8. **SQL logging désactivé** en production

---

## 9. PLAN DE MIGRATION SQL

### Ordre d'exécution
```
database/master_migration.php :

1. ALTER projects → ADD COLUMN slug, client, project_date (IF NOT EXISTS)
2. CREATE blog_tags (IF NOT EXISTS)
3. CREATE blog_post_tags (IF NOT EXISTS)
4. CREATE blog_comments (IF NOT EXISTS)
5. CREATE contact_messages (IF NOT EXISTS)
6. CREATE menus (IF NOT EXISTS)
7. CREATE menu_items (IF NOT EXISTS)
8. INSERT menu par défaut "Principal" avec items depuis pages existantes
9. Re-run blog tables (blog_posts, blog_categories) avec IF NOT EXISTS
10. Re-run hero_features / hero_articles ALTER (IF column NOT EXISTS)
```

### Idempotence garantie
Toutes les opérations utilisent :
- `CREATE TABLE IF NOT EXISTS`
- `ALTER TABLE ... ADD COLUMN IF NOT EXISTS` (MariaDB) ou try/catch PDOException duplicate column

---

## 10. CHECKLIST DE VALIDATION FINALE

Avant de considérer le CMS complet :

- [ ] `/` → HTTP 200, hero dynamique, sections dynamiques, menu depuis DB
- [ ] `/blog` → HTTP 200, articles, catégories, tags visibles
- [ ] `/blog/{slug}` → HTTP 200, article complet, commentaires, articles liés
- [ ] `/realisations` → HTTP 200, grille projets avec filtres catégorie
- [ ] `/realisations/{slug}` → HTTP 200, détail projet
- [ ] `/admin/dashboard` → 5+ stats cards, recent pages, recent media
- [ ] `/admin/blog` → liste articles, CRUD fonctionnel
- [ ] `/admin/messages` → inbox messages contact
- [ ] `/admin/menus` → CRUD menus + items + drag & drop
- [ ] `/admin/projects` → CRUD avec slug, client, date, description
- [ ] `/admin/settings` → tous les champs disponibles et sauvegardés
- [ ] Contact form → sauvegarde en DB + email envoyé
- [ ] Menu nav → 100% depuis DB, aucun lien hardcodé
- [ ] Footer nav → 100% depuis DB
- [ ] 0 erreur PHP, 0 erreur JS console
- [ ] Mobile menu toggle fonctionnel
