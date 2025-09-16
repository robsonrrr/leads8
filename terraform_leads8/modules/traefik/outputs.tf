output "router_name" {
  description = "Nome do router Traefik"
  value       = "${var.service_name}-router"
}

output "service_url" {
  description = "URL do serviço"
  value       = "https://${var.host_domain}${var.path_prefix}"
}

output "insecure_url" {
  description = "URL insegura do serviço (HTTP)"
  value       = "http://${var.host_domain}${var.path_prefix}"
}

output "path_prefix" {
  description = "Prefixo do caminho configurado"
  value       = var.path_prefix
}