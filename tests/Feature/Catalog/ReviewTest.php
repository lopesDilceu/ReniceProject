<?php

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->customer = User::factory()->create();
    $this->product = Product::factory()->create(['slug' => 'copo-anime']);
});

function buy(User $customer, Product $product, OrderStatus $status): Order
{
    $order = Order::factory()->for($customer)->status($status)->create();
    OrderItem::factory()->for($order)->for($product)->create();

    return $order;
}

function review(array $data = ['rating' => 5, 'comment' => 'Ótimo!']): array
{
    return $data;
}

describe('SEC-05 reviews', function () {
    it('requires sign-in', function () {
        $this->post(route('reviews.store', 'copo-anime'), review())->assertRedirect(route('login'));
    })->issue(21);

    it('accepts reviews only from customers who received the product', function (?OrderStatus $status) {
        if ($status) {
            buy($this->customer, $this->product, $status);
        }

        $this->actingAs($this->customer)->post(route('reviews.store', 'copo-anime'), review())->assertForbidden();

        expect(Review::count())->toBe(0);
    })->with([
        'never bought' => [null],
        'payment pending' => [OrderStatus::PendingPayment],
        'paid, not delivered' => [OrderStatus::Paid],
        'cancelled' => [OrderStatus::Cancelled],
    ])->issue(21);

    it('records the review of a delivered product', function () {
        buy($this->customer, $this->product, OrderStatus::Delivered);

        $this->actingAs($this->customer)->post(route('reviews.store', 'copo-anime'), review())->assertSessionHasNoErrors();

        expect(Review::sole())
            ->user_id->toBe($this->customer->id)
            ->product_id->toBe($this->product->id)
            ->rating->toBe(5);
    })->issue(21);

    it('accepts one review per product', function () {
        buy($this->customer, $this->product, OrderStatus::Delivered);
        $this->actingAs($this->customer)->post(route('reviews.store', 'copo-anime'), review());

        $this->actingAs($this->customer)->post(route('reviews.store', 'copo-anime'), review())->assertForbidden();

        expect(Review::count())->toBe(1);
    })->issue(21);

    it('validates the rating between 1 and 5', function (mixed $rating) {
        buy($this->customer, $this->product, OrderStatus::Delivered);

        $this->actingAs($this->customer)->post(route('reviews.store', 'copo-anime'), review(['rating' => $rating]))
            ->assertSessionHasErrors('rating');
    })->with([0, 6, 'cinco', null])->issue(21);
});

it('offers the review form only for delivered, unreviewed products', function () {
    $delivered = buy($this->customer, $this->product, OrderStatus::Delivered);
    $reviewed = Product::factory()->create();
    OrderItem::factory()->for($delivered)->for($reviewed)->create();
    Review::factory()->for($this->customer)->for($reviewed)->create();

    $this->actingAs($this->customer)->get(route('orders.show', $delivered))
        ->assertInertia(fn (Assert $page) => $page->where('reviewableProductIds', [$this->product->id]));

    $pending = buy($this->customer, Product::factory()->create(), OrderStatus::PendingPayment);
    $this->actingAs($this->customer)->get(route('orders.show', $pending))
        ->assertInertia(fn (Assert $page) => $page->where('reviewableProductIds', []));
});
