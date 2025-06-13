<?php
// En-têtes CORS
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Max-Age: 3600");

session_start();
// Initialiser la variable utilisateur_id depuis la session
$utilisateur_id = isset($_SESSION['utilisateur_id']) ? $_SESSION['utilisateur_id'] : null;


include_once '../config/Database.php';
include_once '../models/Voiture.php';

// Initialize database connection
$database = new Database();
$db = $database->connect();
if (!$db) {
    http_response_code(500);
    echo json_encode(array('message' => 'Erreur de connexion à la base de données'));
    exit;
}
$voiture = new Voiture($db);

// Récupération de la méthode HTTP et des données
$method = $_SERVER['REQUEST_METHOD'];
// Récupération des données JSON ou POST selon le format
$data =json_decode(file_get_contents("php://input"));
//Récupération de l'action envoyer dans le formulaire
// Récupérer l'action soit de $_POST soit de $data (JSON)
$action = null;
if (isset($_POST['action'])) {
    $action = $_POST['action'];
} elseif (isset($data->action)) {
    $action = $data->action;
}

// Déterminer la méthode appropriée
if ($method == 'POST' && $action == 'PUT') {
    // Si la méthode est PUT, on la change
    $method = 'PUT';
} elseif ($method == 'POST' && $action == 'DELETE') {
    // Si la méthode est DELETE, on la change
    $method = 'DELETE';
} else {
    $method = $_SERVER['REQUEST_METHOD'];
}

// Si l'utilisateur n'est pas authentifié, on renvoie une erreur 401
if (!$utilisateur_id && ($method == 'POST' || $method == 'PUT')) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit;
}

