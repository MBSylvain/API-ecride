<?php
require_once '../config/session.php';

// Inclusions
include_once '../config/Database.php';
include_once '../models/Utilisateur.php';
include_once '../Controllers/checkAuth.php';
include_once '../models/Credit.php';


// Initialisation de la base de données
$database = new Database();
$db = $database->connect();
$utilisateur = new Utilisateur($db);

// Récupération de la méthode HTTP et des données
$method = $_SERVER['REQUEST_METHOD'];


// Fonction utilitaire pour nettoyer les entrées
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

// Inscription utilisateur
function registerUser($data, $utilisateur) {
    if (empty($data->nom) || empty($data->mot_de_passe) || empty($data->email)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Nom, email et mot de passe sont requis']);
        exit;
    }
    if ($data->mot_de_passe !== $data->confirm_password) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Les mots de passe ne correspondent pas']);
        exit;
    }
    $utilisateur->nom = sanitize($data->nom);
    $utilisateur->prenom = sanitize($data->prenom ?? '');
    $utilisateur->email = sanitize($data->email);
    $utilisateur->password = $data->mot_de_passe;
    $utilisateur->telephone = !empty($data->telephone) ? sanitize($data->telephone) : null;
    $utilisateur->adresse = !empty($data->adresse) ? sanitize($data->adresse) : null;
    $utilisateur->date_naissance = !empty($data->date_naissance) ? sanitize($data->date_naissance) : null;
    $utilisateur->pseudo = !empty($data->pseudo) ? sanitize($data->pseudo) : null;
    $utilisateur->role = sanitize($data->role ?? 'utilisateur');
    if ($utilisateur->create()) {
        http_response_code(201);
        echo json_encode(['success' => true, 'message' => 'Compte créé avec succès']);
        // nettoyer la session pour éviter les conflits
        session_destroy();
        // redémarrer la session avec les données de l'utilisateur
        session_start();
        $_SESSION['utilisateur_id'] = $utilisateur->utilisateur_id;
        $_SESSION['nom'] = $utilisateur->nom;
        $_SESSION['email'] = $utilisateur->email;
        $_SESSION['role'] = $utilisateur->role;
        // Crédits de bienvenue
        $credit = new Credit($utilisateur->conn);
        $credit->utilisateur_id = $utilisateur->utilisateur_id;
        $credit->montant = 20;
        $credit->type_operation = 'offert';
        $credit->commentaire = 'Crédits offerts à l\'inscription';
        $credit->createCredit();

    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Échec de la création du compte']);
    }
    exit;
}

