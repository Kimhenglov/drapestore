# ============================================================
# Terraform Variables
# File: aws/terraform/variables.tf
# ============================================================

variable "aws_region" {
  description = "AWS region to deploy in"
  type        = string
  default     = "ap-southeast-1"
}

variable "db_username" {
  description = "RDS master username"
  type        = string
  default     = "drapestore_admin"
  sensitive   = true
}

variable "db_password" {
  description = "RDS master password"
  type        = string
  sensitive   = true
}

variable "admin_ip_cidrs" {
  description = "IP ranges allowed to SSH"
  type        = list(string)
  default     = ["0.0.0.0/0"]
}

variable "ec2_key_name" {
  description = "EC2 SSH key pair name"
  type        = string
  default     = "drapestore-key"
}

variable "environment" {
  description = "Environment name"
  type        = string
  default     = "production"
}
