FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl unzip zip libpng-dev libonig-dev libxml2-dev \
    libzip-dev zip libpq-dev gnupg nodejs npm \
    && docker-php-ext-install pdo pdo_mysql zip

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy Composer from official image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy app files (Laravel, package.json, vite.config.js, etc.)
COPY . .

# Set Apache to serve from Laravel's public directory
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|' /etc/apache2/sites-available/000-default.conf && \
    echo '<Directory /var/www/html/public>\n\tAllowOverride All\n\tRequire all granted\n</Directory>' >> /etc/apache2/apache2.conf

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Install JS dependencies & build Vite assets
RUN npm install && npm run build

# If Vite outputs to .vite/manifest.json, move it
RUN if [ -f public/build/.vite/manifest.json ]; then mv public/build/.vite/manifest.json public/build/manifest.json; fi

# Fix permissions for Laravel
RUN mkdir -p storage/logs storage/framework/{cache,sessions,views} bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache && \
    chown -R www-data:www-data storage bootstrap/cache public

# Generate app key
RUN php artisan key:generate

# Cache config, routes, views
RUN php artisan config:cache \
 && php artisan route:cache \
 && php artisan view:cache

# Create startup script
RUN echo '#!/bin/bash\n\
sleep 5\n\
php artisan migrate --force\n\
apache2-foreground' > /start.sh && chmod +x /start.sh

# Expose port 80
EXPOSE 80

# Start Laravel app
CMD ["/start.sh"]
