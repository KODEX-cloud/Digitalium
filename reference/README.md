# Dossier de Référence - Digitalium CMS

Ce dossier contient les ressources de référence (fichiers HTML statiques maquettés et images sources) servant de guide pour la construction et l'évolution du site vitrine et du système d'administration de **digitaliumgroup.com**.

## Organisation du dossier
* `/reference/html/` : Contient des intégrations HTML/CSS de référence statiques des différentes sections pour servir de modèle d'intégration de secours ou de gabarit visuel.
* `/reference/images/` : Contient les visuels de démonstration, les icônes de marque et les captures d'écran des maquettes structurelles.

## Rappel des sections gérées par le Page Builder
Chaque section ci-dessous est entièrement dynamique et administrable via le panneau de contrôle :

1. **Bannière Principale (`hero`)** : Titre HTML enrichi (Quill editor), sous-titre textuel, bouton CTA (texte + lien) et image de fond/d'illustration.
2. **Expertises (`services`)** : Section d'introduction générale + grille de cartes répétables contenant chacune un titre, une description textuelle et une icône dynamique (Lucide Icons).
3. **Portfolio (`portfolio`)** : Galerie filtrable dynamiquement par catégories (Tous, Ingénierie, Cloud, DevOps...) contenant des cartes de projets avec liens externes.
4. **Équipe (`team`)** : Trombinoscope d'ingénieurs avec liens vers leurs profils sociaux (LinkedIn, Twitter, GitHub).
5. **Témoignages (`testimonials`)** : Système de citations clients avec évaluation par étoiles interactive.
6. **Questions Fréquentes (`faq`)** : Accordéon dynamique avec animation fluide pour les questions techniques courantes.
7. **Blog (`blog`)** : Liste d'articles techniques avec en-tête d'information (tag, date de publication, résumé et lien de lecture).
8. **Contact (`contact`)** : Section double-colonne présentant les coordonnées modifiables du groupe et un formulaire de contact asynchrone (AJAX) connecté à l'API de messagerie locale.
