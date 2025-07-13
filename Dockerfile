# Pake image PHP-FPM dengan Alpine Linux biar ukurannya kecil
# Ubah versi PHP ke 8.3
FROM php:8.3-fpm-alpine

# Instal aplikasi sistem yang dibutuhin
RUN apk add --no-cache \
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
    jpeg-dev

# Instal ekstensi PHP yang dibutuhin Laravel
# Sesuaikan aja sama kebutuhan proyek Laravel-mu, ya
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath opcache zip
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd

# Atur folder kerja di dalam container
WORKDIR /var/www/html

# Salin Composer dari image Composer resmi
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Salin semua file proyek ke dalam container *sebelum* composer install
COPY . .

# Salin file composer.json dan composer.lock dari root proyek ke dalam container
# Karena konteks build sekarang adalah root proyek, kita bisa langsung COPY
# Baris ini tidak lagi diperlukan karena sudah tercakup oleh 'COPY . .' di atas
# COPY composer.json composer.lock ./

# Install dependensi Composer (gak pake dev dependencies buat produksi)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Atur izin yang bener buat folder storage dan bootstrap/cache
# Ini penting banget biar Laravel bisa nulis log, cache, dll.
RUN chown -R www-data:www-data storage bootstrap/cache
RUN chmod -R 775 storage bootstrap/cache

# Jalanin perintah optimasi Laravel buat produksi
RUN php artisan optimize

# Buka port 9000 buat PHP-FPM
EXPOSE 9000

# Perintah default pas container jalan (PHP-FPM)
CMD ["php-fpm"]
