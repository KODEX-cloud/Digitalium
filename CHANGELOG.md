# CHANGELOG — Digitalium CMS Enterprise

Toutes les modifications notables de ce projet sont documentées ici.  
Format : [Sémantique de version](https://semver.org/lang/fr/)

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
