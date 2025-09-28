# Utiliser une image officielle PHP avec Apache
FROM php:8.1-apache

# Installer les extensions PHP nécessaires (ajoutez celles dont vous avez besoin)
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copier les fichiers de votre projet dans le conteneur
COPY . /var/www/html/

# Définir le répertoire de travail
WORKDIR /var/www/html/

# Donner les permissions nécessaires
RUN chown -R www-data:www-data /var/www/html

# Exposer le port 80 pour le serveur Apache
EXPOSE 80