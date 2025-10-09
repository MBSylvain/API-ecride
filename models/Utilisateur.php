<?php
class Utilisateur {
    private $conn;
    private $table = 'utilisateurs';

    public $utilisateur_id;
    public $nom;
    public $prenom;
    public $email;
    public $password;
    public $telephone;
    public $adresse;
    public $date_naissance;
    public $photo;
    public $pseudo;
    public $role;
    public $date_inscription;
    public $compte_actif;
    public $date_modification;
    




    public function __construct($db) {
        $this->conn = $db;
    }

   

    // Dans read_by_email()
    public function read_by_email() {
        $query = "SELECT * FROM utilisateurs WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $this->email);
        $stmt->execute();
    
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if($row) {
        $this->utilisateur_id = $row['utilisateur_id'];
        $this->nom = $row['nom'];
        $this->prenom = $row['prenom'];
        $this->email = $row['email'];
        $this->telephone = $row['telephone'];
        $this->adresse = $row['adresse'];
        $this->date_naissance = $row['date_naissance'];
        $this->pseudo = $row['pseudo'];
        $this->role = $row['role'];
        // ... autres attributs
        return true;
    }
    
    return false;
    }
    // Lire un utilisateur par ID
    public function read_single() {
        $query = "SELECT * FROM utilisateurs WHERE utilisateur_id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->utilisateur_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            $this->utilisateur_id = $row['utilisateur_id'];
            $this->nom = $row['nom'];
            $this->prenom = $row['prenom'];
            $this->email = $row['email'];
            $this->telephone = $row['telephone'];
            $this->adresse = $row['adresse'];
            $this->date_naissance = $row['date_naissance'];
            $this->pseudo = $row['pseudo'];
            $this->role = $row['role'];
            $this->date_inscription = $row['date_inscription'];
            $this->compte_actif = $row['compte_actif'];
            return true;
        }
    
