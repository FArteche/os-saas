<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'legal_name' => null,
            'document' => fake()->numerify('##############'),
            'email' => fake()->companyEmail(),
            'phone' => fake()->numerify('119########'),
            'zip_code' => fake()->numerify('########'),
            'street' => fake()->streetName(),
            'number' => fake()->buildingNumber(),
            'complement' => null,
            'district' => fake()->word(),
            'city' => fake()->city(),
            'state' => fake()->randomElement(['SP', 'RJ', 'MG', 'RS', 'PR', 'SC', 'BA']),
            'niche' => 'generic',
            'settings' => [],
        ];
    }
}
