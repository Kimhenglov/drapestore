<?php
// ============================================================
// Migration 2: Create PRODUCTS table
// File: 2024_01_01_000002_create_products_table.php
//
// Stores fashion product catalog.
// Independent of users — runs second.
// ============================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->text('description');
            $table->decimal('price', 10, 2);                 // Up to 99,999,999.99
            $table->enum('category', [
                'tops', 'bottoms', 'dresses', 'accessories', 'outerwear'
            ]);
            $table->string('image_url', 500);
            $table->integer('stock')->default(100);
            $table->boolean('is_active')->default(true);     // Soft delete flag
            $table->string('size_options', 100)
                  ->default('XS,S,M,L,XL');                  // Comma-separated sizes
            $table->timestamps();

            // Index for filtering by category (very common query)
            $table->index('category');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
