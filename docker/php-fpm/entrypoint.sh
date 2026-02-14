#!/bin/sh

set -eux

mkdir -p -m 700 ./var
mkdir -p -m 700 ./var/cache
mkdir -p -m 700 ./var/log
mkdir -p -m 700 ./var/sessions
mkdir -p -m 700 /tmp/phpstan

for TARGET in \
        /var/www/html/var \
        /tmp/phpstan \
; do
    setfacl  -R -m u:www-data:rwX -m u:"$DOCKER_UID":rwX "$TARGET"
    setfacl -dR -m u:www-data:rwX -m u:"$DOCKER_UID":rwX "$TARGET"
done

exec php-fpm
