<?php
require_once '../config/session.php';

// Inclusions
include_once '../config/Database.php';
include_once '../models/Utilisateur.php';
include_once '../Controllers/checkAuth.php';



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
    $utilisateur->role = sanitize($data->role ?? 'utilisateur');

    // Création du compte
    if ($utilisateur->create()) {
        http_response_code(201);
        echo json_encode(['success' => true, 'message' => 'Compte créé avec succès']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Échec de la création du compte']);
    }
    exit;
}

/**
 * Gère la connexion d'un utilisateur existant - CORRIGÉ
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
    $utilisateur->password = $data->password;
    
    if ($utilisateur->login()) {
        // ✅ CORRECTION : Mettre à jour la session APRÈS la vérification du login
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
                'id' => $utilisateur->utilisateur_id,
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

/**
 * Gère la déconnexion
 */
function handleLogout() {
    // Destruction de la session
    $_SESSION = array();

    // Suppression du cookie de session
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

// === ROUTEUR PRINCIPAL CORRIGÉ ===

// Récupération des données selon la méthode
$data = null;
$action = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT') {
    $contentType = isset($_SERVER["CONTENT_TYPE"]) ? $_SERVER["CONTENT_TYPE"] : "";

    if (strpos($contentType, "application/json") !== false) {
        $data = json_decode(file_get_contents("php://input"));
    } else {
        // Pour les formulaires standards
        $data = (object)$_POST;
    }
    // Associer la valeur de action à celle de la méthode si présente
    if (isset($data->action) && !empty($data->action)) {
        $method = strtoupper($data->action);
    }
}

switch ($method) {
    case 'GET':
        // ✅ CORRECTION : Vérification d'authentification simplifiée
        verifyAuth(); // Utilisateur doit être authentifié pour les GET
        
        // Lecture par utilisateur_id
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
                    'pseudo' => $utilisateur->pseudo,
                    'role' => $utilisateur->role,
                    'date_inscription' => $utilisateur->date_inscription
                ];
                echo json_encode($response);
            } else {
                http_response_code(404);
                echo json_encode(['message' => 'Utilisateur non trouvé']);
            }
        } 
        // Lecture par email
        else if (isset($_GET['email'])) {
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
                    'pseudo' => $utilisateur->pseudo,
                    'role' => $utilisateur->role,
                    'date_inscription' => $utilisateur->date_inscription
                ];
                echo json_encode($response);
            } else {
                http_response_code(404);
                echo json_encode(['message' => 'Utilisateur non trouvé']);
            }
        } 
        // Lecture utilisateur par trajet_id
        else if (isset($_GET['trajet_id'])) {
            $trajet_id = sanitize($_GET['trajet_id']);
            $utilisateur_id = $utilisateur->getUtilisateurIdByTrajetId($trajet_id);

            if ($utilisateur_id !== null) {
                echo json_encode([
                    'success' => true,
                    'utilisateur_id' => $utilisateur_id
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Aucun utilisateur trouvé pour ce trajet.'
                ]);
            }
        }



        // Lecture de tous les utilisateurs (admin seulement)
        else {
            // Vérifier si l'utilisateur est admin
            if ($_SESSION['role'] !== 'admin') {
                http_response_code(403);
                echo json_encode(['message' => 'Accès non autorisé']);
                exit;
            }
            
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
        // Pas de verifyAuth() ici - permet l'inscription et la connexion
        
        if (!$data) {
            http_response_code(400);
            echo json_encode(['message' => 'Données manquantes']);
            exit;
        }
        
        // Vérifier l'action pour différencier connexion, inscription, déconnexion
        if (isset($data->action)) {
            switch ($data->action) {
                case 'register':
                    handleRegistration($data, $utilisateur);
                    break;
                case 'logout':
                    handleLogout();
                    break;
                default:
                    // Si action non reconnue, tentative de connexion standard
                    if (isset($data->email) && isset($data->password)) {
                        handleLogin($data, $utilisateur);
                    } else {
                        http_response_code(400);
                        echo json_encode(['message' => 'Action non reconnue ou données manquantes']);
                    }
            }
        } 
        // Tentative de connexion standard (sans action spécifique)
        else if (isset($data->email) && isset($data->password)) {
            handleLogin($data, $utilisateur);
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'Données requises manquantes']);
        }
        break;

    case 'PUT':
        // ✅ CORRECTION : Vérification d'authentification
        verifyAuth();
        
        if (!$data) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Données manquantes']);
            exit;
        }

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
            $utilisateur->password = $data->password;
        }

        // Exécution de la mise à jour
        if ($utilisateur->update()) {
            // Mettre à jour aussi les données de session si nécessaire
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
        break;

    case 'DELETE':
        // ✅ CORRECTION : Vérification d'authentification
        verifyAuth();
        
        $utilisateur->utilisateur_id = $_SESSION['utilisateur_id'];

        if ($utilisateur->delete()) {
            session_destroy();
            echo json_encode(['success' => true, 'message' => 'Utilisateur supprimé']);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Échec de la suppression']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
        break;
}
?>