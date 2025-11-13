<?php
// utils/NotificationMails.php
require_once __DIR__ . '/Mailer.php';


// 1. Notification de création de signalement (à l'admin ou équipe)
function sendSignalementCreationNotification($to, $signalement, $user) {
    $subject = "Nouveau signalement créé";
    $body = "Bonjour,<br>Un nouveau signalement a été créé par {$user['prenom']} {$user['nom']}<br>"
        . "<b>Motif :</b> {$signalement['motif']}<br>"
        . "<b>Description :</b> {$signalement['description']}<br>"
        . "Merci de le traiter dans l'espace d'administration.";
    return sendMail($to, $subject, $body);
}

// 2. Notification de changement de statut du signalement (à l'utilisateur ayant signalé)
function sendSignalementStatusUpdate($to, $signalement, $user) {
    $subject = "Mise à jour de votre signalement";
    $body = "Bonjour {$user['prenom']},<br>Le statut de votre signalement (motif : <b>{$signalement['motif']}</b>) a été mis à jour : <b>{$signalement['statut']}</b>.";
    return sendMail($to, $subject, $body);
}

// 3. Notification d'affectation d'un signalement à un employé
function sendSignalementAffectationEmploye($to, $signalement, $employe) {
    $subject = "Un signalement vous a été affecté";
    $body = "Bonjour {$employe['prenom']},<br>Un signalement (motif : <b>{$signalement['motif']}</b>) vous a été affecté pour traitement.";
    return sendMail($to, $subject, $body);
}

// 4. Notification de résolution du signalement (à l'utilisateur concerné)
function sendSignalementResolution($to, $signalement, $user) {
    $subject = "Votre signalement a été résolu";
    $body = "Bonjour {$user['prenom']},<br>Votre signalement (motif : <b>{$signalement['motif']}</b>) a été résolu. Décision : <b>{$signalement['statut']}</b>. Merci pour votre vigilance.";
    return sendMail($to, $subject, $body);
}


// 1. Confirmation de réservation
function sendReservationConfirmation($to, $trajet, $user) {
    $subject = "Confirmation de votre réservation";
    $body = "Bonjour {$user['prenom']},<br>Votre réservation pour le trajet <b>{$trajet['ville_depart']} → {$trajet['ville_arrivee']}</b> est confirmée.";
    return sendMail($to, $subject, $body);
}

// 2. Annulation de réservation
function sendReservationCancellation($to, $trajet, $user) {
    $subject = "Annulation de votre réservation";
    $body = "Bonjour {$user['prenom']},<br>Votre réservation pour le trajet <b>{$trajet['ville_depart']} → {$trajet['ville_arrivee']}</b> a été annulée.";
    return sendMail($to, $subject, $body);
}

// 3. Création de réservation (statut en attente)
function sendReservationCreated($to, $trajet, $user) {
    $subject = "Réservation créée - en attente de confirmation";
    $body = "Bonjour {$user['prenom']},<br>Votre réservation pour le trajet <b>{$trajet['ville_depart']} → {$trajet['ville_arrivee']}</b> a été enregistrée et est en attente de confirmation du conducteur.";
    return sendMail($to, $subject, $body);
}

// 4. Réservation refusée
function sendReservationRefused($to, $trajet, $user) {
    $subject = "Réservation refusée";
    $body = "Bonjour {$user['prenom']},<br>Votre réservation pour le trajet <b>{$trajet['ville_depart']} → {$trajet['ville_arrivee']}</b> a été refusée par le conducteur.";
    return sendMail($to, $subject, $body);
}

// 5. Réservation modifiée
function sendReservationModified($to, $trajet, $user, $modifications) {
    $subject = "Réservation modifiée";
    $body = "Bonjour {$user['prenom']},<br>Votre réservation pour le trajet <b>{$trajet['ville_depart']} → {$trajet['ville_arrivee']}</b> a été modifiée.<br>Détails des modifications : {$modifications}";
    return sendMail($to, $subject, $body);
}

// 6. Rappel de réservation
function sendReservationReminder($to, $trajet, $user) {
    $subject = "Rappel : réservation à venir";
    $body = "Bonjour {$user['prenom']},<br>Votre réservation pour le trajet <b>{$trajet['ville_depart']} → {$trajet['ville_arrivee']}</b> aura lieu bientôt. Pensez à vérifier les détails et à vous préparer !";
    return sendMail($to, $subject, $body);
}

// 3. Confirmation de création de trajet
function sendTrajetCreationConfirmation($to, $trajet, $user) {
    $subject = "Trajet créé avec succès";
    $body = "Bonjour {$user['prenom']},<br>Votre trajet <b>{$trajet['ville_depart']} → {$trajet['ville_arrivee']}</b> a bien été créé.";
    return sendMail($to, $subject, $body);
}

