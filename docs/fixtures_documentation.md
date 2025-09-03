# Documentation des Données d'Exemple (Fixtures)

## Table des matières
1. [Introduction](#introduction)
2. [Structure des Tables](#structure-des-tables)
3. [Jeux de Données](#jeux-de-données)
4. [Relations entre les Tables](#relations-entre-les-tables)
5. [Utilisation des Fixtures](#utilisation-des-fixtures)

## Introduction

Ce document détaille les données d'exemple (fixtures) utilisées pour tester et démontrer les fonctionnalités de l'application de covoiturage. Les données couvrent tous les aspects du système, notamment les utilisateurs, les trajets, les réservations, les messages et les avis.

## Structure des Tables

### 1. Table `utilisateurs`
```sql
CREATE TABLE `utilisateurs` (
  `utilisateur_id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `telephone` varchar(15) DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `date_naissance` date DEFAULT NULL,
  `pseudo` varchar(50) DEFAULT NULL,
  `date_inscription` datetime DEFAULT current_timestamp(),
  `compte_actif` tinyint(1) DEFAULT 1,
  `role` enum('Administrateur','Conducteur','Passager','Modérateur') NOT NULL DEFAULT 'Passager'
)
```

### 2. Table `trajets`
```sql
CREATE TABLE `trajets` (
  `trajet_id` int(11) NOT NULL AUTO_INCREMENT,
  `ville_depart` varchar(100) NOT NULL,
  `ville_arrivee` varchar(100) NOT NULL,
  `adresse_depart` text DEFAULT NULL,
  `adresse_arrivee` text DEFAULT NULL,
  `date_depart` datetime NOT NULL,
  `heure_depart` time NOT NULL,
  `heure_arrivee` time DEFAULT NULL,
  `nombre_places` int(11) NOT NULL,
  `prix` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `bagages_autorises` tinyint(1) DEFAULT 1,
  `fumeur_autorise` tinyint(1) DEFAULT 0,
  `animaux_autorises` tinyint(1) DEFAULT 0,
  `statut` enum('planifié','en_cours','terminé','annulé') DEFAULT 'planifié'
)
```

### 3. Table `reservations`
```sql
CREATE TABLE `reservations` (
  `reservation_id` int(11) NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int(11) NOT NULL,
  `trajet_id` int(11) NOT NULL,
  `nombre_places_reservees` int(11) NOT NULL,
  `statut` enum('confirmée','en_attente','annulée','refusée') DEFAULT 'en_attente',
  `date_reservation` datetime DEFAULT current_timestamp(),
  `date_confirmation` datetime DEFAULT NULL,
  `point_rdv` text DEFAULT NULL,
  `commentaire` text DEFAULT NULL,
  PRIMARY KEY (`reservation_id`),
  KEY `utilisateur_id` (`utilisateur_id`),
  KEY `trajet_id` (`trajet_id`)
)
```

### 4. Table `messages`
```sql
CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL AUTO_INCREMENT,
  `expediteur_id` int(11) NOT NULL,
  `destinataire_id` int(11) NOT NULL,
  `trajet_id` int(11) DEFAULT NULL,
  `contenu` text NOT NULL,
  `date_envoi` datetime DEFAULT current_timestamp(),
  `lu` tinyint(1) DEFAULT 0,
  `utilisateur_id` int(11) NOT NULL,
  PRIMARY KEY (`message_id`),
  KEY `expediteur_id` (`expediteur_id`),
  KEY `destinataire_id` (`destinataire_id`),
  KEY `trajet_id` (`trajet_id`)
)
```

### 5. Table `avis`
```sql
CREATE TABLE `avis` (
  `avis_id` int(11) NOT NULL AUTO_INCREMENT,
  `auteur_id` int(11) NOT NULL,
  `destinataire_id` int(11) NOT NULL,
  `trajet_id` int(11) NOT NULL,
  `commentaire` text NOT NULL,
  `note` int(11) NOT NULL CHECK (`note` between 1 and 5),
  `date_creation` datetime DEFAULT current_timestamp(),
  `statut` enum('publié','modéré','signalé') DEFAULT 'publié',
  `utilisateur_id` int(11) NOT NULL,
  PRIMARY KEY (`avis_id`),
  KEY `auteur_id` (`auteur_id`),
  KEY `destinataire_id` (`destinataire_id`),
  KEY `trajet_id` (`trajet_id`)
)
```

### 6. Table `voiture`
```sql
CREATE TABLE `voiture` (
  `voiture_id` int(11) NOT NULL AUTO_INCREMENT,
  `modele` varchar(50) NOT NULL,
  `marque` varchar(50) NOT NULL,
  `immatriculation` varchar(50) NOT NULL,
  `energie` varchar(50) NOT NULL,
  `couleur` varchar(50) DEFAULT NULL,
  `date_premiere_immatriculation` date NOT NULL,
  `nombre_places` int(11) NOT NULL DEFAULT 5,
  `photo_url` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `utilisateur_id` int(11) NOT NULL,
  PRIMARY KEY (`voiture_id`),
  UNIQUE KEY `immatriculation` (`immatriculation`)
)
```

## Jeux de Données

### 1. Utilisateurs de Test
```sql
-- Administrateur
INSERT INTO `utilisateurs` VALUES (4, 'Admin', 'System', 'admin@covoiturage.com', '[password_hash]', NULL, NULL, NULL, 'admin', '2025-04-23 22:25:22', 1, 'Administrateur');

-- Conducteur Test
INSERT INTO `utilisateurs` VALUES (10, 'Conducteur', 'Test', 'conducteur@test.com', '[password_hash]', '0611111111', '1 rue du Conducteur', '1985-05-05', 'conducteur', '2025-04-23 23:32:09', 1, 'Conducteur');

-- Passager Test
INSERT INTO `utilisateurs` VALUES (11, 'Passager', 'Test', 'passager@test.com', '[password_hash]', '0622222222', '1 rue du Passager', '1990-10-10', 'passager', '2025-04-23 23:32:09', 1, 'Passager');
```

### 2. Trajets d'Exemple
```sql
INSERT INTO `trajets` VALUES 
(1, 'Paris', 'Lyon', '12 rue de la Paix, 75002 Paris', '34 avenue des Champs, 69002 Lyon', '2025-05-10 08:00:00', '08:00:00', '12:00:00', 4, 25.00, 'Trajet direct autoroute, pause possible à mi-chemin', 1, 0, 0, 'planifié', 1),
(2, 'Lyon', 'Marseille', '34 avenue des Champs, 69002 Lyon', '5 boulevard Liberté, 13001 Marseille', '2025-05-15 07:30:00', '07:30:00', '10:30:00', 3, 20.00, 'Départ tôt le matin, arrivée pour déjeuner', 1, 0, 1, 'planifié', 2);
```

### 3. Réservations Test
```sql
INSERT INTO `reservations` VALUES
(1, 17, 1, 1, 'confirmée', '2025-04-23 22:25:22', '2025-04-20 14:30:00', '12 rue de la Paix, Paris', NULL),
(2, 3, 2, 1, 'confirmée', '2025-04-23 22:25:22', '2025-04-21 09:15:00', '34 avenue des Champs, Lyon', NULL);
```

### 4. Messages Test
```sql
INSERT INTO `messages` VALUES
(1, 3, 1, 1, 'Bonjour, je suis intéressé par votre trajet Paris-Lyon. Où exactement partez-vous ?', '2025-04-23 22:25:22', 1, 0),
(2, 1, 3, 1, 'Je pars de chez moi au 12 rue de la Paix. Ça vous convient ?', '2025-04-23 22:25:22', 1, 0);
```

### 5. Avis Test
```sql
INSERT INTO `avis` VALUES
(1, 3, 1, 1, 'Très bon conducteur, ponctuel et voiture confortable', 5, '2025-04-23 22:25:22', 'publié', 0),
(2, 1, 2, 2, 'Trajet agréable mais un peu de retard au départ', 4, '2025-04-23 22:25:22', 'publié', 0);
```

### 6. Voitures Test
```sql
INSERT INTO `voiture` VALUES
(1, 'Clio', 'opel', 'noeifjoefn', 'essence', 'feef', '2025-05-13', 4, '', '', 0),
(4, 'corsa D', 'Renault', '12-12-12', 'electrique', 'feef', '2025-04-30', 4, '', 'deccc', 0),
(6, 'SIVIC', 'HONDA', '14725-5-77', 'hybride', 'ULTRA VIOLET', '2025-04-28', 3, 'WWW.LOOOOL JE TAI EU', 'TU ES OU LA ', 0);
```

## Relations entre les Tables

1. **Trajets et Utilisateurs**
   - Chaque trajet est lié à un utilisateur (conducteur)
   - Contrainte de clé étrangère sur `utilisateur_id`
   - Un trajet peut être associé à une voiture spécifique

2. **Réservations**
   - Liées aux trajets et aux utilisateurs
   - Permet de suivre qui réserve quel trajet
   - Stocke le nombre de places réservées et le statut

3. **Messages**
   - Liés aux utilisateurs (expéditeur et destinataire)
   - Peuvent être liés à un trajet spécifique
   - Système de suivi de lecture des messages

4. **Avis**
   - Liés aux utilisateurs (auteur et destinataire)
   - Liés au trajet concerné
   - Système de notation de 1 à 5 étoiles
   - Système de modération des avis

5. **Voitures**
   - Liées à un utilisateur (propriétaire)
   - Utilisées dans les trajets
   - Informations détaillées sur le véhicule
   - Contrainte d'unicité sur l'immatriculation

## Utilisation des Fixtures

### Installation des Données

1. Importez le fichier SQL complet :
```bash
mysql -u [username] -p covoiturage_db < covoiturage_db.sql
```

### Réinitialisation de la Base

Pour réinitialiser la base avec les données de test :
1. Supprimez la base existante :
```sql
DROP DATABASE IF EXISTS covoiturage_db;
```

2. Recréez la base :
```sql
CREATE DATABASE covoiturage_db;
USE covoiturage_db;
```

3. Importez les fixtures :
```sql
SOURCE chemin/vers/covoiturage_db.sql;
```

### Cas de Test

Les données fournies permettent de tester :

1. **Gestion des Utilisateurs**
   - Connexion avec différents rôles
   - Gestion des profils

2. **Gestion des Trajets**
   - Création et recherche de trajets
   - Filtrage par ville/date

3. **Système de Réservation**
   - Process complet de réservation
   - Différents statuts de réservation

4. **Messagerie**
   - Communication entre utilisateurs
   - Notifications

5. **Système d'Avis**
   - Notation des trajets
   - Modération des commentaires

## Notes Importantes

1. **Sécurité**
   - Les mots de passe des comptes de test sont hashés
   - Ne pas utiliser en production

2. **Dates**
   - Les dates sont configurées pour 2025
   - À adapter selon les besoins

3. **Volumes de Test**
   - Suffisant pour tester les fonctionnalités
   - Peut être étendu selon les besoins

---
*Document généré le 21 août 2025*
