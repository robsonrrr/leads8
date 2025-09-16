terraform {
  required_version = ">= 1.0"
  required_providers {
    docker = {
      source  = "kreuzwerker/docker"
      version = "~> 3.0"
    }
    null = {
      source  = "hashicorp/null"
      version = "~> 3.0"
    }
  }
}

# Configure the Docker Provider
provider "docker" {
  host = "unix:///var/run/docker.sock"
}

# Import modules
# Build the Docker image first (only when not using bind mounts)
module "build_image" {
  count = var.use_bind_mounts ? 0 : 1
  source = "./modules/build"
  
  service_name = var.service_name
  image_name   = var.image_name
  image_tag    = var.image_tag
  save_image   = var.save_image
}

# Deploy the Docker service (with build dependency)
module "docker_service_with_build" {
  count = var.use_bind_mounts ? 0 : 1
  source = "./modules/docker"
  
  service_name          = var.service_name
  image_name            = var.image_name
  image_tag             = var.image_tag
  network_name          = var.network_name
  replicas              = var.replicas
  host_domain           = var.host_domain
  path_prefix           = var.path_prefix
  container_port        = var.container_port
  conflicting_services  = []
  use_bind_mounts       = var.use_bind_mounts
  source_path           = var.source_path
  
  depends_on = [module.build_image[0]]
}

# Deploy the Docker service (with bind mounts, no build)
module "docker_service_bind_mount" {
  count = var.use_bind_mounts ? 1 : 0
  source = "./modules/docker"
  
  service_name          = var.service_name
  image_name            = var.image_name
  image_tag             = var.image_tag
  network_name          = var.network_name
  replicas              = var.replicas
  host_domain           = var.host_domain
  path_prefix           = var.path_prefix
  container_port        = var.container_port
  conflicting_services  = []
  use_bind_mounts       = var.use_bind_mounts
  source_path           = var.source_path
}

# Traefik config for build-based deployment
module "traefik_config_with_build" {
  count = var.use_bind_mounts ? 0 : 1
  source = "./modules/traefik"
  
  service_name   = var.service_name
  host_domain    = var.host_domain
  path_prefix    = var.path_prefix
  container_port = var.container_port
  
  depends_on = [module.docker_service_with_build[0]]
}

# Traefik config for bind mount deployment
module "traefik_config_bind_mount" {
  count = var.use_bind_mounts ? 1 : 0
  source = "./modules/traefik"
  
  service_name   = var.service_name
  host_domain    = var.host_domain
  path_prefix    = var.path_prefix
  container_port = var.container_port
  
  depends_on = [module.docker_service_bind_mount[0]]
}