// 4. Annulation de trajet
function sendTrajetCancellation($to, $trajet, $user) {
    $subject = "Annulation du trajet";
    $body = "Bonjour {$user['prenom']},<br>Le trajet <b>{$trajet['ville_depart']} → {$trajet['ville_arrivee']}</b> a été annulé par le conducteur.";
    return sendMail($to, $subject, $body);
}

// 5. Notification de démarrage du trajet
function sendTrajetStart($to, $trajet, $user) {
    $subject = "Votre trajet a démarré";
    $body = "Bonjour {$user['prenom']},<br>Le trajet <b>{$trajet['ville_depart']} → {$trajet['ville_arrivee']}</b> vient de démarrer.";
    return sendMail($to, $subject, $body);
}

// 6. Notification d’arrivée à destination
function sendTrajetArrival($to, $trajet, $user) {
    $subject = "Arrivée à destination";
    $body = "Bonjour {$user['prenom']},<br>Vous êtes arrivé à destination pour le trajet <b>{$trajet['ville_depart']} → {$trajet['ville_arrivee']}</b>. Merci d'utiliser EcoRide !";
    return sendMail($to, $subject, $body);
}

// 7. Demande d’avis
function sendReviewRequest($to, $trajet, $user) {
    $subject = "Donnez votre avis sur le trajet";
    $body = "Bonjour {$user['prenom']},<br>Merci de donner votre avis sur le trajet <b>{$trajet['ville_depart']} → {$trajet['ville_arrivee']}</b>.";
    return sendMail($to, $subject, $body);
}


// 8. Validation/refus d’avis
function sendReviewModeration($to, $avis, $isAccepted) {
    $subject = $isAccepted ? "Votre avis a été publié" : "Votre avis a été refusé";
    $body = $isAccepted ? "Bonjour,<br>Votre avis a été validé et publié." : "Bonjour,<br>Votre avis a été refusé par l'équipe de modération.";
    return sendMail($to, $subject, $body);
}

// 9. Notification de suspension de compte
function sendAccountSuspension($to, $user) {
    $subject = "Votre compte a été suspendu";
    $body = "Bonjour {$user['prenom']},<br>Votre compte EcoRide a été suspendu. Contactez le support pour plus d'informations.";
    return sendMail($to, $subject, $body);
}

// 10. Notification de crédit offert
function sendCreditOffer($to, $user, $credits) {
    $subject = "Crédits offerts";
    $body = "Bonjour {$user['prenom']},<br>Vous avez reçu <b>{$credits} crédits</b> sur votre compte EcoRide.";
    return sendMail($to, $subject, $body);
}

// 11. Notification de message privé
function sendPrivateMessageNotification($to, $fromUser, $message) {
    $subject = "Nouveau message reçu";
    $body = "Bonjour,<br>Vous avez reçu un nouveau message de {$fromUser['prenom']} {$fromUser['nom']}.<br>Message : {$message}";
    return sendMail($to, $subject, $body);
}

// 12. Notification de modification de trajet
function sendTrajetModification($to, $trajet, $user) {
    $subject = "Modification de votre trajet";
    $body = "Bonjour {$user['prenom']},<br>Le trajet <b>{$trajet['ville_depart']} → {$trajet['ville_arrivee']}</b> a été modifié. Merci de vérifier les nouveaux détails.";
    return sendMail($to, $subject, $body);
}

// 13. Notification de rappel de trajet
function sendTrajetReminder($to, $trajet, $user) {
    $subject = "Rappel : votre trajet approche";
    $body = "Bonjour {$user['prenom']},<br>Votre trajet <b>{$trajet['ville_depart']} → {$trajet['ville_arrivee']}</b> aura lieu bientôt. Pensez à vous préparer !";
    return sendMail($to, $subject, $body);
}

// === Notifications liées aux avis ===

// 1. Notification nouvel avis reçu
function sendAvisNotification($to, $destinataire, $note, $commentaire) {
    $subject = "Vous avez reçu un nouvel avis sur EcoRide";
    $body = "Bonjour {$destinataire['prenom']},<br>Vous venez de recevoir un nouvel avis :<br><b>Note :</b> {$note}/5<br><b>Commentaire :</b> {$commentaire}";
    return sendMail($to, $subject, $body);
}

// 2. Notification avis en attente de validation
function sendAvisEnAttenteValidation($to, $avis, $employe) {
    $subject = "Nouvel avis en attente de validation";
    $body = "Bonjour {$employe['prenom']},<br>Un nouvel avis est en attente de validation :<br><b>Note :</b> {$avis['note']}/5<br><b>Commentaire :</b> {$avis['commentaire']}<br>Merci de le traiter dans l'espace employé.";
    return sendMail($to, $subject, $body);
}

