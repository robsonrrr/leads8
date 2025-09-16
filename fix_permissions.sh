#!/bin/bash

# Cores para output
GREEN='\033[0;32m'
NC='\033[0m'

# Função para exibir mensagens
log() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log "Ajustando permissões..."

# Configura permissões base
chmod -R 755 .

# Configura permissões especiais
chmod -R 777 application/logs
chmod -R 777 application/cache
chmod -R 777 application/tests

# Cria diretórios de teste se não existirem
mkdir -p application/tests/controllers
mkdir -p application/tests/models
mkdir -p application/tests/libraries
chmod -R 777 application/tests

# Ajusta propriedade dos arquivos (se estiver rodando como root)
if [ "$(id -u)" = "0" ]; then
    chown -R www-data:www-data .
fi

log "Permissões ajustadas com sucesso!"


