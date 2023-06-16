FROM php:8.0-apache

RUN apt-get update \
    && apt-get install -y \
        libicu-dev \
        libzip-dev \
        unzip \
        git

RUN docker-php-ext-install \
    intl \
    pdo_mysql \
    zip

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-scripts \
    && chown -R www-data:www-data .

RUN a2enmod rewrite

CMD ["apache2-foreground"]
