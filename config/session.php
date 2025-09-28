<?php
// === CORRECTION : Vérifier si la session n'est pas déjà active ===
if (session_status() === PHP_SESSION_NONE) {
    // Configuration des sessions (UNIQUEMENT si session pas encore démarrée)
    session_set_cookie_params([
        'lifetime' => 24 * 60 * 60, // 24 heures
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    
    // Démarrer la session
    session_start();
}

// En-têtes CORS
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Max-Age: 3600");

// Gérer les requêtes OPTIONS (pré-vol CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}


?>