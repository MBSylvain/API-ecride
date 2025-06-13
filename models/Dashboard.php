<?php
class Dashboard {
    private $conn;
    private $utilisateur_id;

    public function __construct($db, $utilisateur_id) {
        $this->conn = $db;
        $this->utilisateur_id = $utilisateur_id;
    }

    // Récupère TOUTES les données pour le dashboard
    public function getData() {
        $data = [
            'utilisateur' => $this->getUtilisateur(),
            'trajets' => $this->getTrajets(),
            'reservations' => $this->getReservations(),
            'voitures' => $this->getVoitures(),
            'stats' => $this->getStats() // Optionnel : agrégations
        ];

        return $data;
    }

    private function getUtilisateur() {
        $query = 'SELECT nom, prenom, email, telephone, date_inscription, adresse, role  FROM utilisateurs WHERE utilisateur_id = ?';
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->utilisateur_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getTrajets() {
        $query = 'SELECT * FROM trajets WHERE utilisateur_id = ? ORDER BY date_depart DESC LIMIT 5';
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->utilisateur_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getReservations() {
        $query = 'SELECT r.*, t.ville_depart, t.ville_arrivee 
                  FROM reservations r
                  JOIN trajets t ON r.trajet_id = t.trajet_id
                  WHERE r.utilisateur_id = ?
                  ORDER BY r.date_reservation DESC
                  LIMIT 5';
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->utilisateur_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Ajouter une gestion d'erreurs pour toutes les méthodes
    private function executeQuery($query, $params = []) {
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Erreur SQL: " . $e->getMessage());
            return false;
        }
    }

    private function getVoitures() {
        $query = 'SELECT * FROM voiture WHERE utilisateur_id = ?';
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->utilisateur_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Optionnel : statistiques agrégées
    private function getStats() {
        $query = 'SELECT 
            COUNT(DISTINCT t.trajet_id) AS total_trajets,
            COUNT(DISTINCT r.reservation_id) AS total_reservations
            FROM utilisateurs u
            LEFT JOIN trajets t ON u.utilisateur_id = t.utilisateur_id
            LEFT JOIN reservations r ON u.utilisateur_id = r.utilisateur_id
            WHERE u.utilisateur_id = ?';
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->utilisateur_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>