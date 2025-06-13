<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../../config/Database.php';
include_once '../../models/Role.php';

$database = new Database();
$db = $database->connect();

$role = new Role($db);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if(isset($_GET['id'])) {
            $role->role_id = $_GET['id'];
            if($role->read_single()) {
                $role_arr = array(
                    'role_id' => $role->role_id,
                    'libelle' => $role->libelle
                );
                echo json_encode($role_arr);
            } else {
                echo json_encode(array('message' => 'Rôle non trouvé'));
            }
        } else {
            $result = $role->read();
            $num = $result->rowCount();

            if($num > 0) {
                $roles_arr = array();
                $roles_arr['data'] = array();

                while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);

                    $role_item = array(
                        'role_id' => $role_id,
                        'libelle' => $libelle
                    );

                    array_push($roles_arr['data'], $role_item);
                }

                echo json_encode($roles_arr);
            } else {
                echo json_encode(array('message' => 'Aucun rôle trouvé'));
            }
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));

        $role->libelle = $data->libelle;

        if($role->create()) {
            echo json_encode(array('message' => 'Rôle créé'));
        } else {
            echo json_encode(array('message' => 'Échec de la création'));
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));

        $role->role_id = $data->role_id;
        $role->libelle = $data->libelle;

        if($role->update()) {
            echo json_encode(array('message' => 'Rôle mis à jour'));
        } else {
            echo json_encode(array('message' => 'Échec de la mise à jour'));
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));

        $role->role_id = $data->role_id;

        if($role->delete()) {
            echo json_encode(array('message' => 'Rôle supprimé'));
        } else {
            echo json_encode(array('message' => 'Échec de la suppression'));
        }
        break;
}
?>