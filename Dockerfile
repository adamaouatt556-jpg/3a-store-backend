FROM php:8.4-fpm-alpine

# Installer les extensions PHP nécessaires
RUN apk add --no-cache \
    nginx \
    postgresql-dev \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    curl

RUN docker-php-ext-install \
    pdo_pgsql \
    pgsql \
    gd \
    zip \
    opcache

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copier le projet
WORKDIR /var/www/html
COPY . .

# Installer les dépendances
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Copier la config nginx
COPY docker/nginx.conf /etc/nginx/nginx.conf

# Script de démarrage
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 10000

CMD ["/start.sh"]