    
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

    // Vérifie si une immatriculation existe déjà
    public function immatriculationExists($immatriculation) {
        $query = "SELECT voiture_id FROM voiture WHERE immatriculation = :immatriculation";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(":immatriculation", $immatriculation);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

public function read_single() {
    $query = 'SELECT * FROM ' . $this->table . ' WHERE voiture_id = ? LIMIT 1';
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(1, $this->voiture_id);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Vérification de la récupération des données
    if(empty($row)) {
        return false;
    }
    
    // Assigner les valeurs aux propriétés de l'objet
    $this->marque = $row['marque'];
    $this->modele = $row['modele'];
    $this->immatriculation = $row['immatriculation'];
    $this->energie = $row['energie'];
    $this->couleur = $row['couleur'];
    $this->date_premiere_immatriculation = $row['date_premiere_immatriculation'];
    $this->nombre_places = $row['nombre_places'];
    $this->photo_url = $row['photo_url'];
    $this->description = $row['description'];
    $this->utilisateur_id = $row['utilisateur_id'];
    
    // Retourne les données
    return $row;
}

 // Récupérer toutes les voitures (tableau associatif)
    public function readAll() {
        $query = 'SELECT * FROM ' . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer une voiture par son ID
    public function readOne($id) {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE voiture_id = ? LIMIT 1';
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row : null;
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
        SET modele = :modele, 
            marque = :marque,  
            immatriculation = :immatriculation, 
            energie = :energie, 
            couleur = :couleur, 
            date_premiere_immatriculation = :date_premiere_immatriculation,
            photo_url = :photo_url, 
            description = :description,
            nombre_places = :nombre_places,
            utilisateur_id = :utilisateur_id  
        WHERE voiture_id = :voiture_id';

        $stmt = $this->conn->prepare($query);
       // Liaison des paramètres complets - AJOUT de :utilisateur_id
    $stmt->bindParam(':modele', $this->modele);
    $stmt->bindParam(':marque', $this->marque);
    $stmt->bindParam(':immatriculation', $this->immatriculation);
    $stmt->bindParam(':energie', $this->energie);
    $stmt->bindParam(':couleur', $this->couleur);
    $stmt->bindParam(':date_premiere_immatriculation', $this->date_premiere_immatriculation);
    $stmt->bindParam(':photo_url', $this->photo_url);
    $stmt->bindParam(':description', $this->description);
    $stmt->bindParam(':nombre_places', $this->nombre_places);
    $stmt->bindParam(':utilisateur_id', $this->utilisateur_id); // ← AJOUT IMPORTANT !
    $stmt->bindParam(':voiture_id', $this->voiture_id);

        // Ajout de logs pour déboguer la méthode update
        error_log('Requête SQL exécutée : ' . $query);

        // Ajout de logs pour afficher les paramètres liés à la requête SQL
        error_log('Paramètres liés à la requête SQL : ' . json_encode([
            'modele' => $this->modele,
            'immatriculation' => $this->immatriculation,
            'energie' => $this->energie,
            'couleur' => $this->couleur,
            'date_premiere_immatriculation' => $this->date_premiere_immatriculation,
            'photo_url' => $this->photo_url,
            'description' => $this->description,
            'nombre_places' => $this->nombre_places,
            'voiture_id' => $this->voiture_id
        ]));
        if (!$stmt->execute()) {
            error_log('Erreur SQL : ' . print_r($stmt->errorInfo(), true));
            return false;
        }

        return $stmt ;
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