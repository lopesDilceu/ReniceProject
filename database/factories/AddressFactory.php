<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\User;
use Faker\Provider\pt_BR\Address as BrazilianAddress;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $faker = fake('pt_BR');

        return [
            'user_id' => User::factory(),
            'cep' => $faker->numerify('########'),
            'street' => $faker->streetName(),
            'number' => (string) $faker->numberBetween(1, 9999),
            'complement' => $faker->optional(0.3)->bothify('Apto ###'),
            'neighborhood' => $faker->randomElement(['Centro', 'Ibituruna', 'Todos os Santos', 'Major Prates', 'Morada do Parque']),
            'city' => $faker->city(),
            'state' => BrazilianAddress::stateAbbr(),
        ];
    }
}
