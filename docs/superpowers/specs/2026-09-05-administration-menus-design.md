# Administration complète des menus de navigation

> Conception validée le 2026-09-05. Digitalium Group CMS.
> Objectif : supprimer, déplacer et organiser tous les menus du site depuis le backend.

---

## 1. Audit — l'état de départ

### Ce qui existait déjà

`/admin/menus` permet de créer, renommer et supprimer un menu ; l'éditeur permet d'ajouter un lien
(depuis une page ou personnalisé), de le retirer, de le réordonner par glisser-déposer, et de
choisir parent, cible, icône et état actif. Tables `menus` (name, slug, location) et `menu_items`
(arbre par `parent_id`), avec clés étrangères `menu_id → menus` (CASCADE), `parent_id → menu_items`
(SET NULL) et `page_id → pages` (SET NULL).

### Les trois défauts constatés

| # | Défaut | Effet observé |
|---|---|---|
| 1 | `MenuItem::saveForMenu` efface toutes les lignes puis les réinsère, en réutilisant les `parent_id` de l'ancien cycle | La clé étrangère `parent_id` est violée, la transaction est annulée : **tout enregistrement d'un menu comportant un sous-lien échoue**. Un parent tout juste ajouté arrive sous la forme `new_1001`, que `(int)` réduit à `0` — même violation. |
| 2 | Le pied de page est construit depuis `pages.in_navigation` ([layout.php:569](../../../app/Views/frontend/layout.php)), jamais depuis le module Menus | Incohérence visible en production : l'en-tête affiche « Labs » (menu_items), le pied « Digitalium Labs » (titre de page). Le pied n'est pas administrable. |
| 3 | La colonne « Services » du pied recopie la section Services de l'accueil | Ses six liens pointent tous vers `/contact`, sans moyen de les corriger depuis le pied. |

Corrigé séparément et déjà en ligne (commit `0aa84d7`) : `/admin/menus/edit/{id}` répondait **500**
dès que le menu contenait un lien, la vue appelant `MenuItem::resolveUrl()` sans qualifier le nom
de classe.

### Ce qui fonctionne déjà et ne sera pas touché

Le rendu des sous-menus de l'en-tête ([layout.php:430-448](../../../app/Views/frontend/layout.php))
est correct : il n'a simplement jamais reçu d'enfants. Réparer l'enregistrement suffit à débloquer
les menus déroulants, sans une ligne de frontend.

---

## 2. Décisions

Trois décisions prises avec le CTO avant conception.

1. **Périmètre** — en-tête, pied de page, et menus secondaires réutilisables.
2. **Nouvelles pages** — une page publiée et cochée « Afficher dans la navigation » est ajoutée
   automatiquement en fin de menu, **une seule fois**. Ensuite le menu fait autorité : si le lien
   est retiré ou renommé, rien ne le remet. Le CTO ne perd jamais une page de vue et garde le
   dernier mot.
3. **Colonne Services du pied** — devient un vrai menu administrable, semé depuis la source
   actuelle pour que rien ne change visuellement le jour du déploiement.

---

## 3. Modèle de données

Aucune table nouvelle. `menus.location` devient le point d'ancrage : le frontend demande un
**emplacement**, jamais un identifiant.

| Emplacement | Rôle |
|---|---|
| `primary` | Navigation de l'en-tête |
| `footer` | Colonne « Navigation » du pied de page |
| `footer_services` | Colonne « Services » du pied de page |
| *libre* | Menus secondaires, réutilisables |

Une seule colonne ajoutée : `pages.nav_seeded TINYINT NOT NULL DEFAULT 0` — la mémoire du
« déjà proposé une fois ».

### Profondeur limitée à deux niveaux

L'en-tête sait afficher une racine et un niveau d'enfants. Au-delà, un lien serait enregistré mais
**invisible**. Le modèle refuse donc qu'un enfant devienne parent : un lien dont le parent est
lui-même un enfant est ramené à la racine. Cette règle élimine du même coup les cycles
(`A → B → A`), qui feraient boucler le rendu.

---

## 4. L'enregistrement : réconciliation

`MenuItem::saveForMenu($menuId, $items)` passe de « effacer puis réécrire » à « réaligner » — la
doctrine déjà appliquée aux scripts de construction du projet. Une seule transaction, trois temps :

1. **Écriture à plat.** Chaque ligne envoyée est mise à jour si son identifiant appartient bien à ce
   menu, insérée sinon — toujours avec `parent_id = NULL`. On construit la correspondance
   `référence du formulaire → identifiant réel` (les nouvelles lignes arrivent sous `new_1001`).
