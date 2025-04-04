FROM php:8.2-apache AS ospos
LABEL maintainer="jekkos"

RUN apt update && apt-get install -y libicu-dev libgd-dev
RUN a2enmod rewrite
RUN docker-php-ext-install mysqli bcmath intl gd
RUN echo "date.timezone = \"\${PHP_TIMEZONE}\"" > /usr/local/etc/php/conf.d/timezone.ini

WORKDIR /app
COPY . /app
RUN ln -s /app/*[^public] /var/www && rm -rf /var/www/html && ln -nsf /app/public /var/www/html
RUN chmod -R 770 /app/writable/uploads /app/writable/logs /app/writable/cache && chown -R www-data:www-data /app

FROM ospos AS ospos_test

COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN apt-get install -y libzip-dev wget git
RUN wget https://raw.githubusercontent.com/vishnubob/wait-for-it/master/wait-for-it.sh -O /bin/wait-for-it.sh && chmod +x /bin/wait-for-it.sh
RUN docker-php-ext-install zip
RUN composer install -d/app
#RUN sed -i 's/backupGlobals="true"/backupGlobals="false"/g' /app/tests/phpunit.xml
WORKDIR /app/tests

CMD ["/app/vendor/phpunit/phpunit/phpunit"]

FROM ospos AS ospos_dev

ARG USERID
ARG GROUPID

RUN echo "Adding user uid $USERID with gid $GROUPID"
RUN ( addgroup --gid $GROUPID ospos || true ) && ( adduser --uid $USERID --gid $GROUPID ospos )

RUN yes | pecl install xdebug \
    && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_autostart=off" >> /usr/local/etc/php/conf.d/xdebug.ini
