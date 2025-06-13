<?php
session_start();
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');
require_once '../config/Database.php';
include_once '../controllers/DashboardController.php';

class UserSession {
    public $userEmail;
    public $userMotdepasse;
    
    
    private $userId;

    public function __construct() {
        $this->checkSession();
        $this->createsession();
    }
    
    public function checkSession() {
        if (!isset($_SESSION['utilisateur_id'])) {
            http_response_code(401);
            echo json_encode(array('message' => 'Session non authentifiée'));
            exit;
        }
    }
    
    public function createsession() {
        if (isset($_SESSION['email'])) {
            $this->userEmail = $_SESSION['email'];
        }
    }
    
    public function read_by_email() {
        $query = "SELECT utilisateur_id, nom, email FROM utilisateurs WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $this->email);
        $stmt->execute();
        
        return $row=$stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->utilisateur_id = $row['utilisateur_id'];
            $this->email = $row['email'];
            $this->nom = $row['nom'];
            $this->prenom = $row['prenom'];
            // ... autres champs
            return $row ;
        }
        
        return false;
    }
}
?>

