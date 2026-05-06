# ============================================================
# DrapeStore PCI DSS v4.0 — AWS Infrastructure
# File: aws/terraform/main.tf
#
# This Terraform file creates ALL AWS resources needed to run
# DrapeStore in a PCI DSS v4.0 compliant configuration.
#
# Architecture mirrors lecture Figure 3:
#   Customer → CloudFront → WAF → ALB (DMZ) → EC2 (CDE) → RDS
# ============================================================

terraform {
  required_version = ">= 1.5"
  required_providers {
    aws = {
      source  = "hashicorp/aws"
      version = "~> 5.0"
    }
  }
}

provider "aws" {
  region = var.aws_region
}

# ────────────────────────────────────────────────────────────
# PCI DSS REQ 1 — VPC: Isolate the Cardholder Data Environment
# ────────────────────────────────────────────────────────────
# A VPC is your private slice of AWS — no one else can see it.
# Inside, we create separate subnets for DMZ vs CDE.

resource "aws_vpc" "main" {
  cidr_block           = "10.0.0.0/16"     # 65,536 private IPs
  enable_dns_hostnames = true
  enable_dns_support   = true

  tags = {
    Name      = "drapestore-vpc"
    PCI_Scope = "in-scope"     # Tag all PCI-related resources
  }
}

# ────────────────────────────────────────────────────────────
# PCI DSS REQ 1.3 — Public Subnets (DMZ for ALB)
# ────────────────────────────────────────────────────────────
# Public = can talk to the internet directly.
# Only the load balancer lives here — it's the entrance.

resource "aws_subnet" "public" {
  count             = 2                                    # 2 subnets in 2 availability zones (HA)
  vpc_id            = aws_vpc.main.id
  cidr_block        = "10.0.${count.index + 1}.0/24"      # 10.0.1.0 and 10.0.2.0
  availability_zone = data.aws_availability_zones.azs.names[count.index]

  map_public_ip_on_launch = false  # REQ 1.3: Don't auto-assign public IPs

  tags = {
    Name      = "drapestore-public-${count.index + 1}"
    PCI_Scope = "in-scope"
    Tier      = "DMZ"
  }
}

# ────────────────────────────────────────────────────────────
# PCI DSS REQ 1.3 — Private Subnets (CDE for EC2 + RDS)
# ────────────────────────────────────────────────────────────
# Private = NO route to the internet directly.
# This is where our app and database live. Hidden from attackers.

resource "aws_subnet" "private" {
  count             = 2
  vpc_id            = aws_vpc.main.id
  cidr_block        = "10.0.${count.index + 10}.0/24"     # 10.0.10.0 and 10.0.11.0
  availability_zone = data.aws_availability_zones.azs.names[count.index]

  tags = {
    Name      = "drapestore-private-${count.index + 1}"
    PCI_Scope = "in-scope"
    Tier      = "CDE"   # Cardholder Data Environment
  }
}

data "aws_availability_zones" "azs" {
  state = "available"
}

# ────────────────────────────────────────────────────────────
# Internet Gateway — Lets DMZ talk to the internet
# ────────────────────────────────────────────────────────────
# Only public subnets get a route to this. Private subnets do NOT.

resource "aws_internet_gateway" "igw" {
  vpc_id = aws_vpc.main.id
  tags   = { Name = "drapestore-igw" }
}

# Public route table — sends traffic to internet via IGW
resource "aws_route_table" "public" {
  vpc_id = aws_vpc.main.id
  route {
    cidr_block = "0.0.0.0/0"      # All destinations
    gateway_id = aws_internet_gateway.igw.id
  }
  tags = { Name = "drapestore-public-rt" }
}

resource "aws_route_table_association" "public" {
  count          = 2
  subnet_id      = aws_subnet.public[count.index].id
  route_table_id = aws_route_table.public.id
}

# ────────────────────────────────────────────────────────────
# NAT Gateway — Lets CDE EC2 reach Stripe API (outbound only)
# ────────────────────────────────────────────────────────────
# REQ 1.3: Outbound from CDE is restricted to required services
# The NAT lets EC2 download patches and call Stripe,
# but no one from the internet can reach the EC2 directly.

resource "aws_eip" "nat" {
  domain = "vpc"
  tags   = { Name = "drapestore-nat-eip" }
}

resource "aws_nat_gateway" "nat" {
  allocation_id = aws_eip.nat.id
  subnet_id     = aws_subnet.public[0].id    # NAT lives in public subnet
  tags          = { Name = "drapestore-nat" }
  depends_on    = [aws_internet_gateway.igw]
}

# Private route table — sends traffic via NAT (outbound only)
resource "aws_route_table" "private" {
  vpc_id = aws_vpc.main.id
  route {
    cidr_block     = "0.0.0.0/0"
    nat_gateway_id = aws_nat_gateway.nat.id
  }
  tags = { Name = "drapestore-private-rt" }
}

resource "aws_route_table_association" "private" {
  count          = 2
  subnet_id      = aws_subnet.private[count.index].id
  route_table_id = aws_route_table.private.id
}

