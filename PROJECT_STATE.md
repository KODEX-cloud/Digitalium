# PROJECT_STATE — Digitalium Group CMS
> Dernière mise à jour : 2026-08-31 — Homepage v2 — Refonte fidèle au visuel, 100% CMS

---

## ÉTAT GLOBAL : 🚀 ENTERPRISE CI/CD v2.0 — DÉPLOIEMENT TOTALEMENT AUTONOME

**Homepage v2 (2026-08-31)** — Nouvelle page d'accueil construite fidèlement à la maquette fournie par le client, 100% pilotée par le CMS (`pages` → `sections` → `blocks`), zéro hardcode.
**⚠ ACTION REQUISE UTILISATEUR** : exécuter `php database/build_home_v2.php` sur l'environnement WAMP local (cette session n'a pas de PHP CLI accessible — aucun `php.exe` trouvé sur `C:\`, à vérifier), puis uploader les images manquantes (hero, photo équipe "8+ ans", 3 réalisations, 6 avatars membres) depuis `/admin/pages/edit/{id}` → onglet Médiathèque. Voir détail en fin de fichier.

### Nouveaux types de section (Homepage v2)
- `logos_strip` — bandeau logos/clients ("Ils nous font confiance")
- `stats_intro` — intro texte + grille de 4 statistiques
- `about_visual` — image + badge overlay + checklist
- `projects_showcase` — vitrine curatée de 3 réalisations (distinct de `portfolio`, qui liste toute la table `projects`)
- `testimonials_carousel` — témoignages en carrousel (flèches nav, sans étoiles ; distinct de `testimonials`)
- `team` (existant, enrichi) — nouveau champ `member_dept` (pastille département colorée) + `single.more_text`/`more_url`

Fichiers : `app/Views/frontend/sections/{logos_strip,stats_intro,about_visual,projects_showcase,testimonials_carousel}.php`, `team.php` (modifié), `database/build_home_v2.php` (seed idempotent).

**CI/CD v2.0 (2026-07-25)** — Pipeline GitHub Actions entièrement réécrit. Autonome, auto-réparable, rollback automatique.  
**⚠ BLOQUEUR** : Secrets GitHub non configurés — déploiement impossible jusqu'à leur ajout (voir `docs/github-secrets.md`).  
**UI/UX Light Theme (2026-07-25)** — Refonte complète frontend : thème blanc/teal, hero navy, sections redesignées.  
**Recovery Center v1.5 maintenu** — Restauration complète depuis `/admin/system/recovery` sans SSH.

### Garanties CI/CD v2.0
- ✅ Pre-flight : validation des secrets avec messages explicites si manquants
- ✅ PHP Syntax Check avant tout déploiement
- ✅ Détection premier déploiement (git init + clone automatique)
- ✅ 8 phases SSH : git sync → permissions → migrations → cache → deploy → intégrité → smoke tests → résumé
- ✅ Modes : full / quick / repair / rollback (workflow_dispatch)
- ✅ Notifications Slack optionnelles (succès + échec)
- ✅ Zero human intervention après configuration des secrets
- ✅ Rollback SQL via `workflow_dispatch --mode=rollback`
- ✅ Documentation complète dans `docs/` (6 fichiers)

### Garanties Recovery Center v1.5
- ✅ Restauration complète depuis le navigateur — aucun SSH requis
- ✅ 16 diagnostics temps réel à l'ouverture de la page
- ✅ Pipeline 11 phases (BootCheck → Backup → Migrate → Sync → Cache → Assets → Uploads → Menus → Settings → Health → Smoke)
- ✅ Progress bar + terminal log animé en temps réel
- ✅ Auto-Rollback SQL si erreur critique détectée
- ✅ Toggle maintenance (ON/OFF) depuis la page
- ✅ Rapport final détaillé (étapes, durées, smoke tests)
- ✅ Rollback d'urgence depuis l'interface

### Garanties Enterprise CI/CD v1.4
- ✅ Push sur `main` → déploiement automatique Hostinger (GitHub Actions)
- ✅ Backup SQL automatique avant chaque déploiement (RollbackManager)
- ✅ Rollback automatique si HealthCheck score < 5 (bin/deploy.php)
- ✅ Rollback manuel depuis Deploy Center (`/admin/system/deploy-center`)
- ✅ Historique complet des déploiements (`storage/deployments/*.json`)
- ✅ Smoke tests HTTP automatiques (/ /blog /admin /sitemap.xml)
- ✅ Pipeline idempotent — exécutable 100× sans effet de bord

### Garanties Enterprise Fault Tolerant
- ❌ Stack trace jamais visible en production
- ❌ Erreur SQL jamais fatale pour le frontend
- ❌ Menu absent jamais bloquant (fallback pages)
- ❌ Table manquante jamais fatale
- ✅ Chaque module peut tomber indépendamment
- ✅ BootCheck valide l'état avant tout déploiement
- ✅ DeployPipeline annulé si check critique échoue

---

## COMMITS GIT

| Hash | Description | Date | Production |
|---|---|---|---|
| `5862b4d` | **feat: amélioration premium page À Propos — Design System v4.1** | 2026-07-29 | ✅ Déployé |
| `ff9ee06` | feat: Enterprise CI/CD v2.0 + Documentation complète | 2026-07-25 | ✅ |
| `3c9264d` | fix: suppression `environment: production` (bloquage deploy) | 2026-07-25 | ✅ |
| `5d55994` | feat: UI/UX Light Theme — CSS v3.0, sections redesignées | 2026-07-25 | ✅ |
| `258e3b0` | fix: CRITICAL — Restauration production + Mode Maintenance | 2026-07-05 | ✅ |
| `e4ac231` | feat: Enterprise CI/CD v1.4 — GitHub Actions + RollbackManager | 2026-07-05 | ✅ |
| `2da7bd4` | CMS Enterprise — Stabilisation | 2026-06-24 | ✅ |

**Remote :** `https://github.com/KODEX-cloud/Digitalium.git` — branche `main`  
**Déploiement :** GitHub Actions → SSH Hostinger (autonome après config secrets)

---

## MODULES ✅ COMPLETS

| Module | Fichiers clés | Statut |
|---|---|---|
| Pages CMS | `Page.php`, `PageController.php`, `pages/` views | ✅ CRUD + sections + blocs |
| Hero | `HeroSlide.php`, `partials/hero.php`, `sections/hero.php` | ✅ Slides + stats + CTAs |
| Sections/Blocs | `Section.php`, `Block.php`, `section_renderer.php` | ✅ 27 types dynamiques |
| Blog | `Post.php`, `BlogController.php`, `blog/` views | ✅ CRUD + tags + catégories |
| Commentaires blog | `Comment.php`, `admin/blog/comments.php` | ✅ Frontend + modération admin |
| Tags blog | `Tag.php`, sync automatique | ✅ Normalisé + texte |
| Réalisations | `Project.php`, `ProjectController.php`, `portfolio_*.php` | ✅ CRUD + public |
| Contact | `Message.php`, `HomeController.php`, `contact_details.php` | ✅ AJAX + inbox + no hardcode |
| Messages admin | `MessageController.php`, `messages/` views | ✅ Inbox + archivage |
| Menus | `Menu.php`, `MenuItem.php`, `MenuController.php` | ✅ DB-driven + drag-drop |
| Médias | `Media.php`, `MediaController.php`, `media/` views | ✅ Upload + library picker |
| Settings | `Setting.php`, `AdminController.php`, `settings.php` | ✅ Branding + couleurs + scripts |
| SEO | `pages` table + `layout.php` | ✅ Meta + OG + sitemap.xml |
| Couleurs admin | `settings.php` + `layout.php` CSS inject | ✅ 5 variables CSS |
| Scripts admin | `settings.php` + `layout.php` inject | ✅ head/footer scripts + custom CSS |
| 404 stylée | `frontend/404.php`, `HomeController::render404()` | ✅ Layout complet |
| Gouvernance | `CLAUDE.md`, `PROJECT_STATE.md`, `CHANGELOG.md`, `RELEASE_NOTES.md` | ✅ |
| **DSM v1.1** | `app/System/` — 15 managers SOLID + 10 migrations métier | ✅ déployé |
| **DSM OS v1.2** | `DeployPipeline`, `SelfHealManager`, `GitManager`, `PerformanceManager`, `SystemApiController`, `bin/dsm_cli.php`, `deploy_center.php` | ✅ Operating System actif |
| **Fault Tolerant v1.3** | `ErrorHandler.php`, `BootCheck.php`, `Database.php` (safe reads), `HomeController.php` (isolated sections), `Views/errors/500+503.php`, `public/index.php` | ✅ Production hardened |
| **CI/CD v1.4** | `.github/workflows/deploy.yml`, `bin/deploy.php` (11 phases), `RollbackManager.php`, `DeploymentLog.php`, `deploy-center` amélioré, routes deploy-log + rollback-latest | ✅ Pipeline automatisé |
| **Recovery Center v1.5** | `RecoveryController.php`, `Views/admin/system/recovery.php`, 4 routes API, sidebar link, `bin/recover-production.php` | ✅ Restauration browser sans SSH |

---

## DETTE TECHNIQUE RÉSOLUE ✅

| Ref | Description | Résolution |
|---|---|---|
| DT-01 | `Settings.php` doublon | ✅ Supprimé — zéro références confirmées |
| BUG-01 | render404() HTML brut | ✅ Layout complet |
| BUG-02 | POST /blog/comment manquant | ✅ Endpoint créé |
| B-01 | onclick contact bug | ✅ Supprimé |
| B-02 | Options contact hardcodées | ✅ DB-driven via blocs |
| B-03 | Modération commentaires absente | ✅ Module complet |
| B-04 | .env production absent | ✅ Créé + documenté |

## DETTE TECHNIQUE RESTANTE

| Ref | Description | Sévérité | Note |
|---|---|---|---|
| DT-02 | `blog_posts.tags` (texte) + `blog_post_tags` (table) | Faible | Double source intentionnelle — Tag::syncForPost() maintient la cohérence |
| BUG-04 | HTTP 200 sur 404 (WAMP local) | Dev only | Non reproductible en production PHP-FPM |
| OPS-01 | BootCheck + ErrorHandler non enregistrés dans l'index.php de boot principal | Faible | Handlers inline dans index.php suffisent — ErrorHandler::register() est disponible pour future intégration |

---

## VALIDATION LOCALE (post-certification)

| URL | HTTP | Temps | Résultat |
|---|---|---|---|
| `/` | 200 | 1600ms | ✅ |
| `/blog` | 200 | 1663ms | ✅ |
| `/realisations` | 200 | 708ms | ✅ |
| `/contact` | 200 | 651ms | ✅ |
| `/admin` | 200 | 691ms | ✅ |
| `/admin/blog` | 200 | 694ms | ✅ |
| `/admin/blog/comments` | 200 | 635ms | ✅ NOUVEAU |
| `/admin/messages` | 200 | 639ms | ✅ |
| `/admin/menus` | 200 | 686ms | ✅ |
| `/admin/settings` | 200 | 681ms | ✅ |
| `/admin/projects` | 200 | 676ms | ✅ |
| `/admin/pages` | 200 | 706ms | ✅ |
| `/admin/media` | 200 | 790ms | ✅ |

---

## ADMINISTRABILITÉ — MATRICE COMPLÈTE

| Élément visible | Admin-éditable | Via |
|---|---|---|
| Logo (desktop/mobile/clair/sombre) | ✅ | Settings → Branding |
| Favicon | ✅ | Settings → Branding |
| Nom du site | ✅ | Settings → site_name |
| Header CTA (texte + lien) | ✅ | Settings → header_cta_* |
| Navigation (menus) | ✅ | Admin → Navigation |
| Hero (titre/sous-titre/badge) | ✅ | Blocs → hero |
| Hero CTA × 2 | ✅ | Blocs → hero |
| Hero image | ✅ | Blocs → hero → bg_image |
| Hero stats (3 cards) | ✅ | Blocs → hero |
| Slides | ✅ | Admin → Pages → Slides |
| Services | ✅ | Blocs → services_grid |
| Blog articles | ✅ | Admin → Blog |
| Blog catégories | ✅ | Admin → Blog → Catégories |
| Blog tags | ✅ | Édition article |
| Blog commentaires | ✅ | Admin → Commentaires |
| Réalisations | ✅ | Admin → Réalisations |
| Contact (adresse/tél/email) | ✅ | Settings + Blocs |
| Contact options services | ✅ | Blocs → services_primary_list |
| Contact options additionnelles | ✅ | Blocs → services_extra_list |
| Coordonnées footer | ✅ | Settings → Coordonnées |
| Réseaux sociaux (6) | ✅ | Settings → Réseaux sociaux |
| Footer slogan/CTA/copyright | ✅ | Settings → Header/Footer |
| Footer lien légal | ✅ | Settings |
| WhatsApp | ✅ | Settings → site_whatsapp |
| SEO (meta/title/desc) | ✅ | Admin → Pages |
| Images / Médias | ✅ | Bibliothèque Média |
| Couleurs (5 CSS vars) | ✅ | Settings → Couleurs & Thème |
| Scripts tracking | ✅ | Settings → Scripts & CSS |
| CSS personnalisé | ✅ | Settings → Scripts & CSS |
| Typographies | ✅ | Settings → custom_css |
| Pages CMS | ✅ | Admin → Pages |
| Sections | ✅ | Admin → Pages → Sections |
| Blocs | ✅ | Admin → Pages → Blocs |

---

## PROCHAINES ÉTAPES

### ⚠️ ACTION IMMÉDIATE — Activer la Homepage v2 (2026-08-31)
1. **[ACTION USER]** Exécuter `php database/build_home_v2.php` (chemin PHP CLI documenté en §1 de CLAUDE.md à vérifier — non trouvé dans cette session).
2. **[ACTION USER]** Ouvrir `/admin/pages/edit/{id de la page home}` et uploader via la Médiathèque : l'image hero, la photo "8+ ans d'expérience", les 3 images de réalisations, les 6 avatars de l'équipe.
3. **[ACTION USER]** Vérifier le rendu sur `/` (desktop, tablette, mobile) et confirmer la fidélité au visuel fourni.
4. **[ACTION USER]** Vérifier que le menu principal contient bien Accueil/À propos/Services/Réalisations/Blog/Contact (`/admin/menus`) — non modifié par cette tâche.

### ✅ Dashboard production restauré (2026-07-29)
- `/admin/dashboard` en production : HTTP 200 confirmé par l'utilisateur
- Commit `5862b4d` déployé via CI/CD Hostinger

### En attente de validation utilisateur
- **[USER]** Valider la page `/a-propos` en production (Desktop, Tablette, Mobile)
- Après validation → amélioration page suivante (stratégie page par page)

### Amélioration Design System v4.1 — À Propos (commit `5862b4d`)
- `sections/about.php` — gradient checkpoints, val-cards accent cyclique, décoration glow
- `sections/mission.php` — icônes gradient, bg-alt, bordure latérale accent
- `sections/values.php` — vbar-top animée, icon→gradient hover, transition-delay
- `sections/team.php` — avatar ring gradient, social links premium
- `sections/team_roles.php` — ::before top bar, avatar gradient hover, flèche hint
- 17/17 tests régression backend passés

### Prochaines évolutions
- **[P1-UX]** Amélioration page suivante (après validation À Propos)
- **[P2-DEV]** Gestion multi-utilisateurs (admin users CRUD)
- **[P3-DEV]** Galerie d'images avancée pour réalisations
- **[P2-OPS]** Monitoring automatique : cron BootCheck + alerte email si check critique échoue

---

## HOMEPAGE V2 — ZÉRO HARDCODE + DÉDOUBLONNAGE (2026-09-02)

### Règle #2 appliquée intégralement à la page d'accueil (commit `8f09dd6`)
Audit des 9 sections + hero. Tout texte/libellé/lien écrit en dur supprimé :
- `sections/team.php` — badge « Équipe » en dur → bloc CMS `tag` ; **suppression du bloc
  de repli contenant 2 faux membres fictifs codés en dur** (« Alexandre Dumas »,
  « Thomas Morel ») affichés dès que la grille était vide ; fallbacks « Nos Experts »,
  « Nom du Membre », « Consultant » supprimés
- `sections/projects_showcase.php` — libellé « Résultat : » en dur → bloc `result_label`
- `sections/process_timeline.php` — numéro d'étape calculé (`$i+1`) ignorait le champ CMS
  `proc_num` → lit désormais la valeur saisie en admin
- `sections/testimonials_carousel.php` — message d'état vide en dur supprimé
- `stats_intro`, `about_visual`, `services_grid_v2`, `logos_strip`, `cta` — tous les
  fallbacks de titres/sous-titres et liens en dur (`/contact`, `/realisations`, `/service`)
  supprimés ; chaque champ vide est masqué au lieu d'afficher du texte du code
- `partials/hero.php` — `alt="Corporate Visual"` → titre du hero (contenu CMS)

**Nouveaux blocs CMS :** `team.tag`, `projects_showcase.result_label` — créés en production
par `database/fix_hero_layout_v2.php` (idempotent, auto-exécuté au déploiement).

### BUG-DUP-01 — Sections dupliquées (commit `2cd92db`) — CORRIGÉ
La page rendait **11 sections au lieu de 9** : `services_grid_v2` et `process_timeline`
affichées deux fois (12 cartes de services, frise des 6 étapes en double).
Cause : le type-swap de `fix_hero_layout_v2.php` renommait l'ancienne section vers le
nouveau type même si une section de ce type existait déjà.
Correctifs : (1) le swap désactive l'ancienne section au lieu de la renommer si le type
cible existe ; (2) passe de dédoublonnage auto-réparatrice à chaque déploiement —
conserve la section portant le plus de blocs, désactive les autres (statut `inactive`,
jamais de suppression — Règle #4, réactivable depuis `/admin/pages`).

### Preuves production (Règle #5) — `https://digitaliumgroup.com/`, HTTP 200
| Contrôle | Avant | Après |
|---|---|---|
| Sections rendues | 11 | **9** |
| Types en double | 2 | **0** |
| `.svc-v2-card` | 12 | **6** |
| `.proc-timeline-step` | 12 | **6** |
| Poids page | 100 551 o | **84 019 o** |
| Badge équipe | (en dur) | **« Notre équipe »** depuis la base |
| Libellés `Résultat :` | (en dur) | **3/3** depuis la base |
| Faux membres en dur | 2 | **0** |
| Membres réels affichés | — | **6/6** |

Runs CI/CD : #31 (`8f09dd6`) et #32 (`2cd92db`) — succès.

### Reconstruction fidèle au modèle de référence (commit `037ecf6`, run #33)
Alignement des gabarits sur le visuel fourni, sans réintroduire de hardcode :
- `sections/stats_intro.php` — 4 cartes sur **une rangée** (au lieu d'une grille 2×2) et
  nouveau bloc **`stat_label`** : chaque carte porte le nombre en bleu, le libellé en gras
  et la description grise
- `sections/services_grid_v2.php` — carte **horizontale** (`grid-template-columns: 52px 1fr`) :
  pastille d'icône colorée à gauche, titre + texte à droite, flèche ronde en bas à droite
- `sections/process_timeline.php` — pastille numérotée pleine **suivie** de la pastille
  d'icône (côte à côte), reliées par la ligne pointillée
- `sections/cta.php` — bandeau en **dégradé bleu** avec motif réseau, titre/sous-titre à
  gauche et boutons à droite (au lieu d'un bloc sombre centré)
- `frontend/layout.php` — footer réordonné selon le modèle
  (**Liens utiles / Services / Contact / Newsletter**), **colonne Newsletter ajoutée**
  (champ email + bouton, protégée CSRF + honeypot) ; titres de colonnes, coordonnées,
  libellé « Remonter » et logo de repli désormais pilotés par Settings ; la liste des
  services du footer est lue depuis la section Services de l'accueil (`services_grid_v2`)
  au lieu d'une liste écrite en dur

**Nouveaux réglages Settings** (créés automatiquement par `fix_hero_layout_v2.php`) :
`footer_nav_title`, `footer_services_title`, `footer_contact_title`,
`footer_newsletter_title`, `footer_newsletter_text`, `footer_newsletter_placeholder`,
`footer_backtotop_text`.

**Preuves production (Règle #5)** — `HTTP 200`, 9 sections / 0 doublon :
4 `.stat-intro-card` + 4 `.stat-intro-label` · 6 `.svc-v2-card` + 6 `.svc-v2-body` ·
6 `.proc-timeline-head` (num + icône) · `.cta-band` avec dégradé bleu et 2 boutons ·
footer `["Liens utiles","Services","Contact","Newsletter"]` + champ newsletter ·
0 occurrence de « Ingénierie Logicielle », « Applications Cloud », « SEO & Stratégie »,
« Paris, France ».

### Reste à faire — page d'accueil
- **[USER]** Uploader les 6 photos réelles de l'équipe et les 3 avatars clients
  (témoignages) via `/admin/pages` → Médiathèque — actuellement icônes de substitution
- **[USER]** Remplacer si besoin les 5 photos d'illustration (hero, « 8+ ans », 3 cartes
  Réalisations), aujourd'hui des photos de banque d'images sous licence libre

---

## 2026-09-03 — CALIBRAGE VISUEL HOMEPAGE (commit 9a19d9e)

Retour utilisateur : « la page d'accueil est toujours délabreuse ». Audit sur la
production : **aucune casse technique** (6/6 images et 2/2 feuilles CSS en HTTP 200).
Le problème était un calibrage d'espacement et de grilles.

### Bugs corrigés

| Ref | Défaut | Cause | Correctif |
|---|---|---|---|
| BUG-CAL-01 | Sections flottant dans le vide | `theme_space_section = 130` → 260px entre sections | → `92` (60 / 42 dérivés) |
| BUG-CAL-02 | Vide massif sous le hero | `min-height: 65vh` + `padding 140/80` + centrage | `min-height: 0`, `padding 116/84` |
| BUG-CAL-03 | Titre stats cassé en 4 lignes, cartes illisibles | `1fr 2.6fr` (~300px) et `repeat(4,1fr)` (~175px/carte) | `1fr 1.55fr` et cartes en 2×2 |
| BUG-CAL-04 | Équipe en 4+2, rangée orpheline | `.team-grid: repeat(auto-fill,minmax(240px,1fr))` | `repeat(3,1fr)`, `max-width: 940px` |
| BUG-CAL-05 | Bandeau logos minuscule (9 items) | `font-size: 0.82rem`, `padding: 0 20px` | `0.95rem`, `padding: 4px 26px` |

### Point d'architecture — piège de la double couche

`--space-section` n'est **pas** piloté par `index.css` : il est administrable via
`settings.theme_space_section` et injecté en ligne dans `<style id="cms-theme">`
(`app/Views/frontend/layout.php:40`), ce qui écrase la feuille de style. Modifier
`index.css` seul n'aurait eu aucun effet en production. Quatre points ont donc été
alignés : seed `master_migration.php:266`, fallback `layout.php:40`, panneau
`/admin/theme`, variables `index.css:66-68`.

La valeur `130` étant déjà en base, la correction est appliquée par
`database/fix_hero_layout_v2.php` avec **garde d'idempotence** : elle ne s'applique
que si la valeur est encore celle d'origine, jamais sur une valeur choisie par
l'admin. L'espacement reste pilotable depuis `/admin/theme` (Règle #2 préservée).

### Preuve de production (Règle #5)

```
HTTP 200 · 9 sections · 0 doublon
--space-section    : 92px / 60px / 42px      (était 130 / 80 / 54)
hero               : padding 116px 0 84px 0 · min-height 0
.stats-intro-grid  : 1fr 1.55fr
.stats-intro-cards : repeat(2, 1fr) — 4 libellés intacts
.team-grid         : repeat(3,1fr) max-width 940px — 6 membres
```

### Dette de sécurité découverte (non corrigée — à valider)

| Ref | Description | Priorité |
|---|---|---|
| SEC-01 | `master_migration.php:294-312` force le hash admin à **chaque** déploiement : tout mot de passe changé depuis l'admin est écrasé au push suivant. Devrait être *insert-if-missing*. | Haute |
| SEC-02 | Mot de passe admin en clair dans le dépôt Git (`master_migration.php:296`, commentaire). | Haute |

---

## 2026-09-03 — THÈME V3 « FINTECH VERT PROFOND » (commit b5b40af)

Nouveau modèle de référence fourni par la direction. Consigne : appliquer
intégralement le thème et le design, ne conserver que les informations.

### Palette (extraite pixel par pixel du visuel, via GD)

| Rôle | Valeur | Usage dans le modèle |
|---|---|---|
| Primaire | `#1D6363` | aplats, panneaux, footer — **jamais** sur un bouton |
| Secondaire | `#346A6D` | pastilles d'icônes |
| Accent | `#004D3F` | texte des badges |
| Texte principal | `#272727` | charbon, pas noir pur |
| Texte secondaire | `#4D4F63` | ardoise |
| Fond de page | `#FBFBFB` | blanc cassé |
| Fond doux | `#E8F3E9` | menthe |
| CTA | `#272727` | charbon — trait distinctif du modèle |

Rayons : rectangles arrondis (`btn 12`, `pill 8`, `card 22`), pas de pilules.

### 7 jetons de design introduits (tous administrables)

`--btn-primary-bg`, `--btn-primary-text`, `--badge-bg`, `--badge-text`,
`--footer-bg`, `--surface-dark` — déclarés dans `layout.php`, inscrits dans la
liste blanche de `AdminController::saveTheme()` et seedés dans `master_migration.php`.

### Violations de la Règle #2 corrigées

Trois éléments rendaient tout changement de thème impossible :

| Élément | Avant | Après |
|---|---|---|
| `.btn-primary` | `background: var(--primary)` | `var(--btn-primary-bg)` |
| `.section-badge` | `rgba(37,99,235,0.08)` **en dur** | `var(--badge-bg)` / `var(--badge-text)` |
| `.site-footer` | `background: #0f172a` **en dur** | `var(--footer-bg)` |

### CSS rendu agnostique de la couleur

89 occurrences de `rgba(37,99,235,…)` / `rgba(8,145,178,…)` / `rgba(245,158,11,…)`
converties en `color-mix(in srgb, var(--primary) N%, transparent)` dans `index.css`
et 40 vues frontend (blog, portfolio, 404, partials inclus).
**Résiduel de bleu/indigo dans tout le frontend : 0.**

### Application en production

`database/apply_theme_v3.php` (ajouté au pipeline avant `fix_hero_layout_v2.php`),
protégé par `storage/theme_v3.lock` : palette posée **une seule fois**, jamais
réécrite ensuite — les ajustements faits depuis `/admin/theme` sont préservés.

### Preuve de production (Règle #5)

```
HTTP 200 · 91KB · 9 sections · 0 doublon
--primary #1d6363   --secondary #346a6d   --accent #004d3f
--text-main #272727 --text-muted #4d4f63
--bg-base #fbfbfb   --bg-alt #e8f3e9
--btn-primary-bg #272727   --badge-bg #e0f1df   --badge-text #004d3f
--footer-bg #1d6363        --surface-dark #12202c
--radius-btn 12px   --radius-pill 8px   --radius-lg 22px
CSS servi : 0 rgba(37,99,235) · 46 color-mix
.btn-primary -> var(--btn-primary-bg)   .site-footer -> var(--footer-bg)
```

### Reste à faire — alignement structurel sur le modèle

Le thème est appliqué ; la **structure** de certaines sections diffère encore :
- bandeau logos : le modèle utilise de grands sigles gris clair, pas du texte fin
- cartes services : une carte mise en avant en aplat vert profond
- hero : bloc de preuve sociale (avatars + grand chiffre + citation) sous les CTA
- section FAQ en accordéon (absente du site)

---

## 2026-09-03 — TYPOGRAPHIE, ESPACEMENTS ET FORMES DE CARTES (commit 9c499e4)

Retour : « pas bon — applique les mêmes styles d'écritures, d'espacements, les
tailles et formes des cartes ».

### Méthode : mesure, pas estimation

Analyse pixel du visuel de référence via GD : détection des bornes de la maquette
navigateur (x=240..3131, largeur 2891 px pour un viewport 1440) → **échelle
2.008 px image = 1 px CSS**. Puis profil de lignes (luminance < 140) pour relever
hauteurs de glyphes et interlignes.

### Écarts relevés et corrigés

| Élément | Modèle | Site avant | Après |
|---|---|---|---|
| H1 | ~69 px | 67 px | 4.3 rem |
| H2 | ~48 px | 45 px | 3 rem |
| Titres de carte | 20 px | 17 px | 1.25 rem |
| Interligne du corps | ~1.40 | **1.78** | 1.55 |
| Interlettrage titres | — | -0.032 | -0.02 |
| Police du corps | une seule famille | Inter (titres en Jakarta) | Plus Jakarta Sans partout |
| Hauteur des boutons | 57 px | **46 px** | padding 17/34, 1.05 rem |
| Chapô du hero | 20 px, interligne 1.45 | 16.8 px, interligne **1.85** | 1.25 rem / 1.45 |
| Badge | 14 px, casse normale, sans point | 10.7 px, MAJUSCULES, point pulsant | 0.875 rem, casse normale, fond menthe |
| Rayon des boutons | ~14 px | 12 px | 14 px |
| Rayon des pilules | rectangle arrondi ~9 px | **100 px** (pilule) | 9 px |

`.hero-description` portait aussi une couleur en dur `#94a3b8` → `var(--text-muted)`.

### Formes de cartes — services_grid_v2 reconstruit

| | Avant | Après (modèle) |
|---|---|---|
| Disposition | horizontale (icône 52px à gauche) | **verticale** |
| Pastille d'icône | carré arrondi 14 px, fond translucide | **cercle** 56 px plein primaire, icône blanche |
| Titre / description | 0.98 / 0.82 rem | 1.25 / 0.95 rem |
| Hauteur mini | — | 268 px |
| Mise en avant | aucune | **une carte en aplat vert**, texte blanc, bouton blanc |

La mise en avant est pilotée par le bloc CMS `svc_featured` et le libellé du bouton
par `card_link_text` — aucun choix codé en dur (Règle #2).

### Application

`database/apply_typography_v3.php`, ajouté au pipeline, verrouillé par
`storage/typography_v3.lock` : posé une seule fois, jamais réécrit ensuite.

### Preuve de production (Règle #5)

```
HTTP 200 · 9 sections · 0 doublon
--font-size-h1 clamp(2rem, 5vw, 4.3rem)   --font-size-h3 1.25rem
--line-height-body 1.55   --letter-spacing-heading -0.02em
--radius-btn 14px   --radius-pill 9px   --radius-lg 20px
--font-main 'Plus Jakarta Sans'
bouton : padding 17px 34px · 1.05rem
badge  : 0.875rem · text-transform none
cartes : 6 · pastille border-radius 50% fond var(--primary) · 1 carte mise en avant
```

---

## 2026-09-04 — HERO V4 « VISUEL + CARTES FLOTTANTES » (commit aebaf55)

Nouveau modèle de hero fourni par la direction. Consigne : supprimer le hero de
la page d'accueil et appliquer le nouveau dans sa totalité, en version administrable.

### Suppression de l'ancien hero — sans toucher au moteur

`pages.hero_status = 0` sur la page `home`. C'est un champ **déjà administrable**
(sélecteur « Statut de la Hero Section » dans `admin/pages/edit.php:581`) et
`partials/hero.php:8` fait un `return` immédiat quand il vaut 0. Aucune
modification du moteur de hero, aucune régression sur les autres pages.

### Nouveau type de section : `hero_media_cards`

| Zone | Contenu |
|---|---|
| Colonne texte | badge à point, titre bicolore, chapô, deux CTA en pilule |
| Colonne visuelle | image + cartes d'information flottantes superposées |
| Décors | cercle menthe, courbe, vague, trame de points (SVG/CSS) |

Tous les décors dérivent de `var(--primary)` via `color-mix` — aucune couleur en dur.

### Mesures relevées sur le modèle (échelle 2.222 px image = 1 px CSS)

| Élément | Valeur |
|---|---|
| H1 | 72 px, interligne 1.05, `#191C1C` |
| Accent du titre | même corps, **graisse légère (300)**, couleur d'accent |
| Chapô | 20 px / 1.55 |
| Boutons | 50 px de haut, pilule, pastille d'icône ronde 30 px |
| Cartes | rayon 16 px, ombre douce, pastille ronde 34 px |
| Palette | teal `#005354`, menthe `#D3EEE7`, bord `#BEC9C8` |

### Administrabilité (Règle #2)

13 blocs `single` : `badge`, `title`, `title_accent`, `text`, `cta1_text/url/icon`,
`cta2_text/url/icon`, `image`, `image_alt`, `decor`.

Cartes flottantes en `groups` répétables : `card_icon`, `card_label`, `card_badge`,
`card_value`, `card_unit`, `card_title`, `card_meta`, `card_progress`, `card_avatar`,
**`card_top` et `card_left`** — la position de chaque carte est elle-même éditable.

### Reprise du contenu existant

`build_hero_v4.php` lit les champs `hero_*` de la page et les recopie en blocs.
Le titre est scindé : la partie encadrée par `<span>` devient `title_accent`.
Les deux premières cartes reprennent des chiffres **déjà affichés** sur le site
(section `stats_intro`) — aucune nouvelle affirmation introduite.

### Analyse de risque menée avant écriture (Règle #3)

| Risque | Vérification | Conclusion |
|---|---|---|
| `fix_hero_layout_v2.php` réactive le hero à chaque déploiement | `$data = $page` — fusion de la ligne complète | `hero_status = 0` préservé |
| `Page::updatePage` remet `hero_status` à 1 par défaut (`?? 1`, ligne 105) | le formulaire admin expose bien le champ | pas de réactivation silencieuse |
| `build_home_v2.php:69` force `hero_status = 1` | protégé par `storage/homepage_v2.lock` | no-op en production |

### Preuve de production (Règle #5)

```
HTTP 200 · 100KB
.premium-hero (ancien hero)  : 0 occurrence
sections : 10 · 1re = hero_media_cards « Hero — visuel et cartes » · 0 doublon
titre  : "Digitaliser. / Automatiser."   accent : "Faire avancer votre entreprise."
CTA    : "Découvrir nos services" (primaire) · "Demander un audit" (secondaire)
image  : présente        décors : présents
cartes : 4 — Clients accompagnés 100+ | Taux de satisfaction 95 | Premier échange | Vos données
positions : 4%/46% · 32%/30% · 58%/18% · 82%/6%      barre de progression : 1
```

### Correctif de suivi

`hero_badge` étant vide sur la page, la pastille ne s'affichait pas. Ajout
idempotent dans `fix_hero_layout_v2.php` : le bloc `badge` n'est posé que s'il
est absent, donc jamais réécrit une fois personnalisé.

### Reste à faire

- **[USER]** Le modèle utilise une photo **détourée** en pleine hauteur. L'image
  actuelle est une photo rectangulaire affichée dans un cadre arrondi. Pour coller
  au modèle, uploader un visuel détourné (PNG à fond transparent) via la Médiathèque.

---

## 2026-09-04 — INCIDENT BUG-HERO-01 : page d'accueil sans hero (commit 6a5865e)

### Symptôme

Après le déploiement du hero v4, la page d'accueil s'est retrouvée **sans aucun
hero** : l'ancien désactivé (`hero_status = 0`), le nouveau absent du rendu.
Production : 9 sections au lieu de 10, `hero_media_cards` non rendue.

### Cause racine

`database/build_home_v2.php:91-96` désactive toute section dont le type n'est pas
listé dans `$targetTypes` :

```php
foreach ($existingSections as $sec) {
    if (!in_array($sec['type'], $targetTypes, true)) {
        Database::query("UPDATE sections SET status = 'inactive' WHERE id = :id", ...);
    }
}
```

`hero_media_cards` n'y figurait pas → passée en `inactive` au déploiement suivant
sa création. `build_hero_v4.php` étant un **one-shot verrouillé**, il sortait dès
la première ligne et ne la réactivait jamais.

### Fausses pistes écartées en chemin

| Hypothèse | Vérification | Verdict |
|---|---|---|
| Garde `hero_variant !== 'hero_corporate'` (fix_hero_layout_v2.php:49) | script autonome sans garde déployé — badge toujours absent | **fausse** |
| Passe de déduplication (fix_hero_layout_v2.php:250) | n'agit qu'à partir de 2 sections actives de même type | **fausse** |
| Échec du déploiement | API GitHub Actions : 3 runs `completed/success` | **fausse** |

Le diagnostic n'a abouti qu'en comptant les sections réellement rendues plutôt
qu'en cherchant le seul badge manquant.

### Correctifs

1. `hero_media_cards` ajouté à `$targetTypes` (`build_home_v2.php`) — plus jamais
   désactivé, quel que soit l'état de `storage/homepage_v2.lock`.
2. `build_hero_v4.php` devient **réconciliateur** : à chaque déploiement il
   réaligne existence, statut actif et position de la section. Auto-réparateur.
3. Position fixée à `sort_order = -1`, en amont des sections 0..N réordonnées par
   `build_home_v2` — supprime l'égalité de tri avec `logos_strip`.

Le contenu reste protégé : blocs semés uniquement si la section est vide,
`pages.hero_status` touché uniquement au premier passage (verrou `hero_v4.lock`).

### Leçon d'architecture

Tout nouveau type de section destiné à la page d'accueil **doit** être déclaré
dans `$targetTypes` de `build_home_v2.php`, sinon il est désactivé au déploiement
suivant. À vérifier systématiquement (voir Règle #8).

### Preuve de production (Règle #5)

```
HTTP 200 · 100KB
sections : 10 · 1re = hero_media_cards · 0 doublon
ancien hero .premium-hero : 0 occurrence
badge  : "Transformation digitale"
titre  : "Digitaliser. / Automatiser."  accent : "Faire avancer votre entreprise."
CTA    : "Découvrir nos services" | "Demander un audit"
image  : présente        décors : présents
cartes : 4 — Clients accompagnés | Taux de satisfaction | Premier échange | Vos données
```

---

## 2026-09-04 — PAGE /service RECONSTRUITE (commits c515786, 39eb31f)

Consigne : reconstruire intégralement la page suivante à partir du langage
visuel de la page d'accueil (hero + contenu).

### Méthode : échange de type, pas réécriture

Les gabarits v2 lisent **exactement les mêmes clés de blocs** que les anciens.
Changer `sections.type` bascule donc le rendu sans toucher au contenu.

| Avant | Après | Compatibilité |
|---|---|---|
| `services_grid` | `services_grid_v2` | clés identiques (+ `svc_tag` ajouté au gabarit) |
| `process_strip` | `process_timeline` | `proc_num` / `proc_icon` / `proc_title` / `proc_desc` |
| `testimonials_grid` | `testimonials_carousel` | `client_company` → `client_role` (report explicite) |
| `cta` | `cta` | inchangé, déjà au nouveau style |
| — | `hero_media_cards` | nouvelle section en position -1 |

Aucun texte réécrit, aucune section supprimée : les doublons éventuels sont
désactivés, jamais effacés (Règle #4).

### Deux pertes de contenu évitées

1. **Catégories de services.** `services_grid` affichait `svc_tag`
   (Web, Informatique, Infrastructure, Vidéo, Contenu, IA) ; `services_grid_v2`
   ne le rendait pas. Ajout d'un rendu conditionnel `.svc-v2-tag` — la page
   d'accueil, qui n'a pas de `svc_tag`, est inchangée.
2. **Liens des cartes.** Aucune carte n'a de bloc `svc_link` : l'ancien gabarit
   masquait le trou avec un repli **codé en dur** (`services_grid.php:52`,
   `$svc['svc_link'] ?? '/contact'`, libellés « Découvrir » et « Service Pro »
   également en dur). Après l'échange, ni flèche ni bouton ne s'affichaient.
   `svc_link = '/contact'` est désormais posé en base : comportement identique,
   mais éditable (Règle #2).

### Hero

Titre scindé pour le rendu bicolore : la partie `<span>` prime, à défaut les
~40 % de mots finaux deviennent l'accent.
« Des solutions digitales sur mesure pour » + « propulser votre business ».

Les 4 cartes flottantes reprennent des éléments **déjà publiés** sur la page
(6 domaines, processus en 4 étapes, devis gratuit, support continu) — aucune
affirmation nouvelle.

### Script réconciliateur (leçon de BUG-HERO-01)

`database/build_service_v2.php` réaligne à chaque déploiement l'existence, le
statut et la position (`sort_order = -1`) de la section hero. Le contenu n'est
semé que si la section est vide ; `pages.hero_status` n'est touché qu'au premier
passage (verrou `storage/service_v2.lock`).

### Preuve de production (Règle #5)

```
HTTP 200 · 64KB
sections (5) : hero_media_cards, services_grid_v2, process_timeline,
               testimonials_carousel, cta      · 0 doublon
ancien hero .premium-hero : 0    anciens gabarits : 0
hero   : badge "Nos Prestations Digitales" · titre bicolore · 2 CTA · image · 4 cartes
services : 6 cartes · 1 mise en avant · bouton blanc 1 · flèches 5
           catégories Web | Informatique | Infrastructure | Vidéo | Contenu | IA
process  : 4 étapes        témoignages : 3 (fonctions reportées)
```

### Reste à faire

- **[USER]** Visuel du hero : `/assets/images/services_3d.png` est une image 3D
  rectangulaire. Comme sur l'accueil, un PNG détouré donnerait le rendu du modèle.
- Pages restantes à traiter : `/about` (4 sections), `/contact` (3 sections),
  `/realisations` et `/blog` (listings dynamiques, nature différente).

---

## FICHIERS INTOUCHABLES SANS ANALYSE

- `app/Services/Router.php`
- `app/Services/Database.php`
- `app/Services/CSRF.php`
- `database/master_migration.php`
- `routes/web.php`
