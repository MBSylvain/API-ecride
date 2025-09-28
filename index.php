<?php
// filepath: c:\xampp\htdocs\api\index.php

// Exemple de point d'entrée pour votre API
header('Content-Type: application/json');

// Inclure les fichiers nécessaires
require_once './config/session.php';
require_once './Controllers/checkAuth.php';

// Exemple de réponse par défaut
echo json_encode([
    'success' => true,
    'message' => 'Bienvenue sur l\'API !'
]);