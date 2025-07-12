FROM php:8.0-fpm

# Set working directory
WORKDIR /var/www

# Copy composer.lock and composer.json
COPY composer.lock composer.json ./

# Install dependencies
RUN apt-get update && apt-get install -y \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        && docker-php-ext-configure gd --with-freetype --with-jpeg \
        && docker-php-ext-install gd \
        && docker-php-ext-install pdo pdo_mysql \
        && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copy source code
COPY . .

# Expose port
EXPOSE 9000

# Command to run the application
CMD ["php-fpm"]
