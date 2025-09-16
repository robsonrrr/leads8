#!/bin/bash

# Cores para output
GREEN='\033[0;32m'
NC='\033[0m'

# Função para exibir mensagens
log() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

# Limpa o cache do Docker
log "Limpando cache do Docker..."
docker builder prune -f

# Constrói a imagem Docker
log "Construindo imagem Docker..."
docker build --no-cache -t leads8:latest .

# Verifica se a construção foi bem-sucedida
if [ $? -eq 0 ]; then
    log "Imagem construída com sucesso"
    
    # Executa o script de implantação
    log "Implantando serviço..."
    ./leads8.sh
    
    if [ $? -eq 0 ]; then
        log "Serviço implantado com sucesso"
        log "Acesse: https://dev.office.internut.com.br/leads8/"
    else
        log "Erro ao implantar serviço"
        exit 1
    fi
else
    log "Erro ao construir imagem"
    exit 1
fi
