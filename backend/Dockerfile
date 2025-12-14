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

# Create entrypoint script directly in Dockerfile (no external file dependency)
RUN echo '#!/bin/sh' > /usr/local/bin/docker-entrypoint.sh && \
    echo 'set -e' >> /usr/local/bin/docker-entrypoint.sh && \
    echo '' >> /usr/local/bin/docker-entrypoint.sh && \
    echo 'echo "Starting backend entrypoint..."' >> /usr/local/bin/docker-entrypoint.sh && \
    echo '' >> /usr/local/bin/docker-entrypoint.sh && \
    echo '# Install composer dependencies if vendor directory is empty' >> /usr/local/bin/docker-entrypoint.sh && \
    echo 'if [ ! -d "vendor" ] || [ -z "$(ls -A vendor 2>/dev/null)" ]; then' >> /usr/local/bin/docker-entrypoint.sh && \
    echo '  echo "Installing Composer dependencies..."' >> /usr/local/bin/docker-entrypoint.sh && \
    echo '  composer install --no-interaction --prefer-dist --optimize-autoloader' >> /usr/local/bin/docker-entrypoint.sh && \
    echo 'fi' >> /usr/local/bin/docker-entrypoint.sh && \
    echo '' >> /usr/local/bin/docker-entrypoint.sh && \
    echo '# Create necessary directories and set permissions' >> /usr/local/bin/docker-entrypoint.sh && \
    echo 'echo "Setting up directories and permissions..."' >> /usr/local/bin/docker-entrypoint.sh && \
    echo 'mkdir -p var/cache var/log' >> /usr/local/bin/docker-entrypoint.sh && \
    echo 'chown -R www-data:www-data var' >> /usr/local/bin/docker-entrypoint.sh && \
    echo 'chmod -R 775 var' >> /usr/local/bin/docker-entrypoint.sh && \
    echo '' >> /usr/local/bin/docker-entrypoint.sh && \
    echo '# Wait for database to be ready' >> /usr/local/bin/docker-entrypoint.sh && \
    echo 'echo "Waiting for database..."' >> /usr/local/bin/docker-entrypoint.sh && \
    echo 'until php bin/console dbal:run-sql "SELECT 1" > /dev/null 2>&1; do' >> /usr/local/bin/docker-entrypoint.sh && \
    echo '  echo "Database not ready, waiting..."' >> /usr/local/bin/docker-entrypoint.sh && \
    echo '  sleep 2' >> /usr/local/bin/docker-entrypoint.sh && \
    echo 'done' >> /usr/local/bin/docker-entrypoint.sh && \
    echo 'echo "Database is ready!"' >> /usr/local/bin/docker-entrypoint.sh && \
    echo '' >> /usr/local/bin/docker-entrypoint.sh && \
    echo '# Run migrations automatically' >> /usr/local/bin/docker-entrypoint.sh && \
    echo 'echo "Running database migrations..."' >> /usr/local/bin/docker-entrypoint.sh && \
    echo 'php bin/console doctrine:migrations:migrate --no-interaction' >> /usr/local/bin/docker-entrypoint.sh && \
    echo '' >> /usr/local/bin/docker-entrypoint.sh && \
    echo 'echo "Backend startup complete!"' >> /usr/local/bin/docker-entrypoint.sh && \
    echo '' >> /usr/local/bin/docker-entrypoint.sh && \
    echo 'exec "$@"' >> /usr/local/bin/docker-entrypoint.sh && \
    chmod +x /usr/local/bin/docker-entrypoint.sh

# Health check
HEALTHCHECK --interval=10s --timeout=3s --start-period=30s \
    CMD SCRIPT_NAME=/ping SCRIPT_FILENAME=/ping REQUEST_METHOD=GET cgi-fcgi -bind -connect 127.0.0.1:9000 || exit 1

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]
