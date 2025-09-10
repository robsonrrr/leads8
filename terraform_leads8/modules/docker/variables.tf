variable "service_name" {
  description = "Nome do serviço Docker"
  type        = string
}

variable "image_name" {
  description = "Nome da imagem Docker"
  type        = string
}

variable "image_tag" {
  description = "Tag da imagem Docker"
  type        = string
}

variable "network_name" {
  description = "Nome da rede Docker"
  type        = string
}

variable "replicas" {
  description = "Número de réplicas do serviço"
  type        = number
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

variable "conflicting_services" {
  description = "Lista de nomes de serviços que podem conflitar com este deployment"
  type        = list(string)
  default     = []
}