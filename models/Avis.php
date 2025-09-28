<?php
class Avis {
    private $conn;
    private $table = 'Avis';

    public $avis_id;
    public $utilisateur_id;
    public $commentaire;
    public $note;
    public $statut;
    public $auteur_id;
    public $destinataire_id;
    public $trajet_id;
    public $date_creation;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read() {
        $query = 'SELECT * FROM ' . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function read_single() {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE avis_id = ? LIMIT 1';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->avis_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->utilisateur_id = $row['utilisateur_id'];
            $this->commentaire = $row['commentaire'];
            $this->note = $row['note'];
            $this->statut = $row['statut'];
            return true;
        }
        return false;
    }
    public function read_by_conducteur() {
        $query = 'SELECT * FROM avis WHERE utilisateur_id = ?';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->utilisateur_id);
        $stmt->execute();
        return $stmt;
    }

    public function read_by_user() {
        $query = 'SELECT * FROM avis WHERE auteur_id = ? OR destinataire_id = ? OR utilisateur_id = ?';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam( 1, $this->auteur_id);
        $stmt->bindParam( 2, $this->destinataire_id);
        $stmt->bindParam( 3, $this->utilisateur_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return json_encode($row);
    }
    
    
    public function read_by_trajet() {
        $query = 'SELECT * FROM avis WHERE trajet_id = ?';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->trajet_id);
        $stmt->execute();
        return $stmt;
    }

    public function create() {
        // Vérifiez si le destinataire existe
        $query = 'SELECT COUNT(*) FROM utilisateurs WHERE utilisateur_id = :destinataire_id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':destinataire_id', $this->destinataire_id);
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            throw new Exception('Le destinataire spécifié n\'existe pas.');
        }

        // Vérifiez si l'auteur existe
        $query = 'SELECT COUNT(*) FROM utilisateurs WHERE utilisateur_id = :auteur_id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':auteur_id', $this->auteur_id);
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            throw new Exception('L\'auteur spécifié n\'existe pas.');
        }

        // Vérifiez si le trajet existe
        $query = 'SELECT COUNT(*) FROM trajets WHERE trajet_id = :trajet_id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':trajet_id', $this->trajet_id);
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            throw new Exception('Le trajet spécifié n\'existe pas.');
        }

        // Requête d'insertion
        $query = 'INSERT INTO avis (auteur_id, destinataire_id, trajet_id, commentaire, note, statut, date_creation)
                  VALUES (:auteur_id, :destinataire_id, :trajet_id, :commentaire, :note, :statut, :date_creation)';
        $stmt = $this->conn->prepare($query);

        // Liaison des paramètres
        $stmt->bindParam(':auteur_id', $this->auteur_id);
        $stmt->bindParam(':destinataire_id', $this->destinataire_id);
        $stmt->bindParam(':trajet_id', $this->trajet_id);
        $stmt->bindParam(':commentaire', $this->commentaire);
        $stmt->bindParam(':note', $this->note);
        $stmt->bindParam(':statut', $this->statut);
        $stmt->bindParam(':date_creation', $this->date_creation);

        // Exécution de la requête
        if ($stmt->execute()) {
            return true;
        }

        // Gestion des erreurs
        error_log('Erreur SQL : ' . print_r($stmt->errorInfo(), true));
        return false;
    }

    public function read_by_auteur() {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE auteur_id = ?';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->auteur_id);
        $stmt->execute();
        return $stmt;
    }
    public function read_by_destinataire() {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE destinataire_id = ?';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->destinataire_id);
        $stmt->execute();
        return $stmt;
    }

    public function update() {
        $query = 'UPDATE ' . $this->table . ' 
            SET utilisateur_id = :utilisateur_id, commentaire = :commentaire, 
            note = :note, statut = :statut
            WHERE avis_id = :avis_id';

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':utilisateur_id', $this->utilisateur_id);
        $stmt->bindParam(':commentaire', $this->commentaire);
        $stmt->bindParam(':note', $this->note);
        $stmt->bindParam(':statut', $this->statut);
        $stmt->bindParam(':avis_id', $this->avis_id);
        $stmt->bindParam(':auteur_id', $this->auteur_id);
        $stmt->bindParam(':destinataire_id', $this->destinataire_id);
        $stmt->bindParam(':trajet_id', $this->trajet_id);
        $stmt->bindParam(':date_creation', $this->date_creation);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = 'DELETE FROM ' . $this->table . ' WHERE avis_id = :avis_id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':avis_id', $this->avis_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>