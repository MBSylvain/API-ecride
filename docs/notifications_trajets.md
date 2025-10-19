# Notifications des trajets - Fonctions et rôles

Ce fichier recense les fonctions de notification liées aux trajets dans EcoRide, avec leur rôle et contexte d'utilisation.

---

## 1. sendTrajetCreationConfirmation
- **Rôle** : Notifier le conducteur que son trajet a bien été créé.
- **Contexte** : Lorsqu'un utilisateur crée un nouveau trajet.

## 2. sendTrajetCancellation
- **Rôle** : Informer les participants que le trajet a été annulé.
- **Contexte** : Lorsqu'un trajet est annulé par le conducteur ou l'administrateur.

## 3. sendTrajetStart
- **Rôle** : Notifier les participants que le trajet a démarré.
- **Contexte** : Lors du passage du statut à "en_cours".

## 4. sendTrajetArrival
- **Rôle** : Notifier les participants de l'arrivée à destination.
- **Contexte** : Lors du passage du statut à "terminé".

## 5. sendTrajetModification
- **Rôle** : Informer les participants d'une modification du trajet.
- **Contexte** : Lorsqu'un détail du trajet est modifié (horaire, lieu, etc.).

## 6. sendTrajetReminder
- **Rôle** : Rappeler aux participants qu'un trajet va bientôt commencer.
- **Contexte** : À l'approche de la date/heure de départ.

---

Chaque fonction doit recevoir les informations nécessaires (email, prénom, détails du trajet, etc.) et utiliser le mailer centralisé pour l'envoi.

Ce fichier peut être enrichi selon les besoins du projet.
