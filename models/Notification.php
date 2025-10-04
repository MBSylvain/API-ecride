<?php
// Notification.php
// Modèle pour la table notifications
class Notification {
    public $notification_id;
    public $utilisateur_id;
    public $type;
    public $contenu;
    public $date_envoi;
    public $statut;

    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Créer une notification
    public function createNotification() {
        $query = "INSERT INTO notifications (utilisateur_id, type, contenu, statut) VALUES (:utilisateur_id, :type, :contenu, :statut)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':utilisateur_id', $this->utilisateur_id);
        $stmt->bindParam(':type', $this->type);
        $stmt->bindParam(':contenu', $this->contenu);
        $stmt->bindParam(':statut', $this->statut);
        return $stmt->execute();
    }

    // Récupérer toutes les notifications d'un utilisateur
    public function getNotificationsByUser($utilisateur_id) {
        $query = "SELECT * FROM notifications WHERE utilisateur_id = :utilisateur_id ORDER BY date_envoi DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':utilisateur_id', $utilisateur_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer une notification par son id
    public function getNotification($notification_id) {
        $query = "SELECT * FROM notifications WHERE notification_id = :notification_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':notification_id', $notification_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Mettre à jour le statut d'une notification (ex: marquer comme lue)
    public function updateNotification($notification_id, $statut = 'lu') {
        $query = "UPDATE notifications SET statut = :statut WHERE notification_id = :notification_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':statut', $statut);
        $stmt->bindParam(':notification_id', $notification_id);
        return $stmt->execute();
    }

    // Supprimer une notification
    public function deleteNotification($notification_id) {
        $query = "DELETE FROM notifications WHERE notification_id = :notification_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':notification_id', $notification_id);
        return $stmt->execute();
    }
}
?>