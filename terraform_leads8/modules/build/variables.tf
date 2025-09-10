variable "service_name" {
  description = "Name of the service"
  type        = string
}

variable "image_name" {
  description = "Docker image name"
  type        = string
}

variable "image_tag" {
  description = "Docker image tag"
  type        = string
  default     = "latest"
}

variable "save_image" {
  description = "Whether to save the built image to a tar file"
  type        = bool
  default     = false
}