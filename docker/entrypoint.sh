#!/bin/sh
set -e

ENV_FILE="/var/www/html/.env"

# Auto-generate APP_KEY on first run and write it back to the mounted .env
if [ -f "$ENV_FILE" ] && ! grep -q "^APP_KEY=.\+" "$ENV_FILE"; then
    echo "Generating application key..."
    php artisan key:generate --force
fi

# Register service providers (writes bootstrap/cache/packages.php)
php artisan package:discover --ansi

# Cache config, routes, views and events for performance
php artisan optimize

# Run pending migrations (idempotent — safe to run on every startup)
echo "Running database migrations..."
php artisan migrate --force

exec "$@"
