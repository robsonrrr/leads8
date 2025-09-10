# Terraform Leads8 Deployment

Este projeto contém a configuração Terraform para deploy do serviço Leads8, convertido do sistema Fabric original.

## Estrutura do Projeto

```
terraform_leads8/
├── main.tf                 # Configuração principal
├── variables.tf            # Variáveis de entrada
├── outputs.tf              # Saídas do Terraform
├── deploy.sh               # Script de deployment automatizado
├── environments/           # Configurações por ambiente
│   ├── dev/
│   │   └── terraform.tfvars
│   └── prod/
│       └── terraform.tfvars
└── modules/                # Módulos reutilizáveis
    ├── build/              # Módulo de build da imagem Docker
    │   ├── main.tf
    │   ├── variables.tf
    │   └── outputs.tf
    ├── docker/             # Módulo do serviço Docker
    │   ├── main.tf
    │   ├── variables.tf
    │   └── outputs.tf
    └── traefik/            # Módulo de configuração Traefik
        ├── main.tf
        ├── variables.tf
        └── outputs.tf
```

## Pré-requisitos

- Terraform >= 1.0
- Docker instalado e configurado
- Rede Docker `traefik-net` criada
- Traefik configurado e rodando
- Imagem Docker `koseven/leads8:latest` disponível

## Como usar

### 1. Inicializar o Terraform

```bash
cd terraform_leads8
terraform init
```

### 2. Planejar o deployment

```bash
# Para produção
terraform plan -var-file="environments/prod/terraform.tfvars"

# Para desenvolvimento
terraform plan -var-file="environments/dev/terraform.tfvars"
```

### 3. Aplicar o deployment

```bash
# Para produção
terraform apply -var-file="environments/prod/terraform.tfvars"

# Para desenvolvimento
terraform apply -var-file="environments/dev/terraform.tfvars"
```

### 4. Destruir o deployment

```bash
terraform destroy -var-file="environments/prod/terraform.tfvars"
```

## Configurações

### Variáveis Principais

- `service_name`: Nome do serviço Docker (padrão: "koseven-leads8")
- `image_name`: Nome da imagem Docker (padrão: "koseven/leads8")
- `image_tag`: Tag da imagem (padrão: "latest")
- `host_domain`: Domínio para roteamento (padrão: "office.vallery.com.br")
- `path_prefix`: Prefixo do caminho (padrão: "/leads8/")
- `network_name`: Nome da rede Docker (padrão: "traefik-net")
- `replicas`: Número de réplicas (padrão: 1)
- `container_port`: Porta do container (padrão: 80)

## Processo de Build da Imagem

O módulo `build` automatiza todo o processo de construção da imagem Docker, replicando exatamente o comportamento do comando `fab leads8` original:

### Etapas do Build:

1. **Limpeza**: Remove diretórios de build anteriores
2. **Preparação**: 
   - Copia arquivos da aplicação de `/home/ubuntu/environment/Office/Apps/inProduction/leads/leads8`
   - Cria diretórios necessários (`cache`, `logs`)
   - Limpa cache e logs existentes
   - Copia configurações do EC2
   - Define permissões adequadas
   - Copia o Dockerfile personalizado
3. **Build**: Executa `docker build --no-cache` para criar a imagem
4. **Backup** (opcional): Salva a imagem em arquivo tar se `save_image = true`

### Dependências:

- Aplicação deve estar em: `/home/ubuntu/environment/Office/Apps/inProduction/leads/leads8`
- Configurações em: `/home/ubuntu/environment/Office/Configs/ec2`
- Dockerfile em: `/home/ubuntu/environment/Deployment/Office/Production/fabric/run/fabfile_reorganized/modules/services/leads8_dockerfile`

### Configuração do Traefik

O serviço é configurado automaticamente com:
- Roteamento HTTPS com certificado SSL automático
- Roteamento HTTP para redirecionamento
- Strip prefix para remover `/leads8/` das requisições
- Load balancer configurado na porta 80

## Equivalência com Fabric

Este projeto Terraform substitui o comando `fab leads8` com as seguintes funcionalidades:

| Fabric | Terraform |
|--------|----------|
| `fab leads8` | `terraform apply -var-file="environments/prod/terraform.tfvars"` |
| Remoção do serviço existente | `null_resource.remove_existing_service` |
| Criação do Docker service | `docker_service.leads8` |
| Labels do Traefik | Configurados no `container_spec.labels` |
| Configuração de rede | `networks_advanced` |

## Saídas

Após o deployment, o Terraform fornece:
- `service_name`: Nome do serviço criado
- `service_id`: ID do serviço Docker
- `service_url`: URL HTTPS do serviço
- `image_full_name`: Nome completo da imagem utilizada

## Funcionalidades Implementadas

- ✅ **Build automático de imagem**: Constrói a imagem Docker automaticamente antes do deployment
- ✅ **Preparação do ambiente**: Copia arquivos da aplicação e configurações necessárias
- ✅ **Remoção automática de serviços**: Remove serviços existentes antes de criar novos
- ✅ **Criação de serviços Docker**: Configura serviços Docker Swarm com todas as especificações
- ✅ **Configuração completa do Traefik**: Inclui todas as labels necessárias para roteamento
- ✅ **Roteamento HTTPS**: Configuração automática de certificados SSL
- ✅ **Strip Prefix**: Remove prefixos de path automaticamente
- ✅ **Suporte multi-ambiente**: Configurações separadas para dev e prod
- ✅ **Script de deployment**: Automatização completa do processo
- ✅ **Documentação completa**: README detalhado com instruções de uso

## Troubleshooting

### Verificar se o serviço está rodando

```bash
sudo docker service ls | grep koseven-leads8
```

### Verificar logs do serviço

```bash
sudo docker service logs koseven-leads8
```

### Verificar configuração do Traefik

```bash
sudo docker service inspect koseven-leads8 | grep -A 20 Labels
```

## Migração do Fabric

Para migrar do sistema Fabric atual:

1. Pare o serviço atual: `sudo docker service rm koseven-leads8`
2. Execute o Terraform: `terraform apply -var-file="environments/prod/terraform.tfvars"`
3. Verifique se o serviço está funcionando: `curl -I https://office.vallery.com.br/leads8/`

## Contribuição

Para modificar a configuração:
1. Edite as variáveis em `environments/*/terraform.tfvars`
2. Para mudanças estruturais, modifique os módulos em `modules/`
3. Execute `terraform plan` para revisar as mudanças
4. Execute `terraform apply` para aplicar