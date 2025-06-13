<?php
session_start();
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

if (!isset($_SESSION['utilisateur_id'])) {
    echo json_encode(['success' => false]);
    exit();
}

echo json_encode([
    'success' => true,
    'user' => [
        'id' => $_SESSION['utilisateur_id'],
        'nom' => $_SESSION['nom'],
        'prenom' => $_SESSION['prenom'],
        'email' => $_SESSION['email']
    ]
]);
?>