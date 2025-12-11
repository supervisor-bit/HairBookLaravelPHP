# Dockerfile pro HairBook
FROM php:8.2-fpm-alpine

# Metadata
LABEL maintainer="HairBook"
LABEL version="1.0.0"

# Instalace systémových závislostí
RUN apk add --no-cache \
    sqlite \
    sqlite-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    nginx \
    supervisor

# Instalace PHP rozšíření
RUN docker-php-ext-install \
    pdo_sqlite \
    zip \
    bcmath

# Instalace Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Nastavení pracovního adresáře
WORKDIR /var/www/html

# Kopírování aplikace
COPY . .

# Instalace PHP závislostí
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Vytvoření databáze a nastavení oprávnění
RUN touch /var/www/html/database/database.sqlite && \
    mkdir -p /var/www/html/storage/logs \
             /var/www/html/storage/framework/cache \
             /var/www/html/storage/framework/sessions \
             /var/www/html/storage/framework/views \
             /var/www/html/bootstrap/cache && \
    chown -R www-data:www-data /var/www/html/storage \
                                 /var/www/html/bootstrap/cache \
                                 /var/www/html/database

# Nginx konfigurace
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# Supervisor konfigurace
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Exponování portu
EXPOSE 80

# Spuštění aplikace
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
