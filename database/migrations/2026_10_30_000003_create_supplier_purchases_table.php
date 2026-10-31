<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('supplier_purchases', function (Blueprint $table) {
            $table->id();
            // Purchase history must survive the product: products are soft-deleted.
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->string('supplier', 100);
            $table->unsignedInteger('quantity');
            // Cost is recorded here and never copied into the sale price (#14).
            $table->unsignedInteger('unit_cost_cents');
            $table->timestamp('purchased_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_purchases');
    }
};
