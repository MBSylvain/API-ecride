<?php
class Trajet {
    private $conn;
    public $trajet_id;
    public function __construct($db) {
        $this->conn = $db;
    }
    // Récupérer tous les trajets (admin)
    public function readAll() {
        $query = "SELECT * FROM trajets";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Récupérer les trajets problématiques (exemple : statut = 'probleme' ou champ dédié)
    public function readProblemes() {
        $query = "SELECT * FROM trajets WHERE statut = 'probleme'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Supprimer un trajet (admin)
    public function delete() {
        $query = "DELETE FROM trajets WHERE trajet_id = :trajet_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':trajet_id', $this->trajet_id);
        return $stmt->execute();
    }
}
