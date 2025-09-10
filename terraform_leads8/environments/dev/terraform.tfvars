# Configuração para ambiente de desenvolvimento
service_name     = "koseven-leads8-dev"
image_name       = "koseven/leads8"
image_tag        = "dev"
network_name     = "traefik-net"
replicas         = 1
host_domain      = "dev.office.internut.com.br"
path_prefix      = "/leads8/"
container_port   = 80
cert_resolver    = "myresolver"