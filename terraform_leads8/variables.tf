variable "service_name" {
  description = "Nome do serviço Docker"
  type        = string
  default     = "koseven-leads8"
}

variable "image_name" {
  description = "Nome da imagem Docker"
  type        = string
  default     = "koseven/leads8"
}

variable "image_tag" {
  description = "Tag da imagem Docker"
  type        = string
  default     = "latest"
}

variable "network_name" {
  description = "Nome da rede Docker"
  type        = string
  default     = "traefik-net"
}

variable "replicas" {
  description = "Número de réplicas do serviço"
  type        = number
  default     = 1
}

variable "host_domain" {
  description = "Domínio do host para roteamento Traefik"
  type        = string
  default     = "office.vallery.com.br"
}

variable "path_prefix" {
  description = "Prefixo do caminho para roteamento"
  type        = string
  default     = "/leads8/"
}

variable "container_port" {
  description = "Porta do container"
  type        = number
  default     = 80
}

variable "cert_resolver" {
  description = "Resolver de certificado TLS"
  type        = string
  default     = "myresolver"
}

variable "save_image" {
  description = "Whether to save the built Docker image to a tar file"
  type        = bool
  default     = false
}