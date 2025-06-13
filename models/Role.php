<?php
class Role {
    private $conn;
    private $table = 'role';

    public $role_id;
    public $libelle;
    public $description;

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
        $query = 'SELECT * FROM ' . $this->table . ' WHERE role_id = ? LIMIT 1';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->role_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->libelle = $row['libelle'];
            return true;
        }
        return false;
    }
    public function read_by_user() {
        $query = 'SELECT u.* FROM utilisateurs u 
                  JOIN possede p ON u.utilisateur_id = p.utilisateur_id 
                  WHERE p.role_id = :role_id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':role_id', $this->role_id);
        $stmt->execute();
        return $stmt;
    }

    public function create() {
        $query = 'INSERT INTO ' . $this->table . ' SET libelle = :libelle';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':libelle', $this->libelle);
        $stmt->bindParam(':description', $this->description);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function update() {
        $query = 'UPDATE ' . $this->table . ' SET libelle = :libelle WHERE role_id = :role_id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':libelle', $this->libelle);
        $stmt->bindParam(':role_id', $this->role_id);
        $stmt->bindParam(':description', $this->description);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = 'DELETE FROM ' . $this->table . ' WHERE role_id = :role_id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':role_id', $this->role_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>