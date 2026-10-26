
# --------------------------------------------------
# Stage 1: Build React + Vite assets
# --------------------------------------------------
FROM node:22-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY . .
RUN npm run build


# --------------------------------------------------
# Stage 2: PHP production application
# --------------------------------------------------
FROM php:8.2-apache AS app

RUN apt-get update && apt-get install -y \
    git unzip curl \
    libzip-dev libicu-dev libxml2-dev \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    && docker-php-ext-configure gd \
       --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
       pdo_mysql mbstring bcmath exif gd zip intl \
       pcntl dom xml simplexml \
    && docker-php-ext-enable opcache \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

# Copy Composer executable
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Make Apache serve Laravel's public directory
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri \
    -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf \
    /etc/apache2/apache2.conf \
    /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html

# Copy Laravel source
COPY . .

# Install production PHP dependencies
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --optimize-autoloader

# Copy built Vite assets
COPY --from=frontend /app/public/build ./public/build

# Prepare Laravel writable directories
RUN mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && chown -R www-data:www-data \
       storage bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache

# PHP production settings
RUN printf '%s\n' \
    'opcache.enable=1' \
    'opcache.validate_timestamps=0' \
    'memory_limit=256M' \
    'upload_max_filesize=50M' \
    'post_max_size=50M' \
    > /usr/local/etc/php/conf.d/production.ini

EXPOSE 80

CMD ["apache2-foreground"]
