#!/bin/bash

# Script de deploy para Leads8 usando Terraform
# Equivalente ao comando 'fab leads8'

set -e

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Função para log
log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] $1${NC}"
}

warn() {
    echo -e "${YELLOW}[$(date +'%Y-%m-%d %H:%M:%S')] WARNING: $1${NC}"
}

error() {
    echo -e "${RED}[$(date +'%Y-%m-%d %H:%M:%S')] ERROR: $1${NC}"
    exit 1
}

# Verificar se o Terraform está instalado
if ! command -v terraform &> /dev/null; then
    error "Terraform não está instalado. Instale o Terraform primeiro."
fi

# Verificar se o Docker está rodando
if ! docker info &> /dev/null; then
    error "Docker não está rodando ou não está acessível."
fi

# Definir ambiente (padrão: prod)
ENVIRONMENT=${1:-prod}

if [[ "$ENVIRONMENT" != "prod" && "$ENVIRONMENT" != "dev" ]]; then
    error "Ambiente deve ser 'prod' ou 'dev'. Uso: $0 [prod|dev]"
fi

log "Iniciando deploy do Leads8 para ambiente: $ENVIRONMENT"

# Verificar se o arquivo de variáveis existe
VAR_FILE="environments/$ENVIRONMENT/terraform.tfvars"
if [[ ! -f "$VAR_FILE" ]]; then
    error "Arquivo de variáveis não encontrado: $VAR_FILE"
fi

# Inicializar Terraform se necessário
if [[ ! -d ".terraform" ]]; then
    log "Inicializando Terraform..."
    terraform init
fi

# Planejar deployment
log "Planejando deployment..."
terraform plan -var-file="$VAR_FILE" -out="terraform.tfplan"

# Confirmar deployment
echo
read -p "Deseja aplicar o deployment? (y/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    log "Aplicando deployment..."
    terraform apply "terraform.tfplan"
    
    # Remover arquivo de plano
    rm -f terraform.tfplan
    
    log "Deployment concluído com sucesso!"
    
    # Mostrar informações do serviço
    echo
    log "Informações do serviço:"
    terraform output
    
    echo
    log "Para verificar o status do serviço:"
    echo "  sudo docker service ls | grep koseven-leads8"
    echo "  sudo docker service logs koseven-leads8"
    
else
    warn "Deployment cancelado pelo usuário."
    rm -f terraform.tfplan
    exit 0
fi