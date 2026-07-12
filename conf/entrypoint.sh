#!/usr/bin/env sh
set -e

php-fpm82 -D
sleep 5

echo "FPM is running and ready. Starting nginx."
nginx -g 'daemon off;'
sleep .5
#chown -R nginx:nginx /run/php-fpm82.sock

#tail -f /dev/null