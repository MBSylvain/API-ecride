<?php
require_once '../config/session.php';
// CreditController.php
// Contrôleur pour la gestion des crédits utilisateurs


require_once '../config/Database.php';
require_once '../models/Credit.php';
require_once '../utils/NotificationMails.php';

$database = new Database();
$db = $database->connect();
$credit = new Credit($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// Ajouter un mouvement de crédit
	$data = json_decode(file_get_contents('php://input'));
	var_dump($data);
	$credit->utilisateur_id = $data->utilisateur_id;
	$credit->montant = $data->montant;
	$credit->type_operation = $data->type_operation;
	// Prend en compte la casse pour le commentaire
	if (isset($data->commentaire)) {
		$credit->commentaire = $data->commentaire;
	} elseif (isset($data->Commentaire)) {
		$credit->commentaire = $data->Commentaire;
	} else {
		$credit->commentaire = null;
	}
	if ($credit->createCredit()) {
		echo json_encode(['success' => true, 'message' => 'Crédit ajouté']);
		// Récupération des informations utilisateur pour l'email
		require_once '../models/Utilisateur.php';
		$utilisateurModel = new Utilisateur($db);
		$utilisateurModel->utilisateur_id = $credit->utilisateur_id;
		$result = $utilisateurModel->read_single();
		$user = null;
		if ($result === true) {
			// Les propriétés sont directement sur $utilisateurModel
			$user = [
				'email' => $utilisateurModel->email,
				'prenom' => $utilisateurModel->prenom,
				'nom' => $utilisateurModel->nom,
				'utilisateur_id' => $utilisateurModel->utilisateur_id
			];
		} elseif ($result instanceof PDOStatement) {
			$row = $result->fetch(PDO::FETCH_ASSOC);
			if ($row) {
				$user = $row;
			}
		}

		if ($user && isset($user['email'])) {
		   // Appelle la fonction de notification avec les bonnes infos
		   sendCreditOffer($user['email'], $user, $credit->montant);
		} else {
			error_log("Erreur lors de l'envoi du mail de crédit offert à utilisateur_id: {$credit->utilisateur_id}");
		}

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
		// Notification mouvement crédit
		require_once '../models/Utilisateur.php';
		$utilisateurModel = new Utilisateur($db);
		$utilisateurModel->utilisateur_id = $credit->utilisateur_id;
		$result = $utilisateurModel->read_single();
		$user = null;
		if ($result === true) {
			$user = [
				'email' => $utilisateurModel->email,
				'prenom' => $utilisateurModel->prenom,
				'nom' => $utilisateurModel->nom,
				'utilisateur_id' => $utilisateurModel->utilisateur_id
			];
		} elseif ($result instanceof PDOStatement) {
			$row = $result->fetch(PDO::FETCH_ASSOC);
			if ($row) {
				$user = $row;
			}
		}
		if ($user && isset($user['email'])) {
			if (function_exists('sendCreditMovement')) {
				sendCreditMovement($user['email'], $user, $montant, $type_operation);
			}
		}
		echo json_encode(['success' => true, 'message' => 'Crédit mis à jour']);
	} else {
		echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
	}
}
else if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['credit_id'])) {
	// Supprimer un mouvement de crédit
	// On suppose que l'ID utilisateur est connu via $credit->utilisateur_id
	require_once '../models/Utilisateur.php';
	$utilisateurModel = new Utilisateur($db);
	$utilisateurModel->utilisateur_id = $credit->utilisateur_id;
	$result = $utilisateurModel->read_single();
	$user = null;
	if ($result === true) {
		$user = [
			'email' => $utilisateurModel->email,
			'prenom' => $utilisateurModel->prenom,
			'nom' => $utilisateurModel->nom,
			'utilisateur_id' => $utilisateurModel->utilisateur_id
		];
	} elseif ($result instanceof PDOStatement) {
		$row = $result->fetch(PDO::FETCH_ASSOC);
		if ($row) {
			$user = $row;
		}
	}
	if ($credit->deleteCredit($_GET['credit_id'])) {
		if ($user && isset($user['email'])) {
			if (function_exists('sendCreditExpired')) {
				sendCreditExpired($user['email'], $user);
			}
		}
		echo json_encode(['success' => true, 'message' => 'Crédit supprimé']);
	} else {
		echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression']);
	}
}

?>