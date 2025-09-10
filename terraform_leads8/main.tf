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
# Build the Docker image first
module "build_image" {
  source = "./modules/build"
  
  service_name = var.service_name
  image_name   = var.image_name
  image_tag    = var.image_tag
  save_image   = var.save_image
}

# Deploy the Docker service after image is built
module "docker_service" {
  source = "./modules/docker"
  
  service_name          = var.service_name
  image_name            = var.image_name
  image_tag             = var.image_tag
  network_name          = var.network_name
  replicas              = var.replicas
  host_domain           = var.host_domain
  path_prefix           = var.path_prefix
  container_port        = var.container_port
  conflicting_services  = ["leads8"]  # Lista de serviços que podem conflitar
  
  depends_on = [module.build_image]
}

module "traefik_config" {
  source = "./modules/traefik"
  
  service_name   = var.service_name
  host_domain    = var.host_domain
  path_prefix    = var.path_prefix
  container_port = var.container_port
  
  depends_on = [module.docker_service]
}