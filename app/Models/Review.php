<?php

namespace App\Models;

use App\Jobs\SummarizeReviews;
use Database\Factories\ReviewFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    /** @use HasFactory<ReviewFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::saved(fn (Review $review) => SummarizeReviews::debounce($review->product_id));

        // A removed review (moderation) must not live on in the summary:
        // drop it now and let the next run summarize what remains.
        static::deleted(function (Review $review) {
            ReviewSummary::query()->whereKey($review->product_id)->delete();
            SummarizeReviews::debounce($review->product_id);
        });
    }

    /**
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'rating',
        'comment',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
}
