<?php
// En-têtes CORS
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Max-Age: 3600");


// Démarrer la session
session_start();

// Gestion des requêtes OPTIONS (CORS pré-vol)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Inclusions
include_once '../config/Database.php';
include_once '../models/Utilisateur.php';

// Initialisation de la base de données
$database = new Database();
$db = $database->connect();
$utilisateur = new Utilisateur($db);

// Récupération de la méthode HTTP et des données
$method = $_SERVER['REQUEST_METHOD'];
// Récupération des données JSON ou POST selon le format
$data = json_decode(file_get_contents("php://input"));
//Récupération de l'action envoyer dans le formulaire
//
// Récupérer l'action soit de $_POST soit de $data (JSON)
$action = null;
if (isset($_POST['action'])) {
    $action = $_POST['action'];
} elseif (isset($data->action)) {
    $action = $data->action;
}

// Déterminer la méthode appropriée
if ($method == 'POST' && $action == 'PUT') {
    // Si la méthode est PUT, on la change
    $method = 'PUT';
} elseif ($method == 'POST' && $action == 'DELETE') {
    // Si la méthode est DELETE, on la change
    $method = 'DELETE';
} else {
    $method = $_SERVER['REQUEST_METHOD'];
}


// Fonction utilitaire pour nettoyer les entrées
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

/**
 * Gère l'inscription d'un nouvel utilisateur
 */
function handleRegistration($data, $utilisateur) {
    // Validation des données requises
    if (empty($data->nom) || empty($data->mot_de_passe)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Nom et mot de passe sont requis']);
        exit;
    }

    if (empty($data->email)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email est requis']);
        exit;
    }

    // Vérification de la correspondance des mots de passe
    if ($data->mot_de_passe !== $data->confirm_password) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Les mots de passe ne correspondent pas']);
        exit;
    }

    // Assignation des données utilisateur
    $utilisateur->nom = sanitize($data->nom);
    $utilisateur->prenom = sanitize($data->prenom ?? '');
    $utilisateur->email = sanitize($data->email);
    $utilisateur->password = $data->mot_de_passe; // Le hash est fait dans create()
    $utilisateur->telephone = !empty($data->telephone) ? sanitize($data->telephone) : null;
    $utilisateur->adresse = !empty($data->adresse) ? sanitize($data->adresse) : null;
    $utilisateur->date_naissance = !empty($data->date_naissance) ? sanitize($data->date_naissance) : null;
    $utilisateur->pseudo = !empty($data->pseudo) ? sanitize($data->pseudo) : null;
    $utilisateur->role = sanitize($data->role ?? 'utilisateur'); // Valeur par défaut si non fournie

    // Création du compte
    if ($utilisateur->create()) {
        http_response_code(201); // Created
        echo json_encode(['success' => true, 'message' => 'Compte créé avec succès']);
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(['success' => false, 'message' => 'Échec de la création du compte']);
    }
    exit;
}

/**
 * Gère la connexion d'un utilisateur existant
 */
function handleLogin($data, $utilisateur) {
    // Validation des données requises
    if (empty($data->email) || empty($data->password)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email et mot de passe sont requis']);
        exit;
    }

    // Tentative de connexion
    $utilisateur->email = sanitize($data->email);
    $utilisateur->password = $data->password; // Ne pas sanitizer le mot de passe
    
    if ($utilisateur->login()) {
        http_response_code(200); // OK
        echo json_encode([
            'success' => true, 
            'message' => 'Connexion réussie',
            'user' => [
                'id' => $_SESSION['utilisateur_id'],
                'nom' => $_SESSION['nom'],
                'email' => $_SESSION['email'],
                'role' => $_SESSION['role'] ?? 'utilisateur'
            ]
        ]);
    } else {
        http_response_code(401); // Unauthorized
        echo json_encode(['success' => false, 'message' => 'Email ou mot de passe incorrect']);
    }
    exit;
}

