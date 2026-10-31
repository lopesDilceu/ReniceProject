<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ReviewController extends Controller
{
    public function index(): Response
    {
        $reviews = Review::query()
            ->with(['product:id,name,slug', 'user:id,name,email'])
            ->latest('id')
            ->paginate(20);

        return Inertia::render('admin/reviews/index', [
            'reviews' => $reviews->through(fn (Review $review) => [
                'id' => $review->id,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'product' => $review->product->name,
                'product_slug' => $review->product->slug,
                // Admins see who wrote it; the storefront shows "Maria S.".
                'author' => $review->user->name,
                'author_public' => ReviewResource::displayName($review->user->name),
                'created_at' => $review->created_at?->toIso8601String(),
            ]),
        ]);
    }

    public function destroy(Review $review): RedirectResponse
    {
        $review->delete();

        $this->toast('success', 'Avaliação removida.');

        return back();
    }
}
