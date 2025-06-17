<?php
// En-têtes CORS
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Max-Age: 3600");
//session_start();
include_once '../config/Database.php';
include_once '../models/Trajet.php';

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

// Handle special case for search functionality
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'SEARCH') {
    $method = 'SEARCH';
    $ville_depart = isset($_GET['ville_depart']) ? htmlspecialchars($_GET['ville_depart']) : '';
$ville_arrivee = isset($_GET['ville_arrivee']) ? htmlspecialchars($_GET['ville_arrivee']) : '';
$date_depart = isset($_GET['date_depart']) ? htmlspecialchars($_GET['date_depart']) : '';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'PUT') {
    $method = 'PUT';

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'DELETE') {
    $method = 'DELETE';

    
} else {
    $method = $_SERVER['REQUEST_METHOD'];
}

// Gestion des différentes méthodes
switch ($method) {
    case 'GET':
        if (isset($utilisateur_id)) {
            $trajet->trajet_id = $utilisateur_id;
            if ($trajet->read_by_user($utilisateur_id )) {
                $trajet_arr = array(
                    'trajet_id' => $trajet->trajet_id,
                    'ville_depart' => $trajet->ville_depart,
                    'ville_arrivee' => $trajet->ville_arrivee,
                    'adresse_depart' => $trajet->adresse_depart,
                    'adresse_arrivee' => $trajet->adresse_arrivee,
                    'date_depart' => $trajet->date_depart,
                    'heure_depart' => $trajet->heure_depart,
                    'nombre_places' => $trajet->nombre_places,
                    'prix' => $trajet->prix,
                    'description' => $trajet->description,
                    'bagages_autorises' => $trajet->bagages_autorises,
                    'fumeur_autorise' => $trajet->fumeur_autorise,
                    'animaux_autorises' => $trajet->animaux_autorises,
                    'statut' => $trajet->statut,
                    'utilisateur_id' => $trajet->utilisateur_id,
                    'voiture_id' => $trajet->voiture_id,
                    'date_creation' => $trajet->date_creation
                );
                echo json_encode($trajet_arr);
            } else {
                http_response_code(404);
                echo json_encode(array('message' => 'Trajet non trouvé'));
            }
        } else {
            $result = $trajet->read();
            if ($result) {
            $trajets_arr = $result;
            echo json_encode([
                'message' => 'La totalité desTrajets récupérés avec succès',
                'data' => $trajets_arr
            ]);
            } else {
                http_response_code(500);
                echo json_encode(array('message' => 'Erreur lors de la récupération des trajets'));
            }
        }
        break;
    case 'SEARCH':
        if (isset($_GET['ville_depart']) && isset($_GET['ville_arrivee']) && isset($_GET['date_depart'])) {
            $ville_depart = $_GET['ville_depart'];
            $ville_arrivee = $_GET['ville_arrivee'];
            $date_depart = $_GET['date_depart'];

            $result = $trajet->filtre_by_searchbar($ville_depart, $ville_arrivee, $date_depart);
            if ($result) {
                echo json_encode($result);
                echo json_encode(array('message' => 'Trajets trouvés avec succès')); 
                exit;
            } else {
                http_response_code(404);
                echo json_encode(array('message' => 'Aucun trajet trouvé'));
                exit;
            }
        } else {
            http_response_code(400);
            echo json_encode(array('message' => 'Paramètres de recherche manquants'));
        }
        break;
            
    case 'POST':
        $data = (object)$_POST;
        
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
        $trajet->fumeur_autorise = isset($data->fumeur_autorise) ? 1 : 0;
        $trajet->animaux_autorises = isset($data->animaux_autorises) ? 1 : 0;
        $trajet->statut = 'planifié';
        $trajet->utilisateur_id = $utilisateur_id;
        $trajet->voiture_id = $data->voiture_id;

        if ($trajet->create()) {
            http_response_code(201);
            echo json_encode([
                'message' => 'Trajet créé avec succès',
                'trajet_id' => $trajet->trajet_id
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Échec de la création du trajet']);
        }
        break;

    case 'PUT':
        $data =(array)$_POST;
        var_dump($data);
        var_dump($data['trajet_id']);
        // recuperation de l'id du trajet
        if (!$data) {
            http_response_code(400);
            echo json_encode(array('message' => 'Données invalides'));
            break;
        }
         $trajet= new Trajet($db);
         // recuperation de l'id du trajet du formulaire
         $trajet_id= $data['trajet_id'];
         
        // Récupération du trajet existant
        $trajet->trajet_id = $trajet_id;
        if (!$trajet->read_single($trajet_id)) {
            http_response_code(404);
            echo json_encode(array('message' => 'Trajet non trouvé'));
            break;
        }
        $trajets = $trajet;
        // debugging line to check the input data
        var_dump(json_encode($trajets));
       
        if (!$trajets) {
            http_response_code(404);
            echo json_encode(array('message' => 'Trajet non trouvé'));
            break;
        }

        // Assignation des valeurs
        $trajet->ville_depart = $data->ville_depart ?? $trajet->ville_depart;
        $trajet->ville_arrivee = $data->ville_arrivee ?? $trajet->ville_arrivee;
        $trajet->adresse_depart = $data->adresse_depart ?? $trajet->adresse_depart;
        $trajet->adresse_arrivee = $data->adresse_arrivee ?? $trajet->adresse_arrivee;
        $trajet->date_depart = $data->date_depart ?? $trajet->date_depart;
        $trajet->heure_depart = $data->heure_depart ?? $trajet->heure_depart;
        $trajet->nombre_places = $data->nombre_places ?? $trajet->nombre_places;
        $trajet->prix = $data->prix ?? $trajet->prix;
        $trajet->description = $data->description ?? $trajet->description;
        $trajet->bagages_autorises = $data->bagages_autorises ?? $trajet->bagages_autorises;
        $trajet->fumeur_autorise = $data->fumeur_autorise ?? $trajet->fumeur_autorise;
        $trajet->animaux_autorises = $data->animaux_autorises ?? $trajet->animaux_autorises;
        $trajet->voiture_id = $data->voiture_id ?? $trajet->voiture_id;

        if ($trajet->update()) {
            echo json_encode(array('message' => 'Trajet mis à jour'));
        } else {
            http_response_code(500);
            echo json_encode(array('message' => 'Échec de la mise à jour'));
        }
        break;

    case 'DELETE':
        //$data = json_decode(file_get_contents("php://input"));
        $data = (array)$_POST;
        if (!$data || !isset($data->trajet_id)) {
            http_response_code(400);
            echo json_encode(array('message' => 'ID de trajet manquant'));
            break;
        }

        $trajet->trajet_id = $data['trajet_id'];
        if ($trajet->delete()) {
            echo json_encode(array('message' => 'Trajet supprimé'));
        } else {
            http_response_code(500);
            echo json_encode(array('message' => 'Échec de la suppression'));
        }
        break;
}
?>