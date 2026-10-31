<?php

namespace App\Jobs;

use App\Ai\Agents\ReviewSummarizer;
use App\Models\Product;
use App\Models\Review;
use App\Models\ReviewSummary;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Laravel\Ai\Responses\StructuredAgentResponse;
use UnexpectedValueException;

/**
 * (Re)generates a product's review summary.
 *
 * - Debounced: dispatched with a delay and unique per product, so a burst of
 *   reviews produces one call.
 * - Skipped when the prompt version, model and reviews are unchanged.
 * - On failure the previous summary stays (still true of the reviews it
 *   came from), and the job retries with backoff.
 */
class SummarizeReviews implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public readonly Product $product)
    {
        $this->afterCommit();
    }

    public static function enabled(): bool
    {
        return filled(Config::get('reviews.summaries.provider'));
    }

    /**
     * Schedules a summary after the debounce window.
     */
    public static function debounce(int $productId): void
    {
        if (! self::enabled()) {
            return;
        }

        $product = Product::withTrashed()->find($productId);

        if ($product !== null) {
            self::dispatch($product)->delay(now()->addSeconds(Config::integer('reviews.summaries.debounce_seconds')));
        }
    }

    public function uniqueId(): string
    {
        return (string) $this->product->getKey();
    }

    public function uniqueFor(): int
    {
        return Config::integer('reviews.summaries.debounce_seconds') + 60;
    }

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [30, 300];
    }

    public function handle(): void
    {
        if (! self::enabled()) {
            return;
        }

        $reviews = $this->product->reviews()
            ->whereNotNull('comment')->where('comment', '!=', '')
            ->latest('id')
            ->limit(Config::integer('reviews.summaries.max_reviews'))
            ->get(['id', 'rating', 'comment']);

        if ($reviews->count() < Config::integer('reviews.summaries.min_reviews')) {
            ReviewSummary::query()->whereKey($this->product->getKey())->delete();

            return;
        }

        $model = Config::get('reviews.summaries.model');
        $hash = hash('sha256', ReviewSummarizer::VERSION."\n".$model."\n".$reviews
            ->map(fn (Review $review) => "{$review->id}:{$review->rating}:{$review->comment}")
            ->implode("\n"));

        if (ReviewSummary::query()->whereKey($this->product->getKey())->value('source_hash') === $hash) {
            return;
        }

        $response = (new ReviewSummarizer)->prompt(
            ReviewSummarizer::promptFor($this->product, $reviews),
            provider: Config::string('reviews.summaries.provider'),
            model: is_string($model) && $model !== '' ? $model : null,
            timeout: Config::integer('reviews.summaries.timeout'),
        );

        // Model output is input like any other: check its shape before storing.
        $output = $response instanceof StructuredAgentResponse ? $response->toArray() : [];
        $text = trim((string) ($output['summary'] ?? ''));

        if ($text === '') {
            throw new UnexpectedValueException('The review summary came back without a summary.');
        }

        $summary = ReviewSummary::query()->find($this->product->getKey()) ?? new ReviewSummary;

        $summary->forceFill([
            'product_id' => $this->product->getKey(),
            'summary' => Str::limit($text, 280),
            'pros' => self::points($output['pros'] ?? []),
            'cons' => self::points($output['cons'] ?? []),
            'reviews_count' => $reviews->count(),
            'source_hash' => $hash,
            'prompt_version' => ReviewSummarizer::VERSION,
            'model' => (string) ($response->meta->model ?? $model ?? 'default'),
        ])->save();
    }

    /**
     * At most three short, non-empty strings.
     *
     * @return list<string>
     */
    private static function points(mixed $points): array
    {
        return collect(is_array($points) ? $points : [])
            ->filter(fn ($point) => is_string($point) && trim($point) !== '')
            ->map(fn (string $point) => Str::limit(trim($point), 80))
            ->take(3)
            ->values()
            ->all();
    }
}
