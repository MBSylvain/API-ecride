<?php
require_once '../config/session.php';
require_once '../config/Database.php';
require_once '../ModelAdministrateur/Avis.php';
require_once '../Controllers/checkAuth.php';

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Administrateur' && $_SESSION['role'] !== 'Employe' && $_SESSION['role'] !== 'Modérateur')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès réservé à l\'administrateur, employé ou modérateur']);
    exit;
}

$database = new Database();
$db = $database->connect();
$avis = new Avis($db);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Admin : tous les avis, Employé : seulement les avis à valider
        if ($_SESSION['role'] === 'Administrateur') {
            $result = $avis->readAll();
        } else if ($_SESSION['role'] === 'Modérateur') {
            // Modérateur : ne voir que les avis en attente de validation
            $result = $avis->readByStatut('modéré' );
        }
        echo json_encode($result);
        break;
    case 'PUT':
        // Valider ou refuser un avis
        $data = json_decode(file_get_contents('php://input'));
        if (empty($data->avis_id) || !isset($data->statut)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID et statut requis']);
            exit;
        }
        // Employé : ne peut que valider/refuser, Admin : peut aussi supprimer ou éditer plus
        if ($_SESSION['role'] === 'Employe' && !in_array($data->statut, ['valide', 'refuse'])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Action non autorisée pour employé']);
            exit;
        }
        $avis->avis_id = $data->avis_id;
        $avis->statut = $data->statut;
        if ($avis->updateStatut()) {
            echo json_encode(['success' => true, 'message' => 'Statut avis modifié']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur modification']);
        }
        break;
    case 'DELETE':
        // Seul l'administrateur peut supprimer un avis
        if ($_SESSION['role'] !== 'Administrateur') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Suppression réservée à l\'administrateur']);
            exit;
        }
        if (!isset($_GET['avis_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID requis']);
            exit;
        }
        $avis->avis_id = $_GET['avis_id'];
        if ($avis->delete()) {
            echo json_encode(['success' => true, 'message' => 'Avis supprimé']);
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
