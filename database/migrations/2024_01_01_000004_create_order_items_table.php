<?php
// ============================================================
// Migration 4: Create ORDER_ITEMS table
// File: 2024_01_01_000004_create_order_items_table.php
//
// Depends on: orders, products (foreign keys)
// Must run AFTER orders migration.
//
// Each row = one product line in an order.
// Example: An order with 3 products has 3 rows here.
// ============================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->foreignId('order_id')
                  ->constrained('orders')
                  ->cascadeOnDelete();        // Delete items if order deleted

            $table->foreignId('product_id')
                  ->nullable()
                  ->constrained('products')
                  ->nullOnDelete();           // Keep item record if product deleted

            // We snapshot the product info so historical orders stay accurate
            // even if the product is later renamed or deleted
            $table->string('product_name', 200);
            $table->string('size', 10)->nullable();
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);

            $table->timestamps();

            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
