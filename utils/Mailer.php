<?php
// utils/Mailer.php
require_once __DIR__ . '/../lib/PHPMailer-7.0.0/src/PHPMailer.php';
require_once __DIR__ . '/../lib/PHPMailer-7.0.0/src/SMTP.php';
require_once __DIR__ . '/../lib/PHPMailer-7.0.0/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Envoie un email via PHPMailer (SMTP)
 * @param string $to Destinataire
 * @param string $subject Sujet du mail
 * @param string $body Contenu HTML du mail
 * @param string $from Adresse d'expédition (optionnel)
 * @return bool true si succès, false sinon
 */
function sendMail($to, $subject, $body, $from = 'noreply@ecoride.com') {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // À adapter
        $mail->SMTPAuth = true;
        $mail->Username = 'ton.email@gmail.com'; // À adapter
        $mail->Password = 'ton_mot_de_passe';    // À adapter
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom($from, 'EcoRide');
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Mailer Error: ' . $mail->ErrorInfo);
        return false;
    }
}
