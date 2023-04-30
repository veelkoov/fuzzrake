#!/bin/sh

set -eux

if [ "$DEV_MACHINE" = "yes" ]; then
    sed '/host.docker.internal/d' /etc/hosts > /tmp/hosts.new && cat /tmp/hosts.new > /etc/hosts
    ip r | grep "default via" | cut -f3 -d" " | tr -d "\n" >> /etc/hosts
    echo "	host.docker.internal" >> /etc/hosts
fi

setfacl  -R -m u:www-data:rwX -m u:"$DOCKER_UID":rwX /var/www/html/var
setfacl -dR -m u:www-data:rwX -m u:"$DOCKER_UID":rwX /var/www/html/var
setfacl  -R -m u:www-data:rwX -m u:"$DOCKER_UID":rwX /composer
setfacl -dR -m u:www-data:rwX -m u:"$DOCKER_UID":rwX /composer

exec php-fpm
