#!/bin/bash
set -e

echo "🚀 Starting production environment..."

# Ensure storage directories exist and have correct permissions
echo "📁 Setting up storage directories..."
mkdir -p /var/www/storage/framework/{sessions,views,cache}
mkdir -p /var/www/storage/logs
mkdir -p /var/www/bootstrap/cache

# Set permissions (if running as root initially)
if [ "$(id -u)" = "0" ]; then
    chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache
fi

# Verify build assets exist
if [ ! -f "/var/www/public/build/manifest.json" ]; then
    echo "⚠️  WARNING: manifest.json not found in /var/www/public/build/"
    echo "   Frontend assets may not load correctly."
    echo "   Contents of /var/www/public/build/:"
    ls -la /var/www/public/build/ 2>/dev/null || echo "   Directory does not exist!"
fi

# Cache configuration for production
echo "⚡ Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations if AUTO_MIGRATE is set
if [ "${AUTO_MIGRATE:-false}" = "true" ]; then
    echo "🗄️  Running database migrations..."
    php artisan migrate --force
fi

echo "✅ Production environment ready!"

exec "$@"
