<?php

namespace App\Console\Commands;

use App\Jobs\SummarizeReviews;
use App\Models\Product;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

#[Signature('reviews:summarize')]
#[Description('Summarize the reviews of every product (skips summaries that are already up to date)')]
class SummarizeAllReviews extends Command
{
    public function handle(): int
    {
        if (! SummarizeReviews::enabled()) {
            $this->warn('Review summaries are off: set REVIEW_SUMMARY_PROVIDER (and the provider key).');

            return self::FAILURE;
        }

        $failed = 0;

        Product::query()->has('reviews')->orderBy('id')->each(function (Product $product) use (&$failed) {
            try {
                SummarizeReviews::dispatchSync($product);
                $this->line("  ✓ {$product->name}");
            } catch (Throwable $exception) {
                $failed++;
                $this->error("  ✗ {$product->name}: {$exception->getMessage()}");
            }
        });

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
