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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            // Orders are financial records: users and products are soft-deleted,
            // so hard deletes that would orphan an order are refused.
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('status', 30)->index();
            $table->unsignedInteger('total_cents');
            // Snapshot of the delivery address at checkout time.
            $table->json('shipping_address');
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            // Snapshots: renaming or repricing a product must not rewrite past orders.
            $table->string('product_name', 100);
            $table->unsignedInteger('unit_price_cents');
            $table->unsignedInteger('quantity');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
