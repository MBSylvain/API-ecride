<?php
require_once '../config/session.php';

// Inclusions
include_once '../config/Database.php';
include_once '../models/Utilisateur.php';
include_once '../Controllers/checkAuth.php';
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

// Gestion de la recherche
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
        } elseif (isset($utilisateur_id)) {
            // Si un ID utilisateur est fourni, on récupère les trajets de cet utilisateur
            $results = $trajet->read_by_user($utilisateur_id);
            if ($results && !empty($results)) {
                echo json_encode($results);
            } else {
                http_response_code(404);
                echo json_encode([
                    'message' => 'Trajet non trouvé',
                    'error' => true
                ]);
            }
        } else {
            // Sinon, on récupère tous les trajets
            $result = $trajet->read();
            if ($result) {
                $trajets_arr = $result;
                echo json_encode([
                    'message' => 'La totalité des Trajets récupérés avec succès',
                    'data' => $trajets_arr
                ]);
            } else {
                http_response_code(500);
                echo json_encode(array('message' => 'Erreur lors de la récupération des trajets'));
            }
        }
        break;
    case 'SEARCH':
        // Récupération des paramètres de recherche
        $ville_depart = isset($_GET['ville_depart']) ? $_GET['ville_depart'] : null;
        $ville_arrivee = isset($_GET['ville_arrivee']) ? $_GET['ville_arrivee'] : null;
        $date_depart = isset($_GET['date_depart']) ? $_GET['date_depart'] : null;
        
        // logique de recherche filtrée
        if ($ville_depart !== null || $ville_arrivee !== null || $date_depart !== null) {
            $result = $trajet->filtre_by_searchbar($ville_depart, $ville_arrivee, $date_depart);
            
            if ($result && !empty($result)) {
                echo json_encode($result);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Aucun trajet trouvé',
                    'data' => []
                ]);
            }
        } else {
            // Aucun paramètre fourni - retourner tous les trajets ou une erreur
            $result = $trajet->read();
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Tous les trajets récupérés',
                    'data' => $result
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Aucun trajet disponible',
                    'data' => []
                ]);
            }
        }
        break;
            
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

                    // Add other fields as needed
                ]
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Échec de la mise à jour']);
        }
        break;

    case 'DELETE':
        // Récupérer l'ID depuis l'URL ou le corps de la requête
$trajet_id = isset($_GET['trajet_id']) ? $_GET['trajet_id'] : 
            (isset($data['trajet_id']) ? $data['trajet_id'] : null);

if (!$trajet_id) {
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