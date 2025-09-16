output "image_full_name" {
  description = "Full Docker image name with tag"
  value       = "${var.image_name}:${var.image_tag}"
}

output "image_name" {
  description = "Docker image name"
  value       = var.image_name
}

output "image_tag" {
  description = "Docker image tag"
  value       = var.image_tag
}

output "build_id" {
  description = "Build resource ID for dependency tracking"
  value       = null_resource.build_image.id
}

output "service_name" {
  description = "Service name"
  value       = var.service_name
}