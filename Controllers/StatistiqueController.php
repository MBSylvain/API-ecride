<?php
require_once '../config/session.php';
include_once '../config/Database.php';

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

            // --- Utilisateurs ---
            if ($action === 'utilisateurs') {
                $stats = [];
                // Total utilisateurs
                $query = "SELECT COUNT(*) as total_utilisateurs FROM utilisateurs";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $stats['total_utilisateurs'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_utilisateurs'];

                // Utilisateurs actifs/inactifs
                $query = "SELECT compte_actif, COUNT(*) as total FROM utilisateurs GROUP BY compte_actif";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $actifs = 0; $inactifs = 0;
                foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                    if ($row['compte_actif'] == "Actif" || $row['compte_actif'] == 1) $actifs = $row['total'];
                    else $inactifs = $row['total'];
                }
                $stats['total_utilisateurs_actifs'] = $actifs;
                $stats['total_utilisateurs_inactifs'] = $inactifs;

                // Nouveaux inscrits aujourd'hui
                $query = "SELECT COUNT(*) as nouveaux_inscrits_jour FROM utilisateurs WHERE DATE(date_inscription) = CURDATE()";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $stats['nouveaux_inscrits_jour'] = $stmt->fetch(PDO::FETCH_ASSOC)['nouveaux_inscrits_jour'];

                // Comptes suspendus
                $query = "SELECT COUNT(*) as comptes_suspendus FROM utilisateurs WHERE statut = 'suspendu'";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $stats['comptes_suspendus'] = $stmt->fetch(PDO::FETCH_ASSOC)['comptes_suspendus'];

                // Comptes supprimés
                $query = "SELECT COUNT(*) as comptes_supprimes FROM utilisateurs WHERE statut = 'supprime'";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $stats['comptes_supprimes'] = $stmt->fetch(PDO::FETCH_ASSOC)['comptes_supprimes'];

                // Utilisateurs en attente de validation
                $query = "SELECT COUNT(*) as utilisateurs_en_attente FROM utilisateurs WHERE statut = 'en_attente'";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $stats['utilisateurs_en_attente'] = $stmt->fetch(PDO::FETCH_ASSOC)['utilisateurs_en_attente'];

                echo json_encode($stats);
                exit;
            }

            // Trajets
            if ($action === 'trajets') {
                $stats = [];
                // Total trajets
                $query = "SELECT COUNT(*) as total_trajets FROM trajets";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $stats['total_trajets'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_trajets'];

                // Trajets par jour (30 derniers jours)
                $query = "SELECT DATE(date_depart) as jour, COUNT(*) as total FROM trajets GROUP BY jour ORDER BY jour DESC LIMIT 30";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $stats['trajets_par_jour'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Trajets annulés
                $query = "SELECT COUNT(*) as trajets_annules FROM trajets WHERE statut = 'annulée'";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $stats['trajets_annules'] = $stmt->fetch(PDO::FETCH_ASSOC)['trajets_annules'];

                // Trajets problématiques
                $query = "SELECT COUNT(*) as trajets_problemes FROM historique_actions WHERE type_action IN ('annulation','suspension')";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $stats['trajets_problemes'] = $stmt->fetch(PDO::FETCH_ASSOC)['trajets_problemes'];

                // Trajets écologiques (voiture électrique)
                $query = "SELECT COUNT(*) as trajets_ecologiques FROM trajets t JOIN voiture v ON t.voiture_id = v.voiture_id WHERE v.energie = 'Electrique'";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $stats['trajets_ecologiques'] = $stmt->fetch(PDO::FETCH_ASSOC)['trajets_ecologiques'];

                // Trajets en cours / terminés
                $query = "SELECT statut, COUNT(*) as total FROM trajets GROUP BY statut";
                $stmt = $db->prepare($query);
                $stmt->execute();
                foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                    $stats['trajets_'.$row['statut']] = $row['total'];
                }

                echo json_encode($stats);
                exit;
            }

            // Crédits
            if ($action === 'credits') {
                $stats = [];
                // Total crédits gagnés
                $query = "SELECT SUM(montant) as total_credits FROM credits WHERE type_operation = 'ajout'";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $stats['total_credits'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_credits'];

                // Crédits gagnés par jour (30 derniers jours)
                $query = "SELECT DATE(date_operation) as jour, SUM(montant) as credits FROM credits WHERE type_operation = 'ajout' GROUP BY jour ORDER BY jour DESC LIMIT 30";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $stats['credits_par_jour'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

                echo json_encode($stats);
                exit;
            }

            // Avis 
            if ($action === 'avis') {
                $stats = [];
                // Avis par statut
                $query = "SELECT statut, COUNT(*) as total FROM avis GROUP BY statut";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $stats['avis_statut'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Avis en attente de validation
                $query = "SELECT COUNT(*) as avis_en_attente FROM avis WHERE statut = 'en_attente'";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $stats['avis_en_attente'] = $stmt->fetch(PDO::FETCH_ASSOC)['avis_en_attente'];

                echo json_encode($stats);
                exit;
            }

            // Employés
            if ($action === 'employes') {
                $stats = [];
                // Total employés
                $query = "SELECT COUNT(*) as total_employes FROM utilisateurs WHERE role = 'Employe'";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $stats['total_employes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_employes'];

                // Employés actifs/inactifs/suspendus/en attente
                $query = "SELECT statut, COUNT(*) as total FROM utilisateurs WHERE role = 'Employe' GROUP BY statut";
                $stmt = $db->prepare($query);
                $stmt->execute();
                foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                    $stats['employes_'.$row['statut']] = $row['total'];
                }

                // Nombre d'administrateurs
                $query = "SELECT COUNT(*) as employes_admins FROM utilisateurs WHERE role = 'Administrateur'";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $stats['employes_admins'] = $stmt->fetch(PDO::FETCH_ASSOC)['employes_admins'];

                // Employés ajoutés ce mois
                $query = "SELECT COUNT(*) as employes_ajoutes_ce_mois FROM utilisateurs WHERE role = 'Employe' AND MONTH(date_inscription) = MONTH(CURDATE()) AND YEAR(date_inscription) = YEAR(CURDATE())";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $stats['employes_ajoutes_ce_mois'] = $stmt->fetch(PDO::FETCH_ASSOC)['employes_ajoutes_ce_mois'];

                echo json_encode($stats);
                exit;
            }

            //  Statistiques Voitures 
            if ($action === 'voitures') {
                $stats = [];
                // Nombre total de voitures
                $query = "SELECT COUNT(*) as total_voitures FROM voiture";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $stats['total_voitures'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_voitures'];

                // Voitures par marque
                $query = "SELECT marque, COUNT(*) as total FROM voiture GROUP BY marque ORDER BY total DESC";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $stats['voitures_par_marque'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Voitures par énergie
                $query = "SELECT energie, COUNT(*) as total FROM voiture GROUP BY energie ORDER BY total DESC";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $stats['voitures_par_energie'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Voitures par nombre de places
                $query = "SELECT nombre_places, COUNT(*) as total FROM voiture GROUP BY nombre_places ORDER BY nombre_places";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $stats['voitures_par_places'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

                echo json_encode($stats);
                exit;
            }

            //  Statistiques Réservations 
            if ($action === 'reservations') {
                $stats = [];
                // Nombre total de réservations
                $query = "SELECT COUNT(*) as total_reservations FROM reservations";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $stats['total_reservations'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_reservations'];

                // Réservations par statut
                $query = "SELECT statut, COUNT(*) as total FROM reservations GROUP BY statut ORDER BY total DESC";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $stats['reservations_par_statut'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Réservations par jour (30 derniers jours)
                $query = "SELECT DATE(date_reservation) as jour, COUNT(*) as total FROM reservations GROUP BY jour ORDER BY jour DESC LIMIT 30";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $stats['reservations_par_jour'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Réservations par nombre de places réservées
                $query = "SELECT nombre_places_reservees, COUNT(*) as total FROM reservations GROUP BY nombre_places_reservees ORDER BY nombre_places_reservees";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $stats['reservations_par_places'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Réservations par utilisateur (top 10)
                $query = "SELECT utilisateur_id, COUNT(*) as total FROM reservations GROUP BY utilisateur_id ORDER BY total DESC LIMIT 10";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $stats['reservations_top_utilisateurs'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

                echo json_encode($stats);
                exit;
            }

            //  Répartition véhicules (pour PieChart)
            if ($action === 'vehicules_repartition') {
                $query = "SELECT energie, COUNT(*) as total FROM voiture GROUP BY energie";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($result);
                exit;
            }

            // Action non reconnue 
            http_response_code(400);
            echo json_encode(['message' => 'Action statistique non reconnue']);
            exit;
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'Action statistique manquante']);
            exit;
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['message' => 'Méthode non autorisée']);
        exit;
}
?>