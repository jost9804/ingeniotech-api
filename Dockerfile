FROM php:8.4-fpm

WORKDIR /app

RUN apt-get update && apt-get install -y \
    git curl zip unzip postgresql-client libpq-dev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_pgsql fileinfo

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . .

RUN composer install --no-dev --optimize-autoloader && \
    mkdir -p storage/logs && \
    chown -R www-data:www-data storage bootstrap/cache

# Normaliza saltos de línea (Windows CRLF rompe el shebang de bash) y da permisos
RUN sed -i 's/\r$//' start.sh && chmod +x start.sh

EXPOSE 8000

CMD ["./start.sh"]
