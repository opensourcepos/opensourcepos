FROM php:7.4-apache

RUN apt-get update && DEBIAN_FRONTEND=noninteractive apt-get install -y \
    libicu-dev \
    libgd-dev \
    openssl

RUN a2enmod rewrite headers
RUN docker-php-ext-install mysqli bcmath intl gd
RUN echo "date.timezone = \"Africa/Lagos\"" > /usr/local/etc/php/conf.d/timezone.ini

WORKDIR /app
COPY . /app
RUN ln -s /app/*[^public] /var/www && rm -rf /var/www/html && ln -nsf /app/public /var/www/html
RUN chmod -R 750 /app/public/uploads /app/application/logs && chown -R www-data:www-data /app/public /app/application
