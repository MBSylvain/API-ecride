# Notifications des préférences conducteur - Fonctions et rôles

Ce fichier recense les fonctions de notification liées aux préférences conducteur dans EcoRide, avec leur rôle et contexte d'utilisation.

---

## 1. sendPreferenceAdded
- **Rôle** : Notifier l'utilisateur que ses préférences conducteur ont été ajoutées.
- **Contexte** : Lorsqu'un utilisateur ajoute ses préférences pour la première fois.

## 2. sendPreferenceUpdated
- **Rôle** : Informer l'utilisateur qu'une préférence conducteur a été modifiée.
- **Contexte** : Lorsqu'une préférence individuelle est mise à jour.

## 3. sendPreferenceDeleted
- **Rôle** : Informer l'utilisateur qu'une préférence conducteur a été supprimée.
- **Contexte** : Lorsqu'une préférence individuelle est supprimée.

---

Chaque fonction doit recevoir les informations nécessaires (email, prénom, type de préférence, nouvelle valeur, etc.) et utiliser le mailer centralisé pour l'envoi.

Ce fichier peut être enrichi selon les besoins du projet.
