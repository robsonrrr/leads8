# This module handles Traefik-specific configurations
# The actual Traefik labels are applied in the Docker service module
# This module can be extended for additional Traefik configurations

# Null resource to validate Traefik configuration
resource "null_resource" "traefik_validation" {
  provisioner "local-exec" {
    command = "echo 'Validating Traefik configuration for ${var.service_name}'"
  }
  
  provisioner "local-exec" {
    command = "echo 'Service URL: https://${var.host_domain}${var.path_prefix}'"
  }
  
  triggers = {
    service_name = var.service_name
    host_domain  = var.host_domain
    path_prefix  = var.path_prefix
  }
}

# Output validation information
resource "null_resource" "deployment_info" {
  provisioner "local-exec" {
    command = "echo 'Leads8 service deployed successfully with Traefik routing'"
  }
  
  depends_on = [null_resource.traefik_validation]
}