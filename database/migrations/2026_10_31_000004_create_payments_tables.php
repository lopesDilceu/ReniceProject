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
        // One Pix charge per order. The provider's id is the link back from webhooks.
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->restrictOnDelete();
            $table->string('provider', 30);
            $table->string('provider_payment_id', 64);
            $table->string('status', 20)->index();
            $table->unsignedInteger('amount_cents');
            // Pix "copia e cola" and the QR code image as a data URI.
            $table->text('pix_code');
            $table->text('pix_qr_image');
            $table->timestamp('expires_at');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'provider_payment_id']);
        });

        // Every webhook notification received, once: the unique key makes
        // retries and replays from the provider no-ops.
        Schema::create('webhook_calls', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 30);
            $table->string('event_key', 100);
            $table->string('type', 50);
            $table->json('payload');
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['provider', 'event_key']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE payments ADD CONSTRAINT payments_amount_positive CHECK (amount_cents > 0)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_calls');
        Schema::dropIfExists('payments');
    }
};
