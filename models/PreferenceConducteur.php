<?php
// PreferenceConducteur.php
// Modèle pour la table preferences_conducteur
class PreferenceConducteur {
    public $preference_id;
    public $utilisateur_id;
    public $type;
    public $valeur;

    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Créer une préférence
    public function createPreference() {
        $query = "INSERT INTO preferences_conducteur (utilisateur_id, type, valeur) VALUES (:utilisateur_id, :type, :valeur)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':utilisateur_id', $this->utilisateur_id);
        $stmt->bindParam(':type', $this->type);
        $stmt->bindParam(':valeur', $this->valeur);
        return $stmt->execute();
    }

    // Récupérer les préférences d'un conducteur
    public function getPreferencesByUser($utilisateur_id) {
        $query = "SELECT * FROM preferences_conducteur WHERE utilisateur_id = :utilisateur_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':utilisateur_id', $utilisateur_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer une préférence par son id
    public function getPreference($preference_id) {
        $query = "SELECT * FROM preferences_conducteur WHERE preference_id = :preference_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':preference_id', $preference_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Mettre à jour une préférence
    public function updatePreference($preference_id, $type, $valeur) {
        $query = "UPDATE preferences_conducteur SET type = :type, valeur = :valeur WHERE preference_id = :preference_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':valeur', $valeur);
        $stmt->bindParam(':preference_id', $preference_id);
        return $stmt->execute();
    }

    // Supprimer une préférence
    public function deletePreference($preference_id) {
        $query = "DELETE FROM preferences_conducteur WHERE preference_id = :preference_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':preference_id', $preference_id);
        return $stmt->execute();
    }
}
?>