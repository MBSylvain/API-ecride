<?php
require_once '../config/session.php';
require_once '../config/Database.php';
require_once '../models/Reservation.php';
require_once '../Controllers/checkAuth.php';

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Administrateur' && $_SESSION['role'] !== 'Employe')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès réservé à l\'administrateur ou employé']);
    exit;
}

$database = new Database();
$db = $database->connect();
$reservation = new Reservation($db);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Liste ou détail d'une réservation
        if (isset($_GET['id'])) {
            $result = $reservation->readOne($_GET['id']);
        } else {
            $result = $reservation->readAll();
        }
        echo json_encode($result);
        break;
    case 'POST':
        // Création d'une réservation
        $data = json_decode(file_get_contents('php://input'), true);
        $result = $reservation->create($data);
        echo json_encode($result);
        break;
    case 'PUT':
        // Modification d'une réservation
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID requis']);
            exit;
        }
        $result = $reservation->update($data['id'], $data);
        echo json_encode($result);
        break;
    case 'DELETE':
        // Suppression d'une réservation (admin uniquement)
        if ($_SESSION['role'] !== 'Administrateur') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Seul un administrateur peut supprimer une réservation']);
            exit;
        }
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID requis']);
            exit;
        }
        $result = $reservation->delete($_GET['id']);
        echo json_encode($result);
        break;
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
        break;
}
