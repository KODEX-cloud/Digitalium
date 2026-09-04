# PROJECT_STATE — Digitalium Group CMS
> Dernière mise à jour : 2026-09-04 — ⚠ contenu de démonstration inventé en ligne (voir suite 13)

---

### 2026-09-04 (suite 13) — ⚠ Contenu de démonstration inventé, à remplacer

**Décision de l'utilisateur, 2026-09-04** : autorisation explicite d'inventer clients et contenus
pour combler les emplacements vides, avec correction ultérieure depuis l'admin. Cette entrée existe
pour que personne ne redécouvre par surprise que ces références sont fausses.

**Ce qui est inventé** — six réalisations dans la table `projects`, avec noms de clients, contextes,
solutions, fonctionnalités et résultats. Aucune n'existe. Elles sont **publiées** et visibles sur
`/realisations`, sur `/solutions` et dans le sitemap.

**Mesures de traçabilité prises**
- Le champ `client` de chaque ligne commence par **« Démo — »** : impossible de les confondre avec
  une référence réelle, en base comme en admin.
- **Aucun témoignage** n'a été fabriqué. Une citation signée du nom d'une personne qui n'existe pas
  est l'élément le plus exposé, et le gabarit d'étude de cas n'affiche ce bloc que s'il est rempli :
  son absence ne laisse aucun vide visible. À ajouter seulement sur demande explicite.
- Les résultats sont formulés en ordres de grandeur (« délai divisé par deux ») plutôt qu'en
  pourcentages précis, qui se vérifient et s'opposent.

**Suppression** — `/admin/projects`, supprimer les six lignes « Démo — … ». Rien d'autre à défaire :
visuels et activation de section sont du réglage, pas du contenu inventé.

**Ce qui n'est PAS inventé** — les visuels. Aucune image n'a été créée : le script réutilise les six
fichiers déjà présents dans la Bibliothèque Média, dont trois (`proj-finance`, `proj-health`,
`proj-logistics`) avaient manifestement été téléversés pour cet usage. Les six pages Solutions
reçoivent un visuel de hero, uniquement là où le champ était vide.

