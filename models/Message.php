<?php
class Message {
    private $conn;
    private $table = 'messages';

    // Propriétés
    public $message_id;
    public $expediteur_id;
    public $destinataire_id;
    public $trajet_id;
    public $contenu;
    public $date_envoi;
    public $lu;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read() {
        $query = 'SELECT * FROM ' . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function read_conversation($user1_id, $user2_id) {
        $query = 'SELECT * FROM ' . $this->table . ' 
                  WHERE (expediteur_id = :user1_id AND destinataire_id = :user2_id)
                  OR (expediteur_id = :user2_id AND destinataire_id = :user1_id)
                  ORDER BY date_envoi ASC';
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user1_id', $user1_id);
        $stmt->bindParam(':user2_id', $user2_id);
        $stmt->execute();
        
        return $stmt;
    }

    public function read_single() {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE message_id = ? LIMIT 1';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->message_id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->expediteur_id = $row['expediteur_id'];
            $this->destinataire_id = $row['destinataire_id'];
            $this->trajet_id = $row['trajet_id'];
            $this->contenu = $row['contenu'];
            $this->date_envoi = $row['date_envoi'];
            $this->lu = $row['lu'];
            return true;
        }
        return false;
    }

    public function create() {
        $query = 'INSERT INTO ' . $this->table . ' 
                  SET expediteur_id = :expediteur_id, 
                      destinataire_id = :destinataire_id, 
                      trajet_id = :trajet_id,
                      contenu = :contenu,
                      date_envoi = :date_envoi,
                      lu = :lu';
                      
        $stmt = $this->conn->prepare($query);
        
        // Nettoyage des données
        $this->expediteur_id = htmlspecialchars(strip_tags($this->expediteur_id));
        $this->destinataire_id = htmlspecialchars(strip_tags($this->destinataire_id));
        $this->trajet_id = htmlspecialchars(strip_tags($this->trajet_id ?? null));
        $this->contenu = htmlspecialchars(strip_tags($this->contenu));
        $this->date_envoi = htmlspecialchars(strip_tags($this->date_envoi ?? date('Y-m-d H:i:s')));
        $this->lu = $this->lu ?? 0;
        
        // Liaison des paramètres
        $stmt->bindParam(':expediteur_id', $this->expediteur_id);
        $stmt->bindParam(':destinataire_id', $this->destinataire_id);
        $stmt->bindParam(':trajet_id', $this->trajet_id);
        $stmt->bindParam(':contenu', $this->contenu);
        $stmt->bindParam(':date_envoi', $this->date_envoi);
        $stmt->bindParam(':lu', $this->lu);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function update() {
        // Principalement pour marquer comme lu
        $query = 'UPDATE ' . $this->table . ' 
                  SET lu = :lu 
                  WHERE message_id = :message_id';
                  
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':lu', $this->lu);
        $stmt->bindParam(':message_id', $this->message_id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = 'DELETE FROM ' . $this->table . ' WHERE message_id = :message_id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':message_id', $this->message_id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
    
    public function get_unread_count($user_id) {
        $query = 'SELECT COUNT(*) as count FROM ' . $this->table . ' 
                  WHERE destinataire_id = :user_id AND lu = 0';
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'];
    }
}
?>