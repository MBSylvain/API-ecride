<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../config/Database.php';
include_once '../models/Reservation.php';

$database = new Database();
$db = $database->connect();

$reservation = new Reservation($db);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if(isset($_GET['userId'])) {
            // Utiliser la méthode read_by_user
            $user_id = $_GET['userId'];
            $reservations = $reservation->read_by_user($user_id);
            
            if(empty($reservations)) {
                echo json_encode(['message' => 'Aucune réservation trouvée']);
            } else {
                echo json_encode($reservations);
            }
        }
        else if(isset($_GET['trajetId'])) {
            $reservation->trajet_id = $_GET['trajetId'];
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
                    'montant_total' => $reservation->montant_total,
                    'date_reservation' => $reservation->date_reservation,
                    'date_confirmation' => $reservation->date_confirmation,
                    'point_rdv' => $reservation->point_rdv,
                    'commentaire' => $reservation->commentaire,
                    'bagages' => $reservation->bagages
                ];
                echo json_encode($reservation_arr);
            } else {
                echo json_encode(['message' => 'Réservation non trouvée']);
            }
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));

        $reservation->utilisateur_id = $data->utilisateur_id;
        $reservation->trajet_id = $data->trajet_id;
        $reservation->nombre_places_reservees = $data->nombre_places_reservees;
        $reservation->statut = 'en_attente'; // Format correct pour le statut
        $reservation->montant_total = $data->montant_total ?? 0;
        $reservation->date_reservation = date('Y-m-d H:i:s');
        $reservation->point_rdv = $data->point_rdv ?? null;
        $reservation->commentaire = $data->commentaire ?? null;
        $reservation->bagages = $data->bagages ?? 0;

        if($reservation->create()) {
            echo json_encode(['message' => 'Réservation créée']);
        } else {
            echo json_encode(['message' => 'Échec de la réservation']);
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