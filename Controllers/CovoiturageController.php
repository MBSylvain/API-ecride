<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once 'config/Database.php';
include_once '../model/Covoiturage.php';
include_once '../Controllers/checkAuth.php';

// Vérifiez l'authentification
verifyAuth();

$database = new Database();
$db = $database->connect();

$covoiturage = new Covoiturage($db);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if(isset($_GET['id'])) {
            $covoiturage->covoiturage_id = $_GET['id'];
            if($covoiturage->read_single()) {
                $covoiturage_arr = array(
                    'covoiturage_id' => $covoiturage->covoiturage_id,
                    'date_depart' => $covoiturage->date_depart,
                    'heure_depart' => $covoiturage->heure_depart,
                    'lieu_depart' => $covoiturage->lieu_depart,
                    'date_arrivee' => $covoiturage->date_arrivee,
                    'heure_arrivee' => $covoiturage->heure_arrivee,
                    'lieu_arrivee' => $covoiturage->lieu_arrivee,
                    'statut' => $covoiturage->statut,
                    'nb_place' => $covoiturage->nb_place,
                    'prix_personne' => $covoiturage->prix_personne,
                    'voiture_id' => $covoiturage->voiture_id
                );
                echo json_encode($covoiturage_arr);
            } else {
                echo json_encode(array('message' => 'Covoiturage non trouvé'));
            }
        } else {
            $result = $covoiturage->read();
            $num = $result->rowCount();

            if($num > 0) {
                $covoiturages_arr = array();
                $covoiturages_arr['data'] = array();

                while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);

                    $covoiturage_item = array(
                        'covoiturage_id' => $covoiturage_id,
                        'date_depart' => $date_depart,
                        'heure_depart' => $heure_depart,
                        'lieu_depart' => $lieu_depart,
                        'date_arrivee' => $date_arrivee,
                        'heure_arrivee' => $heure_arrivee,
                        'lieu_arrivee' => $lieu_arrivee,
                        'statut' => $statut,
                        'nb_place' => $nb_place,
                        'prix_personne' => $prix_personne,
                        'voiture_id' => $voiture_id
                    );

                    array_push($covoiturages_arr['data'], $covoiturage_item);
                }

                echo json_encode($covoiturages_arr);
            } else {
                echo json_encode(array('message' => 'Aucun covoiturage trouvé'));
            }
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));

        $covoiturage->date_depart = $data->date_depart;
        $covoiturage->heure_depart = $data->heure_depart;
        $covoiturage->lieu_depart = $data->lieu_depart;
        $covoiturage->date_arrivee = $data->date_arrivee;
        $covoiturage->heure_arrivee = $data->heure_arrivee;
        $covoiturage->lieu_arrivee = $data->lieu_arrivee;
        $covoiturage->statut = $data->statut;
        $covoiturage->nb_place = $data->nb_place;
        $covoiturage->prix_personne = $data->prix_personne;
        $covoiturage->voiture_id = $data->voiture_id;

        if($covoiturage->create()) {
            echo json_encode(array('message' => 'Covoiturage créé'));
        } else {
            echo json_encode(array('message' => 'Échec de la création'));
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));

        $covoiturage->covoiturage_id = $data->covoiturage_id;
        $covoiturage->date_depart = $data->date_depart;
        $covoiturage->heure_depart = $data->heure_depart;
        $covoiturage->lieu_depart = $data->lieu_depart;
        $covoiturage->date_arrivee = $data->date_arrivee;
        $covoiturage->heure_arrivee = $data->heure_arrivee;
        $covoiturage->lieu_arrivee = $data->lieu_arrivee;
        $covoiturage->statut = $data->statut;
        $covoiturage->nb_place = $data->nb_place;
        $covoiturage->prix_personne = $data->prix_personne;
        $covoiturage->voiture_id = $data->voiture_id;

        if($covoiturage->update()) {
            echo json_encode(array('message' => 'Covoiturage mis à jour'));
        } else {
            echo json_encode(array('message' => 'Échec de la mise à jour'));
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));

        $covoiturage->covoiturage_id = $data->covoiturage_id;

        if($covoiturage->delete()) {
            echo json_encode(array('message' => 'Covoiturage supprimé'));
        } else {
            echo json_encode(array('message' => 'Échec de la suppression'));
        }
        break;
}
?>