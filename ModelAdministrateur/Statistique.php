<?php
class Statistique {
    // Statistiques sur les voitures (total, par marque, par statut si dispo)
    public function getStatsVoitures() {
        $stats = [];
        // Nombre total de voitures
        $sql1 = "SELECT COUNT(*) as total_voitures FROM voiture";
        $stmt1 = $this->conn->prepare($sql1);
        $stmt1->execute();
        $stats['total_voitures'] = $stmt1->fetch(PDO::FETCH_ASSOC)['total_voitures'];

        // Nombre de voitures par marque
        $sql2 = "SELECT marque, COUNT(*) as nb FROM voiture GROUP BY marque ORDER BY nb DESC";
        $stmt2 = $this->conn->prepare($sql2);
        $stmt2->execute();
        $stats['voitures_par_marque'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        // Si un champ 'statut' existe dans voiture, décommentez ci-dessous
        // $sql3 = "SELECT statut, COUNT(*) as nb FROM voiture GROUP BY statut";
        // $stmt3 = $this->conn->prepare($sql3);
        // $stmt3->execute();
        // $stats['voitures_par_statut'] = $stmt3->fetchAll(PDO::FETCH_ASSOC);

        return $stats;
    }

    // Statistiques sur les réservations (total, par statut, par jour)
    public function getStatsReservations() {
        $stats = [];
        // Nombre total de réservations
        $sql1 = "SELECT COUNT(*) as total_reservations FROM reservations";
        $stmt1 = $this->conn->prepare($sql1);
        $stmt1->execute();
        $stats['total_reservations'] = $stmt1->fetch(PDO::FETCH_ASSOC)['total_reservations'];

        // Nombre de réservations par statut
        $sql2 = "SELECT statut, COUNT(*) as nb FROM reservations GROUP BY statut";
        $stmt2 = $this->conn->prepare($sql2);
        $stmt2->execute();
        $stats['reservations_par_statut'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        // Nombre de réservations par jour (30 derniers jours)
        $sql3 = "SELECT DATE(date_reservation) as jour, COUNT(*) as nb FROM reservations GROUP BY jour ORDER BY jour DESC LIMIT 30";
        $stmt3 = $this->conn->prepare($sql3);
        $stmt3->execute();
        $stats['reservations_par_jour'] = $stmt3->fetchAll(PDO::FETCH_ASSOC);

        return $stats;
    }
    private $conn;
    public function __construct($db) {
        $this->conn = $db;
    }
    // Exemple : statistiques globales
    public function getStatsGlobales() {
        $stats = [];
        $sql1 = "SELECT DATE(date_depart) as jour, COUNT(*) as nb_covoiturages FROM trajets GROUP BY jour ORDER BY jour DESC LIMIT 30";
        $stmt1 = $this->conn->prepare($sql1);
        $stmt1->execute();
        $stats['covoiturages_par_jour'] = $stmt1->fetchAll(PDO::FETCH_ASSOC);
        $sql2 = "SELECT DATE(date_operation) as jour, SUM(montant) as credits_gagnes FROM credits WHERE type_operation = 'ajout' GROUP BY jour ORDER BY jour DESC LIMIT 30";
        $stmt2 = $this->conn->prepare($sql2);
        $stmt2->execute();
        $stats['credits_gagnes_par_jour'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        return $stats;
    }
    // Nombre total d'utilisateurs
    public function getNbUtilisateurs() {
        $sql = "SELECT COUNT(*) as total_utilisateurs FROM utilisateurs WHERE role = 'utilisateur'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Nombre total d'employés
    public function getNbEmployes() {
        $sql = "SELECT COUNT(*) as total_employes FROM utilisateurs WHERE role = 'Employe'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Nombre de compte par jours (utilisateurs et employés)
    public function getNbComptesParJour() {
        $sql = "SELECT DATE(date_inscription) as jour, COUNT(*) as nb FROM utilisateurs GROUP BY jour ORDER BY jour DESC LIMIT 30";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Statistiques sur les trajets (en cours, terminés, annulés)
    public function getStatsTrajets() {
        $sql = "SELECT statut, COUNT(*) as nb FROM trajets GROUP BY statut";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Statistiques sur les avis (en attente, validés, refusés)
    public function getStatsAvis() {
        $sql = "SELECT statut, COUNT(*) as nb FROM avis GROUP BY statut";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Nombre de comptes suspendus (utilisateurs et employés)
    public function getNbSuspendus() {
        $sql = "SELECT COUNT(*) as total_suspendus FROM utilisateurs WHERE suspendu = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Statistiques sur les crédits gagnés par jour (déjà inclus dans getStatsGlobales, mais version dédiée)
    public function getStatsCredits() {
        $sql = "SELECT DATE(date_action) as jour, SUM(montant) as credits_gagnes FROM historique_action WHERE type_action = 'gain' GROUP BY jour ORDER BY jour DESC LIMIT 30";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
