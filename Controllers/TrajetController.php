<?php
require_once (__DIR__ . '/../config/session.php');

// Inclusions
include_once (__DIR__ . '/../config/Database.php');
include_once (__DIR__ . '/../models/Utilisateur.php');
include_once (__DIR__ . '/../Controllers/checkAuth.php');
include_once (__DIR__ . '/../models/Trajet.php');



// Initialize database connection
$database = new Database();
$db = $database->connect();
if (!$db) {
    http_response_code(500);
    echo json_encode(array('message' => 'Erreur de connexion à la base de données'));
    exit;
}

$trajet = new Trajet($db);
// Récupération de la méthode HTTP
$method = $_SERVER['REQUEST_METHOD'];
// Get the raw input data for PUT and DELETE requests
$input_data = file_get_contents("php://input");
$data = json_decode($input_data, true);

// Vérification de l'ID de l'utilisateur
if (isset($_SESSION['utilisateur_id'])) {
    $utilisateur_id = $_SESSION['utilisateur_id'];
} elseif (isset($_GET['utilisateur_id'])) {
    $utilisateur_id = $_GET['utilisateur_id'];
} elseif (isset($data['utilisateur_id'])) {
    $utilisateur_id = $data['utilisateur_id'];
}

// Gestion de la recherche
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'SEARCH') {
    $method = 'SEARCH';
    $ville_depart = isset($_GET['ville_depart']) ? htmlspecialchars($_GET['ville_depart']) : '';
$ville_arrivee = isset($_GET['ville_arrivee']) ? htmlspecialchars($_GET['ville_arrivee']) : '';
$date_depart = isset($_GET['date_depart']) ? htmlspecialchars($_GET['date_depart']) : '';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'PUT') {
            $method = 'PUT';
        } elseif ($_POST['action'] === 'DELETE') {
            $method = 'DELETE';
        } elseif ($_POST['action'] === 'STATUTS') {
            $method = 'STATUTS';
        } else {
            $method = $_SERVER['REQUEST_METHOD'];
        }
    } elseif (isset($data['action'])) {
        if ($data['action'] === 'PUT') {
            $method = 'PUT';
        } elseif ($data['action'] === 'DELETE') {
            $method = 'DELETE';
        } elseif ($data['action'] === 'STATUTS') {
            $method = 'STATUTS';
        } else {
            $method = $_SERVER['REQUEST_METHOD'];
        }
    } else {
        $method = $_SERVER['REQUEST_METHOD'];
    }
} else {
    $method = $_SERVER['REQUEST_METHOD'];
}

// Gestion des différentes méthodes
switch ($method) {
    case 'GET':
        // Si un ID de trajet est fourni dans l'URL, on récupère ce trajet spécifique
        if (isset($_GET['trajet_id']) && !empty($_GET['trajet_id'])) {
            $trajet->trajet_id = $_GET['trajet_id'];
            $result = $trajet->getUtilisateurIdByTrajetId($trajet->trajet_id);
            $resultdetail= $trajet->read_single();
            if ($result || $resultdetail) {
                
                echo json_encode($resultdetail);
            } else {
                http_response_code(404);
                echo json_encode(['message' => 'Trajet non trouvé']);
            }
        } elseif (isset($utilisateur_id) && $_SESSION['role'] !== 'Administrateur') {
            // Si un ID utilisateur est fourni, on récupère les trajets de cet utilisateur
            $results = $trajet->read_by_user($utilisateur_id);
            if ($results && !empty($results)) {
                echo json_encode($results);
            } else {
                http_response_code(201);
                echo json_encode([
                    'message' => 'Trajet non trouvé',
                    'error' => true
                ]);
            }
        } elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'Administrateur') {
            // Si l'utilisateur est admin, on récupère tous les trajets
            $results = $trajet->read();
            if ($results && !empty($results)) {
                echo json_encode($results);
            } else {
                http_response_code(404);
                echo json_encode([
                    'message' => 'Aucun trajet disponible',
                    'data' => []
                ]);
            }
        }
        break;

