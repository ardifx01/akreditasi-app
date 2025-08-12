# Pake image PHP-FPM dengan Alpine Linux biar ukurannya kecil
FROM php:8.3-fpm-alpine

# Instal aplikasi sistem yang dibutuhin
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
    build-base

# Instal ekstensi PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath opcache zip gd

# Instal ekstensi Redis
RUN pecl install redis && docker-php-ext-enable redis && rm -rf /tmp/pear

# Atur folder kerja
WORKDIR /var/www/html

# Salin .env.example dan generate key
COPY .env.example .env
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-dev --optimize-autoloader --no-interaction
RUN php artisan key:generate --force

# Salin semua file proyek
COPY . .

# Buat symlink dan set permission
RUN chown -R www-data:www-data storage bootstrap/cache
RUN chmod -R 775 storage bootstrap/cache
RUN ln -sf /var/www/html/storage/app/public /var/www/html/public/storage

EXPOSE 9000
CMD ["php-fpm"]
