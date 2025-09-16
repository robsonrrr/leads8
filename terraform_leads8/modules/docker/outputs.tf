output "service_name" {
  description = "Nome do serviço Docker criado"
  value       = var.service_name
}

output "service_id" {
  description = "ID do recurso Terraform"
  value       = null_resource.create_docker_service.id
}

output "image_name" {
  description = "Nome da imagem Docker utilizada"
  value       = "${var.image_name}:${var.image_tag}"
}

output "replicas" {
  description = "Número de réplicas configuradas"
  value       = var.replicas
}