// 3. Notification avis validé
function sendAvisValide($to, $destinataire, $avis) {
    $subject = "Votre avis a été validé et publié";
    $body = "Bonjour {$destinataire['prenom']},<br>Votre avis a été validé et est désormais visible sur EcoRide.";
    return sendMail($to, $subject, $body);
}

// 4. Notification avis refusé ou modéré
function sendAvisRefuseOuModere($to, $auteur, $avis) {
    $subject = "Votre avis a été refusé ou modéré";
    $body = "Bonjour {$auteur['prenom']},<br>Votre avis n'a pas été publié ou a été modéré par l'équipe EcoRide.<br><b>Commentaire :</b> {$avis['commentaire']}";
    return sendMail($to, $subject, $body);
}

// 5. Notification avis signalé
function sendAvisSignale($to, $employe, $avis) {
    $subject = "Un avis a été signalé sur EcoRide";
    $body = "Bonjour {$employe['prenom']},<br>Un avis a été signalé par un utilisateur.<br><b>Note :</b> {$avis['note']}/5<br><b>Commentaire :</b> {$avis['commentaire']}<br>Merci de le vérifier dans l'espace employé.";
    return sendMail($to, $subject, $body);
}

// 6. Notification avis supprimé
function sendAvisSupprime($to, $user, $avis) {
    $subject = "Un avis a été supprimé";
    $body = "Bonjour {$user['prenom']},<br>L'avis suivant a été supprimé :<br><b>Note :</b> {$avis['note']}/5<br><b>Commentaire :</b> {$avis['commentaire']}";
    return sendMail($to, $subject, $body);
}


// Préférences ajoutées
function sendPreferenceAdded($to, $user) {
    $subject = "Préférences conducteur ajoutées";
    $body = "Bonjour {$user['prenom']},<br>Vos préférences conducteur ont bien été enregistrées sur EcoRide.";
    return sendMail($to, $subject, $body);
}

// Préférence modifiée
function sendPreferenceUpdated($to, $user, $preference, $valeur) {
    $subject = "Préférence modifiée";
    $body = "Bonjour {$user['prenom']},<br>Votre préférence <b>{$preference}</b> a été modifiée. Nouvelle valeur : <b>{$valeur}</b>.";
    return sendMail($to, $subject, $body);
}

// Préférence supprimée
function sendPreferenceDeleted($to, $user, $preference) {
    $subject = "Préférence supprimée";
    $body = "Bonjour {$user['prenom']},<br>Votre préférence <b>{$preference}</b> a été supprimée de votre profil conducteur.";
    return sendMail($to, $subject, $body);
}

// Mouvement de crédit (ajout, retrait, remboursement...)
function sendCreditMovement($to, $user, $montant, $type_operation) {
    $subject = "Mouvement de crédit sur votre compte";
    $body = "Bonjour {$user['prenom']},<br>Un mouvement de crédit a été effectué sur votre compte EcoRide.<br>"
        . "<b>Type d'opération :</b> {$type_operation}<br>"
        . "<b>Montant :</b> {$montant} crédits.";
    return sendMail($to, $subject, $body);
}

// Crédit expiré ou supprimé
function sendCreditExpired($to, $user) {
    $subject = "Crédit expiré ou supprimé";
    $body = "Bonjour {$user['prenom']},<br>Un ou plusieurs crédits ont expiré ou ont été supprimés de votre compte EcoRide.";
    return sendMail($to, $subject, $body);
}

// Notification générique pour les actions sur une voiture
function sendVoitureNotification($to, $user, $voiture, $action) {
    $actionLabels = [
        'création' => "ajoutée",
        'modification' => "modifiée",
        'suppression' => "supprimée"
    ];
    $label = isset($actionLabels[$action]) ? $actionLabels[$action] : $action;
    $subject = "Votre voiture a été $label";
    $body = "Bonjour {$user['prenom']},<br>La voiture <b>{$voiture->modele} ({$voiture->immatriculation})</b> a été $label sur votre compte EcoRide.";
    return sendMail($to, $subject, $body);
}

// Notification de bienvenue la création de compte
function envoyerEmailBienvenue($to, $user) {
    $subject = "Bienvenue sur EcoRide !";
    $body = "Bonjour {$user['prenom']},<br>Bienvenue sur EcoRide ! Nous sommes ravis de vous compter parmi nos utilisateurs. Profitez de nos services de covoiturage écologique et économique.";
    return sendMail($to, $subject, $body);
}