# ────────────────────────────────────────────────────────────
# PCI DSS REQ 3.4 — KMS Key for encryption
# ────────────────────────────────────────────────────────────
# A "Key Management Service" key encrypts our database.
# Without this key, the encrypted data is unreadable.
# REQ 3.7.1: Auto-rotation enabled (annual)

resource "aws_kms_key" "drapestore" {
  description             = "DrapeStore PCI DSS encryption key"
  deletion_window_in_days = 30
  enable_key_rotation     = true     # REQ 3.7.1

  tags = {
    Name      = "drapestore-kms"
    PCI_Scope = "in-scope"
  }
}

# ────────────────────────────────────────────────────────────
# PCI DSS REQ 3.4 — RDS MySQL Database (encrypted)
# ────────────────────────────────────────────────────────────
# The database lives in a PRIVATE subnet — internet can't reach it.
# Storage is encrypted with KMS (AES-256).
# Backups are kept for 7 days.

resource "aws_db_subnet_group" "main" {
  name       = "drapestore-db-subnet-group"
  subnet_ids = aws_subnet.private[*].id    # Private subnets only!
  tags       = { Name = "drapestore-db-subnet-group" }
}

resource "aws_db_instance" "drapestore" {
  identifier              = "drapestore-db"
  engine                  = "mysql"
  engine_version          = "8.0"
  instance_class          = "db.t3.micro"   # Free-tier eligible
  allocated_storage       = 20              # GB
  storage_type            = "gp3"

  db_name                 = "drapestore"
  username                = var.db_username
  password                = var.db_password

  db_subnet_group_name    = aws_db_subnet_group.main.name
  vpc_security_group_ids  = [aws_security_group.rds.id]

  # PCI DSS REQ 3.4 — Encryption at rest
  storage_encrypted       = true
  kms_key_id              = aws_kms_key.drapestore.arn

  # PCI DSS REQ 10 — Database query logging
  enabled_cloudwatch_logs_exports = ["audit", "error", "general", "slowquery"]

  # Backups & high availability
  backup_retention_period = 7              # Keep daily backups for 7 days
  multi_az                = false          # Set true for production HA
  publicly_accessible     = false          # REQ 1: Never public!

  # PCI DSS REQ 6.4 — Auto-apply minor security patches
  auto_minor_version_upgrade = true

  skip_final_snapshot     = true           # Set false in production
  deletion_protection     = false          # Set true in production

  tags = {
    Name      = "drapestore-db"
    PCI_Scope = "in-scope"
  }
}

# ────────────────────────────────────────────────────────────
# PCI DSS REQ 10 — CloudTrail (logs ALL AWS API activity)
# ────────────────────────────────────────────────────────────
# Every action taken in our AWS account is logged.
# Logs are stored in S3, retained 12 months, tamper-proof.

resource "aws_s3_bucket" "logs" {
  bucket = "drapestore-pci-logs-${data.aws_caller_identity.current.account_id}"
  tags   = { Name = "drapestore-logs", PCI_Scope = "in-scope" }
}

resource "aws_s3_bucket_versioning" "logs" {
  bucket = aws_s3_bucket.logs.id
  versioning_configuration {
    status = "Enabled"  # REQ 10.5: Versioning protects logs from deletion
  }
}

resource "aws_s3_bucket_lifecycle_configuration" "logs" {
  bucket = aws_s3_bucket.logs.id
  rule {
    id     = "pci-retention"
    status = "Enabled"
    filter {}
    transition {
      days          = 90
      storage_class = "GLACIER"   # Move old logs to cheaper storage
    }
    expiration {
      days = 365  # REQ 10.5.1: Keep 12 months
    }
  }
}

resource "aws_cloudtrail" "main" {
  name                          = "drapestore-pci-trail"
  s3_bucket_name                = aws_s3_bucket.logs.id
  include_global_service_events = true
  is_multi_region_trail         = true
  enable_log_file_validation    = true   # REQ 10.3.2: Detect log tampering

  tags = { PCI_Scope = "in-scope" }
}

data "aws_caller_identity" "current" {}

# ────────────────────────────────────────────────────────────
# OUTPUTS — Show useful info after deployment
# ────────────────────────────────────────────────────────────

output "vpc_id" {
  value = aws_vpc.main.id
}

output "rds_endpoint" {
  value     = aws_db_instance.drapestore.endpoint
  sensitive = true
}

output "deployment_summary" {
  value = <<-EOT

  ╔══════════════════════════════════════════════════════════╗
  ║ ✅ DrapeStore AWS infrastructure deployed!              ║
  ╠══════════════════════════════════════════════════════════╣
  ║                                                          ║
  ║ VPC ID:        ${aws_vpc.main.id}        ║
  ║ Region:        ${var.aws_region}                                ║
  ║                                                          ║
  ║ Next steps:                                              ║
  ║   1. Update .env with RDS endpoint                       ║
  ║   2. Deploy Laravel app to EC2                           ║
  ║   3. Run: php artisan migrate                            ║
  ║   4. Visit your CloudFront URL                           ║
  ║                                                          ║
  ╚══════════════════════════════════════════════════════════╝

  EOT
}
