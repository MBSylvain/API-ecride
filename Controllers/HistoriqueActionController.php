<?php
require_once '../config/session.php';
// HistoriqueActionController.php
// Contrôleur pour la gestion de l'historique des actions


require_once '../config/Database.php';
require_once '../models/HistoriqueAction.php';

$database = new Database();
$db = $database->connect();
$action = new HistoriqueAction($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// Ajouter une action à l'historique
	$data = json_decode(file_get_contents('php://input'));
	$action->utilisateur_id = $data->utilisateur_id;
	$action->type_action = $data->type_action;
	$action->cible_id = $data->cible_id ?? null;
	$action->cible_table = $data->cible_table ?? null;
	$action->commentaire = $data->commentaire ?? null;
	if ($action->createAction()) {
		echo json_encode(['success' => true, 'message' => 'Action ajoutée']);
	} else {
		echo json_encode(['success' => false, 'message' => 'Erreur lors de l’ajout']);
	}
}
else if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['utilisateur_id'])) {
	// Récupérer l'historique d'un utilisateur
	$result = $action->getActionsByUser($_GET['utilisateur_id']);
	echo json_encode($result);
}
else if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action_id'])) {
	// Récupérer une action
	$result = $action->getAction($_GET['action_id']);
	echo json_encode($result);
}
else if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['type_action'])) {
	// Récupérer l'historique par type d'action
	$result = $action->getActionsByType($_GET['type_action']);
	echo json_encode($result);
}
else if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['action_id'])) {
	// Supprimer une action
	if ($action->deleteAction($_GET['action_id'])) {
		echo json_encode(['success' => true, 'message' => 'Action supprimée']);
	} else {
		echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression']);
	}
}

?>