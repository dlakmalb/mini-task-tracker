#!/bin/sh
set -e

echo "Starting backend entrypoint..."

# Install composer dependencies if vendor directory is empty
if [ ! -d "vendor" ] || [ -z "$(ls -A vendor 2>/dev/null)" ]; then
  echo "Installing Composer dependencies..."
  composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Create necessary directories and set permissions
echo "Setting up directories and permissions..."
mkdir -p var/cache var/log
chown -R www-data:www-data var
chmod -R 775 var

# Wait for database to be ready
echo "Waiting for database..."
until php bin/console dbal:run-sql "SELECT 1" > /dev/null 2>&1; do
  echo "Database not ready, waiting..."
  sleep 2
done
echo "Database is ready!"

# Run migrations automatically
echo "Running database migrations..."
php bin/console doctrine:migrations:migrate --no-interaction

echo "Backend startup complete!"

exec "$@"
