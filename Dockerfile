FROM php:8.1-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql zip

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html/

# Create necessary directories and set permissions
RUN mkdir -p /var/www/html/application/cache \
    && mkdir -p /var/www/html/application/logs \
    && chown -R www-data:www-data /var/www/html/application/cache \
    && chown -R www-data:www-data /var/www/html/application/logs \
    && chmod -R 777 /var/www/html/application/cache \
    && chmod -R 777 /var/www/html/application/logs

# Expose port 80
EXPOSE 80
