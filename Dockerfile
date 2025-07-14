FROM php:8.3-fpm-alpine

RUN apk update && apk add --no-cache \
    curl \
    libzip-dev \
    zip \
    unzip \
    git \
    mysql-client \
    oniguruma-dev \
    libxml2-dev \
    gd \
    freetype-dev \
    libpng-dev \
    jpeg-dev \
    autoconf \
    build-base # Tambahkan build-base di sini untuk menyediakan C compiler dan alat build lainnya

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath opcache zip gd

RUN pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /tmp/pear # Bersihkan file sementara setelah instalasi

WORKDIR /var/www/html

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN chown -R www-data:www-data storage bootstrap/cache
RUN chmod -R 775 storage bootstrap/cache

RUN php artisan optimize

EXPOSE 9000

CMD ["php-fpm"]
