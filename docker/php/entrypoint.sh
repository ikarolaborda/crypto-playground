#!/bin/sh
set -ex

# Clear cached files
bash -c "php artisan optimize:clear"

# Run the queue worker in the background
bash -c "php artisan queue:work --daemon &"

# Start PHP-FPM (the main process)
exec docker-php-entrypoint php-fpm
