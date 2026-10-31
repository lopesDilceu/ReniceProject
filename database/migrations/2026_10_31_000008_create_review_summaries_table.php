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
        // The AI summary of a product's reviews. Read by the product page;
        // only the queued job writes it, so the page never calls a provider.
        Schema::create('review_summaries', function (Blueprint $table) {
            $table->foreignId('product_id')->primary()->constrained()->cascadeOnDelete();
            $table->text('summary');
            $table->json('pros');
            $table->json('cons');
            $table->unsignedInteger('reviews_count');
            // sha256 of prompt version + model + the reviews summarized.
            $table->char('source_hash', 64);
            $table->string('prompt_version', 20);
            $table->string('model', 100);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_summaries');
    }
};
