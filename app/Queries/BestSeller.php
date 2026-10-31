<?php

namespace App\Queries;

use App\Models\Product;

final readonly class BestSeller
{
    public function __construct(
        public Product $product,
        public int $unitsSold,
        public int $revenueCents,
    ) {}
}
