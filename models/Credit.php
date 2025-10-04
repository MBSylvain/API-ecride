<?php
// Credit.php
// Modèle pour la table credits
class Credit {
    public $credit_id;
    public $utilisateur_id;
    public $montant;
    public $type_operation;
    public $date_operation;
    public $commentaire;

    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Créer un mouvement de crédit
    public function createCredit() {
        $query = "INSERT INTO credits (utilisateur_id, montant, type_operation, commentaire) VALUES (:utilisateur_id, :montant, :type_operation, :commentaire)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':utilisateur_id', $this->utilisateur_id);
        $stmt->bindParam(':montant', $this->montant);
        $stmt->bindParam(':type_operation', $this->type_operation);
        $stmt->bindParam(':commentaire', $this->commentaire);
        return $stmt->execute();
    }

    // Récupérer l'historique des crédits d'un utilisateur
    public function getCreditsByUser($utilisateur_id) {
        $query = "SELECT * FROM credits WHERE utilisateur_id = :utilisateur_id ORDER BY date_operation DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':utilisateur_id', $utilisateur_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer un mouvement de crédit par son id
    public function getCredit($credit_id) {
        $query = "SELECT * FROM credits WHERE credit_id = :credit_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':credit_id', $credit_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Mettre à jour un mouvement de crédit
    public function updateCredit($credit_id, $montant, $type_operation, $commentaire) {
        $query = "UPDATE credits SET montant = :montant, type_operation = :type_operation, commentaire = :commentaire WHERE credit_id = :credit_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':montant', $montant);
        $stmt->bindParam(':type_operation', $type_operation);
        $stmt->bindParam(':commentaire', $commentaire);
        $stmt->bindParam(':credit_id', $credit_id);
        return $stmt->execute();
    }

    // Supprimer un mouvement de crédit
    public function deleteCredit($credit_id) {
        $query = "DELETE FROM credits WHERE credit_id = :credit_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':credit_id', $credit_id);
        return $stmt->execute();
    }
}
?>