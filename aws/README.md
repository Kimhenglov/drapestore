# 🌥 AWS Infrastructure — DrapeStore PCI DSS v4.0

This folder contains everything you need to deploy DrapeStore to AWS,
exactly matching the architecture from your lecture's Figure 3 diagram.

---

## 📐 Architecture Overview

Your lecture's diagram:
```
[Customer] → [CloudFront] → [WAF] → [API Gateway] → [Lambda+VPC] → [DynamoDB]
                                                              ↓
                                                       [VPN Gateway → Payment Processor]
```

Our DrapeStore equivalent (using EC2 + RDS instead of Lambda + DynamoDB):
```
[Customer] → [CloudFront] → [WAF] → [ALB (DMZ)] → [Laravel EC2 (VPC/CDE)] → [RDS MySQL]
                                                              ↓
                                                       [NAT → Stripe API (HTTPS/TLS)]
```

Both architectures satisfy PCI DSS v4.0 — they're just different AWS service combinations.

---

## 🗺 Network Zones (PCI DSS REQ 1)

| Zone | What's in it | AWS Service | Public access |
|---|---|---|---|
| **Untrusted Internet** | Customer browsers | — | Yes |
| **External Firewall** | First defense layer | CloudFront + AWS WAF | Yes (HTTPS only) |
| **DMZ (Public Subnet)** | Load balancer | Application Load Balancer | Yes (port 443) |
| **CDE (Private Subnet)** | Laravel app + database | EC2 + RDS MySQL | NO — completely isolated |
| **Payment Processor** | Stripe (external) | Internet via NAT Gateway | Outbound only |

---

## 📁 Files in This Folder

| File | What it does |
|---|---|
| `terraform/main.tf` | The full infrastructure as code (VPC, EC2, RDS, etc.) |
| `terraform/variables.tf` | Configurable variables |
| `terraform/security_groups.tf` | Firewall rules (REQ 1) |
| `terraform/waf.tf` | Web Application Firewall (REQ 6) |
| `docs/deployment-guide.md` | Step-by-step deployment instructions |
| `docs/pci-mapping.md` | How each AWS service maps to PCI requirements |

---

## 🚀 Quick Deployment

### Prerequisites
- AWS account (free tier works for testing)
- AWS CLI installed: `aws configure`
- Terraform installed: `terraform --version`

### Steps
```bash
cd aws/terraform

# 1. Initialize Terraform
terraform init

# 2. See what will be created
terraform plan

# 3. Create everything on AWS
terraform apply

# 4. Get the URL of your deployed app
terraform output app_url
```

### Cost estimate (per month)
- EC2 t3.medium: ~$30
- RDS db.t3.micro: ~$13
- CloudFront: pay-per-use (~$1-5)
- WAF: $5 + $1/rule
- **Total: ~$50-80/month** (free tier covers most for first 12 months)

---

## ⚠️ Important Notes

1. **Real PCI compliance** requires a QSA (Qualified Security Assessor) audit.
   This Terraform creates a PCI-compliant *architecture*, but compliance is
   ultimately a process, not a configuration.

2. **Test in dev first** — never deploy to production without thorough testing.

3. **Backup your `terraform.tfstate`** — this file tracks what's deployed.
   Lose it = lose track of your AWS resources.

4. **Destroy resources when done testing** to avoid AWS bills:
   ```bash
   terraform destroy
   ```
