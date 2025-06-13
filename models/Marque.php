<?php
class Marque {
    private $conn;
    private $table = 'marque';


    public $marque_id;
    public $libelle;
    public $date_creation;
    public $pays_origine;
    public $logo_url;

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
        $query = 'SELECT * FROM ' . $this->table . ' WHERE marque_id = ? LIMIT 1';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->marque_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->libelle = $row['libelle'];
            return true;
        }
        return false;
    }

    public function create() {
        $query = 'INSERT INTO ' . $this->table . ' SET libelle = :libelle';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':libelle', $this->libelle);
        $stmt->bindParam(':date_creation', $this->date_creation);
        $stmt->bindParam(':pays_origine', $this->pays_origine);
        $stmt->bindParam(':logo_url', $this->logo_url);
        $stmt->bindParam(':marque_id', $this->marque_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
    public function read_voitures() {
        $query = 'SELECT v.* FROM voiture v 
                  JOIN detient d ON v.voiture_id = d.voiture_id 
                  WHERE d.marque_id = :marque_id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':marque_id', $this->marque_id);

        $stmt->execute();
        return $stmt;
    }

    public function update() {
        $query = 'UPDATE ' . $this->table . ' SET libelle = :libelle WHERE marque_id = :marque_id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':libelle', $this->libelle);
        $stmt->bindParam(':marque_id', $this->marque_id);
        $stmt->bindParam(':date_creation', $this->date_creation);
        $stmt->bindParam(':pays_origine', $this->pays_origine);
        $stmt->bindParam(':logo_url', $this->logo_url);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = 'DELETE FROM ' . $this->table . ' WHERE marque_id = :marque_id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':marque_id', $this->marque_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>