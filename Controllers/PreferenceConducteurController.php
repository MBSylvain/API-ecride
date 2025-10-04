<?php
require_once '../config/session.php';
// PreferenceConducteurController.php
// Contrôleur pour la gestion des préférences personnalisées des conducteurs


require_once '../config/Database.php';
require_once '../models/PreferenceConducteur.php';

$database = new Database();
$db = $database->connect();
$preference = new PreferenceConducteur($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// Ajouter une préférence
	$data = json_decode(file_get_contents('php://input'));
	$preference->utilisateur_id = $data->utilisateur_id;
	$preference->type = $data->type;
	$preference->valeur = $data->valeur;
	if ($preference->createPreference()) {
		echo json_encode(['success' => true, 'message' => 'Préférence ajoutée']);
	} else {
		echo json_encode(['success' => false, 'message' => 'Erreur lors de l’ajout']);
	}
}
else if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['utilisateur_id'])) {
	// Récupérer les préférences d'un conducteur
	$result = $preference->getPreferencesByUser($_GET['utilisateur_id']);
	echo json_encode($result);
}
else if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['preference_id'])) {
	// Récupérer une préférence
	$result = $preference->getPreference($_GET['preference_id']);
	echo json_encode($result);
}
else if ($_SERVER['REQUEST_METHOD'] === 'PUT' && isset($_GET['preference_id'])) {
	// Mettre à jour une préférence
	parse_str(file_get_contents('php://input'), $_PUT);
	$type = $_PUT['type'] ?? null;
	$valeur = $_PUT['valeur'] ?? null;
	if ($preference->updatePreference($_GET['preference_id'], $type, $valeur)) {
		echo json_encode(['success' => true, 'message' => 'Préférence mise à jour']);
	} else {
		echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
	}
}
else if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['preference_id'])) {
	// Supprimer une préférence
	if ($preference->deletePreference($_GET['preference_id'])) {
		echo json_encode(['success' => true, 'message' => 'Préférence supprimée']);
	} else {
		echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression']);
	}
}

?>