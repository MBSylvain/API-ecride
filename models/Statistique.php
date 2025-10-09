<?php
class Statistique {
    private $conn;
    public function __construct($db) {
        $this->conn = $db;
    }

    // Exemple : statistiques globales
    public function getStatsGlobales() {
        $stats = [];
        // Nombre de covoiturages par jour
        $sql1 = "SELECT DATE(date_depart) as jour, COUNT(*) as nb_covoiturages FROM trajets GROUP BY jour ORDER BY jour DESC LIMIT 30";
        $stmt1 = $this->conn->prepare($sql1);
        $stmt1->execute();
        $stats['covoiturages_par_jour'] = $stmt1->fetchAll(PDO::FETCH_ASSOC);
        // Crédits gagnés par jour
        $sql2 = "SELECT DATE(date_action) as jour, SUM(montant) as credits_gagnes FROM historique_action WHERE type_action = 'gain' GROUP BY jour ORDER BY jour DESC LIMIT 30";
        $stmt2 = $this->conn->prepare($sql2);
        $stmt2->execute();
        $stats['credits_gagnes_par_jour'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        return $stats;
    }
}
