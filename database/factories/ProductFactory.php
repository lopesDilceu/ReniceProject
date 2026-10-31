<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $size = fake()->randomElement(['300ml', '500ml']);
        $ice = fake()->randomElement(['pequeno', 'médio', 'grande']);
        $name = "Copo {$size} gelo {$ice} ".fake()->unique()->numerify('###');

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => "Copo de {$size} com gelo de tamanho {$ice}.",
            'price_cents' => fake()->numberBetween(500, 3000),
            'image_path' => null,
            'stock' => 0,
        ];
    }

    public function inStock(int $quantity = 50): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => $quantity,
        ]);
    }
}
