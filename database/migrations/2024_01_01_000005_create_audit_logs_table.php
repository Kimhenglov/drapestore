<?php
// ============================================================
// Migration 5: Create AUDIT_LOGS table  (PCI DSS REQ 10)
// File: 2024_01_01_000005_create_audit_logs_table.php
//
// Depends on: users (foreign key)
// Runs LAST so users table exists.
//
// PCI DSS REQ 10: Log every security event with:
//   10.2.1 — Access to cardholder data
//   10.2.2 — Admin/privileged actions
//   10.2.4 — Failed authentication attempts
//   10.2.5 — Authentication credential changes
//   10.3.1 — User identification (user_id)
//   10.3.2 — Tamper-proof (HMAC checksum)
//   10.3.3 — Timestamp (created_at + NTP-synced server)
//   10.3.4 — Type of event (event_type)
//   10.3.5 — Origin (ip_address)
//   10.3.6 — Identity of affected resource (url)
// ============================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            // What happened (e.g. AUTH_LOGIN_SUCCESS, PAYMENT_PROCESSED)
            $table->string('event_type', 50);

            // Who did it (REQ 10.3.1)
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->string('user_email', 200)->nullable();

            // Where from (REQ 10.3.5)
            $table->string('ip_address', 45);  // IPv6 max length is 45 chars
            $table->text('user_agent')->nullable();

            // What was accessed (REQ 10.3.6)
            $table->string('url', 500)->nullable();

            // Human-readable description
            $table->text('description');

            // Extra context (sanitized — no CVV, no PAN, no passwords)
            $table->json('metadata')->nullable();

            // 🔒 REQ 10.3.2: HMAC-SHA256 checksum to detect tampering
            // If anyone modifies a log row, this checksum won't match anymore
            $table->string('checksum', 64);

            // REQ 10.3.3: Timestamp (Laravel auto-fills)
            $table->timestamp('created_at')->useCurrent();

            // Indexes for fast log searching
            $table->index('event_type');
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
