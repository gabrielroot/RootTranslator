#!/bin/bash
set -e

# If vendor directory doesn't exist, install dependencies
if [ ! -d "/var/www/vendor" ]; then
    echo "📦 Installing Composer dependencies..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# If .env doesn't exist, create from example
if [ ! -f "/var/www/.env" ]; then
    echo "📝 Creating .env file..."
    cp /var/www/.env.example /var/www/.env
    php artisan key:generate
fi

# If node_modules doesn't exist, install npm dependencies
if [ -f "/var/www/package.json" ] && [ ! -d "/var/www/node_modules" ]; then
    echo "📦 Installing npm dependencies..."
    npm install
fi

echo "✅ Application ready!"

# If Octane is being used, start it instead of php-fpm
if [ "$1" = "octane" ] || [ "$1" = "octane:start" ]; then
    echo "🚀 Starting Laravel Octane with Swoole..." >&2
    
    # Check if watch mode is requested via environment variable
    if [ "$OCTANE_WATCH" = "true" ]; then
        echo "👀 Watch mode enabled - auto-reloading on file changes" >&2
        exec php artisan octane:start --server=swoole --host=0.0.0.0 --port=8000 --watch
    else
        echo "⚠️  Watch mode disabled - manual reload required for code changes" >&2
        exec php artisan octane:start --server=swoole --host=0.0.0.0 --port=8000
    fi
else
    exec "$@"
fi
