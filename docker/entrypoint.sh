#!/bin/bash

# Carrega variáveis de ambiente do config.env
if [ -f "/data/config.env" ]; then
    source /data/config.env
fi

# Aguarda MySQL estar disponível
if [ ! -z "$DB_HOST" ]; then
    echo "Aguardando MySQL..."
    while ! mysqladmin ping -h"$DB_HOST" --silent; do
        sleep 1
    done
fi

# Configura o ambiente
if [ ! -f "application/config/config.php" ]; then
    cp application/config/config.example.php application/config/config.php
fi

if [ ! -f "application/config/database.php" ]; then
    cp application/config/database.example.php application/config/database.php
fi

if [ ! -f "application/config/mobile.php" ]; then
    cp application/config/mobile.example.php application/config/mobile.php
fi

# Configura permissões
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html
chmod -R 777 /var/www/html/application/logs
chmod -R 777 /var/www/html/application/cache

# Executa migrações se necessário
php index.php migrate

# Limpa cache
php index.php cache clear

# Inicia Apache
exec "$@"
