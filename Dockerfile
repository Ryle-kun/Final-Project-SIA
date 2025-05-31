FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl unzip zip libpng-dev libonig-dev libxml2-dev \
    libzip-dev zip libpq-dev gnupg \
    && docker-php-ext-install pdo pdo_mysql zip

# Enable Apache mod_rewrite and headers
RUN a2enmod rewrite headers

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

# Install Node.js and build assets
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs \
    && npm install \
    && npm run build

# Create .env for production if it doesn't exist
RUN if [ ! -f .env ]; then cp .env.example .env; fi

# Generate application key
RUN php artisan key:generate --force

# Cache Laravel configurations
RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

# Set correct permissions for Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/public

# Expose port 80
EXPOSE 80

# Start Apache in foreground
CMD ["apache2-foreground"]