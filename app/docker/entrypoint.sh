#!/bin/sh
set -e

# Generate APP_KEY if not set
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:placeholder" ]; then
    echo "Generating APP_KEY..."
    export APP_KEY=$(php artisan key:generate --show)
fi

# Copy public assets to volume for nginx (if volume is empty)
if [ ! -f /var/www/public/build/manifest.json ] 2>/dev/null; then
    echo "Populating public volume..."
    cp -rn /tmp/public_build/. /var/www/public/ 2>/dev/null || true
fi

# Wait for PostgreSQL
echo "Waiting for database..."
until php -r "
    try {
        \$pdo = new PDO(
            'pgsql:host=${DB_HOST};dbname=${DB_DATABASE}',
            '${DB_USERNAME}',
            '${DB_PASSWORD}'
        );
        exit(0);
    } catch (PDOException \$e) {
        exit(1);
    }
" 2>/dev/null; do
    sleep 2
done
echo "Database ready."

# Run migrations
php artisan migrate --force

# Seed if fresh
php artisan db:seed --force 2>/dev/null || true

# Clear caches for development
php artisan config:clear
php artisan route:clear
php artisan view:clear

exec "$@"
