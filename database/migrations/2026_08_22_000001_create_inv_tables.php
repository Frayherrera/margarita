<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inv_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('inv_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inv_category_id')->nullable()->constrained('inv_categories')->nullOnDelete();
            $table->string('name');
            $table->string('unit', 20)->default('unidad');
            $table->decimal('current_stock', 12, 2)->default(0);
            $table->decimal('min_stock', 12, 2)->default(0);
            $table->decimal('purchase_price', 12, 2)->nullable();
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->boolean('active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['active', 'name']);
        });

        Schema::create('inv_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inv_product_id')->constrained('inv_products')->restrictOnDelete();
            $table->string('type', 10); // entrada | salida
            $table->string('reason', 20); // compra | venta | merma | ajuste
            $table->decimal('quantity', 12, 2);
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->date('moved_at');
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['moved_at', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_movements');
        Schema::dropIfExists('inv_products');
        Schema::dropIfExists('inv_categories');
    }
};
