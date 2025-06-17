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
    public $email;
    public $nom;
    public $prenom;
    public $utilisateurSession;
    
    public $utilisateur_id;
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
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
        // Récupérer depuis le corps JSON de la requête
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        if ($data && isset($data['sessionData'])) {
            $this->utilisateurSession = $data['sessionData'];
            
            // Mettre à jour les propriétés individuelles
            if (isset($this->utilisateurSession['utilisateur_id'])) {
                $this->utilisateur_id = $this->utilisateurSession['utilisateur_id'];
                $this->email = $this->utilisateurSession['email'] ?? null;
                $this->nom = $this->utilisateurSession['nom'] ?? null;
                $this->prenom = $this->utilisateurSession['prenom'] ?? null;
            } else {
                // Session invalide
                http_response_code(401);
                echo json_encode(array('message' => 'Session non authentifiée'));
                exit;
            }
        }
        else if (isset($_SESSION['utilisateur_id'])) {
            // Utiliser la session PHP si disponible
            $this->utilisateurSession = [
                'utilisateur_id' => $_SESSION['utilisateur_id'],
                'email' => $_SESSION['email'],
                'nom' => $_SESSION['nom'],
                'prenom' => $_SESSION['prenom']
            ];
            
            $this->utilisateur_id = $_SESSION['utilisateur_id'];
            $this->email = $_SESSION['email']; 
            $this->nom = $_SESSION['nom'];
            $this->prenom = $_SESSION['prenom'];
        } else {
            http_response_code(401);
            echo json_encode(array('message' => 'Session non authentifiée'));
            exit;
        }
    
    }
    
    public function read_by_email() {
        $query = "SELECT utilisateur_id, nom, email, prenom FROM utilisateurs WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $this->email);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->utilisateur_id = $row['utilisateur_id'];
            $this->email = $row['email'];
            $this->nom = $row['nom'];
            $this->prenom = $row['prenom'];
            // ... autres champs
            return $row;
        }
        
        return false;
    }
}
?>

