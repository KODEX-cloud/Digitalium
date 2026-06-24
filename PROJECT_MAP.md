# PROJECT_MAP — Digitalium Group CMS
> Produit le 2026-06-24 — cartographie complète de l'arborescence, contrôleurs, modèles, services, routes, vues et tables SQL.

---

## 1. ARBORESCENCE COMPLÈTE

```
Digitalium/
├── .env.example
├── .gitignore
├── .htaccess                       ← réécriture URL vers public/index.php
├── index.php                       ← point d'entrée racine (inclut public/index.php)
├── admin_fallback/index.php        ← fallback legacy (non utilisé activement)
│
├── app/
│   ├── Controllers/
│   │   ├── Controller.php          ← classe abstraite : render(), redirect(), json(), CSRF
│   │   ├── AdminController.php     ← dashboard, login, logout, settings
│   │   ├── HomeController.php      ← homepage, renderPage(), contactSubmit(), sitemap()
│   │   ├── PageController.php      ← CRUD pages + AJAX sections/blocks/slides
│   │   ├── ProjectController.php   ← CRUD réalisations admin
│   │   ├── MediaController.php     ← bibliothèque média : upload, delete
│   │   └── BlogController.php      ← CRUD blog admin + frontend listing/post
│   │
│   ├── Models/
│   │   ├── Model.php               ← classe abstraite : find, all, delete, count
│   │   ├── Page.php                ← table pages (updatePage, findBySlug, slugify)
│   │   ├── Section.php             ← table sections (getByPage, getActiveByPage, addSection)
│   │   ├── Block.php               ← table blocks (getStructuredContent, setVal, deleteGroup)
│   │   ├── Setting.php             ← table settings (getAll, getVal, setVal)
│   │   ├── Settings.php            ← alias stub extends Setting (compatibilité)
│   │   ├── Media.php               ← table media (add, deleteMedia, search)
│   │   ├── User.php                ← table users (Auth)
│   │   ├── Project.php             ← table projects (add, updateProject, getFeatured, checkTableExists)
│   │   ├── HeroSlide.php           ← table hero_slides (getByPage, add, updateSlide, checkTableExists)
│   │   ├── Post.php                ← table blog_posts (create, update, findBySlug, getPublished)
│   │   └── Category.php            ← table blog_categories (getAllWithCount, create, slugify)
│   │
│   ├── Services/
│   │   ├── Database.php            ← PDO singleton : query, fetch, fetchAll, insert, transactions
│   │   ├── Router.php              ← enregistrement routes + dispatch + validation CSRF globale
│   │   ├── Auth.php                ← check(), attempt(), logout(), user()
│   │   ├── CSRF.php                ← getToken(), validate()
│   │   ├── Cache.php               ← cache fichier avec TTL : get, set, clear
│   │   ├── Session.php             ← setFlash(), getFlash()
│   │   └── MediaManager.php        ← upload sécurisé, validation type/taille
│   │
│   ├── Helpers/
│   │   ├── Validator.php           ← règles : required, email, min, max
│   │   ├── Sanitizer.php           ← nettoyage données POST
│   │   ├── IconHelper.php          ← helper icônes Lucide
│   │   └── MediaHelper.php         ← helper URLs média
│   │
│   └── Views/
│       ├── admin/
│       │   ├── layout.php          ← shell admin : sidebar, header, scripts
│       │   ├── login.php
│       │   ├── dashboard.php       ← stats cards + recent pages + recent media
│       │   ├── settings.php        ← formulaire paramètres globaux
│       │   ├── pages/
│       │   │   ├── index.php       ← liste pages
│       │   │   ├── create.php      ← formulaire création page
│       │   │   └── edit.php        ← éditeur page complet (hero + sections + blocks + slides)
│       │   ├── projects/
│       │   │   ├── index.php
│       │   │   ├── create.php
│       │   │   └── edit.php
│       │   ├── media/
│       │   │   └── index.php
│       │   └── blog/
│       │       ├── index.php
│       │       ├── create.php
│       │       ├── edit.php
│       │       └── categories.php
│       │
│       └── frontend/
│           ├── layout.php          ← HTML complet : header nav dynamique + footer dynamique
│           ├── page.php            ← wrapper page : hero partial + section_renderer
│           ├── section_renderer.php← itère sections actives, require section views
│           ├── blog_index.php      ← listing blog public
│           ├── blog_post.php       ← article blog public
│           ├── partials/
│           │   ├── hero.php        ← hero unifié 10+ variants (split, full, magazine, slider…)
│           │   └── project_card.php
│           └── sections/
│               ├── hero.php            ← section hero avec stats (type: hero)
│               ├── services.php        ← type: services
│               ├── services_grid.php   ← type: services_grid
│               ├── services_hero.php   ← type: services_hero (skippé par renderer)
│               ├── about.php           ← type: about
│               ├── about_hero.php      ← type: about_hero (skippé)
│               ├── blog.php            ← type: blog (articles manuels)
│               ├── blog_grid.php       ← type: blog_grid
│               ├── blog_hero.php       ← type: blog_hero (skippé)
│               ├── blog_topics.php
│               ├── contact.php         ← type: contact (formulaire)
│               ├── contact_details.php
│               ├── contact_hero.php    ← type: contact_hero (skippé)
│               ├── cta.php
│               ├── faq.php
│               ├── features.php
│               ├── mission.php
│               ├── newsletter.php
│               ├── portfolio.php       ← type: portfolio (projets manuels)
│               ├── process.php
│               ├── process_strip.php
│               ├── services_strip.php
│               ├── team.php
│               ├── team_roles.php
│               ├── testimonials.php
│               ├── testimonials_grid.php
│               └── values.php
│
├── bin/
│   ├── seed.php
│   └── clear-cache.php
│
├── config/
│   └── config.php              ← ROOT_PATH, APP_PATH, DB_*, SESSION_*, url() helper
│
├── database/
│   ├── seed.php                ← seed principal
│   ├── add_blog_and_hero_features.php  ← migration blog + hero_features/hero_articles
│   ├── add_advanced_hero_header_fields.php
│   ├── add_hero_variant.php
│   ├── alter_pages_and_settings.php
│   ├── create_case_studies.php
│   ├── create_ia_page.php
│   ├── create_portfolio_pages.php
│   ├── migrate_portfolio_and_hero.php
│   ├── update_hero_schema.php
│   ├── update_images.php
│   └── update_page_heros.php
│
├── public/
│   ├── index.php               ← bootstrap : config, autoloader, Router, routes, dispatch
│   ├── .htaccess
│   ├── assets/
│   │   ├── css/index.css
│   │   ├── images/
│   │   └── uploads/            ← médias uploadés (UPLOAD_PATH)
│   ├── change_admin_prod.php   ← ⚠ DANGER : manipulation admin prod
│   ├── seed_prod.php           ← ⚠ DANGER : seed prod
│   ├── run_blog_migration.php  ← migration publique (doit être supprimée)
│   ├── run_pages_migration.php ← migration publique (doit être supprimée)
│   ├── read_logs.php           ← ⚠ exposition logs serveur
│   └── check_frontend.php      ← outil diagnostic
│
├── routes/
│   └── web.php                 ← toutes les routes GET/POST de l'application
│
└── storage/
    └── logs/
        ├── app.log
        ├── contacts.log
        ├── security.log
        └── php_error.log
```

