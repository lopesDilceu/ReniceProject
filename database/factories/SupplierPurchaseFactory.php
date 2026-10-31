<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\SupplierPurchase;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupplierPurchase>
 */
class SupplierPurchaseFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'supplier' => fake('pt_BR')->company(),
            'quantity' => fake()->numberBetween(10, 100),
            'unit_cost_cents' => fake()->numberBetween(200, 1500),
            'purchased_at' => fake()->dateTimeBetween('-6 months'),
        ];
    }
}
