#!/bin/sh

# Générer la clé si nécessaire
php artisan key:generate --force

# Lancer les migrations
php artisan migrate --force

# Créer le lien storage
php artisan storage:link --force

# Optimiser Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Démarrer PHP-FPM en arrière-plan
php-fpm -D

# Démarrer Nginx au premier plan
nginx -g "daemon off;"