---

## 2. CONTRÔLEURS — Méthodes et Responsabilités

| Contrôleur | Méthodes | Auth requise |
|---|---|---|
| `AdminController` | `dashboard`, `loginForm`, `loginSubmit`, `settingsForm`, `settingsSubmit`, `logout` | Oui (sauf login) |
| `HomeController` | `index`, `renderPage`, `contactSubmit`, `sitemap`, `render404` (private) | Non |
| `PageController` | `index`, `createForm`, `createSubmit`, `editForm`, `editSubmit`, `deletePage`, `addSection`, `sortSections`, `deleteSection`, `updateBlocks`, `addGroup`, `deleteGroup`, `addSlide`, `updateSlides`, `deleteSlide` | Oui |
| `ProjectController` | `index`, `createForm`, `createSubmit`, `editForm`, `editSubmit`, `delete` | Oui |
| `MediaController` | `index`, `upload`, `delete` | Oui |
| `BlogController` | `index`, `createForm`, `createSubmit`, `editForm`, `editSubmit`, `delete`, `categories`, `createCategory`, `deleteCategory`, `frontendIndex`, `frontendPost` | Oui (admin) / Non (frontend) |

---

## 3. ROUTES

### Frontend public
| Méthode | URL | Handler |
|---|---|---|
| GET | `/` | `HomeController@index` |
| GET | `/sitemap.xml` | `HomeController@sitemap` |
| POST | `/contact` | `HomeController@contactSubmit` |
| GET | `/blog` | `BlogController@frontendIndex` |
| GET | `/blog/{slug}` | `BlogController@frontendPost` |
| GET | `/{slug}` | `HomeController@renderPage` (catch-all, LAST) |

