FROM php:7.4-fpm-alpine

ARG INSTALL_XDEBUG=no

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS icu-dev

RUN apk add --no-cache git icu php-intl python3 py3-pip

RUN wget https://get.symfony.com/cli/installer -O /tmp/symfony_installer \
    && sed -ri 's/^binary_dest=.*$/binary_dest=\/usr\/local\/bin/g' /tmp/symfony_installer \
    && sed -ri 's/builtin echo/echo/g' /tmp/symfony_installer \
    && sh /tmp/symfony_installer \
    && rm /tmp/symfony_installer

RUN pip3 install awscli

RUN docker-php-ext-install -j$(nproc) intl opcache

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

RUN wget https://getcomposer.org/installer -O /tmp/composer_installer \
        && php /tmp/composer_installer --install-dir=/usr/local/bin --filename=composer \
        && rm /tmp/composer_installer

RUN if [ "$INSTALL_XDEBUG" == "yes" ]; then \
        echo "Setting up XDebug" \
        && pecl install xdebug \
        && docker-php-ext-enable xdebug.so \
        && sed -r -i'' 's/set -e/set -e\ncd \/etc\nsed \/host.docker.internal\/d hosts > hosts.new\ncat hosts.new > hosts\nip r | grep "default via" | cut -f3 -d" " | tr -d "\\n" >> hosts\necho " host.docker.internal" >> hosts\ncd -\n/' /usr/local/bin/docker-php-entrypoint \
        && echo "xdebug.remote_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
        && echo "xdebug.remote_enable=1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini; \
    else \
        echo "XDebug skipped"; \
    fi

RUN apk del .build-deps

VOLUME /var/www/html
WORKDIR /var/www/html
