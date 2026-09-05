# Page « À propos / Notre vision » — conception

> Route `/a-propos` · Règle #3 : audit, risques et plan **avant** toute écriture de code.

---

## 1. TECHNICAL_AUDIT

### 1.1 La page n'existe pas

| Vérification | Résultat |
|---|---|
| `GET https://digitaliumgroup.com/a-propos` | **404** |
| `GET https://digitaliumgroup.com/about` | **404** |
| `sitemap.xml` | ne contient ni l'une ni l'autre |
| `database/seed.php:116`, `seed_prod.php:114` | une section `about` a existé sur l'**accueil**, jamais une page |

Le lien « À Propos » retiré du menu principal pointait donc vers une page inexistante. Aucun doublon
à craindre : il n'y a rien à réutiliser côté page.

### 1.2 Neuf blocs sur dix existent déjà

Le site compte **44 types de sections**. Le brief se replie presque entièrement dessus :

| Bloc demandé | Type existant | Champs disponibles |
|---|---|---|
| Hero (1250 × 500) | `hero_media_cards` | `badge`, `title`, `title_accent`, `text`, `cta1_*`, `cta2_*`, `image`, `layout`, `image_ratio`… |
| Qui sommes-nous | `about` | `tag`, `title`, `description` + groupes `val_icon`, `val_title`, `val_text` |
| Mission & Vision | `mission` | `tag`, `title`, `description` + groupes `card_icon`, `card_title`, `card_description` |
| Notre modèle (7 étapes) | `process_timeline` | `tag`, `title` + groupes `proc_num`, `proc_icon`, `proc_title`, `proc_desc` |
| Nos piliers (3) | `capabilities_grid` | `tag`, `title`, `subtitle`, **`cta_text`, `cta_url`** + groupes `cap_icon`, `cap_title`, `cap_desc` |
| Nos valeurs (5) | `values` | `tag`, `title` + groupes `val_icon`, `val_title`, `val_text` |
| Notre trajectoire | `flow_chain` | `tag`, `title`, `subtitle` + groupes `flow_label`, `flow_note`, `flow_icon`, `flow_accent` |
| Notre ambition | `process_strip` | `tag`, `title`, `subtitle` + groupes `proc_num`, `proc_icon`, `proc_title`, `proc_desc`, `proc_link` |
| CTA final | `cta` | `eyebrow`, `title`, `subtitle`, `cta_text`, `cta_url`, `cta2_text`, `cta2_url` |

Le bouton « Découvrir Digitalium Labs » demandé sous les piliers existe déjà : `cta_text`/`cta_url`
ont été ajoutés à `capabilities_grid` pour la page Labs. **Rien à créer de ce côté.**

### 1.3 Le seul manque réel : le module Équipe

Le brief exige que chaque collaborateur soit « créé, modifié, **ordonné**, **publié/dépublié** ».

`app/Models/Block.php` montre qu'un groupe répétable porte `group_id` et `sort_order` — donc la
création, la modification et l'ordre sont couverts — mais **aucun indicateur de publication**. Un
membre ne pourrait être retiré du site qu'en supprimant ses données.

Deux sections `team` existent déjà et restent utiles :

- `team.php` — grille de membres, pilotée par des **groupes de blocs** (`member_name`, `member_role`,
  `member_avatar`, `member_dept`, `member_linkedin`…) ;
- `team_roles.php` — grille de **rôles/expertises** (`role_title`, `role_sub`, `role_avatar`).

`team_roles` correspond exactement au repli demandé (« Engineering, AI & Data, Infrastructure,
Design, Business, Support ») — mais un repli doit être **automatique**, pas un second type de section
que l'administrateur devrait activer à la main le jour où il saisit son premier collaborateur.

### 1.4 Deux pièges connus du projet, applicables ici

1. **`section_renderer.php:17`** ignore tout type contenant `_hero`. Le fichier `about_hero.php`
   existe mais **n'est jamais rendu** : le hero doit être un `hero_media_cards`, comme sur l'accueil
   et sur Labs.
2. Les vues s'exécutent dans le **namespace global** : toute classe y est référencée pleinement
   qualifiée. `bin/check_views.php` le vérifie à chaque déploiement.

### 1.5 Contenu que le code n'a pas le droit d'inventer

| Élément | Décision |
|---|---|
| Collaborateurs | **aucun semé** — la table reste vide, le repli « pôles d'expertise » s'affiche |
| Dates de la trajectoire | **aucune** — les étapes sont des libellés, sans année |
| Chiffres | aucun ; `badge_years` du bloc « Qui sommes-nous » reste **vide** |
| Photos | bibliothèque média existante uniquement |

### 1.6 Ce que contient réellement la bibliothèque — décision qui en découle

Inventaire fait sur les pages en ligne : **six visuels seulement**, servis sous
`/public/assets/uploads/…` et `/public/assets/images/…`.

| Visuel | Verdict |
|---|---|
| `digitalium-hero-team.png` | **le seul conforme** — représentation professionnelle africaine, tonalité bleu/blanc. Déjà utilisé par le hero de l'accueil. |
| `about-team-meeting-…jpg` | équipe majoritairement occidentale dans un loft — contredit « privilégier une représentation professionnelle africaine » |
| `about_3d.png` | dominante violette — hors charte (« uniquement les couleurs Digitalium ») |
| `hero-pro-dashboard-…jpg`, `proj-*.jpg` | tableaux de bord — explicitement écartés pour le hero |

