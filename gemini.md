# Digitalium Group — Codebase Handbook & AI Context Guide

Ce document sert de guide de référence et de contexte persistant. Il résume le fonctionnement de l'application, l'architecture technique, les choix de conception et fournit des consignes claires pour qu'un futur modèle d'intelligence artificielle ou développeur comprenne instantanément le projet et ses contraintes.

---

## 1. Description de l'Application

**Digitalium Group** est un site vitrine et de portfolio technologique haut de gamme, équipé d'un **Custom CMS** ultra-léger et modulaire conçu en PHP natif.
L'application permet d'administrer intégralement le contenu et l'apparence visuelle du site page par page depuis un espace d'administration sécurisé, notamment en modulant dynamiquement les sections, les listes d'éléments, l'en-tête (Header) et la bannière principale (Hero).

---

## 2. Fonctionnalités Implémentées

### 🎨 Système "Hero & Header Builder" Avancé (Page par Page)
- **Header Configurable** :
  - **Fonds multiples** : Choix entre Effet Verre (`glass`), Blanc transparent (`clair`), Semi-transparent, Sombre transparent (`sombre`), Flou intense (`blur`) ou Opaque (`plein`).
  - **Contrôles physiques** : Réglage d'opacité (slider `0.1` à `1.0`), d'intensité de flou (`backdrop-filter`) et d'ombres portées (`aucun`, `leger`, `moyen`, `fort`).
  - **Contraste adaptatif** : Thèmes de contraste pour la navigation (`light_on_dark`, `dark_on_light`, `solid`) assurant que le menu reste lisible sur n'importe quel fond d'écran.
- **Sélection Responsive des Logos** :
  - Permet d'uploader et configurer un logo clair (`logo_light`) et sombre (`logo_dark`).
  - Le gabarit charge automatiquement le logo approprié selon le fond d'écran et le contraste de la page, avec ajustement dynamique de la hauteur.
- **Hero ultra-configurable** :
  - **Mise en page** : Formats `grand`, `moyen`, `compact` et `plein écran`.
  - **Positionnement libre** : Alignements horizontaux (`gauche`, `centre`, `droite`) et verticaux (`haut`, `milieu`, `bas`) à l'aide d'une structure pure Flexbox.
  - **Filtres de rendu** : Réglages isolés de luminosité, saturation et flou sur l'illustration de fond pour préserver une netteté de texte maximale (lisibilité premium).
  - **Image mobile alternative** : Balise responsive `<picture>` chargeant un visuel alternatif pour les smartphones.

### 🖥️ Simulateur Interactif Temps Réel (Admin)
- **Visualisation Instantanée** : Intégration d'un module d'affichage en grille côte à côte (split 2 colonnes) dans l'éditeur de page.
- Les modifications de sliders, de textes et de listes déroulantes appliquent immédiatement des styles CSS équivalents en Javascript sur une maquette de simulation.
- **Breakpoints simulés** : Boutons de bascule **Desktop** et **Mobile** simulant le comportement responsif et le masquage adaptatif du Header et de la Hero section.

### 🧱 CMS de Sections Modulaires & Réordonnables
- **Gestionnaire de Sections** : Organisation modulaire en base de données (`pages`, `sections`, `blocks`).
- **Drag-and-Drop** : Barre latérale administrative permettant de réordonner l'ordre d'affichage des sections par glisser-déposer interactif (sauvegarde AJAX instantanée).
- **Blocs Répétables** : Grilles et decks dynamiques (Services, Réalisations, Témoignages, FAQ, Processus) gérant l'ajout et la suppression d'éléments à la volée.

### 🛠️ Système d'Icônes Unifié (`IconHelper`)
- Un helper universel qui détecte et génère automatiquement le bon rendu selon le format saisi par l'admin : icônes FontAwesome (`fa-solid fa-code`), Lucide, SVG brut en ligne, ou chemin d'image.

---

## 3. Structure des Fichiers

L'application suit une architecture MVC (Modèle-Vue-Contrôleur) propre et modulaire en PHP natif :

