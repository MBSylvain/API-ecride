<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../../config/Database.php';
include_once '../../models/Marque.php';

$database = new Database();
$db = $database->connect();

$marque = new Marque($db);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if(isset($_GET['id'])) {
            $marque->marque_id = $_GET['id'];
            if($marque->read_single()) {
                $marque_arr = array(
                    'marque_id' => $marque->marque_id,
                    'libelle' => $marque->libelle
                );
                echo json_encode($marque_arr);
            } else {
                echo json_encode(array('message' => 'Marque non trouvée'));
            }
        } else {
            $result = $marque->read();
            $num = $result->rowCount();

            if($num > 0) {
                $marques_arr = array();
                $marques_arr['data'] = array();

                while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);

                    $marque_item = array(
                        'marque_id' => $marque_id,
                        'libelle' => $libelle
                    );

                    array_push($marques_arr['data'], $marque_item);
                }

                echo json_encode($marques_arr);
            } else {
                echo json_encode(array('message' => 'Aucune marque trouvée'));
            }
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));

        $marque->libelle = $data->libelle;

        if($marque->create()) {
            echo json_encode(array('message' => 'Marque créée'));
        } else {
            echo json_encode(array('message' => 'Échec de la création'));
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));

        $marque->marque_id = $data->marque_id;
        $marque->libelle = $data->libelle;

        if($marque->update()) {
            echo json_encode(array('message' => 'Marque mise à jour'));
        } else {
            echo json_encode(array('message' => 'Échec de la mise à jour'));
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));

        $marque->marque_id = $data->marque_id;

        if($marque->delete()) {
            echo json_encode(array('message' => 'Marque supprimée'));
        } else {
            echo json_encode(array('message' => 'Échec de la suppression'));
        }
        break;
}
?>