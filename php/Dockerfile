FROM php:8.1-fpm

# Standard Pakete installieren
RUN apt-get update && apt-get install -y \
       libmcrypt4 \
       zlib1g-dev \
       libzip-dev \
       unzip

RUN docker-php-ext-install pdo_mysql \
   && docker-php-ext-install mysqli \
   && docker-php-ext-install zip

# composer installieren
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN mkdir -p /var/www/.config && chown -R www-data:www-data /var/www/.config