FROM php:5-apache
MAINTAINER jekkos
RUN apt-get update && DEBIAN_FRONTEND=noninteractive apt-get install -y \
    php5-apcu \
    libicu-dev \
    libgd-dev \
    sendmail

RUN a2enmod rewrite
RUN docker-php-ext-install mysql mysqli bcmath intl gd sockets mbstring
RUN echo "date.timezone = \"\${PHP_TIMEZONE}\"" > /usr/local/etc/php/conf.d/timezone.ini
RUN echo -e “$(hostname -i)\t$(hostname) $(hostname).localhost” >> /etc/hosts

WORKDIR /app
COPY . /app
RUN ln -s /app/*[^public] /var/www && rm -rf /var/www/html && ln -nsf /app/public /var/www/html
RUN chmod 775 /app/public/uploads

RUN sed -i -e "s/\(localhost\)/web/g" test/ospos.js
