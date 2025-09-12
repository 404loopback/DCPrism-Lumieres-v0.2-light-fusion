# DCPrism Infrastructure with OpenTofu/Terraform
# =====================================

terraform {
  required_version = ">= 1.0"
  
  required_providers {
    docker = {
      source  = "kreuzwerker/docker"
      version = "~> 3.0"
    }
  }
}

# Configuration pour Docker provider
provider "docker" {
  host = "unix:///var/run/docker.sock"
}

# Variables
variable "project_name" {
  description = "Name of the project"
  type        = string
  default     = "dcprism"
}

variable "environment" {
  description = "Environment (dev, staging, prod)"
  type        = string
  default     = "dev"
}

# Outputs
output "project_info" {
  value = {
    name        = var.project_name
    environment = var.environment
  }
}
