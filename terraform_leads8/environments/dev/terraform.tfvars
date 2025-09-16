# Configuração para ambiente de desenvolvimento
# Mirrors the configuration from leads8.sh
service_name     = "leads8"
image_name       = "koseven-php8"
image_tag        = "latest"
network_name     = "traefik-net"
replicas         = 1
host_domain      = "dev.office.internut.com.br"
path_prefix      = "/leads8/"
container_port   = 80
cert_resolver    = "myresolver"

# Enable bind mounts for development (like leads8.sh)
use_bind_mounts  = true
source_path      = "/home/ubuntu/environment/Office/Apps/inProduction/leads/leads8"