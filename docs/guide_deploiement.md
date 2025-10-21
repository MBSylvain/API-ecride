
# Guide de déploiement EcoRide

## 1. Installation locale

### Prérequis
- XAMPP (Apache, PHP >= 8.0, MySQL/MariaDB)
- Composer (pour la gestion des dépendances PHP)
- Git (pour cloner le dépôt)
- Navigateur web moderne

### Étapes

1. **Cloner le dépôt**
	```sh
	git clone [URL_DU_DEPOT_GITHUB]
	cd api
	```

2. **Installer les dépendances PHP**
	```sh
	composer install
	```

3. **Configurer la base de données**
	- Démarrer MySQL via XAMPP.
	- Créer la base `covoiturage_db` (ou adapter le nom dans `config/Database.php`).
	- Importer le fichier SQL :
	  ```sh
	  mysql -u root -p covoiturage_db < covoiturage_db(8).sql
	  ```
	- (Adapter le nom du fichier SQL selon la version la plus récente.)

4. **Configurer les fichiers**
	- Vérifier les paramètres de connexion dans `config/Database.php`.
	- Configurer l’envoi de mails dans `utils/Mailer.php` (SMTP, mot de passe, etc.).
	- Si besoin, créer un fichier `.env` pour centraliser les variables sensibles (non versionné).

5. **Lancer le serveur local**
	- Placer le dossier `api` dans `htdocs` de XAMPP.
	- Démarrer Apache et MySQL via XAMPP.
	- Accéder à [http://localhost/api/index.php](http://localhost/api/index.php) pour vérifier l’API.

---

## 2. Déploiement serveur

### Choix de l’hébergeur
- Compatible avec Heroku, Fly.io, Vercel, Azure, etc.
- Pour un hébergement PHP natif, privilégier un VPS ou un PaaS compatible Docker.

### Procédure de déploiement (exemple Docker)

1. **Construire l’image Docker**
	```sh
	docker build -t ecoride-api .
	```

2. **Lancer le conteneur**
	```sh
	docker run -d -p 8080:80 --name ecoride-api ecoride-api
	```

3. **Configurer la base de données distante**
	- Créer la base sur le serveur cible.
	- Importer le fichier SQL comme en local.
	- Adapter les variables de connexion dans `config/Database.php` ou via variables d’environnement.

4. **Configurer le SMTP**
	- Modifier les paramètres SMTP dans `utils/Mailer.php` pour utiliser un compte mail de production (Gmail, Sendinblue, etc.).
	- Tester l’envoi de mail avec un endpoint de test.

5. **Sécuriser l’application**
	- Forcer HTTPS sur le serveur.
	- Restreindre l’accès aux fichiers sensibles.
	- Mettre à jour les mots de passe par défaut.

---

## 3. Tests post-déploiement

1. **Vérification des endpoints**
	- Tester les routes principales via Postman ou curl :
	  ```sh
	  curl http://[URL_SERVEUR]/api/Controllers/UtilisateurController.php
	  ```

2. **Test de l’envoi de notifications**
	- Créer un utilisateur ou une réservation pour déclencher un mail.
	- Vérifier la réception du mail.

3. **Validation du fonctionnement global**
	- Tester la création de compte, connexion, création de trajet, réservation, avis, etc.
	- Vérifier les logs d’erreur (`logs Apache`, `logs PHP`, etc.).

---

> N’hésite pas à ajouter des captures d’écran, des exemples de commandes spécifiques à ton hébergeur, et à adapter les chemins si besoin.