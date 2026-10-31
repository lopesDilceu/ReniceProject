<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'product_name' => fn (array $attributes) => Product::withTrashed()->findOrFail($attributes['product_id'])->name,
            'unit_price_cents' => fn (array $attributes) => Product::withTrashed()->findOrFail($attributes['product_id'])->price_cents,
            'quantity' => fake()->numberBetween(1, 5),
        ];
    }
}
