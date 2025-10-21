<?php
require_once '../config/session.php';

// Inclusions
include_once '../config/Database.php';
include_once '../models/Utilisateur.php';
include_once '../Controllers/checkAuth.php';
include_once '../models/Voiture.php';
include_once '../utils/NotificationMails.php';

// Initialize database connection
$database = new Database();
$db = $database->connect();
if (!$db) {
    http_response_code(500);
    echo json_encode(array('message' => 'Erreur de connexion à la base de données'));
    exit;
}
$voiture = new Voiture($db);

// Vérifiez l'authentification
verifyAuth();

// Récupération de la méthode HTTP et des données
$method = $_SERVER['REQUEST_METHOD'];
// Récupération des données JSON ou POST selon le format
$data =json_decode(file_get_contents("php://input"));
// Récupérer l'action soit de $_POST soit de $data (JSON)
$utilisateur_id = null;
if (isset($_SESSION['utilisateur_id'])) {
    $utilisateur_id = $_SESSION['utilisateur_id'];
} elseif (isset($data->utilisateur_id)) {
    $utilisateur_id = $data->utilisateur_id;
};
$action = null;
if (isset($_POST['action'])) {
    $action = $_POST['action'];
} elseif (isset($data->action)) {
    $action = $data->action;
};

// Déterminer la méthode appropriée
if ($method == 'POST' && $action == 'PUT') {
    // Si la méthode est PUT, on la change
    $method = 'PUT';
} elseif ($method == 'POST' && $action == 'DELETE') {
    // Si la méthode est DELETE, on la change
    $method = 'DELETE';
} else {
    $method = $_SERVER['REQUEST_METHOD'];
};
// Si l'utilisateur n'est pas authentifié, on renvoie une erreur 401
if (!$utilisateur_id && ($method == 'POST' || $method == 'PUT')) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit;
};

