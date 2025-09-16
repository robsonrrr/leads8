# Build module for leads8 Docker image
# This module replicates the image building process from the original Fabric script

# Clean up existing build directory
resource "null_resource" "cleanup_build" {
  provisioner "local-exec" {
    command = <<-EOT
      rm -rf /home/ubuntu/environment/Deployment/Office/Production/fabric/builds/leads/leads8/www
      rm -rf /home/ubuntu/environment/Deployment/Office/Production/fabric/builds/leads/leads8/data
      mkdir -p /home/ubuntu/environment/Deployment/Office/Production/fabric/builds/leads/leads8
    EOT
  }
  
  triggers = {
    always_run = timestamp()
  }
}

# Copy application files and prepare build directory
resource "null_resource" "prepare_build" {
  provisioner "local-exec" {
    command = <<-EOT
      # Copy application files using rsync for a clean copy
      rsync -a --delete /home/ubuntu/environment/Office/Apps/inProduction/leads/leads8/ /home/ubuntu/environment/Deployment/Office/Production/fabric/builds/leads/leads8/www/
      
      # Create necessary directories
      mkdir -p /home/ubuntu/environment/Deployment/Office/Production/fabric/builds/leads/leads8/www/application/cache
      mkdir -p /home/ubuntu/environment/Deployment/Office/Production/fabric/builds/leads/leads8/www/application/logs
      
      # Clean cache and logs
      rm -rf /home/ubuntu/environment/Deployment/Office/Production/fabric/builds/leads/leads8/www/application/cache/*
      rm -rf /home/ubuntu/environment/Deployment/Office/Production/fabric/builds/leads/leads8/www/application/logs/*
      
      # Copy configuration files
      cp -ar /home/ubuntu/environment/Office/Configs/ec2 /home/ubuntu/environment/Deployment/Office/Production/fabric/builds/leads/leads8/data
      
      # Set proper permissions
      sudo chown www-data:www-data -R /home/ubuntu/environment/Deployment/Office/Production/fabric/builds/leads/leads8/www/application/cache
      sudo chmod 777 -R /home/ubuntu/environment/Deployment/Office/Production/fabric/builds/leads/leads8/www/application/cache
      sudo chown www-data:www-data -R /home/ubuntu/environment/Deployment/Office/Production/fabric/builds/leads/leads8/www/application/logs
      sudo chmod 777 -R /home/ubuntu/environment/Deployment/Office/Production/fabric/builds/leads/leads8/www/application/logs
      
      # Copy Dockerfile from the application directory (it already exists there)
      cp /home/ubuntu/environment/Office/Apps/inProduction/leads/leads8/Dockerfile /home/ubuntu/environment/Deployment/Office/Production/fabric/builds/leads/leads8/Dockerfile
    EOT
  }
  
  triggers = {
    always_run = timestamp()
  }
  
  depends_on = [null_resource.cleanup_build]
}

# Build Docker image
resource "null_resource" "build_image" {
  provisioner "local-exec" {
    command = <<-EOT
      cd /home/ubuntu/environment/Deployment/Office/Production/fabric/builds/leads/leads8/www
      docker build --no-cache -t ${var.image_name}:${var.image_tag} .
    EOT
  }
  
  triggers = {
    image_name = var.image_name
    image_tag  = var.image_tag
    always_run = timestamp()
  }
  
  depends_on = [null_resource.prepare_build]
}

# Save image to tar file (optional, for backup/transfer)
resource "null_resource" "save_image" {
  count = var.save_image ? 1 : 0
  
  provisioner "local-exec" {
    command = <<-EOT
      mkdir -p /home/ubuntu/environment/Deployment/Office/Production/fabric/images
      docker save -o /home/ubuntu/environment/Deployment/Office/Production/fabric/images/${var.service_name}.tar ${var.image_name}:${var.image_tag}
    EOT
  }
  
  triggers = {
    image_name   = var.image_name
    image_tag    = var.image_tag
    service_name = var.service_name
  }
  
  depends_on = [null_resource.build_image]
}