        return $stmt;
    }
    
    
    public function read_voitures() {
        $query = 'SELECT v.* FROM voiture v 
                  JOIN possede_voiture pv ON v.voiture_id = pv.voiture_id 
                  WHERE pv.utilisateur_id = :utilisateur_id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':utilisateur_id', $this->utilisateur_id);
        $stmt->execute();
        return $stmt;
    }
    
    public function create() {
        try {
            // Validation renforcée
            if (empty($this->email) || empty($this->password) || empty($this->nom) || empty($this->prenom)) {
                throw new Exception("Tous les champs obligatoires doivent être remplis");
            }
    
            if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Email invalide");
            } 
            // Vérification doublon avec l'email
            $check = $this->conn->prepare("SELECT utilisateur_id FROM {$this->table} WHERE email = ?");
            $check->execute([$this->email]);
            if ($check->fetch()) {
                throw new Exception("Cet email est déjà utilisé");
            }
            // hachage du mot de passe    
            $this->password = password_hash($this->password, PASSWORD_BCRYPT);
    
            // Ajout d'une transaction
            $this->conn->beginTransaction();
    
            $query = "INSERT INTO {$this->table} 
                     (nom, prenom, email, mot_de_passe, telephone, adresse, date_naissance, pseudo)
                     VALUES 
                     (:nom, :prenom, :email, :mot_de_passe, :telephone, :adresse, :date_naissance, :pseudo)";
    
            $stmt = $this->conn->prepare($query);            
            $stmt->bindValue(':nom', $this->nom);
            $stmt->bindValue(':prenom', $this->prenom);
            $stmt->bindValue(':email', $this->email);
            $stmt->bindValue(':mot_de_passe', $this->password);
            $stmt->bindValue(':telephone', $this->telephone ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':adresse', $this->adresse ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':date_naissance', $this->date_naissance ?: null);
            $stmt->bindValue(':pseudo', $this->pseudo ?? null, PDO::PARAM_STR);

            // Exécution de la requête et vérification de son succès   
            if ($stmt->execute()) {
                $this->utilisateur_id = $this->conn->lastInsertId();
                $this->conn->commit();
                return true;
            }
    
            $this->conn->rollBack();
            return false;
    
        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("Erreur PDO: " . $e->getMessage());
            throw new Exception("Erreur technique: " . $e->getMessage());
        }
    }
    public function read() {
        $query = "SELECT utilisateur_id, nom, prenom, email, FROM utilisateurs";
        $stmt = $this->conn->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retourne directement un array
    }

    public function update() {
        try {
            $this->conn->beginTransaction();
            
            $query = "UPDATE {$this->table} SET 
                     nom = :nom,
                     prenom = :prenom,
                     email = :email,
                     telephone = :telephone,
                     adresse = :adresse,
                     date_naissance = :date_naissance,
                     pseudo = :pseudo";
            
            
            if (!empty($this->password)) {
                $query .= ", mot_de_passe = :mot_de_passe";
                $this->password = password_hash($this->password, PASSWORD_BCRYPT);
            }
            
            $query .= " WHERE utilisateur_id = :utilisateur_id";
            
            $stmt = $this->conn->prepare($query);
            
            // Bind des paramètres
            $stmt->bindParam(':nom', $this->nom);
            $stmt->bindParam(':prenom', $this->prenom);
            $stmt->bindParam(':email', $this->email);
            $stmt->bindParam(':telephone', $this->telephone);
            $stmt->bindParam(':adresse', $this->adresse);
            $stmt->bindParam(':date_naissance', $this->date_naissance);
            $stmt->bindParam(':pseudo', $this->pseudo);
            
            if (!empty($this->password)) {
                $stmt->bindParam(':mot_de_passe', $this->password);
            }
            
            $stmt->bindParam(':utilisateur_id', $this->utilisateur_id);
            
            $result = $stmt->execute();
            
            if ($result && $stmt->rowCount() > 0) {
                $this->conn->commit();
                return true;
            } else {
                $this->conn->rollBack();
                error_log("Aucune ligne affectée ou erreur d'exécution");
                return false;
            }
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Erreur PDO dans update(): " . $e->getMessage());
            return false;
        }
    }

    public function delete() {
        $query = 'DELETE FROM ' . $this->table . ' WHERE utilisateur_id = :utilisateur_id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':utilisateur_id', $this->utilisateur_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function login() {
        
     
        $query = 'SELECT * FROM ' . $this->table . ' WHERE email = :email LIMIT 1';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $this->email);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if($row && password_verify($this->password, $row['mot_de_passe'])) {
            $this->utilisateur_id = $row['utilisateur_id'];
            $this->nom = $row['nom'];
            $this->prenom = $row['prenom'];
            $this->email = $row['email'];
            $this->pseudo = $row['pseudo'];
            $this->role = $row['role'];
            //stockage de l'ID de l'utilisateur dans la session
            $_SESSION['utilisateur_id'] = $row['utilisateur_id'];
        $_SESSION['nom'] = $row['nom'];
        $_SESSION['prenom'] = $row['prenom'];
        $_SESSION['email'] = $row['email'];
        $_SESSION['pseudo'] = $row['pseudo'];
        $_SESSION['telephone'] = $row['telephone'];
        $_SESSION['adresse'] = $row['adresse'];
        $_SESSION['date_naissance'] = $row['date_naissance'];
        $_SESSION['role'] = $row['role']; // Stocker le rôle dans la session
             // Important: Sauvegarder la session
        session_write_close();
            return true;
        }
        return false;
    }

    // Add method to add role to user
public function addRole($role_id) {
    $query = "INSERT INTO possede (utilisateur_id, role_id) 
              VALUES (:utilisateur_id, :role_id)";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':utilisateur_id', $this->utilisateur_id);
    $stmt->bindParam(':role_id', $role_id);
    
    return $stmt->execute();
}

// Add method to assign a vehicle to user
public function assignVoiture($voiture_id, $is_primary = false) {
    $query = "INSERT INTO possede_voiture (utilisateur_id, voiture_id, principale) 
              VALUES (:utilisateur_id, :voiture_id, :principale)";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':utilisateur_id', $this->utilisateur_id);
    $stmt->bindParam(':voiture_id', $voiture_id);
    $stmt->bindParam(':principale', $is_primary, PDO::PARAM_BOOL);
    
    return $stmt->execute();
}

// Method to get user's roles
public function getRoles() {
    $query = "SELECT r.* FROM role r
              JOIN possede p ON r.role_id = p.role_id
              WHERE p.utilisateur_id = :utilisateur_id";
              
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':utilisateur_id', $this->utilisateur_id);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

}

?>