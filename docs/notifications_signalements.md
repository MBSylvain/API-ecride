# Fonctions de notification pour les signalements

## 1. sendSignalementCreationNotification($email, $signalementData, $userData)
- Rôle : Notifier l’administrateur ou l’équipe qu’un nouveau signalement a été créé (par un utilisateur ou un employé).

## 2. sendSignalementStatusUpdate($email, $signalementData, $userData)
- Rôle : Notifier l’utilisateur ayant fait le signalement d’un changement de statut (ex : en cours, traité, refusé).

## 3. sendSignalementAffectationEmploye($email, $signalementData, $employeData)
- Rôle : Notifier un employé qu’un signalement lui a été affecté pour traitement.

## 4. sendSignalementResolution($email, $signalementData, $userData)
- Rôle : Notifier l’utilisateur concerné que le signalement a été résolu et l’informer de la décision prise.

---

Chaque fonction doit être appelée au moment clé du cycle de vie d’un signalement (création, affectation, changement de statut, résolution).