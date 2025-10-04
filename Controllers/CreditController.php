<?php
require_once '../config/session.php';
// CreditController.php
// Contrôleur pour la gestion des crédits utilisateurs


require_once '../config/Database.php';
require_once '../models/Credit.php';

$database = new Database();
$db = $database->connect();
$credit = new Credit($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// Ajouter un mouvement de crédit
	$data = json_decode(file_get_contents('php://input'));
	$credit->utilisateur_id = $data->utilisateur_id;
	$credit->montant = $data->montant;
	$credit->type_operation = $data->type_operation;
	$credit->commentaire = $data->commentaire ?? null;
	if ($credit->createCredit()) {
		echo json_encode(['success' => true, 'message' => 'Crédit ajouté']);
	} else {
		echo json_encode(['success' => false, 'message' => 'Erreur lors de l’ajout']);
	}
}
else if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['utilisateur_id'])) {
	// Récupérer l'historique des crédits d'un utilisateur
	$result = $credit->getCreditsByUser($_GET['utilisateur_id']);
	echo json_encode($result);
}
else if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['credit_id'])) {
	// Récupérer un mouvement de crédit
	$result = $credit->getCredit($_GET['credit_id']);
	echo json_encode($result);
}
else if ($_SERVER['REQUEST_METHOD'] === 'PUT' && isset($_GET['credit_id'])) {
	// Mettre à jour un mouvement de crédit
	parse_str(file_get_contents('php://input'), $_PUT);
	$montant = $_PUT['montant'] ?? null;
	$type_operation = $_PUT['type_operation'] ?? null;
	$commentaire = $_PUT['commentaire'] ?? null;
	if ($credit->updateCredit($_GET['credit_id'], $montant, $type_operation, $commentaire)) {
		echo json_encode(['success' => true, 'message' => 'Crédit mis à jour']);
	} else {
		echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
	}
}
else if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['credit_id'])) {
	// Supprimer un mouvement de crédit
	if ($credit->deleteCredit($_GET['credit_id'])) {
		echo json_encode(['success' => true, 'message' => 'Crédit supprimé']);
	} else {
		echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression']);
	}
}

?>