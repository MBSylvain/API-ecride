<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('Access-Control-Allow-Origin: *');

// Vérification de session
if (!isset($_SESSION['utilisateur_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Non authentifié']);
    exit();
}

try {
    require_once '../config/Database.php';
    require_once '../models/Dashboard.php';
    
    $database = new Database();
    $db = $database->connect();
    $dashboard = new Dashboard($db, $_SESSION['utilisateur_id']);

    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            $data = $dashboard->getData();
            echo json_encode([
                'success' => true,
                'data' => $data
            ]);
            break;

        case 'PUT':
            $input = json_decode(file_get_contents("php://input"), true);
            if (!$input) {
                http_response_code(400);
                echo json_encode(['error' => 'Données invalides']);
                break;
            }
            // ... logique de mise à jour
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Méthode non autorisée']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>