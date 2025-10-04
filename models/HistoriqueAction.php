<?php
// HistoriqueAction.php
// Modèle pour la table historique_actions
class HistoriqueAction {
    public $action_id;
    public $utilisateur_id;
    public $type_action;
    public $cible_id;
    public $cible_table;
    public $date_action;
    public $commentaire;

    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Créer une action
    public function createAction() {
        $query = "INSERT INTO historique_actions (utilisateur_id, type_action, cible_id, cible_table, commentaire) VALUES (:utilisateur_id, :type_action, :cible_id, :cible_table, :commentaire)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':utilisateur_id', $this->utilisateur_id);
        $stmt->bindParam(':type_action', $this->type_action);
        $stmt->bindParam(':cible_id', $this->cible_id);
        $stmt->bindParam(':cible_table', $this->cible_table);
        $stmt->bindParam(':commentaire', $this->commentaire);
        return $stmt->execute();
    }

    // Récupérer l'historique d'un utilisateur
    public function getActionsByUser($utilisateur_id) {
        $query = "SELECT * FROM historique_actions WHERE utilisateur_id = :utilisateur_id ORDER BY date_action DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':utilisateur_id', $utilisateur_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer une action par son id
    public function getAction($action_id) {
        $query = "SELECT * FROM historique_actions WHERE action_id = :action_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':action_id', $action_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Récupérer l'historique par type d'action
    public function getActionsByType($type_action) {
        $query = "SELECT * FROM historique_actions WHERE type_action = :type_action ORDER BY date_action DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':type_action', $type_action);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Supprimer une action
    public function deleteAction($action_id) {
        $query = "DELETE FROM historique_actions WHERE action_id = :action_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':action_id', $action_id);
        return $stmt->execute();
    }
}
?>