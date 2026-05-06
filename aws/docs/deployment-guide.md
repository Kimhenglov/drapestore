# 📘 AWS Deployment Guide — DrapeStore PCI DSS v4.0

A step-by-step guide to deploy DrapeStore to AWS, matching your lecture's architecture.

---

## 🎯 What You'll Build

```
                  ┌─────────────┐
   Customer ─────▶│ CloudFront  │  (Step 1 - global CDN with TLS)
                  └──────┬──────┘
                         │
                  ┌──────▼──────┐
                  │   AWS WAF   │  (Step 2 - blocks attacks - REQ 6.4)
                  └──────┬──────┘
                         │
                  ┌──────▼──────┐
                  │     ALB     │  (Step 3 - load balancer in DMZ)
                  └──────┬──────┘
                         │   ┌───────── VPC (Private Network) ─────────┐
                         │   │                                          │
                  ┌──────▼───┴───┐         ┌────────────────┐          │
                  │ EC2 Instance │────────▶│  RDS MySQL     │          │
                  │   (Laravel)  │         │  (encrypted)   │          │
                  │   PCI CDE    │         │   PCI CDE      │          │
                  └──────┬───────┘         └────────────────┘          │
                         │                                              │
                  ┌──────▼──────┐                                       │
                  │ NAT Gateway │  (outbound to Stripe only)            │
                  └──────┬──────┘                                       │
                         │                                              │
                         └──────────────────────────────────────────────┘
                                         │
                                  ┌──────▼──────┐
                                  │   Stripe    │  (REQ 4 - TLS encrypted)
                                  └─────────────┘
```

---

## 📋 Prerequisites Checklist

Before you start, make sure you have:

- [ ] AWS account ([sign up free](https://aws.amazon.com/free))
- [ ] AWS CLI installed and configured (`aws configure`)
- [ ] Terraform v1.5+ installed
- [ ] A domain name (optional, for HTTPS certificate)
- [ ] Stripe account with API keys

---

## 🚀 Step-by-Step Deployment

### STEP 1: Configure AWS CLI
```bash
aws configure
# Enter your AWS Access Key ID
# Enter your AWS Secret Access Key
# Default region: ap-southeast-1 (or your preferred region)
# Default output format: json
```

### STEP 2: Create SSH key pair
```bash
# Create a key pair to SSH into EC2 later
aws ec2 create-key-pair \
    --key-name drapestore-key \
    --query 'KeyMaterial' \
    --output text > ~/.ssh/drapestore-key.pem

chmod 400 ~/.ssh/drapestore-key.pem
```

### STEP 3: Create terraform.tfvars
```bash
cd aws/terraform
cat > terraform.tfvars << EOF
db_password    = "YourSuperStrongDBPassword123!"
admin_ip_cidrs = ["YOUR.IP.HERE/32"]  # Find at whatismyip.com
EOF
```

### STEP 4: Initialize Terraform
```bash
terraform init
```

This downloads the AWS provider plugin (~50 MB).

### STEP 5: Preview what will be created
```bash
terraform plan
```

You'll see ~30 resources to be created:
- 1 VPC
- 4 Subnets (2 public, 2 private)
- 1 Internet Gateway
- 1 NAT Gateway
- 3 Security Groups
- 1 RDS database
- 1 KMS key
- 1 WAF
- 1 CloudTrail
- 1 S3 bucket for logs

### STEP 6: Deploy!
```bash
terraform apply
# Type 'yes' when prompted
# Wait 10-15 minutes (RDS takes the longest)
```

When done, you'll see something like:
```
✅ DrapeStore AWS infrastructure deployed!
VPC ID: vpc-0abc123def456
Region: ap-southeast-1
```

### STEP 7: Get RDS endpoint
```bash
terraform output rds_endpoint
# Example: drapestore-db.cabc123.ap-southeast-1.rds.amazonaws.com
```

### STEP 8: Update your Laravel `.env`
```env
DB_CONNECTION=mysql
DB_HOST=drapestore-db.cabc123.ap-southeast-1.rds.amazonaws.com
DB_PORT=3306
DB_DATABASE=drapestore
DB_USERNAME=drapestore_admin
DB_PASSWORD=YourSuperStrongDBPassword123!
```

### STEP 9: SSH into EC2 and deploy Laravel
```bash
# Get EC2 public IP from AWS console
ssh -i ~/.ssh/drapestore-key.pem ubuntu@YOUR_EC2_IP

# On the EC2:
sudo apt update
sudo apt install php8.2 php8.2-mysql php8.2-mbstring composer nginx -y

# Upload your DrapeStore project (use scp or git clone)
scp -i ~/.ssh/drapestore-key.pem -r drapestore/ ubuntu@YOUR_EC2_IP:/var/www/

# On EC2 again:
cd /var/www/drapestore
composer install --no-dev
php artisan migrate
php artisan db:seed
php artisan config:cache
```

### STEP 10: Set up Nginx
```bash
sudo tee /etc/nginx/sites-available/drapestore << EOF
server {
    listen 80;
    root /var/www/drapestore/public;
    index index.php;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php\$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
    }
}
EOF

sudo ln -s /etc/nginx/sites-available/drapestore /etc/nginx/sites-enabled/
sudo systemctl reload nginx
```

---

## ✅ Verify Deployment

1. **Check the app loads**: Visit your CloudFront URL → see DrapeStore homepage
2. **Check HTTPS works**: URL should auto-redirect to https://
3. **Check WAF works**: Try `https://yoursite.com/?id=1' OR '1'='1` → should be blocked
4. **Check audit logs**: Login as admin → see real audit events
5. **Test payment**: Use card `4242 4242 4242 4242` → should succeed

---

## 💰 Cost Estimate

| Service | Cost (per month) |
|---|---|
| EC2 t3.medium | ~$30 |
| RDS db.t3.micro | ~$13 |
| NAT Gateway | ~$32 |
| CloudFront | $1-5 |
| WAF | ~$5 |
| S3 (logs) | $1 |
| **Total** | **~$80/month** |

💡 **AWS Free Tier** covers most of this for the first 12 months!

---

## 🧹 Cleanup (When Done)

To AVOID ongoing AWS charges, destroy everything:
```bash
cd aws/terraform
terraform destroy
# Type 'yes' to confirm
```

This deletes ALL resources. **You will lose all data!** Take a database backup first if needed.

---

## 🆘 Common Issues

| Problem | Solution |
|---|---|
| "Could not connect to RDS" | Check Security Groups, ensure EC2 SG can reach RDS SG on 3306 |
| "Out of subnet IPs" | Increase subnet CIDR in main.tf (e.g. `/22` instead of `/24`) |
| "WAF blocks legit users" | Check WAF logs in CloudWatch, adjust rules |
| "503 Service Unavailable" | Check ALB health checks; EC2 might not be responding |
| "Stripe API timeout" | Verify NAT Gateway is working; check route tables |

---

## 📊 Monitoring & Compliance

Once deployed, check these regularly:

- **CloudWatch Dashboards** → Service health
- **CloudTrail Console** → All AWS actions logged (REQ 10)
- **WAF Console** → Blocked requests count
- **RDS Performance Insights** → Database queries
- **AWS Config** → Compliance status of all resources

---

*Happy deploying! 🚀*
*Remember: Real PCI DSS compliance requires a QSA audit.*