case 'SEARCH':
    // Récupération des paramètres de recherche
    $ville_depart = isset($_GET['ville_depart']) ? $_GET['ville_depart'] : null;
    $ville_arrivee = isset($_GET['ville_arrivee']) ? $_GET['ville_arrivee'] : null;
    $date_depart = isset($_GET['date_depart']) ? $_GET['date_depart'] : null;
    $prix_max = isset($_GET['prix_max']) ? $_GET['prix_max'] : null;
    $note_min = isset($_GET['note_min']) ? $_GET['note_min'] : null;
    $ecologique = isset($_GET['ecologique']) ? $_GET['ecologique'] : null;

    // logique de recherche filtrée
    if ($ville_depart !== null || $ville_arrivee !== null || $date_depart !== null || $prix_max !== null || $note_min !== null || $ecologique !== null) {
        $result = $trajet->filtre_by_searchbar($ville_depart, $ville_arrivee, $date_depart, $prix_max, $note_min, $ecologique);

       if ($result && !empty($result)) {
        echo json_encode([
            'success' => true,
            'message' => 'Trajets trouvés',
            'data' => $result
        ]);
    } else if ($date_depart) {
        // Si aucun trajet trouvé, proposer des alternatives ±3 jours
        $date = new DateTime($date_depart);
        $date_min = $date->modify('-3 days')->format('Y-m-d');
        $date_max = (new DateTime($date_depart))->modify('+3 days')->format('Y-m-d');
        $alternatives = $trajet->filtre_by_searchbar($ville_depart, $ville_arrivee, null, $prix_max, $note_min, $ecologique);

        // Filtrer les alternatives sur la plage de dates
        $suggestions = array_filter($alternatives, function($t) use ($date_min, $date_max) {
            return $t['date_depart'] >= $date_min && $t['date_depart'] <= $date_max;
        });

        echo json_encode([
            'success' => false,
            'message' => 'Aucun trajet trouvé à la date demandée. Voici des alternatives proches.',
            'data' => [],
            'suggestions' => ($suggestions)
        ]);
    } else {
            echo json_encode([
                'success' => false,
                'message' => 'Aucun trajet disponible',
                'data' => []
            ]);
        }
        break;
    }
    
        case 'POST':
        // Récupération des données du formulaire
        $data = json_decode(file_get_contents("php://input"));
        // Validation
        $required = ['ville_depart', 'ville_arrivee', 'date_depart', 'nombre_places', 'prix', 'voiture_id'];
        foreach ($required as $field) {
            if (empty($data->$field)) {
                http_response_code(400);
                echo json_encode(array('message' => 'Le champ ' . $field . ' est requis'));
                exit;
            }
        }

        // Set all properties
        $trajet->ville_depart = $data->ville_depart;
        $trajet->ville_arrivee = $data->ville_arrivee;
        $trajet->adresse_depart = $data->adresse_depart ?? null;
        $trajet->adresse_arrivee = $data->adresse_arrivee ?? null;
        $trajet->date_depart = $data->date_depart;
        $trajet->heure_depart = isset($data->heure_depart) ? $data->heure_depart : date('H:i:s', strtotime($data->date_depart));
        $trajet->heure_arrivee = $data->heure_arrivee ?? null;
        $trajet->nombre_places = $data->nombre_places;
        $trajet->prix = $data->prix;
        $trajet->description = $data->description ?? null;
        $trajet->bagages_autorises = isset($data->bagages_autorises) ? 1 : 0;
        //$trajet->fumeur_autorise = isset($data->fumeur_autorise) ? 1 : 0;
        $trajet->animaux_autorises = isset($data->animaux_autorises) ? 1 : 0;
        $trajet->statut = 'planifié';
        $trajet->utilisateur_id = $utilisateur_id;
        $trajet->voiture_id = $data->voiture_id;

        // Vérification du solde de crédits avant création
        include_once '../models/Credit.php';
        $credit = new Credit($db);
        $solde = $credit->getSoldeCredits($utilisateur_id);
        if ($solde < $trajet->prix) {
            http_response_code(400);
            echo json_encode([
                'message' => 'Solde de crédits insuffisant pour créer ce trajet',
                'solde' => $solde,
                'prix_trajet' => $trajet->prix
            ]);
            exit;
        }

        // Création du trajet si solde suffisant
        if ($trajet->create()) {
            // Débit du crédit
            $credit->utilisateur_id = $utilisateur_id;
            $credit->montant = -$trajet->prix;
            $credit->type_operation = 'Création de trajet';
            $credit->commentaire = 'Débit pour création de trajet ID ' . $trajet->trajet_id;
            $debitOk = $credit->debitCredit($utilisateur_id, $credit->montant, $credit->type_operation, $credit->commentaire);

            if ($debitOk) {
                http_response_code(201);
                echo json_encode([
                    'message' => 'Trajet créé avec succès',
                    'trajet_id' => $trajet->trajet_id
                ]);
                // Notification création trajet
                require_once '../utils/NotificationMails.php';
                $utilisateurModel = new Utilisateur($db);
                $utilisateurModel->utilisateur_id = $utilisateur_id;
                $result = $utilisateurModel->read_single();
                $user = null;
                if ($result === true) {
                    $user = [
                        'email' => $utilisateurModel->email,
                        'prenom' => $utilisateurModel->prenom,
                        'nom' => $utilisateurModel->nom
                    ];
                }
                if ($user && isset($user['email'])) {
                    sendTrajetCreationConfirmation($user['email'], [
                        'ville_depart' => $trajet->ville_depart,
                        'ville_arrivee' => $trajet->ville_arrivee
                    ], $user);
                }
            } else {
                // Si le débit échoue, on pourrait supprimer le trajet créé pour garder la cohérence
                $trajet->delete();
                http_response_code(500);
                echo json_encode(['message' => 'Échec du débit de crédit, trajet annulé']);
            }
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Échec de la création du trajet']);
        }
        break;

    case 'PUT':
        // Decode as object, not array (remove the 'true' parameter)
        $data = json_decode(file_get_contents("php://input"));
        
        // Get trajet ID
        $trajet_id = isset($_GET['trajet_id']) ? $_GET['trajet_id'] : 
                    (isset($data->trajet_id) ? $data->trajet_id : null);
        
        if (!$trajet_id) {
            http_response_code(400);
            echo json_encode(['message' => 'ID de trajet manquant']);
            break;
        }
        
        // Set ID and retrieve current trajet
        $trajet->trajet_id = $trajet_id;
        if (!$trajet->read_single()) {
            http_response_code(404);
            echo json_encode(['message' => 'Trajet non trouvé']);
            break;
        }
        
        // Update fields (using object notation)
        $trajet->ville_depart = $data->ville_depart ?? $trajet->ville_depart;
        $trajet->ville_arrivee = $data->ville_arrivee ?? $trajet->ville_arrivee;
        $trajet->adresse_depart = $data->adresse_depart ?? $trajet->adresse_depart;
        $trajet->adresse_arrivee = $data->adresse_arrivee ?? $trajet->adresse_arrivee;
        $trajet->date_depart = $data->date_depart ?? $trajet->date_depart;
        $trajet->heure_depart = $data->heure_depart ?? $trajet->heure_depart;
        $trajet->nombre_places = $data->nombre_places ?? $trajet->nombre_places;
        $trajet->prix = $data->prix ?? $trajet->prix;
        $trajet->description = $data->description ?? $trajet->description;
        $trajet->bagages_autorises = isset($data->bagages_autorises) ? $data->bagages_autorises : $trajet->bagages_autorises;
        $trajet->fumeur_autorise = isset($data->fumeur_autorise) ? $data->fumeur_autorise : $trajet->fumeur_autorise;
        $trajet->animaux_autorises = isset($data->animaux_autorises) ? $data->animaux_autorises : $trajet->animaux_autorises;
        $trajet->voiture_id = $data->voiture_id ?? $trajet->voiture_id;
        
        // Perform update
        if ($trajet->update()) {
            // After update, fetch the updated record
            $trajet->read_single();
            // Notification modification trajet
            require_once '../utils/NotificationMails.php';
            // On suppose une méthode getParticipantsEmails($trajet_id) sur le modèle Reservation
            include_once '../models/Reservation.php';
            $reservationModel = new Reservation($db);
            $participants = $reservationModel->getParticipantsEmails($trajet->trajet_id);
            foreach ($participants as $email) {
                $notifSent = sendTrajetModification($email, [
                    'ville_depart' => $trajet->ville_depart,
                    'ville_arrivee' => $trajet->ville_arrivee
                ], ['prenom' => 'Participant']);
            }
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Trajet mis à jour avec succès',
                'data' => [
                    'trajet_id' => $trajet->trajet_id,
                    'ville_depart' => $trajet->ville_depart,
                    'ville_arrivee' => $trajet->ville_arrivee,
                    'date_depart' => $trajet->date_depart,
                    'heure_depart' => $trajet->heure_depart,
                    'adresse_depart' => $trajet->adresse_depart,
                    'adresse_arrivee' => $trajet->adresse_arrivee,
                    'nombre_places' => $trajet->nombre_places,
                    'prix' => $trajet->prix,
                    'description' => $trajet->description,
                    'bagages_autorises' => $trajet->bagages_autorises,
                    'fumeur_autorise' => $trajet->fumeur_autorise,
                    'animaux_autorises' => $trajet->animaux_autorises,
                    'statut' => $trajet->statut,
                    'utilisateur_id' => $trajet->utilisateur_id,
                    'voiture_id' => $trajet->voiture_id,
                    'date_creation' => $trajet->date_creation,
                    'notification_envoyee' => $notifSent
                ]
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Échec de la mise à jour']);
        }
        break;

    case 'DELETE':
        // Récupérer l'ID depuis l'URL, le body (json ou x-www-form-urlencoded), ou l'input brut
        $trajet_id = null;
        if (isset($_GET['trajet_id'])) {
            $trajet_id = $_GET['trajet_id'];
        } elseif (isset($data['trajet_id'])) {
            $trajet_id = $data['trajet_id'];
        } else {
            // Fallback : parser l'input brut (utile pour certains clients DELETE)
            $rawInput = file_get_contents('php://input');
            if ($rawInput) {
                parse_str($rawInput, $deleteVars);
                if (isset($deleteVars['trajet_id'])) {
                    $trajet_id = $deleteVars['trajet_id'];
                }
            }
        }

        if (!$trajet_id) {
            http_response_code(400);
            echo json_encode(array('message' => 'ID de trajet manquant'));
            exit;
        }

        $trajet->trajet_id = $trajet_id;
        if ($trajet->delete()) {
            // Notification annulation trajet
            require_once '../utils/NotificationMails.php';
            include_once '../models/Reservation.php';
            $reservationModel = new Reservation($db);
            $participants = $reservationModel->getParticipantsEmails($trajet->trajet_id);
            foreach ($participants as $email) {
                sendTrajetCancellation($email, [
                    'ville_depart' => $trajet->ville_depart,
                    'ville_arrivee' => $trajet->ville_arrivee
                ], ['prenom' => 'Participant']);
            }
            echo json_encode(array('message' => 'Trajet supprimé'));
        } else {
            http_response_code(500);
            echo json_encode(array('message' => 'Échec de la suppression'));
        }
        break;
    case 'STATUTS':
        // Decode as object, not array (remove the 'true' parameter)
        $data = json_decode(file_get_contents('php://input'));
        if (isset($data->trajet_id) && isset($data->statut)) {
            $trajet->trajet_id = $data->trajet_id;
            if ($trajet->read_single()) {
                $statutActuel = $trajet->statut;
                $nouveauStatut = $data->statut;
                // Vérification et mise à jour du statut
                $statuts_valides = ['planifié', 'en_cours', 'terminé'];
                if (in_array($nouveauStatut, $statuts_valides)) {
                    $trajet->statut = $nouveauStatut;
                    if ($trajet->update()) {
                        // Notification selon le statut
                        require_once '../utils/NotificationMails.php';
                        include_once '../models/Reservation.php';
                        $reservationModel = new Reservation($db);
                        $participants = $reservationModel->getParticipantsEmails($trajet->trajet_id);
                        foreach ($participants as $email) {
                            if ($nouveauStatut === 'en_cours') {
                                sendTrajetStart($email, [
                                    'ville_depart' => $trajet->ville_depart,
                                    'ville_arrivee' => $trajet->ville_arrivee
                                ], ['prenom' => 'Participant']);
                            } elseif ($nouveauStatut === 'terminé') {
                                sendTrajetArrival($email, [
                                    'ville_depart' => $trajet->ville_depart,
                                    'ville_arrivee' => $trajet->ville_arrivee
                                ], ['prenom' => 'Participant']);
                            }
                        }
                        echo json_encode(array('success' => true, 'status' => $trajet->statut));
                    } else {
                        http_response_code(500);
                        echo json_encode(array('success' => false, 'message' => 'Erreur lors de la mise à jour du statut.'));
                    }
                } else {
                    http_response_code(400);
                    echo json_encode(array('success' => false, 'message' => 'Statut non valide.'));
                }
            } else {
                http_response_code(404);
                echo json_encode(array('success' => false, 'message' => 'Trajet non trouvé.'));
            }
        } else if (isset($data->trajet_id)) {
            $trajet->trajet_id = $data->trajet_id;
            $trajet->read_single();
            echo json_encode(array('status' => $trajet->statut));
        } else {
            http_response_code(400);
            echo json_encode(array('message' => 'ID de trajet manquant'));
        }
        break;
}
?>