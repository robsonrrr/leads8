variable "service_name" {
  description = "Nome do serviço para configuração Traefik"
  type        = string
}

variable "host_domain" {
  description = "Domínio do host para roteamento Traefik"
  type        = string
}

variable "path_prefix" {
  description = "Prefixo do caminho para roteamento"
  type        = string
}

variable "container_port" {
  description = "Porta do container"
  type        = number
}