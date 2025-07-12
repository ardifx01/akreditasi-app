FROM php:8.2-fpm-alpine

# Install system dependencies yang dibutuhkan Laravel
RUN apk add --no-cache \
    curl \
    libzip-dev \
    libpng-dev \
    jpeg-dev \
    git \
    supervisor \
    mysql-client \
    oniguruma-dev \
    postgresql-dev \
    autoconf \
    g++ \
    make \
    nginx 

# Install PHP extensions yang umum digunakan Laravel
RUN docker-php-ext-install pdo_mysql zip exif pcntl gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory ke root proyek Laravel
WORKDIR /var/www/html

# Expose port 9000 untuk PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]