**Script** `database/seed_demo_content.php`, ajouté au pipeline après `build_solutions_page.php`.
Trois garde-fous : drapeau `demo_content_seeded_v1` en base (visible et réarmable depuis l'admin),
semis annulé si la table `projects` contient déjà quoi que ce soit, visuels posés uniquement sur les
champs vides. La section Réalisations de `/solutions`, créée inactive faute de contenu, est activée.

**Preuves** — banc d'exécution à blanc, 4 scénarios, **20/20** : site vierge, second passage,
réalisation réelle déjà saisie (aucune démo ajoutée, la vraie intacte), visuel déjà choisi en admin
(conservé). Le banc a par ailleurs attrapé un appel à `mb_substr()`, extension non garantie sur
l'hébergeur — remplacé par une troncature UTF-8 sans dépendance.

---

### 2026-09-04 (suite 12) — Page Solutions (/solutions) + 5 sous-pages + architecture parent/enfant

**Réutilisation avant création.** Sur les 8 sections du brief, 7 sont montées avec des types
existants : `hero_media_cards`, `sectors_grid` (deux fois), `process_strip`, `capabilities_grid`,
`projects_cms`, `cta`. Un seul type a été créé, `needs_router`, faute d'équivalent compact pour la
section « Je veux… ». Trois types existants ont été étendus de façon additive.

**Nouveau type `needs_router`** — `app/Views/frontend/sections/needs_router.php`.
Liste de lignes cliquables plutôt qu'une grille de cartes : trois grilles d'affilée sur une même
page donnent le même rythme de lecture trois fois. `single` : tag, title, subtitle, intro_label.
`groups` : need_icon, need_text, need_solution, need_link. Un besoin sans texte est ignoré (c'est
le moyen de le masquer sans le supprimer) ; sans lien, la ligne s'affiche sans être cliquable.

**Extensions additives (aucune régression sur les pages existantes)**
- `projects_cms` : `limit` (aperçu de N réalisations), `more_text` / `more_url` (bouton sous la
  grille, masqué automatiquement quand la table est vide — un bouton vers une page vide tromperait).
- `sectors_grid` : `more_text` / `more_url`.
- `process_strip` : `subtitle`, qui manquait — le paragraphe du brief n'avait nulle part où aller.

**Architecture parent/enfant — /solutions/{famille}**
- `pages.parent_slug` VARCHAR(150) NULL + index. Le rattachement est une DONNÉE, pas une convention
  de nommage : `Page::findChild()` exige le couple exact (parent, enfant).
- Route `$router->get('/{parent}/{child}', 'HomeController@renderChild')`, déclarée après
  `/blog/{slug}` et `/realisations/{slug}` et avant le catch-all `/{slug}`.
- **Une page enfant n'a qu'une seule URL.** `doRenderPage()` redirige en 301 tout accès à l'URL
  courte vers l'URL imbriquée. Le test se fait après le cache : aucune requête supplémentaire.
  C'est la leçon DT-05 appliquée dès la conception plutôt que constatée après coup.
- `sitemap.xml` déclare l'URL imbriquée (priorité 0.6) et non la courte, qui n'aurait produit qu'une
  liste de redirections.

**SEC-05 (découverte pendant les tests) — `url()` acceptait `javascript:`.**
`config/config.php` laissait passer tel quel tout `javascript:`, ce qui transformait chaque champ
« lien » du CMS en vecteur de script : il suffisait de saisir `javascript:alert(1)` dans un bouton.
Zéro usage légitime dans tout l'arbre (grep). `javascript:`, `data:` et `vbscript:` sont désormais
neutralisés en ancre morte (`#`). Vérifié sur 15 cas, dont les formes évasives (casse mélangée,
espaces avant les deux-points).

**Contenu** — tout provient du brief, rien n'est inventé. La section Réalisations lit le module
existant ; la table `projects` étant vide, elle est créée **inactive** (activable depuis l'admin dès
qu'une réalisation est publiée) plutôt que d'afficher un bloc creux.

**Script** `database/build_solutions_page.php`, ajouté au pipeline (non bloquant). Le
réconciliateur apparie sur le couple **(type, nom)** et non sur le seul type : la page porte deux
`sectors_grid`, un appariement par type les aurait confondues. Position réalignée à chaque
déploiement ; statut posé à la création seulement, pour que l'activation/désactivation en admin ne
soit pas annulée au déploiement suivant.

**Accent** `#0868B0` (bleu clair du logo), posé une seule fois via `pages.accent_color` — modifiable
en admin, et non réécrit ensuite.

**Déploiement 041cc52 en échec — correctif.** Le run a échoué à l'étape « SSH — Deploy Enterprise ».
Le site est resté debout (aucune coupure), le code a bien été déployé (la route `/{parent}/{child}`
répondait), mais la base n'avait pas été modifiée : `/solutions` en 404, absente du sitemap et du
menu. Le seul `exit 1` du script SSH est « au moins 3 smoke tests HTTP en échec » — or les quatre
pages testées (`/`, `/blog`, `/realisations`, `/sitemap.xml`) lisent toutes la table `pages`, celle
sur laquelle le script exécutait deux `ALTER TABLE` successifs. Un `ALTER` verrouille la table.
Correctifs : l'index est supprimé (`pages` compte une dizaine de lignes, il n'apportait rien et
doublait le nombre d'ALTER), l'étape de schéma est isolée dans son propre try/catch, et les
sous-pages sont ignorées si la colonne manque — plutôt que d'être publiées sans chemin d'accès.

**Banc d'essai à blanc** — `scratchpad/h_build.php`. MySQL n'étant pas démarré sur le poste de
développement, le script ne pouvait être vérifié qu'à la lecture : c'est ainsi qu'une garde posée
avant la déclaration de `$children` était passée inaperçue. Le script s'exécute désormais pour de
vrai sur une base bouchonnée, en trois scénarios : site vierge, `ALTER` refusé, second passage.
22 contrôles, dont « aucune section dupliquée », « le titre modifié en admin est conservé »,
« une section éteinte en admin n'est pas rallumée » et « aucun bouton Explorer ne mène nulle part ».

**Preuves** — 3 harnais : sections 38/38 (contenu complet, minimal, vide, hostile ; équilibre des
balises ; échappement), routage 16/16 (dont non-régression de `/blog/{slug}` et
`/realisations/{slug}`, et ordre de déclaration), éditeur d'admin 27/27 (141 `<div>` équilibrés).
`url()` 15/15. `php -l` sur 12 fichiers : 0 erreur.

---

### 2026-09-04 (suite 11) — /secteurs passe au hero plein cadre

Décision de l'utilisateur après le rendu de /realisations : appliquer le même format de hero à
Secteurs d'activités. Les deux pages partagent déjà le gabarit `hero_media_cards` en mode
`overlay`, elles héritaient donc du badge en pavé, du titre en capitales et du dégradé sombre ;
seul le cadrage différait. `/secteurs` était borné à 1300px sur 420px de haut, ce qui, à côté du
hero bord à bord des Réalisations, donnait l'impression de deux gabarits différents.

- `database/build_sectors_page.php` : `image_max_width` → `full`, `overlay_min_height` → `560`,
  dans le seed **et** dans les défauts posés a posteriori, pour qu'une installation neuve obtienne
  directement le bon format.
- Bascule de l'installation déjà en ligne par drapeau `sectors_hero_full_v1`, sur le modèle de
  `realisations_hero_full_v1` : elle ne s'applique **que si les valeurs sont restées `1300` et
  `420`**, c'est-à-dire celles que ce script avait écrites. Un réglage fait depuis l'admin n'est
  pas écrasé, et le script le dit dans sa sortie.
- Aucune modification du gabarit : le mode plein cadre existait déjà (`.hero-ov-full`), c'est un
  changement de contenu CMS, pas de code de rendu.

---

### 2026-09-04 (suite 10) — Dette de sécurité soldée : SEC-01, SEC-02, DT-04 + SEC-03/SEC-04 découvertes

**SEC-01 / SEC-02 — compte admin (`database/master_migration.php`, section 13).**
Le bloc réécrivait le mot de passe admin à **chaque** déploiement à partir d'un hash figé dans le
dépôt, dont le mot de passe en clair était en commentaire juste au-dessus. Toute personne ayant
accès au dépôt connaissait donc le mot de passe de production, et un changement de mot de passe
était annulé au push suivant — il n'existait aucun moyen d'en sortir.
- Le compte n'est plus créé que s'il est **absent** ; son mot de passe n'est ensuite **jamais** touché.
- Première installation : `ADMIN_PASSWORD` du `.env`, sinon mot de passe **engendré au hasard** et affiché une seule fois dans le rapport de migration.
- Voie de secours : `ADMIN_PASSWORD` + `ADMIN_PASSWORD_RESET=1` dans le `.env`, puis retrait des deux lignes.
- Hash : `argon2id` si l'hébergeur le fournit, sinon repli `PASSWORD_DEFAULT` — l'ancien code supposait argon2id disponible.
- Le hash et le mot de passe en clair ont disparu du dépôt (`grep` sur tout l'arbre : 0 occurrence).
- **Preuve** : harnais `7/7` — compte existant sans `.env` (0 écriture), compte absent sans `.env` (mot de passe engendré), compte absent avec `.env`, réinitialisation demandée, `RESET=1` sans mot de passe (refus + erreur), `RESET=0` avec mot de passe présent (aucun effet), valeurs entourées d'espaces. Chaque valeur écrite est vérifiée hachée et relue par `password_verify()`.

**DT-04 — logs suivis par git.** Plus grave que de l'hygiène : le déploiement fait
`git reset --hard origin/main` (`.github/workflows/deploy.yml:257`), donc `app.log`, `contacts.log`
et `security.log` de **production étaient écrasés par ceux de la machine de développement à chaque
déploiement**. Retirés de l'index (`git rm --cached`). `reset --hard` ne touche pas aux fichiers non
suivis : la production conserve désormais ses journaux. Aucun `.gitkeep` nécessaire, `BootCheck`
recrée `storage/logs` lui-même (`app/Services/BootCheck.php:132`, non critique).

**SEC-03 (nouvelle) — le dossier `bin/` était servi par Apache.** Le `.htaccess` bloquait
`app|config|database|routes|storage|vendor` mais **pas `bin`**. Constaté en production :
`/bin/read_logs.php` → **HTTP 200**, divulguant le chemin absolu du serveur
(`/home/u839163661/domains/...`) et prêt à afficher les journaux applicatifs ;
`/bin/deploy.php` → HTTP 500, c'est-à-dire **exécuté** jusqu'au plantage.
- `bin` et `.github` ajoutés à la règle de blocage du `.htaccess`.
- Défense en profondeur : garde `php_sapi_name() !== 'cli'` → 403 ajoutée aux **7** scripts de `bin/` (seul `recover-production.php` l'avait). Un `.htaccess` perdu ne suffit plus à les rendre exécutables.
- Vérifié : aucun code de `app/` ou `routes/` n'inclut ces scripts ; le workflow ne les appelle qu'en CLI par SSH.

**SEC-04 (nouvelle) — `database/change_admin_prod.php`.** Formulaire web qui remplace le mot de
passe admin **sans authentification ni jeton CSRF**. `RISK_ANALYSIS.md` le classait CRITIQUE et
« à supprimer immédiatement » ; il n'était hors d'atteinte que grâce à une seule ligne du
`.htaccess` (403 confirmé en production). Verrouillé derrière le même interrupteur
`ADMIN_PASSWORD_RESET=1` que la migration : inerte par défaut, ouvrable à la demande.
**Suppression pure recommandée** — non effectuée, elle demande votre accord.

**Documentation** : `.env.example` décrit `ADMIN_PASSWORD` et `ADMIN_PASSWORD_RESET`.

**Non-régression** : `php -l` sur les 9 fichiers touchés — 0 erreur. `/admin/login` répond 200.
`database/`, `config/`, `.env` et `storage/logs/` restent en 403 en production.

**⚠ Action requise de votre côté** : le mot de passe `admin` de production est resté celui qui a
circulé dans le dépôt Git. Il doit être changé — voir la procédure `ADMIN_PASSWORD_RESET` du
`.env.example`.

---

### 2026-09-04 (suite 9) — Hero carrousel plein cadre + état vide assumé

**Analyse de la référence (cocodyjuste.com).** Écarts relevés avec le hero livré : pleine largeur
bord à bord, nettement plus haut, dégradé sombre depuis la gauche laissant la photo lisible à
droite, badge en **pavé plein** et non en pastille translucide, H1 très grand **en capitales**, et
un **carrousel** avec flèches latérales et pastilles de pagination.

**Hero**
- `image_max_width` accepte désormais le mot **`full`** : visuel bord à bord sur toute la largeur.
- Le mode `overlay` reçoit son **propre balisage** : dégradé sombre horizontal, badge en pavé plein, titre en capitales (`clamp(2.1rem, 5vw, 3.6rem)`), texte à gauche.
- **Carrousel** : la première diapositive vient des blocs simples, les groupes `slide_*` en ajoutent d'autres (`slide_image`, `slide_alt`, `slide_badge`, `slide_title`, `slide_accent`, `slide_text`). Une seule diapositive = ni flèches ni pastilles.
- Défilement automatique 7 s, suspendu au survol, **désactivé si le système demande des animations réduites**.
- **SEO** : seule la première diapositive porte un `<h1>`, les suivantes des `<h2>` visuellement identiques. Le mode overlay ne rend plus la grille historique, qui aurait laissé un second `<h1>` caché dans le document.
- Sous 560px les flèches disparaissent : elles recouvriraient le texte.

/realisations passe en pleine largeur, 560px de haut — posé **une seule fois** sous drapeau
`realisations_hero_full_v1`.

**État vide de la page.** Une phrase seule au milieu d'une grande surface blanche se lit comme une
page cassée. L'état vide devient un panneau assumé : icône, message, et un bouton d'appel à l'action
administrable (`empty_cta_text`, `empty_cta_url`), posé sur la section déjà en ligne uniquement s'il
manque.

**Preuves (Règle #5)**
- Hero, 5 cas (1 diapo, 3 diapos, borné à 1250, split, vide) : une seule diapo n'affiche ni flèche ni pastille ; 3 diapos donnent **3 pastilles, 2 flèches, 1 `<h1>` et 2 `<h2>`** ; `full` et `1250` produisent bien des conteneurs différents ; balises équilibrées partout
- Harness hero complet **12/12** · Réalisations **17/17** · Éditeur de pages **14/14** · `php -l` 5/5
- Commit `146636c` → run **completed / success**
- Production `/realisations` : conteneur `hero-ov hero-ov-full`, 1 diapositive, **1 `<h1>`**, badge pavé, état vide en panneau avec bouton « Discuter de mon projet », accent `#003060` actif
- Non-régression : `/` et `/service` → `hero-mc-split`, 1 `<h1>` · `/secteurs` → `hero-mc-overlay`, 1 `<h1>`

**À noter** : `/secteurs` étant lui aussi en mode `overlay`, il hérite du nouveau rendu (badge plein,
titre en capitales, dégradé sombre). Il reste borné à 1300px, pas en pleine largeur.

---

### 2026-09-04 (suite 8) — BUG-HERO-02 : texte blanc sur fond blanc sans visuel

**Symptôme.** Sur `/realisations`, le titre, le chapô et le bouton secondaire étaient invisibles.
Seul le bouton principal, blanc plein, se voyait.

**Cause.** En mode `overlay` le texte est blanc, mais le cadre du visuel n'était rendu **que si** un
bloc `image` était renseigné. Sans image : pas de cadre, donc pas de voile coloré, donc du blanc sur
le fond clair de la page. Le défaut vient du gabarit, pas de la saisie — **un gabarit ne doit pas
dépendre de la présence d'un média pour rester lisible**.

**Correctif**
- En mode overlay, le cadre du visuel est **toujours** rendu ; seule la balise `<img>` reste conditionnelle.
- `.hero-mc-overlay .hero-mc-media` reçoit un fond `var(--primary)` : le contraste est garanti sans image, et aussi **pendant le chargement** de l'image.
- Les modes `split` et `banner` sont inchangés : leur texte n'est pas blanc, le cadre n'y est rendu que s'il y a une image.

**Preuves (Règle #5)**
- 5 cas (overlay sans/avec image, split sans/avec image, banner sans image) : le cadre est présent exactement quand il doit l'être, balises équilibrées
- Harness hero complet : **12/12** scénarios toujours OK · `php -l` : OK
- Commit `3f8f4f7` → run **completed / success**
- Production : `/realisations` → `hero-mc-overlay`, cadre=1, img=0, fond de sécurité présent · `/secteurs` → cadre=1, img=1 · `/` → `hero-mc-split`, inchangé

---

### 2026-09-04 (suite 7) — Couleur d'accent PAR PAGE + test du bleu logo sur /realisations

Demande : essayer le bleu du logo sur la page Réalisations uniquement.

Plutôt que de figer des couleurs dans les gabarits de cette page — ce qui aurait créé un second
système de couleurs à maintenir et cassé l'administrabilité du thème —, **une page peut désormais
porter sa propre couleur d'accent**.

- Nouvelle colonne `pages.accent_color`.
- Champ **vide** : la page suit le thème global, aucune règle n'est émise.
- Champ **rempli** : `--primary` est surchargé pour cette page seulement, **en-tête et pied de page compris** — un header resté sur l'ancienne teinte aurait faussé la comparaison.
- **Réversible** : vider le champ rend la page au thème global.
- **Administrable** : sélecteur de couleur + saisie manuelle + bouton Vider dans Admin > Pages.

**Validation stricte aux deux extrémités** : la vue n'émet la règle que si la valeur correspond à
`#RGB` ou `#RRGGBB`, et le contrôleur enregistre une chaîne vide pour toute saisie non conforme.
Une valeur ne peut donc ni s'échapper de la déclaration CSS, ni fermer la balise `<style>`.

La migration pose `#003060` sur /realisations **une seule fois**, sous le drapeau
`realisations_accent_test_v1` : vider le champ en admin le désactive définitivement.

**Preuves (Règle #5)**
- **12 valeurs** testées (`#003060`, `#abc`, vide, espaces, « red », sans dièse, trop court, trop long, `#003060; } body{display:none`, `</style><script>`, null) : aucune ne produit plus d'une déclaration, ni accolade, ni balise
- Éditeur de pages : 14/14 assertions toujours OK · `php -l` : 4/4 OK
- Commit `21ff6d3` → run **completed / success**
- Production : `/realisations` → `<style id="page-accent">:root { --primary: #003060; --primary-dark: #003060; }</style>` · `/`, `/secteurs`, `/service` → **aucun accent**, thème global intact

---

### 2026-09-04 (suite 6) — Page RÉALISATIONS / ÉTUDES DE CAS (/realisations)

**Analyse préalable.** Le module Réalisations existait déjà : routes `/realisations` et
`/realisations/{slug}`, modèle `Project`, CRUD admin, vues frontend. Rien n'a été recréé —
l'existant est étendu (Règle #1). `context` portait déjà le problème et `impact` les résultats :
les dupliquer aurait créé deux colonnes pour une même donnée.

**Base de données — 11 colonnes ajoutées à `projects`**
`status`, `sector`, `year`, `objectives`, `solution`, `features`, `testimonial_quote`,
`testimonial_author`, `testimonial_role`, `meta_title`, `meta_description`.
Les lignes antérieures sans statut passent à `published` : les traiter en brouillon aurait fait
disparaître du contenu déjà en ligne.

**Page /realisations — pilotée par le CMS.** `ProjectController@publicIndex` charge désormais les
sections de la page CMS « realisations » comme n'importe quelle autre page. Hero, expertises et CTA
deviennent administrables. Un repli est prévu si la page n'a pas encore de sections : la grille
s'affiche quand même, la page ne peut jamais être vide.

**Nouvelle section `projects_cms`** — grille filtrable. Les projets viennent **exclusivement** de la
table `projects`. Filtres administrables et ordonnables ; une catégorie déclarée qu'aucun projet
n'utilise n'est pas affichée. Sans catégorie déclarée, les filtres sont déduits des catégories
réellement présentes. Filtrage côté client, toutes les cartes étant déjà rendues.

**Page /realisations/{slug}** — les 11 blocs demandés, **tous conditionnels** : un champ vide
n'affiche ni rubrique, ni titre, ni texte de repli. Les titres de rubriques viennent de 19 réglages
globaux, donc administrables sans saisie par projet. Un brouillon reste consultable par un
administrateur connecté.

**Admin**
- Formulaire factorisé dans `_form.php`, partagé création/modification — un champ ajouté d'un seul côté n'était éditable que d'un côté
- `collectPostData()` unique côté contrôleur, pour la même raison
- Colonne Statut dans la liste des réalisations
- La modale de sélection de média était dupliquée dans les vues alors que `admin/layout` la fournit : supprimée
- `projects_cms` ajoutée au sélecteur de types et aux squelettes ; 6 champs documentés dans `BlockFieldHelper`

**Preuves (Règle #5)**
- 5 scénarios de rendu (table vide, 2 projets, filtres masqués, étude de cas complète, fiche entièrement vide) : balises équilibrées, aucune erreur
- **17 assertions** : message d'attente quand la table est vide, aucune carte ni filtre, catégorie non utilisée exclue, technologies limitées à 4 sur la carte, bloc méta masqué si client/secteur/année vides, 7 rubriques sur une étude complète, aucune rubrique/témoignage/CTA sur une fiche vide → **17/17 OK**
- Éditeur de pages : 14/14 assertions toujours OK · `php -l` : 12/12 OK
- Commit `65d13e2` → run **completed / success**
- Production `/realisations` → **HTTP 200**, 4 sections dans l'ordre `hero-mc-overlay | projects-cms | caps-section | cta-sec`, H1 conforme au brief, **6 expertises**, CTA final présent, message d'attente affiché, **0 carte** (table vide)
- Navigation : `/` `/about` `/service` `/secteurs` `/realisations` `/blog` `/contact`
- Non-régression : `/`, `/secteurs`, `/service` → 200 · `/admin/projects` → 302 vers login

**⚠ La table `projects` est VIDE.** La page affiche son message d'attente jusqu'à saisie de vraies
réalisations. Aucun projet, client, chiffre, résultat ni témoignage n'a été inventé.

---

### 2026-09-04 (suite 5) — BUG-ADM-02 : trois `<div>` non refermés faisaient se chevaucher l'éditeur

**Symptôme.** Dans `/admin/pages/edit/{id}`, la liste des sections, le formulaire de blocs et la
colonne inspecteur se superposaient en cascade.

**Cause réelle**, localisée en analysant la pile d'imbrication du HTML rendu (et non en devinant) :
trois `<div>` du bloc de configuration n'étaient **jamais refermés**.

```
config-grid-wrapper   (grille 1.2fr 0.8fr)
  card                (colonne formulaire)
    div               (Gestionnaire de Hero Section)
```

Le navigateur imbriquait donc tout ce qui suit à l'intérieur, `builder-container` compris. La grille
de l'éditeur devenait un élément d'une **autre** grille, comprimée dans une colonne de 1.2fr et
superposée au simulateur `sticky` de la colonne voisine.

Le défaut était **antérieur** à la colonne inspecteur mais latent : avec deux colonnes il restait
supportable, la troisième l'a rendu visible. L'écart de 3 balises avait été mesuré au commit
précédent et attribué au layout admin **sans vérification** — c'était faux.

**Correctif** — fermeture aux trois points sémantiquement corrects :
- `</div>` du Gestionnaire de Hero Section, avant la ligne du bouton Sauvegarder
- `</div>` de la colonne formulaire, avant le panneau simulateur
- `</div>` de `config-grid-wrapper`, avant `builder-container`

Le simulateur redevient la colonne droite de `config-grid-wrapper`, et `builder-container` un
élément de premier niveau.

**Preuves (Règle #5)**
- **0** `<div>` non refermé, **0** fermeture orpheline — 242 ouvertures / 242 fermetures, contre 242/239 avant
- Pile d'imbrication vérifiée aux trois ancres : simulateur = enfant direct de `config-grid-wrapper`, `builder-container` au niveau racine, inspecteur = enfant direct de `builder-container`
- Les 14 assertions de rendu de l'éditeur : **14/14 OK**
- `php -l` : OK · Commit `ec337ec` → run **completed / success**
- Production : `/admin/pages/edit/6` → 302 vers `/admin/login` · `/` et `/secteurs` → 200, aucune erreur PHP

**Leçon.** Un écart de balises ne doit jamais être expliqué par hypothèse. La pile d'imbrication du
HTML rendu donne la réponse exacte en quelques lignes de script.

---

### 2026-09-04 (suite 4) — Éditeur Pages CMS : champs administrables + colonne inspecteur

**BUG-ADM-01 — des réglages étaient littéralement inaccessibles.**
`app/Views/admin/pages/edit.php` possédait sa **propre** déduction du type de champ, distincte de
celle du contrôleur. La règle `str_contains($key, 'image')` faisait rendre `image_ratio`,
`image_max_width` et `image_radius` comme des **sélecteurs de média** : impossible d'y saisir
« 1300 / 400 ». Même cause pour `sec_link_text`, rendu comme un lien parce que le mot « link »
figure dans la clé.

**Correctif — une seule règle dans tout le projet**
- Nouveau `app/Helpers/BlockFieldHelper.php` : source de vérité unique pour le **type**, l'**intitulé**, l'**aide** et les **valeurs autorisées** d'un champ.
- `PageController::guessBlockType()` délègue désormais à ce helper.
- La vue d'édition le consomme pour les champs simples **et** pour les champs des éléments répétables.

**Champs enfin compréhensibles**
- Intitulés lisibles au lieu des clés techniques : « Proportions du visuel » et non « Image ratio ».
- Une phrase d'aide sous chaque champ connu : format attendu, unité, effet d'un champ vide.
- Champs à choix fermé rendus en **liste déroulante** : `layout`, `decor`, `columns`. Les options de `layout` dépendent du **type de section**, pour ne pas proposer de valeurs sans effet. Une valeur enregistrée hors liste reste proposée : sauvegarder ne l'écrase jamais en silence.

**Réglages jamais initialisés** — une clé absente de la base n'apparaissait nulle part, donc la
fonctionnalité était inaccessible. L'éditeur complète la liste avec les clés que le type de section
sait gérer, en valeur vide.

**Colonne inspecteur — exploitation de l'espace vide à droite**
`.builder-container` passe de `280px 1fr` à `280px 1fr 300px`.
- *Cette page* : adresse, statut, présence au menu, nombre de sections visibles ; liens rapides vers la page en ligne, la Bibliothèque Média et la Navigation.
- *Section en cours* : type, position, visibilité, nombre de réglages et d'éléments, alerte sur les champs vides restants ; boutons sauvegarder et masquer/afficher.
- L'inspecteur suit la section sélectionnée. Sous 1400px il passe **sous** l'éditeur plutôt que de comprimer les champs de saisie.

**Preuves (Règle #5)**
- Rendu complet de la vue avec dépendances stubées : **14 assertions, 14/14 OK** (ratio en champ texte, image restée un média, liste déroulante avec l'option courante sélectionnée, options inapplicables absentes, inspecteur par section, comptage des sections visibles)
- Écart de balises `div` **identique avant et après** (3 — préexistant, dû au layout admin qui enveloppe la vue) : aucune balise cassée par la modification
- **20 cas** de déduction de type vérifiés un à un : 20/20 OK
- `php -l` : 3/3 OK · Commit `68e14fa` → run **completed / success**
- Production : `/admin/pages/edit/6` → **302 vers /admin/login** (auth active, aucune erreur 500) · `/`, `/secteurs`, `/service` → 200, aucune erreur PHP

---

### 2026-09-04 (suite 3) — Hero aligné à gauche, angles droits, et lecture verticale des paires

**Bug corrigé — bouton principal invisible.** `.hero-mc-btn-primary` porte `color: #ffffff !important`
dans la règle de base ; en mode overlay le bouton passe en blanc plein, le libellé était donc
**blanc sur blanc**. Corrigé avec la même priorité, sur le bouton principal et le secondaire.

**Hero — trois changements**
- Le texte ne s'étale plus sur toute l'image : il est **aligné à gauche**, borné à 600px, pour dégager la moitié droite de la photo.
- Le voile devient un **dégradé horizontal** — dense à gauche sous le texte, transparent à droite. Un voile plein masquait la photo. Sous 760px, où le texte occupe toute la largeur, il redevient vertical.
- **Angles droits** sur le visuel : `border-radius` passe à `0` par défaut et devient administrable via le bloc `image_radius` (px). S'applique aux trois mises en page — donc aussi à l'accueil et à /service.

**Section `problems_solutions` — lecture verticale**
| Bloc | Rôle | Défaut |
|---|---|---|
| `layout` | `stack` (constat au-dessus, flèche descendante, réponse dessous) ou `row` (côte à côte) | `stack` |
| `columns` | nombre de colonnes de la grille, 1 à 4 | `2` |

Une paire unique occupe toujours la largeur, sans grille. Sous 860px la grille passe en colonne
unique quel que soit le réglage.

**Incident de déploiement — DEP-01**
Le run du commit `8955cb7` a échoué à l'étape « SSH — Deploy Enterprise ». Diagnostic **par preuve
et non par supposition** : les pages en ligne ne contenaient pas `--hero-media-radius` et leur
dégradé était resté en `180deg` → le `git pull` n'avait pas eu lieu, la production était intacte sur
le commit précédent. Le job « PHP Syntax Validation » étant passé et l'échec se situant avant le
pull, la cause n'était pas le code. Même étape, même symptôme que l'échec transitoire du footer
(commit `bb47e27`). Relance par le commit `66b837f` → **success**.
*Note : les logs d'étape GitHub Actions renvoient 403 sans authentification ; le diagnostic doit
passer par l'observation de la production, jamais par une supposition.*

**Preuves (Règle #5)**
- `problems_solutions` : **10 scénarios** (stack, row, sans réglage, 3 colonnes, colonnes 0 / 99 / texte, `layout` hostile, paire unique, vide) → 10/10 sans erreur, valeurs bornées, aucune injection
- Hero : **12 scénarios** → 12/12 ; variable d'arrondi émise dans les trois modes, saisie hostile (`9"><b>`) neutralisée en `0px`
- `php -l` : 4/4 OK · Commit `66b837f` → run **completed / success**
- `/secteurs` → `hero-mc-overlay` avec `--hero-media-radius:0px`, `<div class="ps-list ps-list-stack" style="--ps-cols:2;">`, **4** `ps-row`, **0** carte flottante
- `/` et `/service` → `hero-mc-split` avec `--hero-media-radius:0px`, **4 cartes** chacune

---

### 2026-09-04 (suite 2) — Hero « overlay » + disposition des cartes problème/solution

**Hero — correction d'une mauvaise lecture de la demande.** La référence fournie montre le texte
centré **par-dessus** l'image, voilée pour rester lisible. Le mode `banner` livré juste avant
plaçait le texte au-dessus et l'image dessous : ce n'était pas cela.

Nouveau `layout = overlay` sur `hero_media_cards` :
| Bloc | Rôle | Défaut |
|---|---|---|
| `layout` | `split` · `banner` · `overlay` | `split` |
| `overlay_opacity` | intensité du voile, 0 à 100 | `62` |
| `overlay_min_height` | hauteur minimale du visuel, en px | `420` |

- Texte et visuel occupent la **même cellule de grille** : aucun décalage possible, quelle que soit la longueur du titre.
- Voile en dégradé à la couleur de marque ; l'opacité est portée par `opacity` plutôt que par un `calc()` imbriqué dans `color-mix`, dont le support est plus incertain.
- Hauteur réelle = le plus grand des deux, proportions du visuel ou hauteur du texte → le titre ne peut jamais déborder du cadre.
- Sur fond photo, le bouton principal passe en **blanc plein** (à la couleur de marque il se serait fondu dans le voile) et le secondaire en contour clair.
- En mode overlay, cartes flottantes et décors ne sont pas rendus : ils n'ont plus de panneau sur lequel se poser.
- `layout` est validé contre une **liste blanche** : une valeur inconnue retombe sur `split` et ne peut rien injecter dans l'attribut `class`.

/secteurs : bascule **unique** `banner` → `overlay` via `settings.sectors_hero_overlay_v1`, et
seulement si la valeur est restée celle écrite par le script — un choix fait en admin est respecté.

**Cartes problème/solution — disposition corrigée**
- `align-items: stretch` : les deux côtés occupent toute la hauteur, le constat ne flotte plus au milieu d'un vide
- le constat reçoit sa **propre surface teintée** avec filet latéral : la moitié gauche se lisait comme un blanc, elle se lit maintenant comme un des deux termes
- colonnes rebalancées `0.95fr / 44px / 1.3fr`, titre de réponse porté à 1.12rem
- palier intermédiaire à 1000px avant le passage en colonne unique à 860px

**Preuves (Règle #5)**
- Hero : **12 scénarios** (split, banner, overlay, opacité 0 / 999 / texte, hauteur invalide, sans image, `layout` hostile, ratio invalide, vide) → 12/12 sans erreur, balises équilibrées, opacité bornée 0-1, **aucune injection**
- `problems_solutions` : 3/3 sans erreur · `php -l` : 4/4 OK
- Commit `af7947b` → run **completed / success**
- `/secteurs` → `hero-mc-overlay`, `--hero-overlay-a:0.62`, **0 carte**, **0 décor**, 4 `ps-row` avec panneau constat
- Non-régression : `/` → `hero-mc-split`, **4 cartes**, inchangé

---

### 2026-09-04 (suite) — Hero : mise en page « bandeau » administrable

Demande : retirer les cartes flottantes de /secteurs et afficher un visuel 1300 × 400
responsive et administrable.

`hero_media_cards` étant **partagé par l'accueil, /service et /secteurs**, la mise en page
est devenue un réglage CMS plutôt qu'une modification de structure — l'accueil n'est pas touché.

**Nouveaux blocs single de `hero_media_cards`**
| Bloc | Rôle | Défaut |
|---|---|---|
| `layout` | `split` (texte à gauche / visuel à droite) ou `banner` (texte en haut, bandeau large dessous) | `split` |
| `image_max_width` | largeur maximale du bandeau, en px | `1300` |
| `image_ratio` | proportions, ex. `1300 / 400` (`x` et `:` acceptés) | `1300 / 400` |
| `image_ratio_mobile` | proportions sous 760px | `16 / 9` |

- Proportions validées par regex : une saisie erronée retombe sur le défaut et **ne peut pas injecter de CSS**.
- Le bandeau déborde du conteneur (1240px) pour atteindre 1300px, borné par `min(largeur, 100vw - 48px)` ; `.hero-mc` étant en `overflow: hidden`, aucun défilement horizontal n'est possible.
- En mode bandeau, une carte flottante cesse de flotter et se range sous le visuel.
- `guessBlockType` : les clés de réglage dérivées d'une image (`_ratio`, `_max_width`, `_alt`, `_width`, `_height`, `_position`) sont désormais des champs **texte**, plus des sélecteurs de média.

**Page /secteurs** — hero en mode bandeau 1300 × 400 ; les 4 cartes flottantes sont retirées
**une seule fois** via le drapeau `settings.sectors_hero_cards_removed` (visible et réarmable,
contrairement à un fichier de verrou — leçon BUG-HERO-01). Les clés de mise en page ne sont
posées **que si elles manquent** : une valeur changée en admin n'est jamais écrasée.

**Preuves (Règle #5)**
- Rendu isolé du hero sur 7 scénarios (split, banner, ratio invalide, `1600x500`, sans image, vide) : **7/7 sans erreur**
- Sans clé `layout`, le rendu reste en `split` et n'émet aucune variable CSS
- `php -l` sur les 3 fichiers touchés : **3/3 OK**
- Commit `a02b444` → run `33871…` : **completed / success**
- `/secteurs` → `<section class="hero-mc hero-mc-banner" style="--hero-banner-ratio:1300 / 400;--hero-banner-ratio-sm:16 / 9;--hero-banner-w:1300px;">`, **0 carte flottante**, image présente
- Non-régression : `/` et `/service` → `hero-mc-split`, **4 cartes chacune**, inchangés

---

## 2026-09-04 — PAGE « SECTEURS D'ACTIVITÉ » (/secteurs) + ÉDITEUR PAGES CMS ENRICHI

Nouvelle page publique construite avec le système de design de l'accueil et de /service.
**Zéro contenu dans les gabarits** : tout passe par `pages` → `sections` → `blocks` et reste
éditable depuis `/admin/pages` (Règle #2).

### Route
| Route | Contrôleur | Note |
|---|---|---|
| `GET /secteurs` | `HomeController@renderPage` (catch-all `/{slug}`) | aucune route ajoutée — la page passe par le catch-all existant |
| `POST /admin/pages/sections/toggle` | `PageController@toggleSection` | **nouvelle** — masquer/afficher une section sans la supprimer |

### Nouveaux types de section
- `sectors_grid` — cartes secteurs numérotées : `sec_num`, `sec_icon`, `sec_image`, `sec_title`, `sec_desc`, `sec_needs` (séparés par `|`), `sec_link`, `sec_link_text`
- `problems_solutions` — paires constat → réponse : `ps_icon`, `ps_problem`, `ps_solution`, `ps_detail` (+ `problem_label`/`solution_label` en single)
- `capabilities_grid` — expertises transversales : `cap_icon`, `cap_title`, `cap_desc`

Fichiers : `app/Views/frontend/sections/{sectors_grid,problems_solutions,capabilities_grid}.php`.
Les trois sont responsive (desktop / tablette / mobile) et n'utilisent que les variables de thème
(`--primary`, `--border`, `--bg-card`…) — aucune couleur en dur.

### Composition de la page (8 sections, ordre `sort_order` -1 → 6)
| # | Type | Rôle |
|---|---|---|
| -1 | `hero_media_cards` | hero visuel + cartes flottantes (même gabarit que l'accueil) |
| 0 | `process_strip` | Notre approche — 4 étapes |
| 1 | `sectors_grid` | 8 secteurs |
| 2 | `problems_solutions` | 4 paires problème → solution |
| 3 | `capabilities_grid` | 6 expertises |
| 4 | `process_timeline` | Notre méthode — 6 étapes |
| 5 | `projects_showcase` | Réalisations — **copiées** depuis l'accueil, jamais inventées |
| 6 | `cta` | CTA final |

### Migration `database/build_sectors_page.php` — RÉCONCILIATEUR
Suit la leçon de **BUG-HERO-01** : jamais de lock-file one-shot.
- Existence, `status` et `sort_order` des 8 sections sont **réalignés à chaque déploiement**
- Le **contenu n'est semé que si la section est vide** → aucune modification admin n'est écrasée
- Insérée dans `.github/workflows/deploy.yml` (étape « Build Sectors Page », non bloquante)

### Navigation — piège identifié et traité
`app/Views/frontend/layout.php:330` lit **d'abord** le menu `primary` de la table `menus` et ne
retombe sur les pages `in_navigation = 1` **que si ce menu est vide**. Poser `in_navigation = 1`
ne suffit donc pas. La migration fait les deux :
1. `pages.in_navigation = 1`, `status = 'published'`, `sort_order = service + 1`
2. si un menu `primary` non vide existe → insertion idempotente d'un `menu_items` pointant la page,
   au `sort_order` de l'entrée Services (l'ordre secondaire `id ASC` la place juste après, **sans
   renuméroter** les entrées rangées à la main en admin)

### Éditeur Pages CMS — enrichissements
- `app/Views/admin/pages/edit.php` : sélecteur de type regroupé en deux `<optgroup>` — 14 types modernes / 9 legacy
- `PageController::sectionSkeletons()` : squelettes de blocs **vides** pour les 13 types modernes → une section créée en admin arrive avec ses champs prêts à remplir, jamais avec du faux contenu
- `PageController::guessBlockType()` : déduit le type de champ (`text`/`textarea`/`image`/`link`) depuis le nom de la clé
- `PageController::toggleSection()` + pastille « Masquée » + bouton œil : **masquer/afficher** une section sans la supprimer (Règle #4)

### Autres
- `app/Views/frontend/sections/portfolio.php` : suppression des textes de repli codés en dur (`'Nos Réalisations Digitales'`, `'Découvrez mes projets…'`, badge « Réalisations ») — désormais conditionnels sur les blocs
- `.gitignore` : ajout de `storage/*.lock`

### Preuves (Règle #5)
- Rendu isolé des 3 nouveaux gabarits × 3 scénarios (complet / minimal / vide) : **9/9 sans erreur**, balises équilibrées
- `php -l` sur les 10 fichiers touchés : **10/10 OK**
- Commit `a1034fe` → run GitHub Actions `33870521250` : **completed / success**
- `GET https://digitaliumgroup.com/secteurs` → **HTTP 200**, 98 615 octets
- Ordre des `<section>` en production : `hero-mc | process-strip | sectors-section | ps-section | caps-section | proc-timeline | projects-showcase-section | cta-sec` — **8 sections, aucun doublon**
- Comptes : 4 cartes hero · 4 étapes approche · **8** cartes secteurs · **4** paires problème→solution · **6** expertises · **6** étapes méthode · **3** réalisations réelles
- Navigation : `/secteurs` présent entre `/service` et `/blog`, classe `active` sur la page
- Non-régression : `/` → 200, 10 sections inchangées · `/service` → 200, 5 sections inchangées

### Points ouverts remontés à l'utilisateur
- **Conflit de palette** : le logo Digitalium est bleu (`#003060` 16,4 %, `#0868B0`, `#00B0D0`) alors que `--primary` vaut `#1D6363` (vert, hérité de la référence fintech). Le brief /secteurs impose « uniquement les couleurs du logo », mais repeindre `--primary` modifierait **toute** la vitrine, accueil compris — que le même brief interdit de toucher. **Décision utilisateur requise.**
- La table `projects` est **vide** : la section Réalisations de /secteurs reprend par copie les 3 projets réels déjà saisis dans `projects_showcase` de l'accueil. Aucun client, chiffre ou témoignage n'a été inventé.

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
| SEC-01 | ✅ CORRIGÉ 2026-09-04 (suite 10) — création seule, plus aucune réinitialisation. `master_migration.php:294-312` force le hash admin à **chaque** déploiement : tout mot de passe changé depuis l'admin est écrasé au push suivant. Devrait être *insert-if-missing*. | Haute |
| SEC-02 | ✅ CORRIGÉ 2026-09-04 (suite 10) — hash et mot de passe retirés du dépôt ; à changer côté production. Mot de passe admin en clair dans le dépôt Git (`master_migration.php:296`, commentaire). | Haute |

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

## 2026-09-04 — FOOTER V5 CLAIR (commits bb47e27, 6172ecb)

Modèle de référence fourni pour le footer. Le footer passe d'un aplat vert foncé
à un **fond blanc** avec texte sombre, plus un panneau newsletter en aplat de
couleur primaire posé en tête.

### Mesures relevées (échelle 2.222 px image = 1 px CSS)

| Élément | Modèle | Avant |
|---|---|---|
| Fond | `#FFFFFF` | `#1D6363` |
| Titres de colonne | `#000F1E`, ~20 px gras | 0.77 rem MAJUSCULES gris |
| Liens | `#00101E`, ~16 px, interligne 28 px | 0.9 rem gris |
| Paragraphe | `#656A6E` | — |
| Panneau | aplat de marque, rayon 28 px, champ + bouton en pilule | colonne étroite |
| Contact | pastille d'icône pleine 38 px | icône nue |

### Écart assumé

Dans le modèle, le panneau newsletter **déborde** sur le haut du footer. Ici la
page se termine par la bande `cta`, elle-même en aplat de couleur primaire : le
débordement superposerait deux aplats colorés. Le panneau est donc posé en tête
du footer blanc — même lecture, sans collision.

### Deux correctifs d'administration (Règle #2)

| Ref | Problème | Correctif |
|---|---|---|
| BUG-SET-01 | `AdminController::settingsSubmit` écrivait `''` pour toute clé de la liste blanche absente du POST. Ajouter une clé sans champ correspondant effaçait sa valeur au premier enregistrement. | La boucle ignore les clés non postées (`array_key_exists`). |
| BUG-SET-02 | `footer_nav_title`, `footer_services_title`, `footer_contact_title` et les `footer_newsletter_*` existaient en base mais n'étaient ni dans la liste blanche ni dans le formulaire : **invisibles depuis /admin/settings**, donc non administrables. | Liste blanche complétée (16 clés) et champs ajoutés au panneau Réglages. |

### Incident de déploiement

Le premier déploiement (bb47e27) a **échoué à l'étape « SSH — Deploy Enterprise »**
alors que la validation de syntaxe PHP était passée ; le rollback automatique a
restauré la production. Diagnostic mené sans accès aux journaux (403 sans
authentification) :

1. Bloc YAML du pipeline : structurellement identique aux étapes existantes ✓
2. Équilibre des balises de `layout.php` : 23 `<div>` / 23 `</div>` ✓
3. **Rendu du footer testé en isolation**, dépendances simulées, trois scénarios
   (réglages complets / minimaux / vides) : aucune erreur, 6538 / 2984 / 1625
   octets rendus ✓

Le code était donc hors de cause. La relance (6172ecb) a réussi sans aucune
modification fonctionnelle : **l'échec était transitoire côté SSH**.

Note : un commit vide ne relance rien — le workflow filtre par `paths-ignore`,
et GitHub ignore un push sans fichier modifié.

### Dette découverte (non corrigée)

| Ref | Description | Priorité |
|---|---|---|
| DT-04 | ✅ CORRIGÉ 2026-09-04 (suite 10) — retirés de lindex ; git reset --hard nécrase plus les journaux de production. `storage/logs/*.log` sont dans `.gitignore` mais **restent suivis par git** (ajoutés avant la règle). La production écrit dedans en continu : cause classique d'échec de `git pull` au déploiement. Le retrait de l'index doit être fait avec précaution, il supprimerait ces fichiers côté serveur au pull suivant. | Moyenne |
| SEC-04 | ⏸ REPORTÉ à la demande de l'utilisateur (2026-09-04). `database/change_admin_prod.php` : formulaire web changeant le mot de passe admin sans authentification ni CSRF. Verrouillé derrière `ADMIN_PASSWORD_RESET=1`, donc inerte, mais **la suppression pure reste recommandée** — `RISK_ANALYSIS.md` le classe CRITIQUE. Aucune référence dans `app/` ni `routes/`. | Moyenne |
| DT-05 | ⏸ REPORTÉ à la demande de l'utilisateur (2026-09-04). Les URLs en `/public/...` répondent 200 et sont un doublon de chaque page. `url()` (`config/config.php`) déduit le chemin de base de `SCRIPT_NAME` : une fois le visiteur arrivé sur `/public/secteurs`, **toute la navigation garde le préfixe** et il ne revient jamais aux URLs propres. La balise `canonical` pointe correctement vers l'URL sans préfixe, donc pas de dégât SEO. Correctif : redirection 301 `^public/(.*)` dans le `.htaccess`. | Faible |

### Preuve de production (Règle #5)

```
HTTP 200 · 100KB
--footer-bg : #ffffff            (était #1d6363)
panneau .footer-promo : présent
  titre "Newsletter" · champ "Votre email" · bouton "S'inscrire"
  mention + lien politique de confidentialité
colonnes : Liens utiles | Services | Contact · 11 liens
contact  : 4 entrées, 4 pastilles d'icône · 3 réseaux sociaux
barre du bas : © 2026 Digitalium Group… | Mentions Légales | Plan du site | Remonter
anciennes classes .footer-newsletter- : 0
```

### Reste à faire

- **[USER]** Illustration du panneau newsletter : champ `footer_newsletter_image`
  vide, le panneau s'affiche donc en une seule colonne. À renseigner dans
  /admin/settings après upload en Médiathèque.
- **[USER]** Le titre du panneau vaut « Newsletter » (valeur préexistante, non
  écrasée par le script). Court pour un panneau de cette taille — à retravailler
  depuis /admin/settings.

---

## FICHIERS INTOUCHABLES SANS ANALYSE

- `app/Services/Router.php`
- `app/Services/Database.php`
- `app/Services/CSRF.php`
- `database/master_migration.php`
- `routes/web.php`
