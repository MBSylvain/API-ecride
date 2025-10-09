<?php
require_once 'config/Database.php';
require_once 'models/Trajet.php';

$db = (new Database())->connect();
$trajetModel = new Trajet($db);

// Remplacez 1 par l'id du trajet à tester
$trajet_id = 1;
$places_restantes = $trajetModel->getPlacesRestantes($trajet_id);

echo "Places restantes pour le trajet $trajet_id : ";
var_dump($places_restantes);
?>