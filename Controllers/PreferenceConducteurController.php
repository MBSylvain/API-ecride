<?php
require_once '../config/session.php';
require_once '../config/Database.php';
require_once '../models/PreferenceConducteur.php';

$database = new Database();
$db = $database->connect();
header('Content-Type: application/json');

// GET : Liste des préférences d'un utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['utilisateur_id'])) {
	$utilisateur_id = $_GET['utilisateur_id'];
	$sql = "SELECT * FROM preferences_conducteur WHERE utilisateur_id = :utilisateur_id";
	$stmt = $db->prepare($sql);
	$stmt->bindValue(":utilisateur_id", $utilisateur_id);
	$stmt->execute();
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	echo json_encode($results);
	exit;
}

// POST : Ajouter des préférences pour un utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$data = json_decode(file_get_contents('php://input'), true);
	if (!isset($data['utilisateur_id'])) {
		echo json_encode(['success' => false, 'message' => 'utilisateur_id requis']);
		exit;
	}
	$utilisateur_id = $data['utilisateur_id'];
	$fields = [
		'fumeur_autorise', 'animaux_autorises', 'bagages_volumineux', 'musique_autorisee',
		'discussion', 'pauses_prevues', 'climatisation', 'nourriture_autorisee',
		'type_conduite', 'accessibilite_pmr'
	];
	$columns = [];
	$placeholders = [];
	$values = [];
	foreach ($fields as $field) {
		if (isset($data[$field])) {
			$columns[] = $field;
			$placeholders[] = ":$field";
			$values[":$field"] = $data[$field];
		}
	}
	if (empty($columns)) {
		echo json_encode(['success' => false, 'message' => 'Aucune préférence à ajouter']);
		exit;
	}
	$columns[] = 'utilisateur_id';
	$placeholders[] = ':utilisateur_id';
	$values[':utilisateur_id'] = $utilisateur_id;
	// Vérifier si déjà existant
	$check = $db->prepare("SELECT * FROM preferences_conducteur WHERE utilisateur_id = :utilisateur_id");
	$check->bindValue(":utilisateur_id", $utilisateur_id);
	$check->execute();
	if ($check->rowCount() > 0) {
		echo json_encode(['success' => false, 'message' => 'Préférences déjà existantes, utilisez PUT pour modifier.']);
		exit;
	}
	$sql = "INSERT INTO preferences_conducteur (".implode(", ", $columns).") VALUES (".implode(", ", $placeholders).")";
	$stmt = $db->prepare($sql);
	foreach ($values as $ph => $val) {
		$stmt->bindValue($ph, $val);
	}
	$success = $stmt->execute();
	if ($success) {
		// Notification ajout préférences
		require_once '../utils/NotificationMails.php';
		require_once '../models/Utilisateur.php';
		$utilisateurModel = new Utilisateur($db);
		$utilisateurModel->utilisateur_id = $utilisateur_id;
		$result = $utilisateurModel->read_single();
		$user = null;
		if ($result === true) {
			$user = [
				'email' => $utilisateurModel->email,
				'prenom' => $utilisateurModel->prenom,
				'nom' => $utilisateurModel->nom
			];
		}
		if ($user && isset($user['email'])) {
			sendPreferenceAdded($user['email'], $user);
		}
		echo json_encode(['success' => true, 'message' => 'Préférences ajoutées']);
	} else {
		echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'ajout']);
	}
	exit;
}

// PUT : Modifier une préférence individuelle
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
	$data = json_decode(file_get_contents('php://input'), true);
	if (!isset($data['preference_id']) || !isset($data['valeur'])) {
		echo json_encode(['success' => false, 'message' => 'preference_id et valeur requis']);
		exit;
	}
	$preference_id = $data['preference_id'];
	$valeur = $data['valeur'];
	$sql = "UPDATE preferences_conducteur SET valeur = :valeur WHERE preference_id = :preference_id";
	$stmt = $db->prepare($sql);
	$stmt->bindValue(":valeur", $valeur);
	$stmt->bindValue(":preference_id", $preference_id);
	$success = $stmt->execute();
	if ($success && $stmt->rowCount() > 0) {
		// Notification modification préférence
		require_once '../utils/NotificationMails.php';
		require_once '../models/Utilisateur.php';
		// On suppose qu'on peut retrouver l'utilisateur via la table (jointure ou requête supplémentaire)
		$sqlUser = "SELECT utilisateur_id FROM preferences_conducteur WHERE preference_id = :preference_id";
		$stmtUser = $db->prepare($sqlUser);
		$stmtUser->bindValue(":preference_id", $preference_id);
		$stmtUser->execute();
		$rowUser = $stmtUser->fetch(PDO::FETCH_ASSOC);
		$user = null;
		if ($rowUser && isset($rowUser['utilisateur_id'])) {
			$utilisateurModel = new Utilisateur($db);
			$utilisateurModel->utilisateur_id = $rowUser['utilisateur_id'];
			$result = $utilisateurModel->read_single();
			if ($result === true) {
				$user = [
					'email' => $utilisateurModel->email,
					'prenom' => $utilisateurModel->prenom,
					'nom' => $utilisateurModel->nom
				];
			}
		}
		if ($user && isset($user['email'])) {
			sendPreferenceUpdated($user['email'], $user, $preference_id, $valeur);
		}
		echo json_encode(['success' => true, 'message' => 'Préférence modifiée']);
	} else {
		echo json_encode(['success' => false, 'message' => 'Aucune modification ou préférence inexistante']);
	}
	exit;
}

// DELETE : Supprimer une préférence individuelle
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['preference_id'])) {
	$preference_id = $_GET['preference_id'];
	$sql = "DELETE FROM preferences_conducteur WHERE preference_id = :preference_id";
	$stmt = $db->prepare($sql);
	$stmt->bindValue(":preference_id", $preference_id);
	$success = $stmt->execute();
	if ($success && $stmt->rowCount() > 0) {
		// Notification suppression préférence
		require_once '../utils/NotificationMails.php';
		require_once '../models/Utilisateur.php';
		// On suppose qu'on peut retrouver l'utilisateur via la table (jointure ou requête supplémentaire)
		$sqlUser = "SELECT utilisateur_id FROM preferences_conducteur WHERE preference_id = :preference_id";
		$stmtUser = $db->prepare($sqlUser);
		$stmtUser->bindValue(":preference_id", $preference_id);
		$stmtUser->execute();
		$rowUser = $stmtUser->fetch(PDO::FETCH_ASSOC);
		$user = null;
		if ($rowUser && isset($rowUser['utilisateur_id'])) {
			$utilisateurModel = new Utilisateur($db);
			$utilisateurModel->utilisateur_id = $rowUser['utilisateur_id'];
			$result = $utilisateurModel->read_single();
			if ($result === true) {
				$user = [
					'email' => $utilisateurModel->email,
					'prenom' => $utilisateurModel->prenom,
					'nom' => $utilisateurModel->nom
				];
			}
		}
		if ($user && isset($user['email'])) {
			sendPreferenceDeleted($user['email'], $user, $preference_id);
		}
		echo json_encode(['success' => true, 'message' => 'Préférence supprimée']);
	} else {
		echo json_encode(['success' => false, 'message' => 'Suppression impossible ou préférence inexistante']);
	}
	exit;
}

// Si aucune route ne correspond
echo json_encode(['success' => false, 'message' => 'Requête non supportée ou paramètres manquants']);
exit;

?>