// Routeur principal
switch ($method) {
    case 'GET':
        // Vérification authentification
        if (!isset($_SESSION['utilisateur_id'])) {
            http_response_code(401);
            echo json_encode(['message' => 'Non authentifié']);
            exit;
        }

        // Lecture par email
        if (isset($_GET['utilisateur_id'])) {
            $utilisateur->utilisateur_id = sanitize($_GET['utilisateur_id']);
            if ($utilisateur->read_single()) {
            $response = [
                'utilisateur_id' => $utilisateur->utilisateur_id,
                'nom' => $utilisateur->nom,
                'prenom' => $utilisateur->prenom,
                'email' => $utilisateur->email,
                'telephone' => $utilisateur->telephone,
                'adresse' => $utilisateur->adresse,
                'date_naissance' => $utilisateur->date_naissance,
                'pseudo' => $utilisateur->pseudo
            ];
            echo json_encode($response);
            } else {
            http_response_code(404);
            echo json_encode(['message' => 'Utilisateur non trouvé']);
            }
        } else if (isset($_GET['email'])) {
            $utilisateur->email = sanitize($_GET['email']);
            if ($utilisateur->read_by_email()) {
            $response = [
                'utilisateur_id' => $utilisateur->utilisateur_id,
                'nom' => $utilisateur->nom,
                'prenom' => $utilisateur->prenom,
                'email' => $utilisateur->email,
                'telephone' => $utilisateur->telephone,
                'adresse' => $utilisateur->adresse,
                'date_naissance' => $utilisateur->date_naissance,
                'pseudo' => $utilisateur->pseudo

            ];
            echo json_encode($response);
            
            } else {
            http_response_code(404);
            echo json_encode(['message' => 'Utilisateur non trouvé']);
            }
        } else {
            // Lecture de tous les utilisateurs
            $result = $utilisateur->read();
            if (is_array($result) && count($result) > 0) {
                echo json_encode($result);
            } else {
                http_response_code(404);
                echo json_encode(['message' => 'Aucun utilisateur trouvé']);
            }
        }
        break;

    case 'POST':
        // Récupération des données JSON ou POST selon le format
        $data = null;
        if (isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
            // Format JSON
            $data = json_decode(file_get_contents("php://input"));
        } else {
            // Format POST standard
            $data = (object)$_POST;
        }
        
        // Vérifier l'action pour différencier connexion et inscription
        if (isset($data->action) && $data->action === 'register') {
            // Traitement de l'inscription
            handleRegistration($data, $utilisateur);
        } else if (isset($data->email) && isset($data->password)) {
            // Tentative de connexion
            handleLogin($data, $utilisateur);
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'Données requises manquantes']);
            exit;
        }
        break;

        

    case 'PUT':
        // Vérification authentification
        if (!isset($_SESSION['utilisateur_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Non authentifié']);
            exit;
        }

        // Récupération des données selon le format
        $data = null;
        $contentType = isset($_SERVER["CONTENT_TYPE"]) ? $_SERVER["CONTENT_TYPE"] : "";
        
        if (strpos($contentType, "application/json") !== false) {
            // Format JSON
            $data = json_decode(file_get_contents("php://input"));
        } else {
            // Format URL encoded
            parse_str(file_get_contents("php://input"), $putData);
            $data = (object)$putData;
        }

        // Debug (à enlever en production)
        error_log("Données reçues: " . print_r($data, true));

        // Validation des données obligatoires
        if (empty($data->nom) || empty($data->email)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Nom et email sont requis']);
            exit;
        }

        // Assignation des valeurs
        $utilisateur->utilisateur_id = $_SESSION['utilisateur_id'];
        $utilisateur->nom = sanitize($data->nom);
        $utilisateur->prenom = isset($data->prenom) ? sanitize($data->prenom) : null;
        $utilisateur->email = sanitize($data->email);
        $utilisateur->telephone = isset($data->telephone) ? sanitize($data->telephone) : null;
        $utilisateur->adresse = isset($data->adresse) ? sanitize($data->adresse) : null;
        $utilisateur->date_naissance = isset($data->date_naissance) ? sanitize($data->date_naissance) : null;
        $utilisateur->pseudo = isset($data->pseudo) ? sanitize($data->pseudo) : null;

        // Gestion spécifique du mot de passe
        if (!empty($data->password)) {
            $utilisateur->password = $data->password; // Le hash est géré dans la méthode update()
        }

        // Exécution de la mise à jour
        if ($utilisateur->update()) {
            // Réponse JSON uniquement (pas de redirection ici)
            echo json_encode([
                'success' => true,
                'message' => 'Utilisateur mis à jour',
                'utilisateur_id' => $utilisateur->utilisateur_id
            ]);
            exit;
        } else {
            // Get error info if available
            $errorInfo = $db->errorInfo();
                        
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Échec de la mise à jour',
                'error' => $errorInfo[2] ?? 'Erreur inconnue'
            ]);
        }
        break;
   

    case 'DELETE':
        // Suppression
        if (!isset($_SESSION['utilisateur_id'])) {
            http_response_code(401);
            echo json_encode(['message' => 'Non authentifié']);
            exit;
        }

        $utilisateur->utilisateur_id = $_SESSION['utilisateur_id'];

        if ($utilisateur->delete()) {
            session_destroy();
            echo json_encode(['message' => 'Utilisateur supprimé']);
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'Échec de la suppression']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['message' => 'Méthode non autorisée']);
        break;
}