Deux conséquences :

1. le hero prend `digitalium-hero-team.png`, **partagé avec l'accueil** tant qu'un visuel dédié n'a pas
   été déposé dans la Bibliothèque Média ;
2. « Qui sommes-nous » utilise `about` (texte + cartes) et **non** `about_visual`, qui réclame une
   image : aucune seconde image ne convient, et une section institutionnelle avec un cadre d'image
   vide ferait moins bien que la même section sans image du tout.

---

## 2. RISK_ANALYSIS

| # | Risque | Probabilité | Impact | Prévention retenue |
|---|---|---|---|---|
| R1 | Le semis écrase du contenu saisi en admin | Moyenne | Élevé | Réconciliateur : position réalignée à chaque passage, **contenu semé seulement si la section est vide**, statut posé à la création uniquement |
| R2 | Une étape annexe (menu, accent) fait échouer la création de la page | Déjà survenu sur `/insights` | **Critique** | Chaque étape non essentielle dans son propre `try/catch` |
| R3 | Deux sections de même type sur la page se écrasent l'une l'autre | Certaine si non traité | Élevé | Réconciliation sur le couple **(type, nom)** — la page porte deux sections à base `proc_*` (`process_timeline` et `process_strip`) |
| R4 | La table `team_members` absente casse la page | Faible | **Critique** | La section attrape l'échec de lecture et bascule sur le repli, comme si aucun membre n'existait |
| R5 | Le module Équipe part vide et la section paraît cassée | Certaine au départ | Moyen | Repli automatique sur les pôles d'expertise, eux-mêmes administrables |
| R6 | L'entrée de menu duplique un lien « À Propos » | Moyenne | Faible | Recherche préalable sur l'URL **et** sur `page_id`, drapeau `about_nav_added_v1` en `settings` |
| R7 | Modification involontaire d'une autre page | Faible | Élevé | Aucun type de section existant n'est modifié ; seuls des **ajouts** (nouveau type, nouveaux libellés de champs) |
| R8 | Le nouveau type de section n'est créable depuis aucun écran | Déjà survenu (Insights, Labs) | Moyen | Ajout dans `sectionSkeletons()` **et** dans la liste déroulante de `admin/pages/edit.php` |

---

## 3. IMPLEMENTATION_PLAN

### Étape 1 — Modèle `TeamMember`

`app/Models/TeamMember.php`, calqué sur `LabProduct` (mêmes garde-fous : `colonnesExistantes()`
adapte l'écriture aux colonnes réellement présentes, borne `LIMIT` plafonnée puis injectée en clair
car PDO tourne sans émulation).

```
team_members
  id, name, role, department, bio, photo, linkedin, email
  sort_order, status (draft|published), created_at
```

`DEPARTEMENTS` = Engineering · AI & Data · Infrastructure · Design · Business · Support — la même
liste que le repli, pour qu'un membre saisi se range naturellement sous son pôle.

### Étape 2 — Section `team_members`

`app/Views/frontend/sections/team_members.php` :

1. lit `TeamMember::getPublic()` ;
2. **si au moins un membre publié** → grille photo / nom / fonction / bio / LinkedIn ;
3. **sinon** → grille des pôles d'expertise depuis les groupes de blocs (`pole_icon`, `pole_title`,
   `pole_desc`).

Le repli est dans la section elle-même : l'administrateur n'a rien à basculer le jour où il saisit
son premier collaborateur.

### Étape 3 — Administration `/admin/team`

`TeamController` + `admin/team/{index, create, edit, _form}.php` — un **seul** formulaire partagé
(leçon de `admin/projects/_form.php`), `MediaHelper::renderField()` pour la photo.

Routes ajoutées dans `routes/web.php` : `/admin/team`, `/admin/team/create` (GET+POST),
`/admin/team/edit/{id}` (GET+POST), `/admin/team/delete/{id}`. Toutes sous `/admin`, donc sans effet
sur le catch-all `/{slug}`.

### Étape 4 — Déclaration du nouveau type

- `PageController::sectionSkeletons()` → squelette `team_members` ;
- `admin/pages/edit.php` → l'option dans la liste déroulante ;
- `BlockFieldHelper` → libellés des champs `pole_*`.

### Étape 5 — Migration `database/build_about_page.php`

Réconciliateur isolé, sur le modèle de `build_labs_page.php` :
`CREATE TABLE team_members` (isolé) → page `a-propos` → accent → entrée de menu (isolée, drapeau)
→ 10 sections → `Cache::clear()`.

### Étape 6 — Pipeline

Étape non bloquante dans `.github/workflows/deploy.yml`, comme Labs et Insights.

### Étape 7 — Bancs d'essai

| Banc | Portée |
|---|---|
| `h_about_build.php` | premier passage, idempotence, contenu préservé, `CREATE TABLE` refusé, `settings` inaccessible, menu vide |
| `h_about_views.php` | rendu des 10 sections, repli Équipe, échappement XSS, aucun texte inventé |
| `h_about_admin.php` | CRUD Équipe, CSRF, publication/dépublication, ordre |
| `h_routes_about.php` | résolution des routes, priorité du catch-all |

### Ordre d'exécution

1. `TeamMember` → 2. section `team_members` → 3. administration → 4. déclaration du type →
5. migration → 6. pipeline → 7. bancs → 8. `PROJECT_STATE.md`.
