<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property list<string> $pros
 * @property list<string> $cons
 * @property int $reviews_count
 *
 * The AI summary of a product's reviews, written by SummarizeReviews.
 */
class ReviewSummary extends Model
{
    protected $primaryKey = 'product_id';

    public $incrementing = false;

    /**
     * @var list<string>
     */
    protected $guarded = ['*'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'pros' => 'array',
            'cons' => 'array',
            'reviews_count' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
