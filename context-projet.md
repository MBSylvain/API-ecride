
# Contexte du projet EcoRide

Ce fichier sert à conserver le contexte du projet pour faciliter la génération de code et l'orientation des développements par l'agent IA.

## Informations générales
- Nom du projet : EcoRide
- Startup créée en France, objectif : réduire l'impact environnemental des déplacements par le covoiturage
- Application web aux couleurs écologiques
- Branche courante : API-MSQL
- Propriétaire : MBSylvain
- Date de création du contexte : 2 octobre 2025

## Description du projet
L'application permet la mise en relation de conducteurs et passagers pour des trajets en voiture.

## User Stories principales

**US1 - Page d'accueil (Visiteur)**
- Présentation entreprise avec images
- Barre de recherche d'itinéraire
- Footer avec mail et mentions légales

**US2 - Menu navigation (Visiteur)**
- Accueil, Covoiturages, Connexion, Contact

**US3 - Vue covoiturages (Visiteur)**
- Formulaire recherche (départ, arrivée, date)
- Affichage résultats : pseudo/photo/note conducteur, places restantes, prix, date/heure, mention écologique, bouton détail
- Seuls les trajets avec places disponibles
- Proposition date alternative si aucun résultat

**US4 - Filtres (Visiteur)**
- Filtres : écologique (voiture électrique), prix max, durée max, note min conducteur

**US5 - Détail covoiturage (Visiteur/User)**
- Tous les détails US3 + avis conducteur, modèle/marque/énergie véhicule, préférences conducteur

**US6 - Participation (Visiteur/User)**
- Bouton "participer" si places et crédits disponibles
- Redirection connexion/inscription si non connecté
- Double confirmation → mise à jour réservation et crédits

**US7 - Création compte (Visiteur)**
- Formulaire : pseudo, mail, mot de passe sécurisé
- 20 crédits offerts à l'inscription

**US8 - Espace utilisateur (User)**
- Choix statut (chauffeur/passager)
- Info chauffeur obligatoires : plaque, date immatriculation, modèle/couleur/marque, nombre places, préférences (fumeur/animal + personnalisées)
- Passager : aucune info spécifique

**US9 - Saisie voyage (User chauffeur)**
- Saisie adresse départ/arrivée
- Prix libre (-2 crédits pour la plateforme)
- Sélection véhicule existant ou nouveau

**US10 - Historique (User)**
- Consultation historique trajets
- Annulation possible → mise à jour crédits/places + mail participants si annulation chauffeur

**US11 - Gestion trajet (User chauffeur)**
- Boutons "Démarrer" et "Arrivée à destination"
- Mail aux participants pour validation et avis
- Si problème → commentaire + traitement employé

**US12 - Espace employé**
- Validation/refus des avis avant publication
- Consultation trajets problématiques (détails complets)

**US13 - Espace administrateur**
- Création comptes employés
- Graphiques : nombre covoiturages/jour et crédits gagnés/jour
- Suspension comptes utilisateurs/employés

## Stack technique recommandé (non obligatoire)
- Front : HTML5, CSS (Bootstrap), JS
- Back-end : PHP avec PDO
- BDD relationnelle : MySQL/MariaDB/PostgreSQL
- BDD NoSQL : MongoDB
- Déploiement : fly.io, Heroku, Azure, Vercel

## Livrables obligatoires
- Lien dépôt GitHub PUBLIC
- Lien application déployée
- Lien outil gestion de projet (Trello/Jira/Notion)

Le dépôt Git doit contenir :
- README.md avec instructions déploiement local
- Bonnes pratiques Git (branches main/dev/features)
- Fichiers SQL de création BDD et données (fixtures)
- Manuel utilisation PDF avec identifiants test
- Charte graphique PDF + maquettes (3 desktop + 3 mobile)
- Documentation gestion de projet
- Documentation technique (choix tech, config env, MCD, diagrammes)
- Tableau Kanban avec colonnes : Backlog, À faire, En cours, Terminé (dev), Livré (main)

## Notes
Ce fichier peut être enrichi au fur et à mesure du projet.