switch ($method) {
   case 'GET':
    // Récupération correcte des paramètres
    $voiture_id = isset($_GET['voiture_id']) ? $_GET['voiture_id'] : null;
    $utilisateur_id = isset($_GET['utilisateur_id']) ? $_GET['utilisateur_id'] : null;
    
    // Logs de diagnostic
    error_log('voiture_id dans l\'URL: ' . ($voiture_id ?? 'Non défini'));
    error_log('utilisateur_id dans l\'URL: ' . ($utilisateur_id ?? 'Non défini'));
    
    if ($voiture_id) {
        error_log('Exécution de read_single');
        // Recherche d'une voiture spécifique par ID
        $voiture->voiture_id = $voiture_id;
        if ($voiture->read_single()) {
            echo json_encode([
                'voiture_id' => $voiture->voiture_id,
                'modele' => $voiture->modele,
                'immatriculation' => $voiture->immatriculation,
                'energie' => $voiture->energie,
                'couleur' => $voiture->couleur,
                'date_premiere_immatriculation' => $voiture->date_premiere_immatriculation,
                'nombre_places' => $voiture->nombre_places,
                'photo_url' => $voiture->photo_url,
                'description' => $voiture->description
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Voiture non trouvée'
            ]);
        }
    }
    // Récupérer l'utilisateur_id si voiture_id n'est pas fourni
    else if ($utilisateur_id) {
        error_log('Exécution de read_by_user');
        // Recherche de voitures par utilisateur
        $voiture->utilisateur_id = $utilisateur_id;
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
            echo json_encode([]);
        }
    } else {
        error_log('Exécution de read (toutes les voitures)');
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

        // Vérification unicité immatriculation via le modèle
        if ($voiture->immatriculationExists($voiture->immatriculation)) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Immatriculation déjà utilisée']);
            exit;
        }

        // Create the car and handle the relationship
        if ($voiture->create()) {
            // Notification création voiture
            require_once '../utils/NotificationMails.php';
            require_once '../models/Utilisateur.php';
            $utilisateurModel = new Utilisateur($db);
            $utilisateurModel->utilisateur_id = $utilisateur_id;
            $utilisateurModel->read_single();
            $user = [
                'email' => $utilisateurModel->email,
                'prenom' => $utilisateurModel->prenom,
                'nom' => $utilisateurModel->nom
            ];
            if ($user && isset($user['email'])) {
                $notifSent = sendVoitureNotification($user['email'], $user, $voiture, 'création');
            }
            http_response_code(201);
            echo json_encode(['message' => 'Voiture créée avec succès', 'voiture_id' => $voiture->voiture_id, 'notification_envoyee' => $notifSent]);
        } else {
            http_response_code(500);
            echo json_encode(array('message' => 'Échec de la création'));
        }
        break;
        
    
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));

        //var_dump($data);
        if (!$data) {
            http_response_code(400);
            echo json_encode(array('message' => 'Données invalides'));
            break;
        }

    // Assigner les valeurs dans le même ordre et avec les mêmes colonnes que dans le modèle Voiture
    $voiture->voiture_id = isset($data->voiture_id) ? $data->voiture_id : (isset($_GET['id']) ? $_GET['id'] : null);
    $voiture->marque = $data->marque ?? null;
    $voiture->modele = $data->modele ?? null;
    $voiture->immatriculation = $data->immatriculation ?? null;
    $voiture->energie = $data->energie ?? null;
    $voiture->couleur = $data->couleur ?? null;
    $voiture->date_premiere_immatriculation = $data->date_premiere_immatriculation ?? null;
    $voiture->nombre_places = $data->nombre_places ?? null;
    $voiture->photo_url = $data->photo_url ?? null;
    $voiture->description = $data->description ?? null;
    $voiture->utilisateur_id = $utilisateur_id;

        // Ajout de logs pour vérifier les données transmises à update
        error_log('Données transmises à update: ' . json_encode([
            'voiture_id' => $voiture->voiture_id,
            'modele' => $voiture->modele,
            'immatriculation' => $voiture->immatriculation,
            'energie' => $voiture->energie,
            'couleur' => $voiture->couleur,
            'date_premiere_immatriculation' => $voiture->date_premiere_immatriculation,
            'nombre_places' => $voiture->nombre_places,
            'photo_url' => $voiture->photo_url,
            'description' => $voiture->description
        ]));

        if ($voiture->update()) {
            // Notification modification voiture
            require_once '../utils/NotificationMails.php';
            require_once '../models/Utilisateur.php';
            $utilisateurModel = new Utilisateur($db);
            $utilisateurModel->utilisateur_id = $utilisateur_id;
            $utilisateurModel->read_single();
            $user = [
                'email' => $utilisateurModel->email,
                'prenom' => $utilisateurModel->prenom,
                'nom' => $utilisateurModel->nom
            ];
            if ($user && isset($user['email'])) {
            }
            $notifSent = sendVoitureNotification($user['email'], $user, $voiture, 'modification');
            echo json_encode([
                'success' => true,
                'message' => 'Voiture mise à jour avec succès',
                'voiture_id' => $voiture->voiture_id,
                'modele' => $voiture->modele,
                'immatriculation' => $voiture->immatriculation,
                'energie' => $voiture->energie,
                'couleur' => $voiture->couleur,
                'date_premiere_immatriculation' => $voiture->date_premiere_immatriculation,
                'nombre_places' => $voiture->nombre_places,
                'photo_url' => $voiture->photo_url,
                'description' => $voiture->description,
                'notification_envoyee' => $notifSent
            ]);

        } else {
            http_response_code(500);
            echo json_encode(array('message' => 'Échec de la mise à jour'));

        }
    break;

    case 'DELETE':
    
    // Pour DELETE, on lit les données de la requête
    $data = json_decode(file_get_contents("php://input"));

    $voiture_id = $data->voiture_id ?? null;
    if (!$voiture_id && isset($_GET['voiture_id'])) {
        $voiture_id = $_GET['voiture_id'];
    }
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
        // Notification suppression voiture
        require_once '../utils/NotificationMails.php';
        require_once '../models/Utilisateur.php';
        $utilisateurModel = new Utilisateur($db);
        $utilisateurModel->utilisateur_id = $utilisateur_id;
        $utilisateurModel->read_single();
        $user = [
            'email' => $utilisateurModel->email,
            'prenom' => $utilisateurModel->prenom,
            'nom' => $utilisateurModel->nom
        ];
        if ($user && isset($user['email'])) {
        }
        http_response_code(200);
        $notifSent = sendVoitureNotification($user['email'], $user
        , $voiture, 'suppression');

        echo json_encode([
            'message' => 'Voiture supprimée avec succès',
            'deleted_id' => $voiture_id,
            'notification_envoyee' => $notifSent
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'message' => 'Échec de la suppression',
        ]);
    }
    break;
}
?>