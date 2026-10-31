<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\StoreReviewRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;

class ReviewController extends Controller
{
    public function store(StoreReviewRequest $request, Product $product): RedirectResponse
    {
        $request->user()->reviews()->create([
            'product_id' => $product->getKey(),
            ...$request->validated(),
        ]);

        $this->toast('success', 'Obrigado pela avaliação!');

        return back();
    }
}
