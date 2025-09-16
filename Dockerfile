FROM php:8.2-apache

# Instala dependências
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    curl \
    unzip \
    git \
    redis-tools \
    libzip-dev \
    && docker-php-ext-install pdo_mysql mysqli mbstring exif pcntl bcmath gd zip \
    && git config --global --add safe.directory /var/www/html

# Instala Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configura Apache
RUN a2enmod rewrite
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# Configura PHP
COPY docker/php.ini /usr/local/etc/php/conf.d/app.ini

# Define diretório de trabalho
WORKDIR /var/www/html

# Copia arquivos do projeto
COPY public/ /var/www/html/
RUN chown -R www-data:www-data /var/www/html

# Instala dependências do projeto
COPY composer.json composer.lock* ./

RUN composer install --no-dev --no-scripts --no-autoloader --ignore-platform-reqs

COPY . .

RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# Script de inicialização
COPY docker/entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]