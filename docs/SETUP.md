# Leads8 Mobile API - Setup Guide

## Requisitos

### Sistema
- PHP 7.4+
- MySQL 5.7+
- Redis (opcional, para cache)
- Composer
- Node.js 18+ (para desenvolvimento da documentação)

### Extensões PHP Necessárias
- php-mysql
- php-redis
- php-curl
- php-json
- php-mbstring

## Instalação

### 1. Clone do Repositório

```bash
# Clone o repositório
git clone [repo-url]
cd leads8

# Instale as dependências PHP
composer install

# Instale as dependências Node (para documentação)
npm install
```

### 2. Configuração do Ambiente

1. Copie o arquivo de exemplo de configuração:
```bash
cp application/config/config.example.php application/config/config.php
cp application/config/database.example.php application/config/database.php
cp application/config/mobile.example.php application/config/mobile.php
```

2. Configure as variáveis de ambiente:
```bash
# .env
DB_HOST=localhost
DB_NAME=leads8
DB_USER=seu_usuario
DB_PASS=sua_senha

REDIS_HOST=localhost
REDIS_PORT=6379

API_URL=http://localhost:8000
API_VERSION=v1

JWT_SECRET=seu_secret_key
```

3. Configure o banco de dados:
```php
// application/config/database.php
$db['default'] = array(
    'hostname' => getenv('DB_HOST'),
    'username' => getenv('DB_USER'),
    'password' => getenv('DB_PASS'),
    'database' => getenv('DB_NAME'),
    'dbdriver' => 'mysqli',
    'dbprefix' => '',
    'pconnect' => FALSE,
    'db_debug' => TRUE,
    'cache_on' => FALSE,
    'cachedir' => '',
    'char_set' => 'utf8',
    'dbcollat' => 'utf8_general_ci',
    'swap_pre' => '',
    'encrypt' => FALSE,
    'compress' => FALSE,
    'stricton' => FALSE,
    'failover' => array(),
    'save_queries' => TRUE
);
```

4. Configure a API mobile:
```php
// application/config/mobile.php
$config['api_version'] = getenv('API_VERSION');
$config['api_url'] = getenv('API_URL');
$config['jwt_secret'] = getenv('JWT_SECRET');
```

### 3. Banco de Dados

1. Crie o banco de dados:
```sql
CREATE DATABASE leads8 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Execute as migrações:
```bash
php index.php migrate
```

3. (Opcional) Carregue dados de exemplo:
```bash
php index.php seed
```

### 4. Cache (Opcional)

Se você estiver usando Redis para cache:

1. Instale o Redis:
```bash
# Ubuntu
sudo apt-get install redis-server

# CentOS
sudo yum install redis
```

2. Configure o Redis:
```php
// application/config/redis.php
$config['redis_default'] = array(
    'host' => getenv('REDIS_HOST'),
    'port' => getenv('REDIS_PORT'),
    'password' => '',
    'database' => 0
);
```

## Execução

### Desenvolvimento

1. Inicie o servidor de desenvolvimento:
```bash
# PHP Built-in Server
php -S localhost:8000

# Ou com Apache/Nginx configurado
# Acesse diretamente via seu servidor web
```

2. Execute os testes:
```bash
# Testes unitários
./vendor/bin/phpunit

# Com coverage
./vendor/bin/phpunit --coverage-html coverage
```

3. Documentação da API (opcional):
```bash
# Inicie o servidor da documentação
npm run docs
```

### Produção

1. Configure o servidor web (Apache/Nginx)

Apache (.htaccess):
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php/$1 [L]
```

Nginx:
```nginx
server {
    listen 80;
    server_name api.leads8.com.br;
    root /path/to/leads8;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

2. Configure SSL (recomendado):
```bash
# Usando Certbot
sudo certbot --nginx -d api.leads8.com.br
```

3. Configure o PHP-FPM:
```ini
; /etc/php/7.4/fpm/php.ini
memory_limit = 256M
max_execution_time = 60
upload_max_filesize = 10M
post_max_size = 10M
```

4. Configure o cache:
```bash
# Limpe o cache
php index.php cache clear

# Aqueça o cache (opcional)
php index.php cache warm
```

## Monitoramento

### Logs

Os logs são armazenados em:
- `application/logs/` - Logs da aplicação
- `application/logs/mobile.log` - Logs específicos da API mobile

### Métricas

Se configurado, as métricas estão disponíveis em:
- `/metrics` - Métricas Prometheus
- `/health` - Status da API

## Comandos Úteis

```bash
# Limpar cache
php index.php cache clear

# Atualizar banco
php index.php migrate

# Executar testes
./vendor/bin/phpunit

# Gerar documentação
npm run docs

# Verificar status
php index.php status

# Listar rotas
php index.php routes
```

## Solução de Problemas

### Permissões

```bash
# Configure as permissões corretas
sudo chown -R www-data:www-data .
sudo chmod -R 755 .
sudo chmod -R 777 application/logs
sudo chmod -R 777 application/cache
```

### Cache

```bash
# Limpe todo o cache
php index.php cache clear_all

# Verifique o Redis
redis-cli ping
redis-cli monitor # Monitor em tempo real
```

### Banco de Dados

```bash
# Verifique as conexões
show processlist;

# Otimize as tabelas
mysqlcheck -o leads8

# Backup
mysqldump -u root -p leads8 > backup.sql
```

### Logs

```bash
# Monitore os logs em tempo real
tail -f application/logs/mobile.log

# Busque erros
grep -r "ERROR" application/logs/
```

## Atualização

1. Faça backup:
```bash
# Backup do banco
mysqldump -u root -p leads8 > backup.sql

# Backup dos arquivos
tar -czf leads8_backup.tar.gz .
```

2. Atualize o código:
```bash
# Pull das alterações
git pull origin main

# Atualize as dependências
composer update
npm update

# Execute as migrações
php index.php migrate
```

3. Limpe o cache:
```bash
php index.php cache clear_all
```

4. Verifique a instalação:
```bash
php index.php status
```

## Segurança

1. Configure o firewall:
```bash
# Permita apenas as portas necessárias
sudo ufw allow 80
sudo ufw allow 443
```

2. Configure rate limiting:
```nginx
# /etc/nginx/conf.d/rate_limit.conf
limit_req_zone $binary_remote_addr zone=api_limit:10m rate=10r/s;

location /api/ {
    limit_req zone=api_limit burst=20 nodelay;
}
```

3. Configure CORS:
```php
// application/config/mobile.php
$config['cors'] = [
    'allowed_origins' => ['https://app.leads8.com.br'],
    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE'],
    'allowed_headers' => ['Authorization', 'Content-Type'],
    'expose_headers' => [],
    'max_age' => 3600
];
```

## Suporte

- Email: suporte@leads8.com.br
- Documentação: https://docs.leads8.com.br
- Status: https://status.leads8.com.br
