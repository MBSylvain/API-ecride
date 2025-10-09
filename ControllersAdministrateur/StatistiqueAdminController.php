<?php
require_once '../config/session.php';
require_once '../config/Database.php';
require_once '../models/administrateur/Statistique.php';
require_once '../Controllers/checkAuth.php';

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Administrateur' && $_SESSION['role'] !== 'Employe')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès réservé à l\'administrateur ou employé']);
    exit;
}

$database = new Database();
$db = $database->connect();
$stat = new Statistique($db);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Paramètre optionnel ?type= pour stats ciblées
        $type = isset($_GET['type']) ? $_GET['type'] : null;
        if ($_SESSION['role'] === 'Administrateur') {
            switch ($type) {
                case 'utilisateurs':
                    $result = $stat->getNbUtilisateurs();
                    break;
                case 'employes':
                    $result = $stat->getNbEmployes();
                    break;
                case 'trajets':
                    $result = $stat->getStatsTrajets();
                    break;
                case 'avis':
                    $result = $stat->getStatsAvis();
                    break;
                case 'suspendus':
                    $result = $stat->getNbSuspendus();
                    break;
                case 'credits':
                    $result = $stat->getStatsCredits();
                    break;
                case 'voitures':
                    $result = $stat->getStatsVoitures();
                    break;
                case 'reservations':
                    $result = $stat->getStatsReservations();
                    break;
                case null:
                default:
                    $result = $stat->getStatsGlobales();
                    break;
            }
        } else {
            // Employé : accès uniquement au nombre de covoiturages par jour
            $stats = $stat->getStatsGlobales();
            $result = [
                'covoiturages_par_jour' => $stats['covoiturages_par_jour']
            ];
        }
        echo json_encode($result);
        break;
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
        break;
}
