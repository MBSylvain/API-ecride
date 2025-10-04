<?php
require_once '../config/session.php';
// NotificationController.php
// Contrôleur pour la gestion des notifications (mails, alertes, sms)


require_once '../config/Database.php';
require_once '../models/Notification.php';

$database = new Database();
$db = $database->connect();
$notification = new Notification($db);

// Routage basique (exemple, à adapter selon votre framework ou router)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// Envoyer une notification
	$data = json_decode(file_get_contents('php://input'));
	$notification->utilisateur_id = $data->utilisateur_id;
	$notification->type = $data->type;
	$notification->contenu = $data->contenu;
	$notification->statut = $data->statut ?? 'envoyé';
	if ($notification->createNotification()) {
		echo json_encode(['success' => true, 'message' => 'Notification envoyée']);
	} else {
		echo json_encode(['success' => false, 'message' => 'Erreur lors de l’envoi']);
	}
}
else if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['utilisateur_id'])) {
	// Récupérer les notifications d'un utilisateur
	$result = $notification->getNotificationsByUser($_GET['utilisateur_id']);
	echo json_encode($result);
}
else if ($_SERVER['REQUEST_METHOD'] === 'PUT' && isset($_GET['notification_id'])) {
	// Marquer une notification comme lue
	parse_str(file_get_contents('php://input'), $_PUT);
	$statut = $_PUT['statut'] ?? 'lu';
	if ($notification->updateNotification($_GET['notification_id'], $statut)) {
		echo json_encode(['success' => true, 'message' => 'Notification mise à jour']);
	} else {
		echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
	}
}
else if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['notification_id'])) {
	// Supprimer une notification
	if ($notification->deleteNotification($_GET['notification_id'])) {
		echo json_encode(['success' => true, 'message' => 'Notification supprimée']);
	} else {
		echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression']);
	}
}

?>