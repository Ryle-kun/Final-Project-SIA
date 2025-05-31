FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl unzip zip libpng-dev libonig-dev libxml2-dev \
    libzip-dev zip libpq-dev gnupg \
    && docker-php-ext-install pdo pdo_mysql zip

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy Composer from official image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy Laravel app into container
COPY . .

# Set Apache to serve from Laravel's public directory
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|' /etc/apache2/sites-available/000-default.conf && \
    echo '<Directory /var/www/html/public>\n\tAllowOverride All\n\tRequire all granted\n</Directory>' >> /etc/apache2/apache2.conf

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Install Node.js and build Vite (Vue) assets
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs \
    && npm install \
    && npm run build

# Ensure Vite outputs to correct path (public/build)
# Make sure your vite.config.js has: outDir: 'public/build', manifest: true

# Create required directories and fix permissions
RUN mkdir -p storage/logs storage/framework/{cache,sessions,views} bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache && \
    chown -R www-data:www-data storage bootstrap/cache public

# Generate Laravel app key (optional if set via .env)
RUN php artisan key:generate

# Cache config/views/routes
RUN php artisan config:cache \
 && php artisan route:cache \
 && php artisan view:cache

# Create startup script
RUN echo '#!/bin/bash\n\
# Wait for database to be ready\n\
sleep 5\n\
# Run migrations\n\
php artisan migrate --force\n\
# Start Apache\n\
apache2-foreground' > /start.sh && chmod +x /start.sh

# Expose port 80
EXPOSE 80

# Use startup script
CMD ["/start.sh"]
