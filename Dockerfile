FROM php:8.2-fpm

WORKDIR /app

RUN apt-get update && apt-get install -y \
    git curl libpq-dev unzip \
    && docker-php-ext-install pdo_pgsql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . .

RUN chmod +x start.sh

RUN touch .env

RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs

EXPOSE 8000
