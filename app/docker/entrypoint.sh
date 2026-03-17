#!/bin/sh
set -e

if [ -d /tmp/public_build ]; then
    cp -r /tmp/public_build/* /var/www/public/
fi

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec "$@"
