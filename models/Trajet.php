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
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function filtre_by_searchbar($ville_depart = null, $ville_arrivee = null, $date_depart = null) {
    $conditions = [];
    $params = [];
    if (!empty($ville_depart)) {
        $conditions[] = "t.ville_depart LIKE ?";
        $params[] = "%$ville_depart%";
    }
    if (!empty($ville_arrivee)) {
        $conditions[] = "t.ville_arrivee LIKE ?";
        $params[] = "%$ville_arrivee%";
    }
    if (!empty($date_depart)) {
        $conditions[] = "DATE(t.date_depart) = ?";
        $params[] = $date_depart;
    }
    $conditions[] = "t.statut = 'planifié'";
    $where = !empty($conditions) ? ('WHERE ' . implode(' AND ', $conditions)) : '';
    $query = "SELECT 
        t.*, 
        u.pseudo AS conducteur_pseudo,
        u.prenom AS conducteur_prenom,
        u.nom AS conducteur_nom,
        u.email AS conducteur_email,
        v.energie, 
        v.photo_url AS voiture_photo,
        AVG(a.note) AS note_conducteur
    FROM trajets t
    JOIN utilisateurs u ON t.utilisateur_id = u.utilisateur_id
    LEFT JOIN voiture v ON t.voiture_id = v.voiture_id
    LEFT JOIN avis a ON a.destinataire_id = u.utilisateur_id
    $where
    GROUP BY t.trajet_id";
    $stmt = $this->conn->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key + 1, $value);
    }
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Si aucun paramètre n'est renseigné, retourner tous les trajets planifiés avec toutes les données jointes
    if (empty($ville_depart) && empty($ville_arrivee) && empty($date_depart)) {
        return $results;
    }
    // Si des paramètres sont renseignés, retourner aussi toutes les données jointes
    return $results;
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

    public function read_single_trajet($trajet_id) {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE trajet_id = ? LIMIT 1';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $trajet_id);
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

        // Calcule le nombre de places restantes pour un trajet donné
        public function getPlacesRestantes($trajet_id) {
        // Récupérer le nombre de places totales du trajet
        $query = 'SELECT nombre_places FROM ' . $this->table . ' WHERE trajet_id = :trajet_id LIMIT 1';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':trajet_id', $trajet_id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null; 
        }
        $nombre_places = (int)$row['nombre_places'];

        // Récupérer le nombre de places déjà réservées
        $queryRes = 'SELECT SUM(nombre_places_reservees) AS places_reservees FROM reservations WHERE trajet_id = :trajet_id AND statut IN ("confirmée", "en_attente")';
        $stmtRes = $this->conn->prepare($queryRes);
        $stmtRes->bindParam(':trajet_id', $trajet_id, PDO::PARAM_INT);
        $stmtRes->execute();
        $rowRes = $stmtRes->fetch(PDO::FETCH_ASSOC);
        $places_reservees = isset($rowRes['places_reservees']) ? (int)$rowRes['places_reservees'] : 0;

        // Calculer le nombre de places restantes
        $places_restantes = $nombre_places - $places_reservees;
        return max($places_restantes, 0); // Jamais négatif
            
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

        // Correction pour éviter la double exécution et récupérer l'ID généré
        if ($stmt->execute()) {
            $this->trajet_id = $this->conn->lastInsertId(); 
            return true;
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

    // Methode pour obtenir l'utilisateur_id par trajet_id
    public function getUtilisateurIdByTrajetId($trajet_id) {
        $query = "SELECT utilisateur_id FROM " . $this->table . " WHERE trajet_id = :trajet_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':trajet_id', $trajet_id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return $row;
        }

        return null; 
    }
}
?>