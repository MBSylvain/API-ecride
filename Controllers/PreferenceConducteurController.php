
<?php
require_once '../config/session.php';
require_once '../config/Database.php';
require_once '../models/PreferenceConducteur.php';
require_once '../utils/NotificationMails.php';
require_once '../models/Utilisateur.php';

$database = new Database();
$db = $database->connect();
// header('Content-Type: application/json');

$preferenceModel = new PreferenceConducteur($db);

function getUserInfo($db, $utilisateur_id) {
	$utilisateurModel = new Utilisateur($db);
	$utilisateurModel->utilisateur_id = $utilisateur_id;
	$result = $utilisateurModel->read_single();
	if ($result === true) {
		return [
			'email' => $utilisateurModel->email,
			'prenom' => $utilisateurModel->prenom,
			'nom' => $utilisateurModel->nom
		];
	}
	return null;
}

// GET : Liste des préférences d'un utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['utilisateur_id'])) {
	$utilisateur_id = $_GET['utilisateur_id'];
	$results = $preferenceModel->getPreferencesByUser($utilisateur_id);
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
	$added = false;
	foreach ($fields as $field) {
		if (isset($data[$field])) {
			$preferenceModel->utilisateur_id = $utilisateur_id;
			$preferenceModel->type = $field;
			$preferenceModel->valeur = $data[$field];
			$added = $preferenceModel->createPreference() || $added;
		}
	}
	if ($added) {
		$user = getUserInfo($db, $utilisateur_id);
		if ($user && isset($user['email'])) {
			sendPreferenceAdded($user['email'], $user);
		}
		echo json_encode(['success' => true, 'message' => 'Préférences ajoutées']);
	} else {
		echo json_encode(['success' => false, 'message' => 'Aucune préférence à ajouter']);
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
	$pref = $preferenceModel->getPreference($preference_id);
	if ($pref) {
		$success = $preferenceModel->updatePreference($preference_id, $pref['type'], $valeur);
		if ($success) {
			$user = getUserInfo($db, $pref['utilisateur_id']);
			if ($data['utilisateur_id'] && isset($data['email'])) {
			}
			echo json_encode(['success' => true, 'message' => 'Préférence modifiée']);
			sendPreferenceUpdated($user['email'], $user, $pref['type'], $valeur);
		} else {
			echo json_encode(['success' => false, 'message' => 'Aucune modification']);
		}
	} else {
		echo json_encode(['success' => false, 'message' => 'Préférence inexistante']);
	}
	exit;
}

// DELETE : Supprimer une préférence individuelle
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['preference_id'])) {
	$preference_id = $_GET['preference_id'];
	$pref = $preferenceModel->getPreference($preference_id);
	if ($pref) {
		$success = $preferenceModel->deletePreference($preference_id);
		if ($success) {
			$user = getUserInfo($db, $pref['utilisateur_id']);
			if ($user && isset($user['email'])) {
				sendPreferenceDeleted($user['email'], $user, $pref['type']);
			}
			echo json_encode(['success' => true, 'message' => 'Préférence supprimée']);
		} else {
			echo json_encode(['success' => false, 'message' => 'Suppression impossible']);
		}
	} else {
		echo json_encode(['success' => false, 'message' => 'Préférence inexistante']);
	}
	exit;
}

// Si aucune route ne correspond
echo json_encode(['success' => false, 'message' => 'Requête non supportée ou paramètres manquants']);
exit;