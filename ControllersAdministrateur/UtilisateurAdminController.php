<?php
require_once '../config/session.php';
require_once '../config/Database.php';
require_once '../ModelAdministrateur/Utilisateur.php';
require_once '../Controllers/checkAuth.php';

// Vérification du rôle administrateur
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Administrateur' && $_SESSION['role'] !== 'Employe')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès réservé à l\'administrateur ou employé']);
    exit;
}

$database = new Database();
$db = $database->connect();
$utilisateur = new Utilisateur($db);

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    if (isset($_POST['_method'])) {
        $method = strtoupper($_POST['_method']);
    } elseif (isset($_GET['_method'])) {
        $method = strtoupper($_GET['_method']);
    }
    elseif (isset($_POST['action'])) {
        $action = strtoupper($_POST['action']);
        if (in_array($action, ['PATCH', 'PUT', 'DELETE'])) {
            $method = $action;
        }
    } elseif (isset($_GET['action'])) {
        $action = strtoupper($_GET['action']);
        if (in_array($action, ['PATCH', 'PUT', 'DELETE'])) {
            $method = $action;
        }
    }
    else {
        $raw = file_get_contents('php://input');
        $json = json_decode($raw);
        if (isset($json->_method)) {
            $method = strtoupper($json->_method);
        } elseif (isset($json->action) && in_array(strtoupper($json->action), ['PATCH', 'PUT', 'DELETE'])) {
            $method = strtoupper($json->action);
        }
    }
}


switch ($method) {
    case 'GET':
        // Liste tous les utilisateurs
        $result = $utilisateur->read();
        echo json_encode($result);
        break;
    case 'POST':
        // Créer un compte employé ou administrateur
        $data = json_decode(file_get_contents('php://input'));
        if (empty($data->nom) || empty($data->email) || empty($data->mot_de_passe) || empty($data->role)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Champs requis manquants']);
            exit;
        }
        $utilisateur->nom = htmlspecialchars(strip_tags($data->nom));
        $utilisateur->prenom = htmlspecialchars(strip_tags($data->prenom ?? ''));
        $utilisateur->email = htmlspecialchars(strip_tags($data->email));
        $utilisateur->password = $data->mot_de_passe;
        $utilisateur->role = htmlspecialchars(strip_tags($data->role));
        if ($utilisateur->create()) {
            http_response_code(201);
            echo json_encode(['success' => true, 'message' => 'Compte créé']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur création compte']);
        }
        break;
    case 'PUT':
        // Modifier un utilisateur (par ID)
        $data = json_decode(file_get_contents('php://input'));
        if (empty($data->utilisateur_id)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID requis']);
            exit;
        }
        $utilisateur->utilisateur_id = $data->utilisateur_id;
        $utilisateur->nom = htmlspecialchars(strip_tags($data->nom ?? ''));
        $utilisateur->prenom = htmlspecialchars(strip_tags($data->prenom ?? ''));
        $utilisateur->email = htmlspecialchars(strip_tags($data->email ?? ''));
        $utilisateur->role = htmlspecialchars(strip_tags($data->role ?? ''));
        if (!empty($data->password)) {
            $utilisateur->password = $data->password;
        }
        if ($utilisateur->update(true)) {
            echo json_encode(['success' => true, 'message' => 'Utilisateur modifié']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur modification']);
        }
        break;
    case 'DELETE':
        // Supprimer un utilisateur (par ID)
        if (!isset($_GET['utilisateur_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID requis']);
            exit;
        }
        $utilisateur->utilisateur_id = $_GET['utilisateur_id'];
        if ($utilisateur->delete()) {
            echo json_encode(['success' => true, 'message' => 'Utilisateur supprimé']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur suppression']);
        }
        break;
    case 'PATCH':
        // Activer/désactiver un compte utilisateur (admin : tout, employé : ne peut pas activer/désactiver admin/employé)
        $data = json_decode(file_get_contents('php://input'));
        if (empty($data->utilisateur_id) || !isset($data->suspendu)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID et statut requis']);
            exit;
        }
        $utilisateur->utilisateur_id = $data->utilisateur_id;
        $utilisateur->suspendu = $data->suspendu ? 1 : 0;
        // Récupérer le rôle du compte cible
        $targetRole = $utilisateur->getRoleById($data->utilisateur_id);
        if ($_SESSION['role'] === 'Employe' && ($targetRole === 'Administrateur' || $targetRole === 'Employe')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Action non autorisée sur ce type de compte']);
            exit;
        }
        if ($utilisateur->updateSuspension()) {
            echo json_encode(['success' => true, 'message' => $utilisateur->suspendu ? 'Compte désactivé' : 'Compte activé']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la modification du statut']);
        }
        break;
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
        break;
};