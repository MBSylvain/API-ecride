<?php
require_once '../config/session.php';
require_once '../config/Database.php';
require_once '../models/Voiture.php';
require_once '../Controllers/checkAuth.php';

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Administrateur' && $_SESSION['role'] !== 'Employe')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès réservé à l\'administrateur ou employé']);
    exit;
}

$database = new Database();
$db = $database->connect();
$voiture = new Voiture($db);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Liste ou détail d'une voiture
        if (isset($_GET['id'])) {
            $result = $voiture->readOne($_GET['id']);
        } else {
            $result = $voiture->readAll();
        }
        echo json_encode($result);
        break;
    case 'POST':
        // Création d'une voiture
        $data = json_decode(file_get_contents('php://input'), true);
        $result = $voiture->create($data);
        echo json_encode($result);
        break;
    case 'PUT':
        // Modification d'une voiture
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID requis']);
            exit;
        }
        $result = $voiture->update($data['id'], $data);
        echo json_encode($result);
        break;
    case 'DELETE':
        // Suppression d'une voiture (admin uniquement)
        if ($_SESSION['role'] !== 'Administrateur') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Seul un administrateur peut supprimer une voiture']);
            exit;
        }
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID requis']);
            exit;
        }
        $result = $voiture->delete($_GET['id']);
        echo json_encode($result);
        break;
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
        break;
}
