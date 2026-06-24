# BUSINESS_ANALYSIS — Digitalium Group
> Produit le 2026-06-24

---

## 1. IDENTITÉ DU PROJET

**Digitalium Group** est une agence digitale française positionnée sur le segment premium.

**Positionnement :** Solutions logicielles sur mesure, transformation digitale, conseil tech.

**Références design déclarées :** Apple, Stripe, Framer, Linear, Webflow Enterprise.

**Environnements :**
- Local : WAMP64 → `http://localhost/Digitalium`
- Production : Hostinger → `https://digitaliumgroup.com`

---

## 2. NATURE DU PRODUIT

Ce n'est pas un simple site vitrine. C'est :

| Dimension | Description |
|---|---|
| Site vitrine premium | Présence en ligne de l'agence avec pages dynamiques |
| CMS administrable | Interface admin complète pour gérer tout le contenu |
| Portfolio | Galerie des réalisations clients avec CRUD |
| Blog professionnel | Articles, catégories, SEO, pagination |
| Générateur de pages | Système de sections/blocks permettant de construire n'importe quelle page |
| Futur écosystème | Roadmap implicite : espace client, portail projets, devis en ligne |

---

## 3. MODULES EXISTANTS ET LEUR ÉTAT

| Module | Existence | État fonctionnel |
|---|---|---|
| Pages dynamiques (CMS) | ✅ Complet | ✅ Fonctionnel |
| Hero engine (10+ variants) | ✅ Complet | ⚠ Partiel (stats labels hardcodés) |
| Sections/Blocks builder | ✅ Complet | ✅ Fonctionnel |
| Admin settings | ✅ Complet | ✅ Fonctionnel |
| Media library | ✅ Complet | ✅ Fonctionnel |
| Navigation dynamique | ✅ Complet | ✅ Fonctionnel |
| Header/Footer dynamiques | ✅ Complet | ✅ Fonctionnel |
| Réalisations (Projects) | ✅ Admin CRUD | ❌ Pas de page publique dédiée |
| Blog | ✅ Admin CRUD + frontend | ⚠ Migration DB non exécutée |
| Hero Slides | ✅ Complet | ✅ Fonctionnel |
| CSRF protection | ✅ Complet | ✅ Fonctionnel (double validation, redondant) |
| Cache système | ✅ Complet | ✅ Fonctionnel |
| Sitemap.xml | ✅ Présent | ⚠ Domaine hardcodé |
| Contact form | ✅ Présent | ⚠ Log fichier seulement, pas d'email |
| Menu manager dédié | ❌ Absent | — |
| Espace client | ❌ Absent | — |
| Devis en ligne | ❌ Absent | — |

---

## 4. OBJECTIFS MÉTIER

### Court terme (état actuel à consolider)
1. Zéro page en erreur 500/404 sur le frontend
2. Tout le contenu visible editable depuis le backend sans exception
3. Blog opérationnel (articles publiables, SEO, pagination)
4. Portfolio public consultable par les visiteurs
5. Formulaire de contact avec envoi email

### Moyen terme
1. Menu manager avec drag-and-drop et liens externes
2. Dashboard enrichi (analytics, actions rapides)
3. Optimisation performance (cache blog, lazy loading)
4. SEO avancé par page (schema.org, Open Graph images personnalisées)

### Long terme (vision CMS)
1. Gestion des utilisateurs admin (rôles/permissions)
2. Espace client avec accès projets
3. Module devis/estimation en ligne
4. Notifications email (contact, blog, alertes)

---

## 5. CONTRAINTES TECHNIQUES

| Contrainte | Impact |
|---|---|
| PHP 8.1+ sans framework | Pas de Composer/PSR autoloading par défaut — autoloading custom |
| WAMP64 local + Hostinger prod | Environnements différents, pas de CI/CD |
| Pas d'email server configuré | Contact form → log fichier seulement |
| Cache fichier | Pas de Redis/Memcached — cache filesystem |
| URLs subdirectory en local (`/Digitalium/`) vs root en prod | url() helper gère cette différence |
| MariaDB/MySQL | PDO strict mode, ENUM types, utf8mb4 |

---

## 6. MODULES MANQUANTS (PRIORITAIRES)

| Module | Priorité | Complexité | Impact |
|---|---|---|---|
| Page publique Réalisations (`/realisations`) | P1 | Faible | Fort |
| Stats labels dynamiques dans sections/hero.php | P1 | Très faible | Moyen |
| Blog migration exécutée (tables créées) | P0 | Aucune (déjà codée) | Critique |
| Dashboard stats blog + projets | P1 | Très faible | Moyen |
| Suppression fichiers publics dangereux | P0 | Aucune | Critique sécurité |
| Envoi email formulaire contact | P2 | Faible | Fort |
| Menu manager admin | P2 | Moyen | Fort |
| 404 page dans le layout | P2 | Faible | Moyen |
| OG image dynamique par page | P3 | Faible | SEO |
| Domaine sitemap depuis settings | P3 | Très faible | SEO |
| Désactivation logs DB en production | P2 | Très faible | Performance/Sécurité |
