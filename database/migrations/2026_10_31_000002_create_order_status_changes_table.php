<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Append-only: every status an order went through; orders.status is the current one.
        Schema::create('order_status_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            // Null on the first row: the order was just placed.
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            // Null when the system made the change (payment webhook, expiry).
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['order_id', 'id']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE order_status_changes ADD CONSTRAINT order_status_changes_status_changes CHECK (from_status IS DISTINCT FROM to_status)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_status_changes');
    }
};
