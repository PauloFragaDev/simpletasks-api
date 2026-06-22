#!/bin/sh
set -e

ENV_FILE="/var/www/html/.env"

# Auto-generate APP_KEY on first run.
# Only runs when .env is mounted (local Docker); Railway injects APP_KEY via env vars.
if [ -f "$ENV_FILE" ] && ! grep -q "^APP_KEY=.\+" "$ENV_FILE"; then
    echo "Generating application key..."
    php artisan key:generate --force
fi

# Register service providers (writes bootstrap/cache/packages.php)
php artisan package:discover --ansi

# Clear any stale cache then rebuild from current env vars
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan event:cache

# Run pending migrations (idempotent — safe to run on every startup)
echo "Running database migrations..."
php artisan migrate --force

# For Railway: use $PORT if injected, otherwise fall back to the port in $@
if [ -n "$PORT" ]; then
    set -- php artisan serve --host=0.0.0.0 --port="$PORT"
fi

exec "$@"
