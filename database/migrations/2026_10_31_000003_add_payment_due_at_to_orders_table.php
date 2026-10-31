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
        Schema::table('orders', function (Blueprint $table) {
            // Until then the stock stays reserved; after it, the order expires.
            $table->timestamp('payment_due_at')->nullable()->after('shipping_address');
            $table->index(['status', 'payment_due_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['status', 'payment_due_at']);
            $table->dropColumn('payment_due_at');
        });
    }
};
