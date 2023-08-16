#!/bin/sh

set -eux

if [ "$DEV_MACHINE" = "yes" ]; then
    sed '/host.docker.internal/d' /etc/hosts > /tmp/hosts.new && cat /tmp/hosts.new > /etc/hosts
    ip r | grep "default via" | cut -f3 -d" " | tr -d "\n" >> /etc/hosts
    echo "	host.docker.internal" >> /etc/hosts
fi

mkdir -p -m 700 ./var
mkdir -p -m 700 ./var/cache
mkdir -p -m 700 ./var/log
mkdir -p -m 700 ./var/sessions

for TARGET in \
        /var/www/html/var \
        /composer \
; do
    setfacl  -R -m u:www-data:rwX -m u:"$DOCKER_UID":rwX "$TARGET"
    setfacl -dR -m u:www-data:rwX -m u:"$DOCKER_UID":rwX "$TARGET"
done

exec php-fpm
