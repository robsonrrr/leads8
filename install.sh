#!/bin/bash

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Função para exibir mensagens
log() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Verifica requisitos
check_requirements() {
    log "Verificando requisitos..."
    
    # PHP
    if ! command -v php &> /dev/null; then
        error "PHP não encontrado"
        exit 1
    fi
    
    php_version=$(php -v | head -n 1 | cut -d " " -f 2 | cut -d "." -f 1,2)
    if (( $(echo "$php_version < 7.4" | bc -l) )); then
        error "PHP 7.4 ou superior é necessário"
        exit 1
    fi
    
    # Composer
    if ! command -v composer &> /dev/null; then
        error "Composer não encontrado"
        exit 1
    fi
    
    # Node.js (opcional)
    if ! command -v node &> /dev/null; then
        warn "Node.js não encontrado (opcional para documentação)"
    else
        node_version=$(node -v | cut -d "v" -f 2 | cut -d "." -f 1)
        if (( node_version < 18 )); then
            warn "Node.js 18 ou superior é recomendado para documentação"
        fi
    fi
    
    # MySQL
    if ! command -v mysql &> /dev/null; then
        error "MySQL não encontrado"
        exit 1
    fi
    
    log "Requisitos verificados com sucesso"
}

# Instala dependências
install_dependencies() {
    log "Instalando dependências PHP..."
    composer install --no-dev
    
    if command -v node &> /dev/null; then
        log "Instalando dependências Node..."
        npm install --production
    fi
}

# Configura ambiente
setup_environment() {
    log "Configurando ambiente..."
    
    # Copia arquivos de exemplo
    if [ ! -f "application/config/config.php" ]; then
        cp application/config/config.example.php application/config/config.php
    fi
    
    if [ ! -f "application/config/database.php" ]; then
        cp application/config/database.example.php application/config/database.php
    fi
    
    if [ ! -f "application/config/mobile.php" ]; then
        cp application/config/mobile.example.php application/config/mobile.php
    fi
    
    # Cria arquivo .env se não existir
    if [ ! -f ".env" ]; then
        cat > .env << EOF
DB_HOST=localhost
DB_NAME=leads8
DB_USER=root
DB_PASS=

REDIS_HOST=localhost
REDIS_PORT=6379

API_URL=http://localhost:8000
API_VERSION=v1

JWT_SECRET=$(openssl rand -hex 32)
EOF
    fi
    
    # Configura permissões
    chmod -R 755 .
    chmod -R 777 application/logs
    chmod -R 777 application/cache
    
    log "Ambiente configurado com sucesso"
}

# Configura banco de dados
setup_database() {
    log "Configurando banco de dados..."
    
    # Carrega variáveis do .env
    source .env
    
    # Cria banco se não existir
    mysql -u"$DB_USER" -p"$DB_PASS" -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    
    # Executa migrações
    php index.php migrate
    
    log "Banco de dados configurado com sucesso"
}

# Limpa cache
clear_cache() {
    log "Limpando cache..."
    
    php index.php cache clear
    
    if command -v redis-cli &> /dev/null; then
        redis-cli flushall
    fi
    
    log "Cache limpo com sucesso"
}

# Verifica instalação
check_installation() {
    log "Verificando instalação..."
    
    # Verifica se a API está respondendo
    response=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:8000/api/v1)
    
    if [ "$response" = "200" ]; then
        log "API respondendo corretamente"
    else
        warn "API não está respondendo corretamente (HTTP $response)"
    fi
    
    # Verifica banco
    php index.php db:check
    
    # Verifica cache
    if command -v redis-cli &> /dev/null; then
        if redis-cli ping > /dev/null; then
            log "Redis respondendo corretamente"
        else
            warn "Redis não está respondendo"
        fi
    fi
    
    log "Verificação concluída"
}

# Menu principal
main() {
    echo "=== Leads8 Mobile API - Instalação ==="
    echo
    
    # Verifica requisitos
    check_requirements
    
    # Pergunta se quer continuar
    read -p "Continuar com a instalação? [y/N] " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
    
    # Executa passos da instalação
    install_dependencies
    setup_environment
    setup_database
    clear_cache
    check_installation
    
    echo
    log "Instalação concluída com sucesso!"
    echo
    echo "Para iniciar o servidor de desenvolvimento:"
    echo "  php -S localhost:8000"
    echo
    echo "Para acessar a documentação:"
    echo "  npm run docs"
    echo
    echo "Para mais informações, consulte docs/SETUP.md"
}

# Executa menu principal
main
