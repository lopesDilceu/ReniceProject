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
        // pgvector only exists on PostgreSQL; the SQLite test database runs
        // without semantic search, which falls back to keyword search.
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        Schema::ensureVectorExtensionExists();

        // A separate table, so catalog queries never load 1024 floats per row.
        Schema::create('product_embeddings', function (Blueprint $table) {
            $table->foreignId('product_id')->primary()->constrained()->cascadeOnDelete();
            $table->vector('embedding', 1024);
            $table->string('model', 100);
            // sha256 of model + embedded text: unchanged products are not re-embedded.
            $table->char('source_hash', 64);
            $table->timestamps();

            // HNSW with cosine distance, what whereVectorSimilarTo() uses.
            $table->vectorIndex('embedding');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_embeddings');
    }
};
