<?php
require_once '../config/session.php';
include_once '../config/Database.php';

// Contrôleur Statistique
$database = new Database();
$db = $database->connect();
if (!$db) {
    http_response_code(500);
    echo json_encode(['message' => 'Erreur de connexion à la base de données']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['action'])) {
            $action = $_GET['action'];
            if ($action === 'covoiturages_par_jour') {
                $query = "SELECT DATE(date_depart) as jour, COUNT(*) as total FROM trajets GROUP BY jour ORDER BY jour DESC LIMIT 30";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['data' => $result]);
            } elseif ($action === 'credits_par_jour') {
                $query = "SELECT DATE(date_action) as jour, SUM(montant) as total_credits FROM historique_actions WHERE type_action = 'gain_credit' GROUP BY jour ORDER BY jour DESC LIMIT 30";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['data' => $result]);
            } elseif ($action === 'vue_admin') {
                // Statistiques globales pour l'admin
                $stats = [];
                // Total utilisateurs
                $query = "SELECT COUNT(*) as total_utilisateurs FROM utilisateurs WHERE compte_actif = 1";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $stats['total_utilisateurs'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_utilisateurs'];

                // Total trajets
                $query = "SELECT COUNT(*) as total_trajets FROM trajets";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $stats['total_trajets'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_trajets'];

                // Trajets annulés
                $query = "SELECT COUNT(*) as trajets_annules FROM trajets WHERE statut = 'annulée'";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $stats['trajets_annules'] = $stmt->fetch(PDO::FETCH_ASSOC)['trajets_annules'];

                // Trajets problématiques (supposons qu'il y a une table ou un champ pour ça)
                // Il n'y a pas de champ 'probleme_signalement' dans la table trajets, mais il y a un historique_actions pour les annulations/suspensions
                $query = "SELECT COUNT(*) as trajets_problemes FROM historique_actions WHERE type_action IN ('annulation','suspension')";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $stats['trajets_problemes'] = $stmt->fetch(PDO::FETCH_ASSOC)['trajets_problemes'];

                // Répartition véhicules (électriques vs thermiques)
                $query = "SELECT energie, COUNT(*) as total FROM voiture GROUP BY energie";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $stats['vehicules_repartition'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Avis validés/refusés
                $query = "SELECT statut, COUNT(*) as total FROM avis GROUP BY statut";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $stats['avis_statut'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

                echo json_encode(['data' => $stats]);
            } else {
                http_response_code(400);
                echo json_encode(['message' => 'Action statistique non reconnue']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'Action statistique manquante']);
        }
        break;
    default:
        http_response_code(405);
        echo json_encode(['message' => 'Méthode non autorisée']);
        break;
}
?>
