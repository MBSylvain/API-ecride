<?php
require_once __DIR__ . '/../utils/Mailer.php';

$to = 'sylvain.mbeumou@gmail.com'; // Mets ici ton adresse email réelle
$subject = 'Test PHPMailer EcoRide';
$body = '<h1>Ceci est un test d\'envoi de mail via PHPMailer</h1>';

$result = sendMail($to, $subject, $body);
if ($result) {
    echo "Email envoyé avec succès !";
} else {
    echo "Échec de l'envoi de l'email.<br>";
    if (file_exists(__DIR__ . '/../logs/php_error.log')) {
        echo nl2br(file_get_contents(__DIR__ . '/../logs/php_error.log'));
    } else {
        echo "Vérifie le fichier de log PHP pour plus de détails.";
    }
}