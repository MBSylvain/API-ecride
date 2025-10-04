-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : ven. 03 oct. 2025 à 21:46
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `covoiturage_db`
--

-- --------------------------------------------------------

--
-- Structure de la table `avis`
--

CREATE TABLE `avis` (
  `avis_id` int(11) NOT NULL,
  `auteur_id` int(11) NOT NULL,
  `destinataire_id` int(11) NOT NULL,
  `trajet_id` int(11) NOT NULL,
  `commentaire` text NOT NULL,
  `note` int(11) NOT NULL CHECK (`note` between 1 and 5),
  `date_creation` datetime DEFAULT current_timestamp(),
  `statut` enum('publié','modéré','signalé') DEFAULT 'publié',
  `utilisateur_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `avis`
--

INSERT INTO `avis` (`avis_id`, `auteur_id`, `destinataire_id`, `trajet_id`, `commentaire`, `note`, `date_creation`, `statut`, `utilisateur_id`) VALUES
(1, 3, 1, 1, 'Très bon conducteur, ponctuel et voiture confortable', 5, '2025-04-23 22:25:22', 'publié', 0),
(2, 1, 2, 2, 'Trajet agréable mais un peu de retard au départ', 4, '2025-04-23 22:25:22', 'publié', 0),
(3, 2, 4, 4, 'Excellent trajet, je recommande ce conducteur', 5, '2025-04-23 22:25:22', 'publié', 0),
(4, 11, 10, 8, 'Très bon conducteur, ponctuel et sympathique', 5, '2025-04-23 23:32:09', 'publié', 0);

-- --------------------------------------------------------

--
-- Structure de la table `credits`
--

CREATE TABLE `credits` (
  `credit_id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `montant` int(11) NOT NULL,
  `type_operation` enum('ajout','retrait','offert','utilisation','annulation') NOT NULL,
  `date_operation` datetime DEFAULT current_timestamp(),
  `commentaire` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `historique_actions`
--

CREATE TABLE `historique_actions` (
  `action_id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `type_action` enum('annulation','suspension','validation','modification','connexion') NOT NULL,
  `cible_id` int(11) DEFAULT NULL,
  `cible_table` varchar(50) DEFAULT NULL,
  `date_action` datetime DEFAULT current_timestamp(),
  `commentaire` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `expediteur_id` int(11) NOT NULL,
  `destinataire_id` int(11) NOT NULL,
  `trajet_id` int(11) DEFAULT NULL,
  `contenu` text NOT NULL,
  `date_envoi` datetime DEFAULT current_timestamp(),
  `lu` tinyint(1) DEFAULT 0,
  `utilisateur_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `messages`
--

INSERT INTO `messages` (`message_id`, `expediteur_id`, `destinataire_id`, `trajet_id`, `contenu`, `date_envoi`, `lu`, `utilisateur_id`) VALUES
(1, 3, 1, 1, 'Bonjour, je suis intéressé par votre trajet Paris-Lyon. Où exactement partez-vous ?', '2025-04-23 22:25:22', 1, 0),
(2, 1, 3, 1, 'Je pars de chez moi au 12 rue de la Paix. Ça vous convient ?', '2025-04-23 22:25:22', 1, 0),
(3, 3, 1, 1, 'Parfait, c\'est à côté de mon travail. Je réserve !', '2025-04-23 22:25:22', 1, 0),
(4, 2, 3, NULL, 'Salut Pierre, tu fais un trajet vers Nice bientôt ?', '2025-04-23 22:25:22', 0, 0);

-- --------------------------------------------------------

--
-- Structure de la table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `type` enum('mail','alerte','sms') NOT NULL,
  `contenu` text NOT NULL,
  `date_envoi` datetime DEFAULT current_timestamp(),
  `statut` enum('envoyé','échoué','lu') DEFAULT 'envoyé'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `preferences_conducteur`
--

CREATE TABLE `preferences_conducteur` (
  `preference_id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `valeur` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `reservations`
--

CREATE TABLE `reservations` (
  `reservation_id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `trajet_id` int(11) NOT NULL,
  `nombre_places_reservees` int(11) NOT NULL,
  `statut` enum('confirmée','en_attente','annulée','refusée') DEFAULT 'en_attente',
  `date_reservation` datetime DEFAULT current_timestamp(),
  `date_confirmation` datetime DEFAULT NULL,
  `point_rdv` text DEFAULT NULL,
  `commentaire` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `reservations`
--

INSERT INTO `reservations` (`reservation_id`, `utilisateur_id`, `trajet_id`, `nombre_places_reservees`, `statut`, `date_reservation`, `date_confirmation`, `point_rdv`, `commentaire`) VALUES
(1, 17, 1, 1, 'confirmée', '2025-04-23 22:25:22', '2025-04-20 14:30:00', '12 rue de la Paix, Paris', NULL),
(2, 3, 2, 1, 'confirmée', '2025-04-23 22:25:22', '2025-04-21 09:15:00', '34 avenue des Champs, Lyon', NULL),
(3, 1, 2, 1, 'en_attente', '2025-04-23 22:25:22', NULL, '34 avenue des Champs, Lyon', NULL),
(4, 2, 4, 2, 'confirmée', '2025-04-23 22:25:22', '2025-04-22 11:20:00', '5 boulevard Liberté, Marseille', NULL),
(5, 11, 8, 1, 'confirmée', '2025-04-23 23:32:09', '2025-04-23 23:32:09', 'Gare de Lyon, devant le café', 'Premier trajet test'),
(6, 19, 1, 1, 'annulée', '2025-06-21 19:25:20', NULL, '', ''),
(7, 19, 9, 1, 'en_attente', '2025-06-22 10:52:40', NULL, '', ''),
(8, 19, 3, 1, 'en_attente', '2025-06-22 16:13:04', NULL, '', ''),
(9, 19, 8, 1, 'annulée', '2025-06-22 16:17:57', NULL, '', ''),
(10, 20, 3, 1, 'en_attente', '2025-07-01 22:50:09', NULL, '', ''),
(11, 20, 10, 1, 'en_attente', '2025-07-04 22:15:38', NULL, '', ''),
(12, 20, 10, 1, 'en_attente', '2025-07-08 19:24:55', NULL, '', 'blabla car cest de la merde lol');

-- --------------------------------------------------------

--
-- Structure de la table `trajets`
--

CREATE TABLE `trajets` (
  `trajet_id` int(11) NOT NULL,
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
  `statut` enum('planifié','en_cours','terminé','annulé') DEFAULT 'planifié',
  `utilisateur_id` int(11) NOT NULL,
  `voiture_id` int(11) DEFAULT NULL,
  `date_creation` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `trajets`
--

INSERT INTO `trajets` (`trajet_id`, `ville_depart`, `ville_arrivee`, `adresse_depart`, `adresse_arrivee`, `date_depart`, `heure_depart`, `heure_arrivee`, `nombre_places`, `prix`, `description`, `bagages_autorises`, `fumeur_autorise`, `animaux_autorises`, `statut`, `utilisateur_id`, `voiture_id`, `date_creation`) VALUES
(1, 'Paris', 'Lyon', '12 rue de la Paix, 75002 Paris', '34 avenue des Champs, 69002 Lyon', '2025-05-10 08:00:00', '08:00:00', '12:00:00', 4, 25.00, 'Trajet direct autoroute, pause possible à mi-chemin', 1, 0, 0, 'planifié', 1, NULL, '2025-04-23 22:25:22'),
(2, 'Lyon', 'Marseille', '34 avenue des Champs, 69002 Lyon', '5 boulevard Liberté, 13001 Marseille', '2025-05-15 07:30:00', '07:30:00', '10:30:00', 3, 20.00, 'Départ tôt le matin, arrivée pour déjeuner', 1, 0, 1, 'planifié', 2, NULL, '2025-04-23 22:25:22'),
(3, 'Paris', 'Bordeaux', 'Place de la Concorde, 75008 Paris', 'Place de la Bourse, 33000 Bordeaux', '2025-05-20 09:00:00', '09:00:00', '13:30:00', 6, 30.00, 'Grande voiture confortable pour long trajet', 1, 0, 0, 'planifié', 2, NULL, '2025-04-23 22:25:22'),
(4, 'Marseille', 'Paris', '5 boulevard Liberté, 13001 Marseille', '12 rue de la Paix, 75002 Paris', '2025-05-25 16:00:00', '16:00:00', '22:00:00', 4, 35.00, 'Retour en soirée avec pauses', 1, 0, 0, 'planifié', 4, NULL, '2025-04-23 22:25:22'),
(8, 'Paris', 'Lyon', 'Gare de Lyon', 'Place Bellecour', '2025-04-30 23:32:09', '08:00:00', '12:00:00', 3, 25.00, 'Trajet de test', 1, 0, 0, 'planifié', 10, NULL, '2025-04-23 23:32:09'),
(9, 'Chelles', 'Paris', 'rue de chelles', 'Rue de paris', '2025-05-23 00:00:00', '00:00:00', NULL, 3, 0.49, '', 0, 0, 0, 'planifié', 17, 6, '2025-05-11 19:27:50'),
(10, 'Chelles', 'new york city', '', '', '2024-05-16 18:29:00', '18:29:00', NULL, 4, 10.00, 'cdcdd', 0, 0, 0, 'planifié', 19, 10, '2025-06-17 23:28:48'),
(14, 'paris', 'lyon', '', '', '2025-09-30 20:00:00', '20:00:00', NULL, 6, 20.00, 'zszszs', 0, NULL, 0, 'planifié', 19, 10, '2025-09-26 06:45:46'),
(15, 'paris', 'lyon', '', '', '2025-10-03 20:00:00', '20:00:00', NULL, 6, 20.00, 'zszszs', 0, NULL, 0, 'planifié', 19, 10, '2025-09-26 06:46:42'),
(16, 'paris', 'lyon', '', '', '2025-10-03 20:00:00', '20:00:00', NULL, 6, 20.00, 'zszszs', 0, NULL, 0, 'planifié', 19, 10, '2025-09-26 06:47:45');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `utilisateur_id` int(11) NOT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`utilisateur_id`, `nom`, `prenom`, `email`, `mot_de_passe`, `telephone`, `adresse`, `date_naissance`, `pseudo`, `date_inscription`, `compte_actif`, `role`) VALUES
(1, 'Dupont', 'Jean', 'jean.dupont@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0612345678', '12 rue de la Paix, Paris', '1985-05-15', 'jdupont', '2025-04-23 22:25:22', 1, 'Conducteur'),
(2, 'Martin', 'Sophie', 'sophie.martin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0698765432', '34 avenue des Champs, Lyon', '1990-08-22', 'smartin', '2025-04-23 22:25:22', 1, 'Conducteur'),
(3, 'Durand', 'Pierre', 'pierre.durand@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0678912345', '5 boulevard Liberté, Marseille', '1988-03-10', 'pdurand', '2025-04-23 22:25:22', 1, 'Passager'),
(4, 'Admin', 'System', 'admin@covoiturage.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, 'admin', '2025-04-23 22:25:22', 1, 'Administrateur'),
(10, 'Conducteur', 'Test', 'conducteur@test.com', '$2y$10$U0pyFB/uPaHCV71oAj6R7uoJkpozbM3qbiZZ1chdy1bg843toO/qy', '0611111111', '1 rue du Conducteur', '1985-05-05', 'conducteur', '2025-04-23 23:32:09', 1, 'Conducteur'),
(11, 'Passager', 'Test', 'passager@test.com', '$2y$10$ibQIEazTjDfRni.UmjcFjubWqcxN27aBX2t7ALaKff.YSyyg9s88G', '0622222222', '1 rue du Passager', '1990-10-10', 'passager', '2025-04-23 23:32:09', 1, 'Passager'),
(19, 'sylvain011', 'mbemou', 'david@gmail.com', '$2y$10$fIVlHngAOC5rKmTaOGMH9OxriLYzEsyIRHQ4E7nluFOD9SrNCGsD6', '0143512386', '1654654684+4', NULL, 'cestbon', '2025-06-07 21:41:39', 1, 'Passager'),
(20, 'david213465', 'FUCK', 'www.20syls@yahoo.fr', '$2y$10$rzs4Vajh19E9h055R2fOsuCS0aNBf5s2HBAOlzQVUbqQyFP3XHPqG', '0143512386', '', '2025-09-30', 'cocorico', '2025-06-23 08:33:41', 1, 'Passager');

-- --------------------------------------------------------

--
-- Structure de la table `voiture`
--

CREATE TABLE `voiture` (
  `voiture_id` int(11) NOT NULL,
  `modele` varchar(50) NOT NULL,
  `marque` varchar(50) NOT NULL,
  `immatriculation` varchar(50) NOT NULL,
  `energie` varchar(50) NOT NULL,
  `couleur` varchar(50) DEFAULT NULL,
  `date_premiere_immatriculation` date NOT NULL,
  `nombre_places` int(11) NOT NULL DEFAULT 5,
  `photo_url` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `utilisateur_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `voiture`
--

INSERT INTO `voiture` (`voiture_id`, `modele`, `marque`, `immatriculation`, `energie`, `couleur`, `date_premiere_immatriculation`, `nombre_places`, `photo_url`, `description`, `utilisateur_id`) VALUES
(1, 'Clio', 'opel', 'noeifjoefn', 'essence', 'feef', '2025-05-13', 4, '', '', 19),
(4, 'corsa D', 'Renault', '12-12-12', 'electrique', 'feef', '2025-04-30', 4, '', 'deccc', 19),
(6, 'SIVIC', 'HONDA', '14725-5-77', 'hybride', 'ULTRA VIOLET', '2025-04-28', 3, 'WWW.LOOOOL JE TAI EU', 'TU ES OU LA ', 17),
(10, 'opel corsa D1.2', 'opel', '123-456-25', 'Diesel', 'orange', '2022-12-14', 3, NULL, NULL, 19),
(11, 'opel corsa D1.20', 'opel', '0101010101', 'Diesel', 'orange', '2222-01-01', 4, NULL, NULL, 20);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `avis`
--
ALTER TABLE `avis`
  ADD PRIMARY KEY (`avis_id`),
  ADD KEY `auteur_id` (`auteur_id`),
  ADD KEY `destinataire_id` (`destinataire_id`),
  ADD KEY `trajet_id` (`trajet_id`);

--
-- Index pour la table `credits`
--
ALTER TABLE `credits`
  ADD PRIMARY KEY (`credit_id`),
  ADD KEY `utilisateur_id` (`utilisateur_id`);

--
-- Index pour la table `historique_actions`
--
ALTER TABLE `historique_actions`
  ADD PRIMARY KEY (`action_id`),
  ADD KEY `utilisateur_id` (`utilisateur_id`);

--
-- Index pour la table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `expediteur_id` (`expediteur_id`),
  ADD KEY `destinataire_id` (`destinataire_id`),
  ADD KEY `trajet_id` (`trajet_id`);

--
-- Index pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `utilisateur_id` (`utilisateur_id`);

--
-- Index pour la table `preferences_conducteur`
--
ALTER TABLE `preferences_conducteur`
  ADD PRIMARY KEY (`preference_id`),
  ADD KEY `utilisateur_id` (`utilisateur_id`);

--
-- Index pour la table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`reservation_id`),
  ADD KEY `utilisateur_id` (`utilisateur_id`),
  ADD KEY `trajet_id` (`trajet_id`);

--
-- Index pour la table `trajets`
--
ALTER TABLE `trajets`
  ADD PRIMARY KEY (`trajet_id`),
  ADD KEY `conducteur_id` (`utilisateur_id`),
  ADD KEY `voiture_id` (`voiture_id`);

--
-- Index pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`utilisateur_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `pseudo` (`pseudo`);

--
-- Index pour la table `voiture`
--
ALTER TABLE `voiture`
  ADD PRIMARY KEY (`voiture_id`),
  ADD UNIQUE KEY `immatriculation` (`immatriculation`),
  ADD KEY `voiture_ibfk_1` (`utilisateur_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `avis`
--
ALTER TABLE `avis`
  MODIFY `avis_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `credits`
--
ALTER TABLE `credits`
  MODIFY `credit_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `historique_actions`
--
ALTER TABLE `historique_actions`
  MODIFY `action_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `preferences_conducteur`
--
ALTER TABLE `preferences_conducteur`
  MODIFY `preference_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `reservation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pour la table `trajets`
--
ALTER TABLE `trajets`
  MODIFY `trajet_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `utilisateur_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT pour la table `voiture`
--
ALTER TABLE `voiture`
  MODIFY `voiture_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `avis`
--
ALTER TABLE `avis`
  ADD CONSTRAINT `avis_ibfk_1` FOREIGN KEY (`auteur_id`) REFERENCES `utilisateurs` (`utilisateur_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `avis_ibfk_2` FOREIGN KEY (`destinataire_id`) REFERENCES `utilisateurs` (`utilisateur_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `avis_ibfk_3` FOREIGN KEY (`trajet_id`) REFERENCES `trajets` (`trajet_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `credits`
--
ALTER TABLE `credits`
  ADD CONSTRAINT `credits_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`utilisateur_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `historique_actions`
--
ALTER TABLE `historique_actions`
  ADD CONSTRAINT `historique_actions_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`utilisateur_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`expediteur_id`) REFERENCES `utilisateurs` (`utilisateur_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`destinataire_id`) REFERENCES `utilisateurs` (`utilisateur_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_3` FOREIGN KEY (`trajet_id`) REFERENCES `trajets` (`trajet_id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`utilisateur_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `preferences_conducteur`
--
ALTER TABLE `preferences_conducteur`
  ADD CONSTRAINT `preferences_conducteur_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`utilisateur_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`utilisateur_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`trajet_id`) REFERENCES `trajets` (`trajet_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `trajets`
--
ALTER TABLE `trajets`
  ADD CONSTRAINT `trajets_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`utilisateur_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `trajets_ibfk_2` FOREIGN KEY (`voiture_id`) REFERENCES `voiture` (`voiture_id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `voiture`
--
ALTER TABLE `voiture`
  ADD CONSTRAINT `voiture_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`utilisateur_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
