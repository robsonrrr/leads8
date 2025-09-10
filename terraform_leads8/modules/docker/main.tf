# Remove existing services that might conflict
resource "null_resource" "remove_existing_services" {
  provisioner "local-exec" {
    command = <<-EOT
      # Remove the current service if it exists
      sudo docker service rm ${var.service_name} 2>/dev/null || true
      
      # Remove any conflicting services
      %{for service in var.conflicting_services~}
      sudo docker service rm ${service} 2>/dev/null || true
      %{endfor~}
    EOT
  }
  
  triggers = {
    service_name         = var.service_name
    conflicting_services = join(",", var.conflicting_services)
  }
}

# Create Docker service using local-exec since kreuzwerker/docker doesn't support Docker Swarm services
resource "null_resource" "create_docker_service" {
  provisioner "local-exec" {
    command = <<-EOT
      sudo docker service create --replicas ${var.replicas} --name ${var.service_name} --network ${var.network_name} \
        --label traefik.enable=true \
        --label 'traefik.http.routers.${replace(var.service_name, "-", "")}.rule=Host(`${var.host_domain}`) && PathPrefix(`${var.path_prefix}`)' \
        --label traefik.http.routers.${replace(var.service_name, "-", "")}.entrypoints=websecure \
        --label 'traefik.http.routers.${replace(var.service_name, "-", "")}-insecure.rule=Host(`${var.host_domain}`) && PathPrefix(`${var.path_prefix}`)' \
        --label traefik.http.routers.${replace(var.service_name, "-", "")}-insecure.entrypoints=web \
        --label traefik.http.routers.${replace(var.service_name, "-", "")}.tls=true \
        --label traefik.http.routers.${replace(var.service_name, "-", "")}.tls.certresolver=myresolver \
        --label traefik.http.services.${replace(var.service_name, "-", "")}.loadbalancer.server.port=${var.container_port} \
        --label traefik.http.routers.${replace(var.service_name, "-", "")}.middlewares=${replace(var.service_name, "-", "")}-stripprefix \
        --label traefik.http.middlewares.${replace(var.service_name, "-", "")}-stripprefix.stripprefix.prefixes=${var.path_prefix} \
        --label traefik.http.routers.${replace(var.service_name, "-", "")}-insecure.middlewares=${replace(var.service_name, "-", "")}-insecure-stripprefix \
        --label traefik.http.middlewares.${replace(var.service_name, "-", "")}-insecure-stripprefix.stripprefix.prefixes=${var.path_prefix} \
        ${var.image_name}:${var.image_tag}
    EOT
  }
  
  triggers = {
    service_name   = var.service_name
    image_name     = var.image_name
    image_tag      = var.image_tag
    network_name   = var.network_name
    replicas       = var.replicas
    host_domain    = var.host_domain
    path_prefix    = var.path_prefix
    container_port = var.container_port
  }
  
  depends_on = [null_resource.remove_existing_services]
}