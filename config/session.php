<?php
// Liste des origines autorisées (Vercel + localhost)
$allowed_origins = [
    "https://ecoride-9628hw1kl-sylvains-projects-15c39aad.vercel.app",
    "https://ecoride-hazel.vercel.app",
    "https://ecoride-git-master-sylvains-projects-15c39aad.vercel.app",
    "https://ecoride-b54ju839x-sylvains-projects-15c39aad.vercel.app",
    "https://ecoride-navplvv1w-sylvains-projects-15c39aad.vercel.app",
    "https://ecoride-90kx8i5kx-sylvains-projects-15c39aad.vercel.app",
    "http://localhost:3000",
    "http://localhost:5173"
];

// Vérification de l'origine
if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
    header("Access-Control-Allow-Credentials: true");
} else {
    http_response_code(403);
    exit; // refuse proprement si origine non autorisée
}

// Headers CORS
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Max-Age: 3600");

// Réponse immédiate pour les requêtes OPTIONS (pré-vol CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Démarrage sécurisé de la session
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 24 * 60 * 60, // 24 heures
        'path' => '/',
        'secure' => true,           // obligatoire en HTTPS
        'httponly' => true,
        'samesite' => 'None'        // indispensable pour cross-domain
    ]);
    session_start();
}
?>