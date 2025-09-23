<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Max-Age: 3600");
session_start();
var_dump($_SESSION);
/**
 * Vérifie si l'utilisateur est authentifié et gère les sessions.
 * Détruit la session après un timeout ou si non authentifié.
 */
function verifyAuth() {
    // Durée maximale d'inactivité (en secondes)
    $timeout = 1800; // 30 minutes

    // Vérifiez si la session a expiré
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
        // Détruire la session si elle a expiré
        session_unset();
        session_destroy();
        http_response_code(401); // Non autorisé
        echo json_encode(['message' => 'Session expirée']);
        exit();
    }

    // Vérifiez si l'utilisateur est authentifié
    if (!isset($_SESSION['utilisateur_id'])) {
        http_response_code(401); // Non autorisé
        echo json_encode(['message' => 'Non authentifié']);
        exit();
    }

    // Mettre à jour l'heure de la dernière activité
    $_SESSION['last_activity'] = time();
};

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Vérifiez si l'utilisateur est connecté
    if (isset($_SESSION['utilisateur_id'])) {
        http_response_code(200); // OK
        echo json_encode([
            'success' => true,
            'message' => 'Utilisateur authentifié',
            'utilisateur_id' => $_SESSION['utilisateur_id'],
            'nom' => $_SESSION['nom'] ?? null,
            'email' => $_SESSION['email'] ?? null,
            'role' => $_SESSION['role'] ?? null
        ]);
    } else {
        http_response_code(401); // Non autorisé
        echo json_encode(['success' => false, 'message' => 'Utilisateur non authentifié']);
    }
    exit();
};

