<?php
// ModelAdministrateur/Signalement.php
class Signalement {
    private $conn;
    private $table = 'signalement';

    public $id;
    public $trajet_id;
    public $utilisateur_id;
    public $date_signalement;
    public $motif;
    public $description;
    public $statut;
    public $employe_id;
    public $date_traitement;
    public $action_effectuee;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Créer un signalement
    public function create($data) {
        $query = 'INSERT INTO ' . $this->table . ' (trajet_id, utilisateur_id, motif, description, statut) VALUES (:trajet_id, :utilisateur_id, :motif, :description, :statut)';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':trajet_id', $data['trajet_id']);
        $stmt->bindParam(':utilisateur_id', $data['utilisateur_id']);
        $stmt->bindParam(':motif', $data['motif']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':statut', $data['statut']);
        return $stmt->execute();
    }

    // Lire tous les signalements
    public function readAll() {
        $query = 'SELECT * FROM ' . $this->table . ' ORDER BY date_signalement DESC';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return  $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lire un signalement par ID
    public function readOne($id) {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE id = ?';
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Mettre à jour le statut et l'action d'un signalement
    public function update($id, $data) {
        $query = 'UPDATE ' . $this->table . ' SET statut = :statut, employe_id = :employe_id, date_traitement = NOW(), action_effectuee = :action_effectuee WHERE id = :id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':statut', $data['statut']);
        $stmt->bindParam(':employe_id', $data['employe_id']);
        $stmt->bindParam(':action_effectuee', $data['action_effectuee']);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Supprimer un signalement
    public function delete($id) {
        $query = 'DELETE FROM ' . $this->table . ' WHERE id = ?';
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }
}
