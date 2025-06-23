<?php
class Trajet {
    private $conn;
    private $table = 'trajets';

    public $trajet_id;
    public $ville_depart;
    public $ville_arrivee;
    public $adresse_depart;
    public $adresse_arrivee;
    public $date_depart;
    public $heure_depart;
    public $heure_arrivee;
    public $nombre_places;
    public $prix;
    public $description;
    public $bagages_autorises;
    public $fumeur_autorise;
    public $animaux_autorises;
    public $statut;
    public $utilisateur_id;
    public $voiture_id;
    public $date_creation;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read() {
        $query = 'SELECT * FROM ' . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $results;
    }

    public function filtre_by_searchbar($ville_depart = null, $ville_arrivee = null, $date_depart = null) {
    $conditions = [];
    $params = [];
    
    if (!empty($ville_depart)) {
        $conditions[] = "ville_depart LIKE ?";
        $params[] = "%$ville_depart%";
    }
    
    if (!empty($ville_arrivee)) {
        $conditions[] = "ville_arrivee LIKE ?";
        $params[] = "%$ville_arrivee%";
    }
    
    if (!empty($date_depart)) {
        $conditions[] = "DATE(date_depart) = ?";
        $params[] = $date_depart;
    }
    
    if (empty($conditions)) {
        return []; // ou retourner tous les résultats si aucun filtre
    }
    
    $query = 'SELECT * FROM ' . $this->table . ' WHERE ' . implode(' AND ', $conditions);
    $stmt = $this->conn->prepare($query);
    
    foreach ($params as $key => $value) {
        $stmt->bindValue($key + 1, $value);
    }
    
    $data =$stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


    public function read_single() {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE trajet_id = ? LIMIT 1';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->trajet_id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->ville_depart = $row['ville_depart'];
            $this->ville_arrivee = $row['ville_arrivee'];
            $this->adresse_depart = $row['adresse_depart'];
            $this->adresse_arrivee = $row['adresse_arrivee'];
            $this->date_depart = $row['date_depart'];
            $this->heure_depart = $row['heure_depart'];
            $this->heure_arrivee = $row['heure_arrivee'];
            $this->nombre_places = $row['nombre_places'];
            $this->prix = $row['prix'];
            $this->description = $row['description'];
            $this->bagages_autorises = $row['bagages_autorises'];
            $this->fumeur_autorise = $row['fumeur_autorise'];
            $this->animaux_autorises = $row['animaux_autorises'];
            $this->statut = $row['statut'];
            $this->utilisateur_id = $row['utilisateur_id'];
            $this->voiture_id = $row['voiture_id'];
            $this->date_creation = $row['date_creation'];
            return ($row);
        }
        return false;
    }

    public function read_by_user($utilisateur_id) {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE utilisateur_id = ?';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $utilisateur_id);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $results;
    }

    public function create() {
        $query = 'INSERT INTO ' . $this->table . ' 
            SET ville_depart = :ville_depart, 
                ville_arrivee = :ville_arrivee,
                adresse_depart = :adresse_depart,
                adresse_arrivee = :adresse_arrivee,
                date_depart = :date_depart,
                heure_depart = :heure_depart,
                nombre_places = :nombre_places,
                prix = :prix,
                description = :description,
                bagages_autorises = :bagages_autorises,
                fumeur_autorise = :fumeur_autorise,
                animaux_autorises = :animaux_autorises,
                statut = :statut,
                utilisateur_id = :utilisateur_id,
                voiture_id = :voiture_id';

        $stmt = $this->conn->prepare($query);

        // Nettoyage des données
        $this->ville_depart = htmlspecialchars(strip_tags($this->ville_depart));
        $this->ville_arrivee = htmlspecialchars(strip_tags($this->ville_arrivee));
        $this->adresse_depart = htmlspecialchars(strip_tags($this->adresse_depart));
        $this->adresse_arrivee = htmlspecialchars(strip_tags($this->adresse_arrivee));
        $this->description = htmlspecialchars(strip_tags($this->description));

        // Liaison des paramètres
        $stmt->bindParam(':ville_depart', $this->ville_depart);
        $stmt->bindParam(':ville_arrivee', $this->ville_arrivee);
        $stmt->bindParam(':adresse_depart', $this->adresse_depart);
        $stmt->bindParam(':adresse_arrivee', $this->adresse_arrivee);
        $stmt->bindParam(':date_depart', $this->date_depart);
        $stmt->bindParam(':heure_depart', $this->heure_depart);
        $stmt->bindParam(':nombre_places', $this->nombre_places);
        $stmt->bindParam(':prix', $this->prix);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':bagages_autorises', $this->bagages_autorises);
        $stmt->bindParam(':fumeur_autorise', $this->fumeur_autorise);
        $stmt->bindParam(':animaux_autorises', $this->animaux_autorises);
        $stmt->bindParam(':statut', $this->statut);
        $stmt->bindParam(':utilisateur_id', $this->utilisateur_id);
        $stmt->bindParam(':voiture_id', $this->voiture_id);

        if ($stmt->execute()) {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':utilisateur_id', $this->utilisateur_id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->trajet_id = $result['id'];
            return $result;
        }
        return false;
    }

    public function update() {
        $query = 'UPDATE ' . $this->table . ' 
            SET ville_depart = :ville_depart, 
                ville_arrivee = :ville_arrivee,
                adresse_depart = :adresse_depart,
                adresse_arrivee = :adresse_arrivee,
                date_depart = :date_depart,
                heure_depart = :heure_depart,
                nombre_places = :nombre_places,
                prix = :prix,
                description = :description,
                bagages_autorises = :bagages_autorises,
                fumeur_autorise = :fumeur_autorise,
                animaux_autorises = :animaux_autorises,
                voiture_id = :voiture_id
            WHERE trajet_id = :trajet_id';

        $stmt = $this->conn->prepare($query);

        // Nettoyage des données
        $this->ville_depart = htmlspecialchars(strip_tags($this->ville_depart));
        $this->ville_arrivee = htmlspecialchars(strip_tags($this->ville_arrivee));
        $this->adresse_depart = htmlspecialchars(strip_tags($this->adresse_depart));
        $this->adresse_arrivee = htmlspecialchars(strip_tags($this->adresse_arrivee));
        $this->description = htmlspecialchars(strip_tags($this->description));

        // Liaison des paramètres
        $stmt->bindParam(':ville_depart', $this->ville_depart);
        $stmt->bindParam(':ville_arrivee', $this->ville_arrivee);
        $stmt->bindParam(':adresse_depart', $this->adresse_depart);
        $stmt->bindParam(':adresse_arrivee', $this->adresse_arrivee);
        $stmt->bindParam(':date_depart', $this->date_depart);
        $stmt->bindParam(':heure_depart', $this->heure_depart);
        $stmt->bindParam(':nombre_places', $this->nombre_places);
        $stmt->bindParam(':prix', $this->prix);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':bagages_autorises', $this->bagages_autorises);
        $stmt->bindParam(':fumeur_autorise', $this->fumeur_autorise);
        $stmt->bindParam(':animaux_autorises', $this->animaux_autorises);
        $stmt->bindParam(':voiture_id', $this->voiture_id);
        $stmt->bindParam(':trajet_id', $this->trajet_id);

        return $stmt->execute();
    }

    public function delete() {
        $query = 'DELETE FROM ' . $this->table . ' WHERE trajet_id = :trajet_id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':trajet_id', $this->trajet_id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>