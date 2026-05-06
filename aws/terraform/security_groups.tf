# ============================================================
# AWS Security Groups (Virtual Firewalls)
# File: aws/terraform/security_groups.tf
#
# PCI DSS REQ 1: Network security controls
# Each Security Group is a firewall that controls what traffic
# is allowed in/out of an AWS resource.
#
# Our 3-layer defense:
#   Layer 1: ALB SG     — accepts public HTTPS
#   Layer 2: EC2 SG     — only accepts traffic from ALB
#   Layer 3: RDS SG     — only accepts traffic from EC2
# ============================================================

# ────────────────────────────────────────────────────────────
# LAYER 1: Application Load Balancer (DMZ)
# Accepts public HTTPS — first contact with customers
# ────────────────────────────────────────────────────────────

resource "aws_security_group" "alb" {
  name        = "drapestore-alb-sg"
  description = "PCI REQ 1: ALB - HTTPS from internet only"
  vpc_id      = aws_vpc.main.id

  # INBOUND: Allow HTTPS (443) from anywhere
  ingress {
    description = "HTTPS from internet (REQ 4 - TLS)"
    from_port   = 443
    to_port     = 443
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  # INBOUND: Allow HTTP (80) for redirect to HTTPS
  ingress {
    description = "HTTP for redirect to HTTPS"
    from_port   = 80
    to_port     = 80
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  # OUTBOUND: Allow ALB to reach EC2 on port 80 (Laravel)
  egress {
    description     = "To EC2 Laravel app"
    from_port       = 80
    to_port         = 80
    protocol        = "tcp"
    security_groups = [aws_security_group.ec2.id]
  }

  tags = {
    Name      = "drapestore-alb-sg"
    PCI_Scope = "in-scope"
  }
}

# ────────────────────────────────────────────────────────────
# LAYER 2: EC2 Laravel App (CDE)
# REQ 1.3: Only accepts traffic from ALB — never directly internet
# ────────────────────────────────────────────────────────────

resource "aws_security_group" "ec2" {
  name        = "drapestore-ec2-sg"
  description = "PCI REQ 1: Laravel app - from ALB only"
  vpc_id      = aws_vpc.main.id

  # INBOUND: Only from ALB Security Group (NOT from public internet!)
  ingress {
    description     = "From ALB only"
    from_port       = 80
    to_port         = 80
    protocol        = "tcp"
    security_groups = [aws_security_group.alb.id]
  }

  # INBOUND: SSH for admin (restricted by IP)
  # REQ 2.3: Encrypt non-console admin access
  ingress {
    description = "SSH from admin IPs only"
    from_port   = 22
    to_port     = 22
    protocol    = "tcp"
    cidr_blocks = var.admin_ip_cidrs   # Set in variables.tf
  }

  # OUTBOUND: Only to RDS on MySQL port
  egress {
    description     = "To RDS MySQL"
    from_port       = 3306
    to_port         = 3306
    protocol        = "tcp"
    security_groups = [aws_security_group.rds.id]
  }

  # OUTBOUND: HTTPS to Stripe API
  # REQ 4: Stripe enforces TLS 1.2+
  egress {
    description = "HTTPS to Stripe API"
    from_port   = 443
    to_port     = 443
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]    # Stripe uses many IPs
  }

  # OUTBOUND: HTTP for package downloads (apt update etc.)
  egress {
    description = "Package downloads"
    from_port   = 80
    to_port     = 80
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  tags = {
    Name      = "drapestore-ec2-sg"
    PCI_Scope = "in-scope"
  }
}

# ────────────────────────────────────────────────────────────
# LAYER 3: RDS Database (Deepest CDE layer)
# REQ 1.3: Only EC2 can talk to it. Database has NO internet access.
# ────────────────────────────────────────────────────────────

resource "aws_security_group" "rds" {
  name        = "drapestore-rds-sg"
  description = "PCI REQ 1: RDS - from EC2 only"
  vpc_id      = aws_vpc.main.id

  # INBOUND: ONLY from EC2 Security Group on MySQL port
  ingress {
    description     = "MySQL from Laravel app only"
    from_port       = 3306
    to_port         = 3306
    protocol        = "tcp"
    security_groups = [aws_security_group.ec2.id]
  }

  # OUTBOUND: NONE — database doesn't need to reach anywhere
  # REQ 1.3: Restrict outbound to only what's needed
  # (no egress block = no outbound allowed)

  tags = {
    Name      = "drapestore-rds-sg"
    PCI_Scope = "in-scope"
  }
}
