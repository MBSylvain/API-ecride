<?php
//require_once '../config/session.php';
require_once '../config/session.php';

/**
 * Vérifie si l'utilisateur est authentifié et gère les sessions.
 * Détruit la session après un timeout ou si non authentifié.
 */
function verifyAuth() {
    $timeout = 3600; // 1 heure

    // Vérifie si la session a expiré
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
        session_unset();
        session_destroy();
        http_response_code(401);
        echo json_encode([
            'isAuthenticated' => false,
            'message' => 'Session expirée'
        ]);
        exit();
    }

    // Vérifie si l'utilisateur est authentifié
    if (!isset($_SESSION['utilisateur_id'])) {
        http_response_code(401);
        echo json_encode([
            'isAuthenticated' => false,
            'message' => 'Non authentifié'
        ]);
        exit();
    }

    // Mettre à jour l'heure de la dernière activité
    $_SESSION['last_activity'] = time();
}

// === GESTION DES REQUÊTES OPTIONS (CORS Pré-vol) ===
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// logique pour vérifier l'authentification
// Si on appelle directement ce fichier en GET, on retourne le statut d'authentification
$current_file = basename($_SERVER['PHP_SELF']);
if ($current_file === 'checkAuth.php' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    
    // Appliquer la vérification d'authentification
    if (isset($_SESSION['utilisateur_id'])) {
        // Utilisateur connecté
        echo json_encode([
            'isAuthenticated' => true,
            'message' => 'Utilisateur authentifié',
            'utilisateur_id' => $_SESSION['utilisateur_id'],
            'nom' => $_SESSION['nom'] ?? null,
            'email' => $_SESSION['email'] ?? null,
            'role' => $_SESSION['role'] ?? null
        ]);
    } else {
        // Utilisateur non connecté
        echo json_encode([
            'isAuthenticated' => false,
            'message' => 'Non authentifié'
        ]);
    }
    exit();
}

// Déconnexion de l 'utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le content-type pour gérer JSON et form-data
    $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
    
    if ($contentType === "application/json") {
        // Récupérer les données JSON
        $content = file_get_contents("php://input");
        $data = json_decode($content, true);
        $action = $data['action'] ?? '';
    } else {
        // Récupérer les données form
        $action = $_POST['action'] ?? '';
    }
    
    if ($action === 'logout') {
        try {
            // Démarrer la session si pas déjà fait
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            // Sauvegarder l'ID utilisateur pour les logs
            $user_id = $_SESSION['utilisateur_id'] ?? null;
            
            // Nettoyer la session
            session_unset();
            session_destroy();
            
            // Réponse JSON cohérente
            header('Content-Type: application/json');
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Déconnexion réussie',
                'isAuthenticated' => false
            ]);
            
            exit();
            
        } catch (Exception $e) {
            error_log("Erreur déconnexion: " . $e->getMessage());
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de la déconnexion',
                'error' => $e->getMessage()
            ]);
            exit();
        }
    }
}