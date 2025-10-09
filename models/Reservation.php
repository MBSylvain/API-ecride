<?php
class Reservation {
    // Récupérer toutes les réservations (tableau associatif)
    public function readAll() {
        $query = 'SELECT * FROM ' . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer une réservation par son ID
    public function readOne($id) {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE reservation_id = ? LIMIT 1';
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row : null;
    }
    /**
     * Récupérer les emails des participants d'un trajet (hors réservations annulées)
     */
    public function getParticipantsEmails($trajet_id) {
        $query = 'SELECT u.email
                  FROM ' . $this->table . ' r
                  JOIN utilisateurs u ON r.utilisateur_id = u.utilisateur_id
                  WHERE r.trajet_id = ? AND r.statut != "annulée"';
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$trajet_id]);
        $emails = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (!empty($row['email'])) {
                $emails[] = $row['email'];
            }
        }
        return $emails;
    }
    // Connexion et table
    private $conn;
    private $table = 'reservations';

    // Propriétés correspondant exactement à la table
    public $reservation_id;
    public $utilisateur_id;
    public $trajet_id;
    public $nombre_places_reservees;
    public $statut;
    public $date_reservation;
    public $date_confirmation;
    public $point_rdv;
    public $commentaire;
    public $bagages;
    public $date_creation;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Lire les réservations d'un utilisateur
     */
    public function read_by_user($user_id) {
        $query = 'SELECT r.*, t.ville_depart, t.ville_arrivee, t.date_depart, t.heure_depart
                  FROM ' . $this->table . ' r
                  JOIN trajets t ON r.trajet_id = t.trajet_id
                  WHERE r.utilisateur_id = ?
                  ORDER BY t.date_depart DESC, t.heure_depart DESC';
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Méthode originale - convertie pour retourner un objet JSON
     */
    public function read_by_user_original() {
        $query = 'SELECT r.*, t.ville_depart, t.ville_arrivee, t.date_depart 
                  FROM ' . $this->table . ' r
                  JOIN trajets t ON r.trajet_id = t.trajet_id
                  WHERE r.utilisateur_id = ?
                  ORDER BY t.date_depart ASC';
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->utilisateur_id);
        $stmt->execute();
        return json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    
    /**
     * Vérifier si un utilisateur a déjà réservé un trajet spécifique
     */
    public function read_by_user_and_trajet($user_id, $trajet_id) {
        $query = 'SELECT * FROM ' . $this->table . ' 
                  WHERE utilisateur_id = ? AND trajet_id = ? AND statut != "annulé"';
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id, $trajet_id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Lire les réservations pour un trajet
     */
    public function read_by_trajet() {
        $query = 'SELECT * FROM ' . $this->table . ' 
                  WHERE trajet_id = ?';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->trajet_id);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Lire une réservation par son ID
     */
    public function read_single() {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE reservation_id = ?';
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->reservation_id]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$row) {
            return false;
        }
        
        // Définir les propriétés de l'objet
        $this->utilisateur_id = $row['utilisateur_id'];
        $this->trajet_id = $row['trajet_id'];
        $this->nombre_places_reservees = $row['nombre_places_reservees'];
        $this->statut = $row['statut'];
        //$this->montant_total = $row['montant_total'];
        $this->date_reservation = $row['date_reservation'];
        $this->date_confirmation = $row['date_confirmation'];
        $this->point_rdv = $row['point_rdv'];
        $this->commentaire = $row['commentaire'];
        $this->bagages = $row['bagages'];
        $this->date_creation = $row['date_creation'];
        //$this->date_modification = $row['date_modification'];
        
        return true;
    }
    // Read par l'administrateur pour toutes les réservations
        public function read_all() {
            $query = 'SELECT * FROM ' . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;   
    }

    /**
     * Créer une réservation
     */
    public function create() {
        $query = 'INSERT INTO ' . $this->table . ' 
                  SET 
                    utilisateur_id = :utilisateur_id,
                    trajet_id = :trajet_id,
                    nombre_places_reservees = :nombre_places_reservees,
                    statut = :statut,
                    date_reservation = :date_reservation,
                    point_rdv = :point_rdv,
                    commentaire = :commentaire';
        
        $stmt = $this->conn->prepare($query);

        // Nettoyage des données
        $this->utilisateur_id = htmlspecialchars(strip_tags($this->utilisateur_id));
        $this->trajet_id = htmlspecialchars(strip_tags($this->trajet_id));
        $this->nombre_places_reservees = htmlspecialchars(strip_tags($this->nombre_places_reservees));
        $this->statut = htmlspecialchars(strip_tags($this->statut ?? "en_attente"));
        $this->date_reservation = htmlspecialchars(strip_tags($this->date_reservation ?? date('Y-m-d H:i:s')));
        $this->point_rdv = htmlspecialchars(strip_tags($this->point_rdv ?? ''));
        $this->commentaire = htmlspecialchars(strip_tags($this->commentaire ?? ''));

        // Liaison des paramètres
        $stmt->bindParam(':utilisateur_id', $this->utilisateur_id);
        $stmt->bindParam(':trajet_id', $this->trajet_id);
        $stmt->bindParam(':nombre_places_reservees', $this->nombre_places_reservees);
        $stmt->bindParam(':statut', $this->statut);
        $stmt->bindParam(':date_reservation', $this->date_reservation);
        $stmt->bindParam(':point_rdv', $this->point_rdv);
        $stmt->bindParam(':commentaire', $this->commentaire);
        if($stmt->execute()) {
            return true;
        }

        printf("Erreur: %s.\n", $stmt->error);
        return false;
    }

    /**
     * Mettre à jour une réservation (statut)
     */
    public function update() {
        $query = 'UPDATE ' . $this->table . ' 
                  SET 
                    statut = :statut,
                    date_confirmation = CASE WHEN :statut = "confirmé" THEN NOW() ELSE date_confirmation END,
                    commentaire = :commentaire
                  WHERE reservation_id = :reservation_id';
        
        $stmt = $this->conn->prepare($query);

        $this->statut = htmlspecialchars(strip_tags($this->statut));
        $this->reservation_id = htmlspecialchars(strip_tags($this->reservation_id));
        $this->commentaire = htmlspecialchars(strip_tags($this->commentaire ?? null));

        $stmt->bindParam(':statut', $this->statut);
        $stmt->bindParam(':reservation_id', $this->reservation_id);
        $stmt->bindParam(':commentaire', $this->commentaire);

        if($stmt->execute()) {
            return true;
        }

        printf("Erreur: %s.\n", $stmt->error);
        return false;
    }

    /**
     * Mettre à jour uniquement le statut d'une réservation
     */
    public function update_status() {
        $query = 'UPDATE ' . $this->table . '
                  SET statut = :statut
                  WHERE reservation_id = :reservation_id';
        
        $stmt = $this->conn->prepare($query);
        
        // Protection des données
        $this->statut = htmlspecialchars(strip_tags($this->statut));
        
        // Bind des données
        $stmt->bindParam(':statut', $this->statut);
        $stmt->bindParam(':reservation_id', $this->reservation_id);
        
        // Exécuter la requête
        if ($stmt->execute()) {
            return true;
        }
        
        // En cas d'erreur
        printf("Erreur: %s.\n", $stmt->error);
        return false;
    }

    /**
     * Supprimer une réservation
     */
    public function delete() {
        $query = 'DELETE FROM ' . $this->table . ' WHERE reservation_id = ?';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->reservation_id);
        
        if($stmt->execute()) {
            return true;
        }
        
        printf("Erreur: %s.\n", $stmt->error);
        return false;
    }
}
?>