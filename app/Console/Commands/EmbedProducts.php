<?php

namespace App\Console\Commands;

use App\Jobs\EmbedProduct;
use App\Models\Product;
use App\Search\SemanticSearch;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

#[Signature('products:embed')]
#[Description('Embed every product for semantic search (skips products whose text and model are unchanged)')]
class EmbedProducts extends Command
{
    public function handle(): int
    {
        if (! SemanticSearch::enabled()) {
            $this->warn('Semantic search is off: set SEARCH_EMBEDDINGS_PROVIDER (and the provider key) on PostgreSQL.');

            return self::FAILURE;
        }

        $failed = 0;

        Product::query()->orderBy('id')->each(function (Product $product) use (&$failed) {
            try {
                EmbedProduct::dispatchSync($product);
                $this->line("  ✓ {$product->name}");
            } catch (Throwable $exception) {
                $failed++;
                $this->error("  ✗ {$product->name}: {$exception->getMessage()}");
            }
        });

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
