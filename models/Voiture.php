<?php
class Voiture {
    private $conn;
    private $table = 'voiture';

    public $voiture_id;
    public $marque;
    public $modele;
    public $immatriculation;
    public $energie;
    public $couleur;
    public $date_premiere_immatriculation;
    public $nombre_places;
    public $photo_url;
    public $description;
    public $utilisateur_id;

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
        $query = 'SELECT * FROM ' . $this->table . ' WHERE voiture_id = ? LIMIT 1';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->voiture_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        //Vérification de la recupération des données
        if(empty($row)) {
        return  false;
        } else {
        return ($row);
        }
    }

    public function read_marque() {
        $query = 'SELECT m.* FROM marque m 
                  JOIN detient d ON m.marque_id = d.marque_id 
                  WHERE d.voiture_id = :voiture_id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':voiture_id', $this->voiture_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function read_by_user() {
    $query = 'SELECT * FROM ' . $this->table . ' WHERE utilisateur_id = ?';
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(1, $this->utilisateur_id);
    
    if($stmt->execute()) {
        return $stmt; // Return PDOStatement, not array
    }
    
    return false;
}
    

    public function create() {
    $query = 'INSERT INTO ' . $this->table . ' 
        SET modele = :modele, immatriculation = :immatriculation, marque = :marque,
        energie = :energie, couleur = :couleur, 
        date_premiere_immatriculation = :date_premiere_immatriculation, photo_url = :photo_url, 
        description = :description, nombre_places = :nombre_places, utilisateur_id = :utilisateur_id';

    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(':modele', $this->modele);
    $stmt->bindParam(':marque', $this->marque);
    $stmt->bindParam(':immatriculation', $this->immatriculation);
    $stmt->bindParam(':energie', $this->energie);
    $stmt->bindParam(':couleur', $this->couleur);
    $stmt->bindParam(':date_premiere_immatriculation', $this->date_premiere_immatriculation);
    $stmt->bindParam(':photo_url', $this->photo_url);
    $stmt->bindParam(':description', $this->description);
    $stmt->bindParam(':nombre_places', $this->nombre_places);
    $stmt->bindParam(':utilisateur_id', $this->utilisateur_id);

   // Exécution avec gestion d'erreur améliorée
    if ($stmt->execute()) {
        $this->voiture_id = $this->conn->lastInsertId(); // Récupère l'ID généré
        return true;
    } else {
        error_log("Erreur SQL: " . print_r($stmt->errorInfo(), true));
        return false;
    }
}

    public function update() {
        $query = 'UPDATE ' . $this->table . ' 
            SET modele = :modele, immatriculation = :immatriculation, 
            energie = :energie, couleur = :couleur, 
            date_premiere_immatriculation = :date_premiere_immatriculation,
            photo_url = :photo_url, description = :description,
            nombre_places = :nombre_places
            WHERE voiture_id = :voiture_id';

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':modele', $this->modele);
        $stmt->bindParam(':immatriculation', $this->immatriculation);
        $stmt->bindParam(':energie', $this->energie);
        $stmt->bindParam(':couleur', $this->couleur);
        $stmt->bindParam(':date_premiere_immatriculation', $this->date_premiere_immatriculation);
        $stmt->bindParam(':voiture_id', $this->voiture_id);
        $stmt->bindParam(':photo_url', $this->photo_url);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':nombre_places', $this->nombre_places);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
    $query = 'DELETE FROM ' . $this->table . ' WHERE voiture_id = :voiture_id';
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':voiture_id', $this->voiture_id, PDO::PARAM_INT);
    
    try {
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Erreur suppression: " . $e->getMessage());
        return false;
    }
}
}
?>