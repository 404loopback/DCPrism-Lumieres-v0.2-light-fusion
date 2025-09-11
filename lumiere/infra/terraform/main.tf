# DCPrism Infrastructure Configuration
# This file contains the main infrastructure configuration using OpenTofu/Terraform

terraform {
  required_version = ">= 1.0"
  required_providers {
    # Add your required providers here
    # Example providers:
    
    # vultr = {
    #   source  = "vultr/vultr"
    #   version = "~> 2.0"
    # }
    
    # aws = {
    #   source  = "hashicorp/aws"
    #   version = "~> 5.0"
    # }
    
    # docker = {
    #   source  = "kreuzwerker/docker"
    #   version = "~> 3.0"
    # }
  }
}

# Example variables (uncomment and modify as needed)
# variable "environment" {
#   description = "Environment name (dev, staging, prod)"
#   type        = string
#   default     = "dev"
# }

# variable "project_name" {
#   description = "Project name"
#   type        = string
#   default     = "dcprism"
# }

# Example locals
# locals {
#   common_tags = {
#     Project     = var.project_name
#     Environment = var.environment
#     ManagedBy   = "OpenTofu"
#   }
# }

# Example outputs
# output "environment" {
#   description = "Current environment"
#   value       = var.environment
# }

# output "project_name" {
#   description = "Project name"
#   value       = var.project_name
# }

# Add your infrastructure resources here
# Example:
# 
# resource "vultr_instance" "web_server" {
#   plan             = "vc2-1c-1gb"
#   region           = "ewr"
#   os_id            = 387
#   label            = "${var.project_name}-${var.environment}-web"
#   tag              = var.environment
#   hostname         = "${var.project_name}-${var.environment}-web"
#   enable_ipv6      = true
#   backups          = "enabled"
#   ddos_protection  = true
#   activation_email = false
#
#   tags = local.common_tags
# }
