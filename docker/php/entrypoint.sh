#!/bin/sh
set -ex

# Check if the vendor directory exists; if not, run composer install
if [ ! -d "/var/www/crypto/vendor" ]; then
  echo "Vendor directory not found. Running composer install..."
  bash -c "composer install --no-dev --optimize-autoloader --no-interaction --no-suggest"
else
  echo "Vendor directory found. Skipping composer install."
fi

# Clear cached files
bash -c "php artisan optimize:clear"

# Run the queue worker in the background
bash -c "php artisan queue:work --daemon &"

# Start PHP-FPM (the main process)
exec docker-php-entrypoint php-fpm
