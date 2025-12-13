FROM php:8.4-fpm-alpine

# Install system deps + PHP extensions
RUN apk add --no-cache \
    git unzip icu-dev oniguruma-dev libzip-dev \
    && docker-php-ext-install intl pdo pdo_mysql opcache zip

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files
COPY composer.json composer.lock symfony.lock ./

COPY bin/ ./bin
COPY config/ ./config
COPY src/ ./src
COPY public/ ./public

# Install dependencies
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

COPY . .

CMD ["php-fpm"]
