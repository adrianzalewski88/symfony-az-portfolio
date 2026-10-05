FROM php:8.5-apache

ARG APP_ENV=dev

ENV APP_ENV=${APP_ENV}
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

WORKDIR /var/www/html

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        zip \
        libicu-dev \
        libonig-dev \
        libzip-dev \
        libxml2-dev \
        default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions
RUN docker-php-ext-configure intl \
    && docker-php-ext-install -j1 intl

RUN docker-php-ext-install -j1 pdo_mysql

RUN docker-php-ext-install -j1 zip

RUN docker-php-ext-install -j1 xml

RUN docker-php-ext-install -j1 mbstring

# Apache
RUN a2enmod rewrite

COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

RUN a2dissite 000-default.conf \
    && a2ensite 000-default.conf

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./

RUN composer install \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

COPY . .

RUN mkdir -p var/cache var/log \
    && chown -R www-data:www-data var

EXPOSE 80

CMD ["apache2-foreground"]