### Admin
| Méthode | URL | Handler |
|---|---|---|
| GET/POST | `/admin/login` | `AdminController@loginForm/loginSubmit` |
| GET | `/admin/logout` | `AdminController@logout` |
| GET | `/admin` `/admin/dashboard` | `AdminController@dashboard` |
| GET/POST | `/admin/settings` | `AdminController@settingsForm/settingsSubmit` |
| GET | `/admin/media` | `MediaController@index` |
| POST | `/admin/media/upload` | `MediaController@upload` |
| POST | `/admin/media/delete` | `MediaController@delete` |
| GET/POST | `/admin/projects` | `ProjectController@index/create/edit/delete` |
| GET/POST | `/admin/pages` | `PageController@index/create/edit/delete` |
| POST | `/admin/pages/sections/add` etc. | `PageController@addSection/sortSections/deleteSection/updateBlocks/addGroup/deleteGroup` |
| POST | `/admin/pages/slides/add` etc. | `PageController@addSlide/updateSlides/deleteSlide` |
| GET/POST | `/admin/blog` | `BlogController@index/create/edit/delete/categories/createCategory/deleteCategory` |

---

## 4. MODÈLES — Tables SQL associées

| Modèle | Table | Colonnes principales |
|---|---|---|
| `Page` | `pages` | id, title, slug, meta_*, status, sort_order, in_navigation, hero_*, header_*, logo_*, responsive_settings, hero_features, hero_articles |
| `Section` | `sections` | id, page_id, name, type, sort_order, status |
| `Block` | `blocks` | id, section_id, block_key, type, value, group_id, sort_order |
| `Setting` | `settings` | id, setting_key, setting_value |
| `Settings` | (alias Setting) | — |
| `Media` | `media` | id, filename, filepath, original_name, file_size, mime_type |
| `User` | `users` | id, username, password, email |
| `Project` | `projects` | id, title, category, logo, main_image, gallery, context, impact, technologies, external_link, sort_order, is_featured |
| `HeroSlide` | `hero_slides` | id, page_id, title, subtitle, badge, image, cta_text, cta_url, sort_order |
| `Post` | `blog_posts` | id, title, slug, excerpt, content, featured_image, category, category_id, author, status, is_featured, meta_title, meta_description, tags, published_at |
| `Category` | `blog_categories` | id, name, slug, description |

---

## 5. FLUX DE DONNÉES — Backend → DB → Frontend

```
[Visiteur] GET /
  → Router → HomeController@index
    → HomeController@renderPage(['slug'=>'home'])
      → Cache::get('page_home') ou
        → Page::findBySlug('home')
        → Section::getActiveByPage($id)
        → Block::getStructuredContent($sectionId) × N
        → Setting::getAll()
        → Page::all() → filtrage in_navigation
      → render('frontend/page', [...], 'frontend/layout')
        → layout.php : header nav dynamique ($menuPages) + footer ($settings)
        → page.php : hero partial + section_renderer
          → partials/hero.php : variant hero_* avec $page['hero_*']
          → section_renderer.php : foreach $sections → require sections/*.php
```

```
[Admin] POST /admin/pages/edit/{id}
  → Router (CSRF global) → PageController@editSubmit
    → middlewareAuth() → validateCsrf()
    → Validator → Page::findBySlug (slug conflict check)
    → Page::updatePage($id, $data) → UPDATE pages SET hero_*...
    → Cache::clear()
    → redirect(/admin/pages/edit/{id}, 'success')
```

---

## 6. SETTINGS — Clés reconnues par l'application

| Clé | Usage |
|---|---|
| `site_name` | Titre onglet, footer, logo text |
| `site_logo` | Logo header/footer |
| `site_logo_light` / `site_logo_dark` | Logo selon mode contraste |
| `site_logo_mobile` | Logo responsive |
| `site_logo_text` / `site_logo_subtext` | Texte logo si pas d'image |
| `site_favicon` | Favicon |
| `site_whatsapp` | Lien WhatsApp footer |
| `header_cta_text` / `header_cta_link` | Bouton CTA header nav |
| `footer_slogan` / `footer_pitch` | Description footer |
| `footer_cta_text` / `footer_cta_link` | CTA footer |
| `footer_copyright` | Copyright footer |
| `footer_legal_text` / `footer_legal_url` | Lien mentions légales |
| `contact_address` / `contact_phone` / `contact_email` | Coordonnées footer |
| `social_facebook` / `social_linkedin` / `social_twitter` / `social_instagram` / `social_youtube` / `social_github` | Liens réseaux sociaux |
