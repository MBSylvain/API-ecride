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
        // Lecture par trajet_id
        else if(isset($_GET['trajet_id'])) {
            $reservation->trajet_id = $_GET['trajet_id'];
            $result = $reservation->read_by_trajet();
            
            $reservations_arr = array();
            while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                array_push($reservations_arr, $row);
            }
            
            echo json_encode($reservations_arr);
        }// Lecture d'une réservation spécifique par reservationId
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
        else if ($_SESSION['role'] !== 'Administrateur') { // Lecture de toutes les réservations (admin seulement)
            http_response_code(403);
            echo json_encode(['message' => 'Accès non autorisé']);
            exit;
        } else {
            // Vérifier si l'utilisateur est admin
            $result = $reservation->read_all();
            $reservations_arr = array();
            while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                array_push($reservations_arr, $row);
            }
            echo json_encode($reservations_arr);
        }
        break;

    case 'POST':
        // Récupérer les données
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

        // Vérification des crédits disponibles
        include_once '../models/Credit.php';
        $creditModel = new Credit($db);
        $creditRows = $creditModel->getCreditsByUser($data->utilisateur_id);
        $soldeCredits = 0;
        foreach ($creditRows as $row) {
            $soldeCredits += floatval($row['montant']);
        }
        // Calcul du montant de la réservation (exemple simple)
        include_once '../models/Trajet.php';
        $trajetModel = new Trajet($db);
        $trajet = $trajetModel->read_single_trajet(intval($data->trajet_id));
        if (!$trajet) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Trajet non trouvé']);
            break;
        }
        $prixParPlace = $trajet['prix'];
        $montantReservation = $data->nombre_places_reservees; //** */ $prixParPlace*/;
        if ($soldeCredits < $montantReservation) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'Crédits insuffisants pour effectuer la réservation',
                'solde' => $soldeCredits,
                'montant_requis' => $montantReservation
            ]);
            break;
        }

        // Vérification des places disponibles
        include_once '../models/Trajet.php';
        $trajetModel = new Trajet($db);
        $trajet_id = intval($data->trajet_id);
        $trajet = $trajetModel->read_single_trajet($trajet_id);
        if (!$trajet) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Trajet non trouvé']);
            break;
        }
        // Calcul des places restantes
        include_once '../models/Reservation.php';
        $reservationModel = new Reservation($db);
        $reservations = $reservationModel->read_by_trajet($data->trajet_id);
        $places_restantes = $trajetModel->getPlacesRestantes($trajet_id);
        $trajet['places_restantes'] = $places_restantes;
        $nombre_places_reservees = intval($data->nombre_places_reservees);
        //echo json_encode('place Reservee: '.$nombre_places_reservees/$trajet['prix']);
        $places_Reservees = $nombre_places_reservees/$trajet['prix'];

        // Validation 1: Places disponibles suffisantes
        // Validation 0: Aucune place disponible
        if ($places_restantes == 0) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Aucune place disponible pour ce trajet',
            ]);
            exit;
        }

    // Validation 1: Places disponibles suffisantes
    if ($places_Reservees > $trajet['places_restantes']) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Pas assez de places disponibles pour cette réservation',
            'places_demandees' => $places_Reservees,
            'places_restantes' => $places_restantes
        ]);
        exit;
    }

    // Validation 2: Réservation existante
    $existingReservation = $reservationModel->read_by_user_and_trajet($data->utilisateur_id, $data->trajet_id);
    if ($existingReservation) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Vous avez déjà une réservation pour ce trajet',
            'reservation_id' => $existingReservation['id']
        ]);
        exit;
    }

    // Toutes les validations sont passées
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Réservation possible',
        'places_demandees' => $places_Reservees,
        'places_restantes' => $places_restantes,
        'trajet_id' => $data->trajet_id
    ]);
    exit;
        

        // Attribution des données
        $reservation->utilisateur_id = $data->utilisateur_id;
        $reservation->trajet_id = $data->trajet_id;
        $reservation->nombre_places_reservees = $data->nombre_places_reservees;
        $reservation->statut = 'en_attente';
        $reservation->date_reservation = date('Y-m-d H:i:s');
        $reservation->commentaire = isset($data->commentaire) ? $data->commentaire : null;
        $reservation->bagages = isset($data->bagages) ? $data->bagages : 0;

        // Double confirmation (exemple simple)
        // Ici, on suppose qu'une réservation doit être confirmée par le conducteur après la demande
        // On crée la réservation avec statut 'en_attente', puis le conducteur doit la valider (statut 'confirmée')

        if ($reservation->create()) {
            // Débit des crédits utilisateur et plateforme
            // On suppose une méthode debitCredit($utilisateur_id, $montant, $type_operation)
            $creditModel->debitCredit($data->utilisateur_id, 1, 'utilisation'); // Débit utilisateur
            // Suppression du débit plateforme pour éviter l'erreur de contrainte

            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Réservation créée avec succès, en attente de confirmation du conducteur',
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

        // Récupérer la réservation avant suppression pour envoyer le mail et créditer l'utilisateur
        $reservationDetails = $reservation->read_single();
        $utilisateur_id = $reservationDetails['utilisateur_id'] ?? null;
        $trajet_id = $reservationDetails['trajet_id'] ?? null;

        if($reservation->delete()) {
            // Créditer l'utilisateur en cas d'annulation
            include_once '../models/Credit.php';
            $creditModel = new Credit($db);
            $creditModel->debitCredit($utilisateur_id, 1, 'annulation');

            // Envoi de mail aux participants si annulation par le chauffeur
            // On suppose une méthode getParticipantsEmails($trajet_id) qui retourne les emails des participants
            if ($trajet_id) {
                $participants = $reservation->getParticipantsEmails($trajet_id);
                $subject = "Annulation du trajet";
                $message = "Bonjour, le trajet auquel vous avez réservé a été annulé. Vos crédits ont été mis à jour.";
                $headers = "From: noreply@tonsite.com\r\nContent-Type: text/plain; charset=UTF-8";
                foreach ($participants as $email) {
                    mail($email, $subject, $message, $headers);
                }
            }

            echo json_encode(['message' => 'Réservation supprimée, crédits mis à jour et mails envoyés']);
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