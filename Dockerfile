FROM php:7.0.29-apache
MAINTAINER jekkos
RUN apt-get update && DEBIAN_FRONTEND=noninteractive apt-get install -y \
    libicu-dev \
    libgd-dev \
    openssl

RUN a2enmod rewrite
RUN docker-php-ext-install mysqli bcmath intl gd
RUN echo "date.timezone = \"\${PHP_TIMEZONE}\"" > /usr/local/etc/php/conf.d/timezone.ini
RUN echo -e “$(hostname -i)\t$(hostname) $(hostname).localhost” >> /etc/hosts

WORKDIR /app
COPY . /app
RUN ln -s /app/*[^public] /var/www && rm -rf /var/www/html && ln -nsf /app/public /var/www/html
RUN chmod 755 /app/public/uploads && chown -R www-data:www-data /app/public /app/application

RUN [ ! -f test/ospos.js ] || sed -i -e "s/\(localhost\)/web/g" test/ospos.js
