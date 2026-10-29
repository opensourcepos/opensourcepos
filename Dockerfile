FROM php:8.2-apache AS ospos
LABEL maintainer="jekkos"

COPY composer.json /tmp/composer.json
RUN php -r '$c = json_decode(file_get_contents("/tmp/composer.json"), true); $branch = PHP_MAJOR_VERSION . "." . PHP_MINOR_VERSION; if (preg_match("/(?:^|[|,\s])>=?\s*" . preg_quote($branch, "/") . "\.(\d+)/", $c["require"]["php"] ?? "", $m) && version_compare(PHP_VERSION, $branch . "." . $m[1], "<")) { fwrite(STDERR, "ERROR: PHP version " . PHP_VERSION . " is below required minimum " . $branch . "." . $m[1] . " (from composer.json). Run docker build --pull to update base image.\n"); exit(1); }' \
    && rm /tmp/composer.json

RUN apt-get update && apt-get install -y --no-install-recommends \
    libicu-dev \
    libgd-dev \
    && docker-php-ext-install mysqli bcmath intl gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* \
    && a2enmod rewrite

RUN echo "date.timezone = \"\${PHP_TIMEZONE}\"" > /usr/local/etc/php/conf.d/timezone.ini \
    && echo "expose_php = Off" > /usr/local/etc/php/conf.d/security.ini

WORKDIR /app
COPY . /app
RUN rm -f /app/composer.json \
    && chmod -R go-w /app \
    && chown -R www-data:www-data /app/writable /app/public/uploads \
    && chmod 750 /app/writable/logs /app/writable/uploads /app/writable/cache /app/public/uploads /app/public/uploads/item_pics \
    && chmod 640 /app/writable/uploads/importCustomers.csv \
    && ln -s /app/*[^public] /var/www \
    && rm -rf /var/www/html \
    && ln -nsf /app/public /var/www/html

FROM ospos AS ospos_dev

ARG USERID
ARG GROUPID

RUN echo "Adding user uid $USERID with gid $GROUPID"
RUN ( addgroup --gid $GROUPID ospos || true ) && ( adduser --uid $USERID --gid $GROUPID ospos )

RUN yes | pecl install xdebug \
    && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_autostart=off" >> /usr/local/etc/php/conf.d/xdebug.ini
