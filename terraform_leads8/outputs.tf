output "service_name" {
  description = "Nome do serviço Docker criado"
  value       = module.docker_service.service_name
}

output "service_id" {
  description = "ID do serviço Docker"
  value       = module.docker_service.service_id
}

output "image_full_name" {
  description = "Full Docker image name with tag"
  value       = module.build_image.image_full_name
}

output "build_id" {
  description = "Build resource ID"
  value       = module.build_image.build_id
}

output "traefik_router_name" {
  description = "Nome do router Traefik"
  value       = "${var.service_name}-router"
}

output "service_url" {
  description = "URL do serviço"
  value       = "https://${var.host_domain}${var.path_prefix}"
}

output "network_name" {
  description = "Nome da rede Docker"
  value       = var.network_name
}