<?php
// ============================================================
// Migration 3: Create ORDERS table
// File: 2024_01_01_000003_create_orders_table.php
//
// Depends on: users (foreign key)
// Must run AFTER users migration.
//
// 🔒 PCI DSS REQ 3 — CRITICAL TABLE DESIGN:
//   ✅ stripe_payment_id  — token reference (safe to store)
//   ✅ card_last_four    — last 4 digits only (safe per REQ 3.4)
//   ✅ card_brand        — "visa", "mastercard" etc (safe)
//   ❌ NO card_number column — never store full PAN
//   ❌ NO cvv column     — NEVER store CVV (illegal!)
//   ❌ NO expiry column  — never store expiry
//   ❌ NO track_data     — never store magnetic stripe data
// ============================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // Foreign key to users — nullable for guest checkout
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')      // Refers to users table
                  ->nullOnDelete();           // If user deleted, keep order but clear user_id

            // Customer info (from checkout form)
            $table->string('customer_name', 100);
            $table->string('customer_email', 200);
            $table->text('shipping_address');

            // Money fields
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax', 10, 2);
            $table->decimal('total', 10, 2);

            // Order status
            $table->enum('status', [
                'pending', 'paid', 'shipped', 'delivered', 'cancelled'
            ])->default('pending');

            // ⚠️ PCI DSS REQ 3 compliant payment fields:
            $table->string('stripe_payment_id', 100)->nullable(); // Token only!
            $table->string('card_last_four', 4)->nullable();      // e.g. "4242"
            $table->string('card_brand', 20)->nullable();          // e.g. "visa"

            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