```markdown
├── app/
│   ├── Controllers/       # Contrôleurs MVC
│   │   ├── Controller.php     # Contrôleur de base (Sécurité, rendu)
│   │   ├── AdminController.php# Gestion d'authentification et tableau de bord
│   │   └── PageController.php # Gestion administrative des pages (Visuals, Sections, Blocks)
│   │
│   ├── Models/            # Modèles d'accès SQL (PDO)
│   │   ├── Model.php          # Classe SQL parent
│   │   ├── Page.php           # Modèle d'administration des pages (16 nouveaux champs)
│   │   ├── Section.php        # Structure des sections de page
│   │   └── Block.php          # Données des blocs et listes répétables
│   │
│   ├── Helpers/           # Classes utilitaires
│   │   ├── IconHelper.php     # Rendu polymorphique d'icônes
│   │   └── MediaHelper.php    # Notice et sélecteur d'upload
│   │
│   └── Views/             # Gabarits et Vues HTML
│       ├── admin/             # Panneau d'administration
│       │   ├── layout.php         # Thème global d'administration
│       │   └── pages/
│       │       └── edit.php           # Constructeur de page et SIMULATEUR interactif
│       │
│       └── frontend/          # Site public visible par les visiteurs
│           ├── layout.php         # Gabarit public (Header, Ambient Glows, Particules)
│           ├── sections/          # Gabarits de rendu des sections (services_grid, feature, process...)
│           └── partials/
│               └── hero.php           # Rendu adaptatif de la Hero section
│
├── public/                # Répertoire public d'exécution HTTP
│   ├── index.php              # Point d'entrée HTTP unique (Routage MVC)
│   └── assets/
│       ├── css/
│       │   └── index.css          # Styles généraux (Tokens Glassmorphism)
│       └── uploads/           # Fichiers de la bibliothèque média
│
├── database/              # Scripts SQL et Migrations
│   ├── schema.sql             # Schéma de base
│   └── add_advanced_hero_header_fields.php # Script d'injection des colonnes visuelles
│
└── config/                # Configurations système et connexions PDO
```

---

## 4. Technologies Utilisées

1. **Serveur & Logique** : PHP 8.2 (Architecture MVC native, requêtes préparées PDO pour contrer les injections SQL).
2. **Base de Données** : MySQL 8.4 (Moteur relationnel optimisé).
3. **Stylisation CSS** : Vanilla CSS 3 natif (Pas de TailwindCSS). Utilisation poussée de variables personnalisées (`:root`), de filtres graphiques de flou (`backdrop-filter`) et de transitions fluides (`cubic-bezier`).
4. **Typographie** : Outfit (Titres technologiques) & Inter (Paragraphes et corps de texte) chargées via Google Fonts.
5. **Icônes** : Lucide Icons (Javascript) & FontAwesome 6 (CSS CDN).
6. **Rendu Frontend** : Canvas HTML5 natif pour l'effet de particules connectées en réseau interactif.

---

## 5. Décisions de Design Clés

### 💎 Identité Visuelle Premium "Glassmorphism"
- **Règle Chromatique Absolue** : La palette du site est strictement préservée (Bleu Digitalium `#1e3a8a`, Violet Premium `#7c3aed`, Orange CTA `#e26d36`, sur fonds clairs légers et atmosphériques). Ne jamais forcer de thème global sombre par défaut ou modifier ces couleurs.
- **Bordures en Verre Trempé** : Les bordures et cadres de cartes doivent rester blancs et semi-transparents (`rgba(255,255,255,0.75)`), mais se détacher élégamment grâce à des ombres portées complexes et des reflets internes lumineux (`--shadow-card`).
- **Netteté des Textes** : L'utilisation de filtres CSS de flou ou de saturation d'image est isolée sur un calque d'arrière-plan dédié (`.hero-bg-layer`) dans la Hero. Cela garantit que le texte reste parfaitement net, opaque, et lisible sans hériter des filtres appliqués à l'image.

---

## 6. Consignes Importantes pour un Futur Modèle d'IA

Lorsque vous modifiez ou étendez cette application, veillez à respecter rigoureusement ces règles architecturales :

### 💾 Persistance de Configurations Complexes
- N'ajoutez pas continuellement de nouvelles colonnes SQL pour des réglages secondaires. Utilisez la colonne TEXT `responsive_settings` sous forme de payload JSON.
- Conservez la structure JSON suivante pour les réglages de breakpoints et de filtres :
  ```json
  {
    "mobile": {
      "header_visible": true,
      "logo_size": 30,
      "hero_text_position": "centre",
      "hero_text_alignment": "center"
    },
    "visual": {
      "brightness": 1.0,
      "saturation": 1.0,
      "blur": 0
    }
  }
  ```

### ⚡ Synchronicité Simulateur ↔ Frontend
- Si vous ajoutez un nouveau paramètre de design dans l'éditeur de page (`edit.php`), vous devez obligatoirement :
  1. Lui assigner la classe `.visual-simulator-trigger`.
  2. Mettre à jour la fonction Javascript `updateSimulator()` pour qu'il soit rendu instantanément sur la maquette de prévisualisation.
  3. Mettre à jour le fichier public frontend correspondant (`layout.php` ou `hero.php`) pour appliquer le style réel en PHP.

### 🔒 Sécurité & Performance
- Toutes les pages administratives doivent valider le jeton CSRF (`$this->validateCsrf()`).
- Après toute modification de fichiers PHP de rendu ou de gabarit, exécutez toujours le script CLI de purge du cache : `php bin/clear-cache.php`.
- N'utilisez jamais la commande `cd` dans vos exécutions shell.
