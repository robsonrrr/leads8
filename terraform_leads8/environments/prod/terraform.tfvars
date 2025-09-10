# Configuração para ambiente de produção
service_name     = "koseven-leads8"
image_name       = "koseven/leads8"
image_tag        = "latest"
network_name     = "traefik-net"
replicas         = 1
host_domain      = "office.vallery.com.br"
path_prefix      = "/leads8/"
container_port   = 80
cert_resolver    = "myresolver"