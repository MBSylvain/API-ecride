
# Documentation technique EcoRide

## 1. Architecture du projet

- **Modèle MVC**
	- **Controllers/** : Logique métier, gestion des requêtes HTTP, routage, validation, appels aux modèles.
		- Exemples : `TrajetController.php`, `ReservationController.php`, `UtilisateurController.php`, `AvisController.php`, `VoitureController.php`, `HistoriqueActionController.php`, `CreditController.php`, `NotificationController.php`, etc.
		- Espace admin : `ControllersAdministrateur/TrajetAdminController.php`, `ControllersAdministrateur/UtilisateurAdminController.php`, `ControllersAdministrateur/SignalementAdminController.php`, etc.
	- **models/** : Accès aux données, mapping des tables SQL, méthodes CRUD.
		- Exemples : `Trajet.php`, `Reservation.php`, `Utilisateur.php`, `Avis.php`, `Voiture.php`, `Credit.php`, `Notification.php`, `HistoriqueAction.php`, `Dashboard.php`, etc.
		- Espace admin : `ModelAdministrateur/Trajet.php`, `ModelAdministrateur/Utilisateur.php`, `ModelAdministrateur/Signalement.php`, `ModelAdministrateur/Statistique.php`
	- **config/** : Configuration BDD, gestion session, headers.
		- Exemples : `Database.php`, `session.php`, `headers.php`
	- **Utils/** : Fonctions utilitaires, notifications, mails.
		- Exemples : `NotificationMails.php`, `Mailer.php`
	- **docs/** : Documentation, manuel utilisateur, fixtures, technique.
		- Exemples : `manuel_utilisation.md`, `fixtures_documentation.md`, `documentation_technique.md`

## 2. Configuration de l’environnement

- Prérequis : XAMPP, PHP >= 7.4, Composer, MySQL/MariaDB
- Installation des dépendances : PHPMailer (pour notifications), autres via `composer.json`
- Configuration SMTP : Paramètres dans `Mailer.php` et variables d’environnement
- Variables d’environnement : accès BDD, clés API, etc.

## 3. Endpoints API

- Structure RESTful, chaque contrôleur gère une ressource
- Exemples :
	- `/trajets` : GET, POST, PUT, DELETE (`TrajetController.php`)
	- `/reservations` : GET, POST, PUT, DELETE (`ReservationController.php`)
	- `/utilisateurs` : GET, POST, PUT, DELETE (`UtilisateurController.php`)
	- `/avis` : GET, POST, PUT, DELETE (`AvisController.php`)
	- `/voitures` : GET, POST, PUT, DELETE (`VoitureController.php`)
	- `/notifications` : POST (`NotificationController.php`)
	- `/historique_actions` : GET, POST, DELETE (`HistoriqueActionController.php`)
	- `/credits` : POST (`CreditController.php`)
- Gestion des erreurs : codes HTTP, messages JSON
- Validation des entrées : contrôleurs + modèles

## 4. Sécurité

- Authentification : gestion session PHP (`session.php`), vérification dans chaque contrôleur
- Rôles : utilisateur, conducteur, employé, administrateur (vérification dans les contrôleurs admin)
- Validation des entrées : fonctions utilitaires, nettoyage dans modèles
- Protection SQL : requêtes préparées PDO dans tous les modèles

## 5. Notifications

- Envoi de mails via PHPMailer (`NotificationMails.php`, `Mailer.php`)
- Points d’intégration : confirmation réservation, annulation, création trajet, avis, signalement
- Modèles de notification : personnalisés selon action et rôle

## 6. Bonnes pratiques

- Organisation du code : séparation stricte MVC, commentaires, documentation
- Utilisation de Git : branches main/dev/features, conventions de commit
- Documentation : manuel utilisateur, doc technique, doc fixtures
- Tests unitaires : à prévoir pour les modèles et contrôleurs critiques

---

Ce plan peut être enrichi avec des exemples de code, des schémas, et des cas d’usage pour chaque section. Pour chaque endpoint, détaille les paramètres, les réponses, et les cas d’erreur. Pour chaque modèle, explique les méthodes principales et leur usage dans les contrôleurs.