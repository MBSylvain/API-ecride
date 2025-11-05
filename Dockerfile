
FROM php:8.1-apache

# Installer les extensions PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Activer le module rewrite
RUN a2enmod rewrite

# Installer Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

# Copier les fichiers du projet
COPY . /var/www/html/
WORKDIR /var/www/html/

# Installer les dépendances PHP (hors PHPMailer)
RUN composer install --no-dev

# Copier le fichier .env
COPY .env /var/www/html/.env

# Donner les permissions
RUN chown -R www-data:www-data /var/www/html

# Exposer le port Apache
EXPOSE 80
