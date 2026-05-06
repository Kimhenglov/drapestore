<?php
// ============================================================
// Migration 1: Create USERS table
// File: database/migrations/2024_01_01_000001_create_users_table.php
//
// MUST run FIRST — other tables (orders, audit_logs) reference users.
//
// PCI DSS REQ 8: User authentication & unique IDs
//   - id (unique identifier per user) — REQ 8.2.1
//   - email (unique, can't share accounts) — REQ 8.2.1
//   - password (bcrypt hashed) — REQ 8.3.1
//   - role (RBAC) — REQ 7
//   - login_attempts + locked_until (lockout) — REQ 8.3.4
//   - last_login_at (track inactivity) — REQ 8.2.6
// ============================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();                                    // Auto-increment unique ID
            $table->string('name', 100);
            $table->string('email', 200)->unique();          // No two users can share email
            $table->string('password');                       // Bcrypt hash (60 chars)
            $table->enum('role', ['customer', 'admin'])
                  ->default('customer');                     // REQ 7: Default to least privilege
            $table->timestamp('last_login_at')->nullable();
            $table->integer('login_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
            $table->rememberToken();                         // Laravel "remember me" feature
            $table->timestamps();                            // created_at + updated_at

            // Index on email for faster login lookups
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
