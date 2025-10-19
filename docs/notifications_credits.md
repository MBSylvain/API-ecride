# Notifications des crédits - Fonctions et rôles

Ce fichier recense les fonctions de notification liées aux crédits dans EcoRide, avec leur rôle et contexte d'utilisation.

---

## 1. sendCreditOffer
- **Rôle** : Notifier l'utilisateur qu'il a reçu des crédits (offerts à l'inscription, bonus, etc.).
- **Contexte** : Lorsqu'un utilisateur reçoit des crédits sur son compte.

## 2. sendCreditMovement
- **Rôle** : Informer l'utilisateur d'un mouvement de crédit (ajout, retrait, remboursement, etc.).
- **Contexte** : Lorsqu'un crédit est ajouté ou retiré suite à une action (réservation, annulation, etc.).

## 3. sendCreditLowAlert
- **Rôle** : Alerter l'utilisateur que son solde de crédits est faible.
- **Contexte** : Lorsque le solde de crédits passe sous un seuil défini.

## 4. sendCreditExpired
- **Rôle** : Informer l'utilisateur que des crédits sont expirés ou supprimés.
- **Contexte** : Lorsqu'une opération d'expiration ou suppression de crédits est effectuée.

---

Chaque fonction doit recevoir les informations nécessaires (email, prénom, montant, type d'opération, etc.) et utiliser le mailer centralisé pour l'envoi.

Ce fichier peut être enrichi selon les besoins du projet.
