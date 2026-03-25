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

exec "$@"
