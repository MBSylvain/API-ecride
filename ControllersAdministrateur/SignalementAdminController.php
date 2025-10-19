<?php
require_once '../config/session.php';
require_once '../config/Database.php';
require_once '../ModelAdministrateur/Signalement.php';
require_once '../Controllers/checkAuth.php';


$method = $_SERVER['REQUEST_METHOD'];
// Restriction stricte pour GET, PUT, DELETE
if ($method !== 'POST') {
    if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Administrateur' && $_SESSION['role'] !== 'Modérateur' && $_SESSION['role'] !== 'Employe')) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Accès réservé à l\'administrateur, modérateur ou employé']);
        exit;
    }
}

$database = new Database();
$db = $database->connect();
$signalement = new Signalement($db);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $result = $signalement->readOne($_GET['id']);
        } else {
            $result = $signalement->readAll();
        }
        echo json_encode($result);
        break;
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['trajet_id']) || empty($data['utilisateur_id']) || empty($data['motif']) || empty($data['statut'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Champs requis manquants']);
            exit;
        }
        $result = $signalement->create($data);
        echo json_encode(['success' => $result]);
        // Notification création signalement (à l'admin)
        if ($result) {
            require_once '../utils/NotificationMails.php';
            include_once '../models/Utilisateur.php';
            $userModel = new Utilisateur($db);
            $userModel->utilisateur_id = $data['utilisateur_id'];
            $userModel->read_single();
            $user = [
                'prenom' => $userModel->prenom,
                'nom' => $userModel->nom
            ];
            // À adapter : email de l'admin ou équipe support
            $adminEmail = 'admin@ecoride.fr';
            sendSignalementCreationNotification($adminEmail, $data, $user);
        }
        break;
    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['id']) || empty($data['statut']) || empty($data['employe_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Champs requis manquants']);
            exit;
        }
        $result = $signalement->update($data['id'], $data);
        echo json_encode(['success' => $result]);
        // Notifications liées au changement de statut ou affectation
        if ($result) {
            require_once '../utils/NotificationMails.php';
            include_once '../models/Utilisateur.php';
            $signalementData = $signalement->readOne($data['id']);
            // Notifier l'utilisateur ayant signalé
            $userModel = new Utilisateur($db);
            $userModel->utilisateur_id = $signalementData['utilisateur_id'];
            $userModel->read_single();
            $user = [
                'prenom' => $userModel->prenom,
                'nom' => $userModel->nom
            ];
            $userEmail = $userModel->email;
            sendSignalementStatusUpdate($userEmail, $signalementData, $user);
            // Notifier l'employé si affectation
            $employeModel = new Utilisateur($db);
            $employeModel->utilisateur_id = $data['employe_id'];
            $employeModel->read_single();
            $employe = [
                'prenom' => $employeModel->prenom,
                'nom' => $employeModel->nom
            ];
            $employeEmail = $employeModel->email;
            sendSignalementAffectationEmploye($employeEmail, $signalementData, $employe);
            // Si statut traité ou refusé, notifier la résolution
            if (in_array($data['statut'], ['traité', 'refusé'])) {
                sendSignalementResolution($userEmail, $signalementData, $user);
            }
        }
        break;
    case 'DELETE':
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID requis']);
            exit;
        }
        $result = $signalement->delete($_GET['id']);
        echo json_encode(['success' => $result]);
        break;
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
        break;
}
