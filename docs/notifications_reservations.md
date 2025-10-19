# Notifications des réservations - Fonctions et rôles

Ce fichier recense les fonctions de notification liées aux réservations dans EcoRide, avec leur rôle et contexte d'utilisation.

---

## 1. sendReservationConfirmation
- **Rôle** : Notifier l'utilisateur que sa réservation a été confirmée.
- **Contexte** : Lorsqu'une réservation est validée par le conducteur.

## 2. sendReservationCancellation
- **Rôle** : Informer l'utilisateur que sa réservation a été annulée.
- **Contexte** : Lorsqu'une réservation est annulée par le conducteur ou l'utilisateur.

## 3. sendReservationCreated
- **Rôle** : Confirmer à l'utilisateur la création de sa réservation (statut en attente).
- **Contexte** : Lorsqu'une réservation est créée et en attente de confirmation.

## 4. sendReservationRefused
- **Rôle** : Informer l'utilisateur que sa réservation a été refusée.
- **Contexte** : Lorsqu'une réservation est refusée par le conducteur.

## 5. sendReservationModified
- **Rôle** : Informer l'utilisateur que sa réservation a été modifiée.
- **Contexte** : Lorsqu'une réservation change (nombre de places, point de RDV, etc.).

## 6. sendReservationReminder
- **Rôle** : Rappeler à l'utilisateur une réservation à venir.
- **Contexte** : Avant le départ du trajet réservé.

---

Chaque fonction doit recevoir les informations nécessaires (email, prénom, détails du trajet, statut, etc.) et utiliser le mailer centralisé pour l'envoi.

Ce fichier peut être enrichi selon les besoins du projet.
