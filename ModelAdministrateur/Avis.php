<?php
class Avis {
    private $conn;
    public $avis_id;
    public $statut;
    public function __construct($db) {
        $this->conn = $db;
    }
    // Récupérer tous les avis (admin)
    public function readAll() {
        $query = "SELECT * FROM avis";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Récupérer les avis par statut (ex: en_attente, valide, refuse)
    public function readByStatut($statut) {
        $query = "SELECT * FROM avis WHERE statut = :statut";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':statut', $statut);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Valider ou refuser un avis (admin)
    public function updateStatut() {
        $query = "UPDATE avis SET statut = :statut WHERE avis_id = :avis_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':statut', $this->statut);
        $stmt->bindValue(':avis_id', $this->avis_id);
        return $stmt->execute();
    }
    // Supprimer un avis (admin)
    public function delete() {
        $query = "DELETE FROM avis WHERE avis_id = :avis_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':avis_id', $this->avis_id);
        return $stmt->execute();
    }
}
