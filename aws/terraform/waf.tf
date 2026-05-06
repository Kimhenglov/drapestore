# ============================================================
# AWS WAF (Web Application Firewall)
# File: aws/terraform/waf.tf
#
# PCI DSS REQ 6.4: WAF for public-facing web applications
# (NEW REQUIREMENT in PCI DSS v4.0!)
#
# WAF inspects incoming requests and blocks attacks like:
#   - SQL Injection
#   - Cross-Site Scripting (XSS)
#   - Brute force attempts
#   - Known bad IPs
# ============================================================

resource "aws_wafv2_web_acl" "drapestore" {
  name        = "drapestore-waf"
  description = "PCI REQ 6.4 - WAF for DrapeStore"
  scope       = "REGIONAL"   # Use REGIONAL for ALB, CLOUDFRONT for CloudFront

  default_action {
    allow {}    # Allow by default, but block matching rules
  }

  # ── RULE 1: AWS Managed Common Rules ────────────────────────
  # Blocks OWASP Top 10 attacks (SQL injection, XSS, etc.)
  rule {
    name     = "AWS-Common-Attacks"
    priority = 1

    override_action {
      none {}    # Use the rule group's default actions
    }

    statement {
      managed_rule_group_statement {
        name        = "AWSManagedRulesCommonRuleSet"
        vendor_name = "AWS"
      }
    }

    visibility_config {
      cloudwatch_metrics_enabled = true
      metric_name                = "common-attacks"
      sampled_requests_enabled   = true
    }
  }

  # ── RULE 2: SQL Injection Protection ────────────────────────
  # Specifically blocks SQL injection attempts
  rule {
    name     = "AWS-SQL-Injection"
    priority = 2

    override_action {
      none {}
    }

    statement {
      managed_rule_group_statement {
        name        = "AWSManagedRulesSQLiRuleSet"
        vendor_name = "AWS"
      }
    }

    visibility_config {
      cloudwatch_metrics_enabled = true
      metric_name                = "sqli-attacks"
      sampled_requests_enabled   = true
    }
  }

  # ── RULE 3: Known Bad IPs ───────────────────────────────────
  # AWS maintains a list of known malicious IPs
  rule {
    name     = "AWS-Bad-IPs"
    priority = 3

    override_action {
      none {}
    }

    statement {
      managed_rule_group_statement {
        name        = "AWSManagedRulesAmazonIpReputationList"
        vendor_name = "AWS"
      }
    }

    visibility_config {
      cloudwatch_metrics_enabled = true
      metric_name                = "bad-ips"
      sampled_requests_enabled   = true
    }
  }

  # ── RULE 4: Rate Limiting (Brute Force Protection) ──────────
  # PCI REQ 8.3.4: Block IPs making too many requests
  rule {
    name     = "Rate-Limit"
    priority = 4

    action {
      block {}    # Block the IP for 5 minutes
    }

    statement {
      rate_based_statement {
        limit              = 1000      # Max 1000 requests per 5 min per IP
        aggregate_key_type = "IP"
      }
    }

    visibility_config {
      cloudwatch_metrics_enabled = true
      metric_name                = "rate-limit"
      sampled_requests_enabled   = true
    }
  }

  visibility_config {
    cloudwatch_metrics_enabled = true
    metric_name                = "drapestore-waf"
    sampled_requests_enabled   = true
  }

  tags = {
    Name      = "drapestore-waf"
    PCI_Scope = "in-scope"
  }
}
