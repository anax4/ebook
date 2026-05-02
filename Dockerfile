FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    curl \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_mysql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

RUN a2enmod rewrite \
    && printf "ServerName localhost\n<Directory /var/www/html>\n    AllowOverride All\n</Directory>\n" > /etc/apache2/conf-available/app.conf \
    && a2enconf app

COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

COPY . .

RUN chown -R www-data:www-data /var/www/html
