FROM php:8.4-fpm

WORKDIR /app

RUN apt-get update && apt-get install -y \
    git curl zip unzip postgresql-client libpq-dev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_pgsql fileinfo

# Permite subir imágenes razonables (el default de PHP es ~2MB y rechaza fotos del celular).
# Lo lee tanto el CLI (php artisan serve) como php-fpm.
RUN { \
      echo "upload_max_filesize=12M"; \
      echo "post_max_size=14M"; \
      echo "memory_limit=256M"; \
    } > /usr/local/etc/php/conf.d/uploads.ini

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . .

RUN composer install --no-dev --optimize-autoloader && \
    mkdir -p storage/logs && \
    chown -R www-data:www-data storage bootstrap/cache

# Normaliza saltos de línea (Windows CRLF rompe el shebang de bash) y da permisos
RUN sed -i 's/\r$//' start.sh && chmod +x start.sh

EXPOSE 8000

CMD ["./start.sh"]
