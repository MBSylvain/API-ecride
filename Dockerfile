FROM php:8.1-apache

# Installer les extensions PHP nécessaires
RUN apt-get update \
	&& apt-get install -y --no-install-recommends libzip-dev unzip git \
	&& docker-php-ext-install mysqli pdo pdo_mysql zip \
	&& rm -rf /var/lib/apt/lists/*

# Activer le module rewrite
RUN a2enmod rewrite

# Copier les fichiers du projet
COPY . /var/www/html/
WORKDIR /var/www/html/

# Permissions pour Apache
RUN chown -R www-data:www-data /var/www/html

# Exposer le port Apache
EXPOSE 80
