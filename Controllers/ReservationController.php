<?php
require_once '../config/session.php';

// Inclusions
include_once '../config/Database.php';
include_once '../models/Utilisateur.php';
include_once '../Controllers/checkAuth.php';
include_once '../models/Reservation.php';

//Connexion à la base de données
$database = new Database();
$db = $database->connect();
if (!$db) {
    http_response_code(500);
    echo json_encode(['message' => 'Échec de la connexion à la base de données']);
    exit();
};
// Instanciation du modèle Reservation
$reservation = new Reservation($db);
// Vérifiez l'authentification
verifyAuth();


// Vérification de la méthode de la requête
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Répondre à la requête OPTIONS
    http_response_code(204);
    exit();
};
// Vérification de l'ID de l'utilisateur
if (isset($_SESSION['utilisateur_id'])) {
    $utilisateur_id = $_SESSION['utilisateur_id'];
} elseif (isset($_GET['utilisateur_id'])) {
    $utilisateur_id = $_GET['utilisateur_id'];
} elseif (isset($data['utilisateur_id'])) {
    $utilisateur_id = $data['utilisateur_id'];
}

// Récupération de la méthode de la requête
$method = $_SERVER['REQUEST_METHOD'];

// Handle method overrides for HTML forms
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['_method'])) {
        if ($_POST['_method'] === 'PUT') {
            $method = 'PUT';
            $data = $_POST;
        } elseif ($_POST['_method'] === 'DELETE') {
            $method = 'DELETE';
            $data = $_POST;
        }
    } else {
        // Regular POST processing
        $data = json_decode(file_get_contents("php://input"));
        if ($data === null) {
            $data = $_POST;
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Process any GET parameters if needed
    if (isset($_GET['utilisateur_id'])) {
        $_utilisateur_id = $_GET['utilisateur_id'];
    }
} else {
    // For PUT, DELETE, etc. get the input data
    $data = json_decode(file_get_contents("php://input"));
}

switch ($method) {
    case 'GET':
        if(isset($_utilisateur_id)) {
            // Utiliser la méthode read_by_user
            $user_id = $_utilisateur_id;
            $reservations = $reservation->read_by_user($utilisateur_id);
            
            if(empty($reservations)) {
                echo json_encode(['message' => 'Aucune réservation trouvée']);
            } else {
                echo json_encode($reservations);
            }
        }
        else if(isset($_GET['trajet_id'])) {
            $reservation->trajet_id = $_GET['trajet_id'];
            $result = $reservation->read_by_trajet();
            
            $reservations_arr = array();
            while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                array_push($reservations_arr, $row);
            }
            
            echo json_encode($reservations_arr);
        }
        else if(isset($_GET['reservationId'])) {
            $reservation->reservation_id = $_GET['reservationId'];
            if($reservation->read_single()) {
                $reservation_arr = [
                    'reservation_id' => $reservation->reservation_id,
                    'utilisateur_id' => $reservation->utilisateur_id,
                    'trajet_id' => $reservation->trajet_id,
                    'nombre_places_reservees' => $reservation->nombre_places_reservees,
                    'statut' => $reservation->statut,
                    'date_reservation' => $reservation->date_reservation,
                    'date_confirmation' => $reservation->date_confirmation,
                    'point_rdv' => $reservation->point_rdv,
                    'commentaire' => $reservation->commentaire,
                ];
                echo json_encode($reservation_arr);
            } else {
                echo json_encode(['message' => 'Réservation non trouvée']);
            }
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        
        // Vérification des données reçues
        if (!$data || !isset($data->utilisateur_id) || !isset($data->trajet_id) || !isset($data->nombre_places_reservees)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Données invalides ou incomplètes',
                'required' => ['utilisateur_id', 'trajet_id', 'nombre_places_reservees'],
                'received' => $data
            ]);
            break;
        }
        // Ajout: action de validation
        if (isset($data->action) && $data->action === 'validate') {
            if (!isset($data->reservation_id) || !isset($data->status)) {
                http_response_code(400);
                echo json_encode(['message' => 'Données invalides ou incomplètes']);
                break;
            }
            $reservation->reservation_id = $data->reservation_id;
            $statut = $data->status === 'valide' ? 'confirmée' : 'refusée';
            $reservation->statut = $statut;
            if ($statut === 'confirmée') {
                $reservation->date_confirmation = date('Y-m-d H:i:s');
            }
            if ($reservation->update()) {
                // Récupérer l'email de l'utilisateur concerné
                if ($reservation->read_single()) {
                    $datautilisateur = $reservation->read_single($reservation->utilisateur_id);
                    $email_utilisateur = $datautilisateur['email'] ?? null; 
                    $subject = ($statut === 'confirmée') ? "Votre réservation a été validée" : "Votre réservation a été refusée";
                    $message = ($statut === 'confirmée') ?
                        "Bonjour, votre réservation pour le trajet a été validée." :
                        "Bonjour, votre réservation pour le trajet a été refusée.";
                    $headers = "From: noreply@tonsite.com\r\nContent-Type: text/plain; charset=UTF-8";
                    if ($email_utilisateur) {
                        mail($email_utilisateur, $subject, $message, $headers);
                    }
                }
                echo json_encode(['message' => 'Statut de la réservation mis à jour et email envoyé']);
            } else {
                http_response_code(500);
                echo json_encode(['message' => 'Échec de la mise à jour']);
            }
            break;
        }
        
        // Attribution des données
        $reservation->utilisateur_id = $data->utilisateur_id;
        $reservation->trajet_id = $data->trajet_id;
        $reservation->nombre_places_reservees = $data->nombre_places_reservees;
        $reservation->statut = 'en_attente';
        $reservation->date_reservation = date('Y-m-d H:i:s');
        $reservation->commentaire = isset($data->commentaire) ? $data->commentaire : null;
        $reservation->bagages = isset($data->bagages) ? $data->bagages : 0;

        if ($reservation->create()) {
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Réservation créée avec succès',
                'reservation_id' => $reservation->reservation_id
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Échec de la création de la réservation'
            ]);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        
        if (!$data || !isset($data->reservation_id)) {
            http_response_code(400);
            echo json_encode(['message' => 'Données invalides ou incomplètes']);
            break;
        }

        $reservation->reservation_id = $data->reservation_id;
        
        // Mettre à jour les champs fournis
        if(isset($data->statut)) {
            $valid_statuses = ['confirmée', 'en_attente', 'annulée', 'refusée'];
            if(!in_array($data->statut, $valid_statuses)) {
                http_response_code(400);
                echo json_encode(['message' => 'Statut invalide']);
                break;
            }
            $reservation->statut = $data->statut;
            
            // Si confirmation, définir date_confirmation
            if ($data->statut == 'confirmée') {
                $reservation->date_confirmation = date('Y-m-d H:i:s');
            }
        }
        
        if(isset($data->nombre_places_reservees)) {
            $reservation->nombre_places_reservees = $data->nombre_places_reservees;
        }
        
        if(isset($data->commentaire)) {
            $reservation->commentaire = $data->commentaire;
        }
        
        if(isset($data->point_rdv)) {
            $reservation->point_rdv = $data->point_rdv;
        }

        if ($reservation->update()) {
            echo json_encode(['message' => 'Réservation mise à jour']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Échec de la mise à jour']);
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));

        if (!isset($data->reservation_id)) {
            http_response_code(400);
            echo json_encode(['message' => 'ID de réservation manquant']);
            break;
        }

        $reservation->reservation_id = $data->reservation_id;

        if($reservation->delete()) {
            echo json_encode(['message' => 'Réservation supprimée']);
        } else {
            echo json_encode(['message' => 'Échec de la suppression']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['message' => 'Méthode non autorisée']);
        break;
}
?>