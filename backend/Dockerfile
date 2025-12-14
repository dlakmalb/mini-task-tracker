FROM php:8.4-fpm-alpine

# Install system dependencies and PHP extensions
RUN apk add --no-cache \
    git \
    unzip \
    icu-dev \
    oniguruma-dev \
    libzip-dev \
    fcgi \
    && docker-php-ext-install intl pdo pdo_mysql opcache zip

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files first for better layer caching
COPY backend/composer.json backend/composer.lock backend/symfony.lock ./

# Install dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# Copy application files
COPY backend/ ./

# Copy PHP configuration from parent directory
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Copy and set up entrypoint from parent directory
COPY docker/backend/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Health check
HEALTHCHECK --interval=10s --timeout=3s --start-period=30s \
    CMD SCRIPT_NAME=/ping SCRIPT_FILENAME=/ping REQUEST_METHOD=GET cgi-fcgi -bind -connect 127.0.0.1:9000 || exit 1

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]