2. **Suppression.** Les lignes de ce menu absentes de l'envoi. Tous les parents valant `NULL` à cet
   instant, aucune contrainte ne peut bloquer.
3. **Repose des parents.** Via la correspondance. Un parent introuvable, égal à soi-même, ou
   lui-même enfant, est ramené à la racine plutôt que de faire échouer l'enregistrement.

Conséquences : un lien **conserve son identifiant** d'un enregistrement à l'autre, la clé étrangère
reste satisfaite en permanence, et une panne au milieu ne peut plus laisser un menu vide.

### Ajout automatique d'une page

`MenuItem::semerPage(array $page): bool` — appelée après la création et après l'enregistrement
d'une page. Elle n'agit que si la page est `published`, `in_navigation = 1` et `nav_seeded = 0`.
Elle ajoute le lien en fin de menu `primary` puis passe `nav_seeded` à 1. Un seul point d'appel
logique pour les deux écrans (Règle #1).

---

## 5. Frontend

`layout.php` demande un emplacement et retombe sur l'ancien comportement si le menu est vide :

| Zone | Source | Repli si le menu est vide |
|---|---|---|
| En-tête | menu `primary` | pages avec `in_navigation = 1` *(déjà en place)* |
| Pied — Navigation | menu `footer` | pages avec `in_navigation = 1` |
| Pied — Services | menu `footer_services` | section Services de l'accueil |

Il est donc **impossible** de se retrouver avec un pied de page vide, y compris si la migration
échoue ou si un menu est supprimé par erreur.

---

## 6. Administration

- **`/admin/menus`** — liste avec emplacement, nombre de liens, et un indicateur « branché sur le
  site » distinguant un menu réellement rendu d'un menu orphelin.
- **Éditeur** — glisser-déposer conservé ; hiérarchie lisible à l'écran ; imbrication et
  désimbrication ; suppression d'un lien ; bascule actif/inactif ; refus explicite du troisième
  niveau avec un message, plutôt qu'une disparition silencieuse.
- **Correctif au passage** — `MenuController` ne transmet pas `currentUser` au layout, contrairement
  à la convention du projet.
- L'éditeur fait aujourd'hui 21 Ko de HTML, CSS et JavaScript dans un seul fichier. Il est découpé
  pendant ce travail, sans changement d'ergonomie.

---

## 7. Migration

`database/build_menus_v2.php`, réconciliateur, chaque étape isolée dans son `try/catch` — la leçon
du 404 d'`/insights`, où une exception sur une étape secondaire avait tué la construction entière.

1. Colonne `pages.nav_seeded`.
2. Menus `footer` et `footer_services` créés s'ils manquent.
3. **Semis unique**, gardé par le drapeau `menus_v2_seeded_v1` : `footer` reçoit les pages
   actuellement en navigation, `footer_services` reçoit les intitulés et destinations actuels de la
   section Services — à l'identique, pour que **rien ne bouge visuellement**.
4. `nav_seeded = 1` sur toutes les pages existantes. **Sans cette étape, le premier enregistrement
   d'une page dupliquerait tout le menu principal.**

---

## 8. Prévention

`bin/check_views.php` — signale toute vue référençant une classe qu'elle ne peut pas résoudre
(nom court, ni pleinement qualifié ni importé par la vue elle-même). Le contrôle de syntaxe du
pipeline ne voit pas cette faute : elle n'apparaît qu'à l'exécution, sous forme de 500. Ajouté au
déploiement en étape **non bloquante**.

---

## 9. Vérification

| Banc | Ce qu'il prouve |
|---|---|
| Réconciliation | Le bouchon **applique réellement la clé étrangère** — sans quoi il validerait le bug actuel. Sous-menus enregistrés, identifiants conservés, suppression, troisième niveau refusé, cycle neutralisé. |
| Rendu frontend | En-tête avec sous-menus ; pied depuis les menus ; les deux replis quand un menu est vide. |
| Écrans d'administration | Liste, éditeur, état vide, échappement, jeton CSRF. |
| Migration à blanc | Site vierge, second passage, `ALTER` refusé, `settings` inaccessible. |
| Routes | Aucune régression sur les pages en ligne. |

---

## 10. Hors périmètre

- La page d'accueil.
- Les URLs `/public/...` (DT-05, reporté par le CTO).
- Le menu latéral de l'administration.
- Les défauts de schéma de `RecoveryController.php`, relevés et toujours en attente.
