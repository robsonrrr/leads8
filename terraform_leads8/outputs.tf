output "service_name" {
  description = "Nome do serviço Docker criado"
  value       = var.use_bind_mounts ? module.docker_service_bind_mount[0].service_name : module.docker_service_with_build[0].service_name
}

output "service_id" {
  description = "ID do serviço Docker"
  value       = var.use_bind_mounts ? module.docker_service_bind_mount[0].service_id : module.docker_service_with_build[0].service_id
}

output "image_full_name" {
  description = "Full Docker image name with tag"
  value       = var.use_bind_mounts ? "${var.image_name}:${var.image_tag}" : module.build_image[0].image_full_name
}

output "build_id" {
  description = "Build resource ID"
  value       = var.use_bind_mounts ? "N/A (bind mount mode)" : module.build_image[0].build_id
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