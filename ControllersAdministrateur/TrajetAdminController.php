<?php
require_once '../config/session.php';
require_once '../config/Database.php';
require_once '../ModelAdministrateur/Trajet.php';
require_once '../Controllers/checkAuth.php';

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Administrateur' && $_SESSION['role'] !== 'Employe')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès réservé à l\'administrateur ou employé']);
    exit;
}

$database = new Database();
$db = $database->connect();
$trajet = new Trajet($db);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Admin : tous les trajets, Employé : trajets problématiques uniquement
        if ($_SESSION['role'] === 'Administrateur') {
            $result = $trajet->readAll();
        } else {
            // Employé : ne voir que les trajets signalés/problématiques (exemple)
            $result = $trajet->readProblemes();
        }
        echo json_encode($result);
        break;
    case 'DELETE':
        // Seul l'administrateur peut supprimer un trajet
        if ($_SESSION['role'] !== 'Administrateur') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Suppression réservée à l\'administrateur']);
            exit;
        }
        if (!isset($_GET['trajet_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID requis']);
            exit;
        }
        $trajet->trajet_id = $_GET['trajet_id'];
        if ($trajet->delete()) {
            echo json_encode(['success' => true, 'message' => 'Trajet supprimé']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur suppression']);
        }
        break;
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
        break;
}
