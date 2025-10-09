<?php
class Utilisateur {
    private $conn;
    public $utilisateur_id;
    public $nom;
    public $prenom;
    public $email;
    public $password;
    public $role;
    public $suspendu;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Lire tous les utilisateurs
    public function read() {
        $sql = "SELECT * FROM utilisateurs";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Créer un utilisateur
    public function create() {
        $sql = "INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role) VALUES (:nom, :prenom, :email, :mot_de_passe, :role)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':nom', $this->nom);
        $stmt->bindValue(':prenom', $this->prenom);
        $stmt->bindValue(':email', $this->email);
        $stmt->bindValue(':mot_de_passe', password_hash($this->password, PASSWORD_DEFAULT));
        $stmt->bindValue(':role', $this->role);
        return $stmt->execute();
    }

    // Mettre à jour un utilisateur (admin)
    public function update($isAdmin = false) {
        $sql = "UPDATE utilisateurs SET nom = :nom, prenom = :prenom, email = :email, role = :role";
        if (!empty($this->password)) {
            $sql .= ", mot_de_passe = :mot_de_passe";
        }
        $sql .= " WHERE utilisateur_id = :utilisateur_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':nom', $this->nom);
        $stmt->bindValue(':prenom', $this->prenom);
        $stmt->bindValue(':email', $this->email);
        $stmt->bindValue(':role', $this->role);
        $stmt->bindValue(':utilisateur_id', $this->utilisateur_id);
        if (!empty($this->password)) {
            $stmt->bindValue(':mot_de_passe', password_hash($this->password, PASSWORD_DEFAULT));
        }
        return $stmt->execute();
    }

    // Supprimer un utilisateur
    public function delete() {
        $sql = "DELETE FROM utilisateurs WHERE utilisateur_id = :utilisateur_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':utilisateur_id', $this->utilisateur_id);
        return $stmt->execute();
    }

    // Récupérer le rôle d'un utilisateur par son ID
    public function getRoleById($id) {
        $sql = "SELECT role FROM utilisateurs WHERE utilisateur_id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['role'] : null;
    }

    // Activer/désactiver un utilisateur
    public function updateSuspension() {
        $sql = "UPDATE utilisateurs SET suspendu = :suspendu WHERE utilisateur_id = :utilisateur_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':suspendu', $this->suspendu);
        $stmt->bindValue(':utilisateur_id', $this->utilisateur_id);
        return $stmt->execute();
    }
}