switch ($method) {
    case 'GET':
        // Récupérer l'utilisateur_id depuis l'URL
        $user_id_param = isset($_GET['utilisateur_id']) ? $_GET['utilisateur_id'] : null;
        
        if ($user_id_param) {
            // Recherche de voitures par utilisateur
            $voiture->voiture_id = $user_id_param;
            $result = $voiture->read_by_user();
        
            
            if ($result && $result->rowCount() > 0) {
            $voitures = [];
            
                while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                    $voitures[] = [
                        'voiture_id' => $row['voiture_id'],
                        'modele' => $row['modele'],
                        'immatriculation' => $row['immatriculation'],
                        'energie' => $row['energie'],
                        'couleur' => $row['couleur'],
                        'date_premiere_immatriculation' => $row['date_premiere_immatriculation'],
                        'nombre_places' => $row['nombre_places'],
                        'photo_url' => $row['photo_url'],
                        'description' => $row['description']
                    ];
            }
            
                echo json_encode($voitures);
            } else {
                http_response_code(404);
                echo json_encode([]);  // Renvoyer un tableau vide
            }
        } else {
            // Recherche de toutes les voitures
        $result = $voiture->read();
        
        if ($result) {
            $num = $result->rowCount();
            
            if ($num > 0) {
                $voitures_arr = [
                    'success' => true,
                    'count' => $num,
                    'data' => []
                ];

                while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                    $voiture_item = [
                        'voiture_id' => $row['voiture_id'],
                        'modele' => $row['modele'],
                        'immatriculation' => $row['immatriculation'],
                        'energie' => $row['energie'],
                        'couleur' => $row['couleur'],
                        'date_premiere_immatriculation' => $row['date_premiere_immatriculation'],
                        'nombre_places' => $row['nombre_places'],
                        'photo_url' => $row['photo_url'],
                        'description' => $row['description']
                    ];
                    
                    array_push($voitures_arr['data'], $voiture_item);
                }
                
                echo json_encode($voitures_arr);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false, 
                    'message' => 'Aucune voiture trouvée'
                ]);
            }
        } elseif ($user_id_param = isset($_GET['voiture_id']) ? $_GET['voiseur_id'] : null) {
            // Recherche d'une voiture spécifique par ID
            $voiture->voiture_id = $_GET['voiture_id'];
            if ($voiture->read_single()) {
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'voiture_id' => $voiture->voiture_id,
                        'modele' => $voiture->modele,
                        'immatriculation' => $voiture->immatriculation,
                        'energie' => $voiture->energie,
                        'couleur' => $voiture->couleur,
                        'date_premiere_immatriculation' => $voiture->date_premiere_immatriculation,
                        'nombre_places' => $voiture->nombre_places,
                        'photo_url' => $voiture->photo_url,
                        'description' => $voiture->description
                    ]
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Voiture non trouvée'
                ]);
            }
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'message' => 'Erreur lors de la récupération des voitures'
            ]);
        }
        }
    break;


    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        
        // Validate required fields
        $required = ['modele', 'immatriculation', 'energie', 'nombre_places'];
        foreach ($required as $field) {
            if (empty($data->$field)) {
                http_response_code(400);
                echo json_encode(array('message' => 'Le champ ' . $field . ' est requis'));
                exit;
            }
        }

        // Assign all values
        $voiture->marque = $data->marque ?? null;
        $voiture->modele = $data->modele;
        $voiture->immatriculation = $data->immatriculation;
        $voiture->energie = $data->energie;
        $voiture->couleur = $data->couleur ?? null;
        $voiture->date_premiere_immatriculation = $data->date_premiere_immatriculation;
        $voiture->nombre_places = $data->nombre_places;
        $voiture->photo_url = $data->photo_url ?? null;
        $voiture->description = $data->description ?? null;
        $voiture->utilisateur_id = $utilisateur_id;

        // Create the car and handle the relationship
        if ($voiture->create()) {
                        
            http_response_code(201);
            echo json_encode(['message' => 'Voiture créée avec succès', 'voiture_id' => $voiture->voiture_id]);
        } else {
            http_response_code(500);
            echo json_encode(array('message' => 'Échec de la création'));
        }
        break;
        
    
    case 'PUT':
        //$data = json_decode(file_get_contents("php://input"));
        $data = (object)$_POST;

        //var_dump($data);
        if (!$data) {
            http_response_code(400);
            echo json_encode(array('message' => 'Données invalides'));
            break;
        }

        $voiture->voiture_id = $data->voiture_id ?? null;
        $voiture->modele = $data->modele ?? null;
        $voiture->immatriculation = $data->immatriculation ?? null;
        $voiture->energie = $data->energie ?? null;
        $voiture->couleur = $data->couleur ?? null;
        $voiture->date_premiere_immatriculation = $data->date_premiere_immatriculation ?? null;
        $voiture->nombre_places = $data->nombre_places ?? null;
        $voiture->photo_url = $data->photo_url  ?? null;
        $voiture->description = $data->description ?? null;
        $voiture->utilisateur_id = $utilisateur_id ?? null;

        if ($voiture->update()) {
            echo json_encode(array('message' => 'Voiture mise à jour'));
        } else {
            http_response_code(500);
            echo json_encode(array('message' => 'Échec de la mise à jour'));

        }
    break;

    case 'DELETE':
    
    // Pour DELETE, on lit les données de la requête
    $data = (object)$_POST;
    $voiture_id = $_POST['voiture_id'] ?? null;
    // Vérification des données
    if (!isset($voiture_id)) {
        http_response_code(400);
        echo json_encode(['message' => 'ID de voiture manquant']);
        exit;
    }

    // Assignation de l'ID
    $voiture->voiture_id = $voiture_id;
    
    // Vérifier si la voiture existe
    if (!$voiture->read_single()) {
        http_response_code(404);
        echo json_encode(['message' => 'Voiture non trouvée']);
        exit;
    }

    // Tentative de suppression
    if ($voiture->delete()) {
        http_response_code(200);
        echo json_encode([
            'message' => 'Voiture supprimée avec succès',
            'deleted_id' => $voiture_id
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'message' => 'Échec de la suppression',
            'error' => $voiture->getLastError() // Vous devriez ajouter cette méthode à votre classe Voiture
        ]);
    }
    break;
}
?>