<?php

namespace App\Jobs;

use App\Models\Product;
use App\Search\SemanticSearch;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

/**
 * Stores a product's embedding for semantic search. Queued after the save
 * commits, unique per product for a minute (several edits in a row embed
 * once), and skipped when model and text are unchanged, so an embedding is
 * only paid for when it can differ.
 */
class EmbedProduct implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public int $uniqueFor = 60;

    public function __construct(public readonly Product $product)
    {
        $this->afterCommit();
    }

    public function uniqueId(): string
    {
        return (string) $this->product->getKey();
    }

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [10, 60, 300];
    }

    public function handle(): void
    {
        if (! SemanticSearch::enabled()) {
            return;
        }

        $text = SemanticSearch::text($this->product);
        $hash = hash('sha256', (SemanticSearch::model() ?? SemanticSearch::provider())."\n".$text);

        $current = DB::table('product_embeddings')->where('product_id', $this->product->getKey())->value('source_hash');

        if ($current === $hash) {
            return;
        }

        $response = SemanticSearch::embed($text);

        DB::table('product_embeddings')->updateOrInsert(
            ['product_id' => $this->product->getKey()],
            [
                // pgvector reads the JSON array notation: [0.1,0.2,...]
                'embedding' => json_encode($response->first(), JSON_THROW_ON_ERROR),
                'model' => (string) ($response->meta->model ?? SemanticSearch::model() ?? 'default'),
                'source_hash' => $hash,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }
}
