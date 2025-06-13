<?php
// En-têtes CORS
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Max-Age: 3600");


// Démarrer la session
session_start();

include_once '../config/Database.php';
include_once '../models/Avis.php';

$database = new Database();
$db = $database->connect();

$avis = new Avis($db);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if(isset($_GET['id'])) {
            $avis->avis_id = $_GET['id'];
            if($avis->read_single()) {
                $avis_arr = array(
                    'avis_id' => $avis->avis_id,
                    'auteur_id' => $avis->auteur_id,
                    'destinataire_id' => $avis->destinataire_id,
                    'trajet_id' => $avis->trajet_id,
                    'commentaire' => $avis->commentaire,
                    'note' => $avis->note,
                    'date_creation' => $avis->date_creation,
                    'statut' => $avis->statut
                );
                echo json_encode($avis_arr);
            } else {
                echo json_encode(array('message' => 'Avis non trouvé'));
            }
        } elseif(isset($_GET['auteur_id'])) {
            // Get reviews by author
            $avis->auteur_id = $_GET['auteur_id'];
            $result = $avis->read_by_auteur();
            $num = $result->rowCount();
            
            if($num > 0) {
                $avis_arr = array();
                while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);
                    $avis_item = array(
                        'avis_id' => $avis_id,
                        'auteur_id' => $auteur_id,
                        'destinataire_id' => $destinataire_id,
                        'trajet_id' => $trajet_id,
                        'commentaire' => $commentaire,
                        'note' => $note,
                        'date_creation' => $date_creation,
                        'statut' => $statut
                    );
                    array_push($avis_arr, $avis_item);
                }
                echo json_encode($avis_arr);
            } else {
                echo json_encode(array('message' => 'Aucun avis trouvé pour cet auteur'));
            }
        } elseif(isset($_GET['destinataire_id'])) {
            // Get reviews by recipient
            $avis->destinataire_id = $_GET['destinataire_id'];
            $result = $avis->read_by_destinataire();
            $num = $result->rowCount();
            
            if($num > 0) {
                $avis_arr = array();
                while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);
                    $avis_item = array(
                        'avis_id' => $avis_id,
                        'auteur_id' => $auteur_id,
                        'destinataire_id' => $destinataire_id,
                        'trajet_id' => $trajet_id,
                        'commentaire' => $commentaire,
                        'note' => $note,
                        'date_creation' => $date_creation,
                        'statut' => $statut
                    );
                    array_push($avis_arr, $avis_item);
                }
                echo json_encode($avis_arr);
            } else {
                echo json_encode(array('message' => 'Aucun avis trouvé pour ce destinataire'));
            }
        } elseif(isset($_GET['trajet_id'])) {
            // Get reviews for a specific trip
            $avis->trajet_id = $_GET['trajet_id'];
            $result = $avis->read_by_trajet();
            $num = $result->rowCount();
            
            if($num > 0) {
                $avis_arr = array();
                while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);
                    $avis_item = array(
                        'avis_id' => $avis_id,
                        'auteur_id' => $auteur_id,
                        'destinataire_id' => $destinataire_id,
                        'trajet_id' => $trajet_id,
                        'commentaire' => $commentaire,
                        'note' => $note,
                        'date_creation' => $date_creation,
                        'statut' => $statut
                    );
                    array_push($avis_arr, $avis_item);
                }
                echo json_encode($avis_arr);
            } else {
                echo json_encode(array('message' => 'Aucun avis trouvé pour ce trajet'));
            }
        } else {
            $result = $avis->read();
            $num = $result->rowCount();

            if($num > 0) {
                $avis_arr = array();
                $avis_arr['data'] = array();

                while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);

                    $avis_item = array(
                        'avis_id' => $avis_id,
                        'auteur_id' => $auteur_id,
                        'destinataire_id' => $destinataire_id,
                        'trajet_id' => $trajet_id,
                        'commentaire' => $commentaire,
                        'note' => $note,
                        'date_creation' => $date_creation,
                        'statut' => $statut
                    );

                    array_push($avis_arr['data'], $avis_item);
                }

                echo json_encode($avis_arr);
            } else {
                echo json_encode(array('message' => 'Aucun avis trouvé'));
            }
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));

        // Validate required fields
        if(!isset($data->auteur_id) || !isset($data->destinataire_id) || 
           !isset($data->trajet_id) || !isset($data->commentaire) || !isset($data->note)) {
            http_response_code(400);
            echo json_encode(array('message' => 'Données incomplètes'));
            break;
        }

        // Validate rating range
        if($data->note < 1 || $data->note > 5) {
            http_response_code(400);
            echo json_encode(array('message' => 'La note doit être entre 1 et 5'));
            break;
        }

        $avis->auteur_id = $data->auteur_id;
        $avis->destinataire_id = $data->destinataire_id;
        $avis->trajet_id = $data->trajet_id;
        $avis->commentaire = $data->commentaire;
        $avis->note = $data->note;
        $avis->statut = $data->statut ?? 'publié'; // Default status is 'publié'
        $avis->date_creation = date('Y-m-d H:i:s'); // Current timestamp

        if($avis->create()) {
            http_response_code(201);
            echo json_encode(array('message' => 'Avis créé'));
        } else {
            http_response_code(500);
            echo json_encode(array('message' => 'Échec de la création'));
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));

        if(!isset($data->avis_id)) {
            http_response_code(400);
            echo json_encode(array('message' => 'ID de l\'avis requis'));
            break;
        }

        $avis->avis_id = $data->avis_id;
        
        // Only update fields that are provided
        if(isset($data->commentaire)) $avis->commentaire = $data->commentaire;
        if(isset($data->note)) {
            if($data->note < 1 || $data->note > 5) {
                http_response_code(400);
                echo json_encode(array('message' => 'La note doit être entre 1 et 5'));
                break;
            }
            $avis->note = $data->note;
        }
if(isset($data->statut)) {
    $valid_statuses = ['publié', 'modéré', 'signalé'];
    if(!in_array($data->statut, $valid_statuses)) {
        http_response_code(400);
        echo json_encode(array('message' => 'Statut invalide'));
        break;
    }
    $avis->statut = $data->statut;
}

if($avis->update()) {
    http_response_code(200);
    echo json_encode(array('message' => 'Avis mis à jour'));
} else {
    http_response_code(500);
    echo json_encode(array('message' => 'Échec de la mise à jour'));
}
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));

        if(!isset($data->avis_id)) {
            http_response_code(400);
            echo json_encode(array('message' => 'ID de l\'avis requis'));
            break;
        }

        $avis->avis_id = $data->avis_id;

        if($avis->delete()) {
            http_response_code(200);
            echo json_encode(array('message' => 'Avis supprimé'));
        } else {
            http_response_code(500);
            echo json_encode(array('message' => 'Échec de la suppression'));
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(array('message' => 'Méthode non autorisée'));
        break;
}