// Connexion utilisateur
function loginUser($data, $utilisateur) {
    if (empty($data->email) || empty($data->password)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email et mot de passe sont requis']);
        exit;
    }
    $utilisateur->email = sanitize($data->email);
    $utilisateur->password = $data->password;
    if ($utilisateur->login()) {
        $_SESSION['utilisateur_id'] = $utilisateur->utilisateur_id;
        $_SESSION['nom'] = $utilisateur->nom;
        $_SESSION['email'] = $utilisateur->email;
        $_SESSION['role'] = $utilisateur->role;
        $_SESSION['last_activity'] = time();
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Connexion réussie',
            'user' => [
                'Utilisateur_id' => $utilisateur->utilisateur_id,
                'nom' => $utilisateur->nom,
                'email' => $utilisateur->email,
                'role' => $utilisateur->role
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Email ou mot de passe incorrect']);
    }
    exit;
}

// Déconnexion utilisateur
function logoutUser() {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    echo json_encode(['success' => true, 'message' => 'Déconnecté avec succès']);
    exit;
}

// Récupérer l'utilisateur courant (par ID session)
function getCurrentUser($utilisateur) {
    if (!isset($_SESSION['utilisateur_id'])) {
        http_response_code(401);
        echo json_encode(['message' => 'Non authentifié']);
        exit;
    }
    $utilisateur->utilisateur_id = $_SESSION['utilisateur_id'];
    if ($utilisateur->read_single()) {
        $response = [
            'utilisateur_id' => $utilisateur->utilisateur_id,
            'nom' => $utilisateur->nom,
            'prenom' => $utilisateur->prenom,
            'email' => $utilisateur->email,
            'telephone' => $utilisateur->telephone,
            'adresse' => $utilisateur->adresse,
            'date_naissance' => $utilisateur->date_naissance,
            'pseudo' => $utilisateur->pseudo,
            'role' => $utilisateur->role,
            'date_inscription' => $utilisateur->date_inscription
        ];
        echo json_encode($response);
    } else {
        http_response_code(404);
        echo json_encode(['message' => 'Utilisateur non trouvé']);
    }
    exit;
}

// Mettre à jour l'utilisateur courant
function updateCurrentUser($data, $utilisateur) {
    if (!isset($_SESSION['utilisateur_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Non authentifié']);
        exit;
    }
    if (empty($data->nom) || empty($data->email)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Nom et email sont requis']);
        exit;
    }
    $utilisateur->utilisateur_id = $_SESSION['utilisateur_id'];
    $utilisateur->nom = sanitize($data->nom);
    $utilisateur->prenom = isset($data->prenom) ? sanitize($data->prenom) : null;
    $utilisateur->email = sanitize($data->email);
    $utilisateur->telephone = isset($data->telephone) ? sanitize($data->telephone) : null;
    $utilisateur->adresse = isset($data->adresse) ? sanitize($data->adresse) : null;
    $utilisateur->date_naissance = isset($data->date_naissance) ? sanitize($data->date_naissance) : null;
    $utilisateur->pseudo = isset($data->pseudo) ? sanitize($data->pseudo) : null;
    if (!empty($data->password)) {
        $utilisateur->password = $data->password;
    }
    if ($utilisateur->update()) {
        $_SESSION['nom'] = $utilisateur->nom;
        $_SESSION['email'] = $utilisateur->email;
        echo json_encode([
            'success' => true,
            'message' => 'Utilisateur mis à jour',
            'utilisateur_id' => $utilisateur->utilisateur_id
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Échec de la mise à jour'
        ]);
    }
    exit;
}

// Supprimer l'utilisateur courant
function deleteCurrentUser($utilisateur) {
    if (!isset($_SESSION['utilisateur_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Non authentifié']);
        exit;
    }
    $utilisateur->utilisateur_id = $_SESSION['utilisateur_id'];
    if ($utilisateur->delete()) {
        session_destroy();
        echo json_encode(['success' => true, 'message' => 'Utilisateur supprimé']);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Échec de la suppression']);
    }
    exit;
}

// Récupération des données selon la méthode
$data = null;
$action = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT') {
    $contentType = isset($_SERVER["CONTENT_TYPE"]) ? $_SERVER["CONTENT_TYPE"] : "";
    if (stripos($contentType, "application/json") !== false) {
        $json = file_get_contents("php://input");
        $tmp = json_decode($json);
        if (isset($tmp->data) && is_object($tmp->data)) {
            $data = $tmp->data;
        } else {
            $data = $tmp;
        }
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode(['message' => 'JSON invalide', 'error' => json_last_error_msg(), 'raw' => $json]);
            exit;
        }
    } else {
        $data = (object)$_POST;
    }
    
}

switch ($method) {
    case 'GET':
        // Récupérer l'utilisateur courant (auth obligatoire)
        verifyAuth();
        getCurrentUser($utilisateur);
        break;

    case 'POST':
        if (!$data) {
            http_response_code(400);
            echo json_encode(['message' => 'Données manquantes']);
            exit;
        }
        if (isset($data->action) && $data->action === 'register') {
            registerUser($data, $utilisateur);
            // nettoyer la session pour éviter les conflits
            session_destroy();
            // redémarrer la session avec les données de l'utilisateur
            session_start();
            $_SESSION['utilisateur_id'] = $utilisateur->utilisateur_id;
            $_SESSION['nom'] = $utilisateur->nom;
            $_SESSION['email'] = $utilisateur->email;
            $_SESSION['role'] = $utilisateur->role;

            if(registerUser($data, $_SESSION['utilisateur_id'])) {
                // Crédits de bienvenue
                $credit = new Credit($utilisateur->conn);
                $credit->utilisateur_id = $_SESSION['utilisateur_id'];
                $credit->montant = 30; // Montant du crédit de bienvenue
                $credit->createCredit();
                // Envoyer une notification par email
                require_once '../models/Utilisateur.php';
                $utilisateurModel = new Utilisateur($utilisateur->conn);
                $utilisateurModel->envoyerEmailBienvenue($utilisateur->utilisateur_id);
                
            }

        } else if (isset($data->action) && $data->action === 'logout') {
            logoutUser();
        } else if (isset($data->email) && isset($data->password)) {
            loginUser($data, $utilisateur);
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'Données requises manquantes ou action non reconnue']);
        }
        break;

    case 'PUT':
        verifyAuth();
        if (!$data) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Données manquantes']);
            exit;
        }
        updateCurrentUser($data, $utilisateur);
        break;
        

    case 'DELETE':
        verifyAuth();
        deleteCurrentUser($utilisateur);
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
        break;
}
?>