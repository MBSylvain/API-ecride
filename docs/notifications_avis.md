# Notifications des avis - Fonctions et rôles

Ce fichier recense les fonctions de notification liées aux avis dans EcoRide, avec leur rôle et contexte d'utilisation.

---

## 1. sendAvisNotification
- **Rôle** : Notifier le destinataire qu'il a reçu un nouvel avis.
- **Contexte** : Lorsqu'un utilisateur reçoit un avis sur son profil.

## 2. sendAvisEnAttenteValidation
- **Rôle** : Notifier l'employé qu'un nouvel avis est en attente de validation.
- **Contexte** : Lorsqu'un avis doit être validé avant publication.

## 3. sendAvisValide
- **Rôle** : Informer le destinataire que l'avis a été validé et publié.
- **Contexte** : Après validation par un employé.

## 4. sendAvisRefuseOuModere
- **Rôle** : Informer l'auteur que son avis a été refusé ou modéré.
- **Contexte** : Après modération ou refus par un employé.

## 5. sendAvisSignale
- **Rôle** : Notifier l'employé qu'un avis a été signalé par un utilisateur.
- **Contexte** : Lorsqu'un avis est signalé pour traitement.

## 6. sendAvisSupprime
- **Rôle** : Informer l'auteur et/ou le destinataire que l'avis a été supprimé.
- **Contexte** : Lorsqu'un avis est supprimé par un employé ou administrateur.

---

Chaque fonction doit recevoir les informations nécessaires (email, prénom, nom, contenu de l'avis, etc.) et utiliser le mailer centralisé pour l'envoi.

Ce fichier peut être enrichi selon les besoins du projet.
