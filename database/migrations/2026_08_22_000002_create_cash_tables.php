<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type', 10); // ingreso | egreso
            $table->timestamps();

            $table->unique(['name', 'type']);
        });

        Schema::create('cash_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_category_id')->nullable()->constrained('cash_categories')->nullOnDelete();
            $table->string('type', 10); // ingreso | egreso
            $table->string('concept');
            $table->decimal('amount', 12, 2);
            $table->string('method', 20); // efectivo | nequi | daviplata | transferencia | datafono
            $table->date('occurred_at');
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['occurred_at', 'type']);
            $table->index('method');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_movements');
        Schema::dropIfExists('cash_categories');
    }
};
