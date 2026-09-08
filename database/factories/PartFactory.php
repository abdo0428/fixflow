<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Part;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Part>
 */
class PartFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name' => fake()->randomElement(['Compressor Capacitor', 'Thermal Paste', 'Solar Fuse', 'Control Relay']),
            'sku' => strtoupper(fake()->unique()->bothify('SKU-####')),
            'unit_price' => fake()->randomFloat(2, 5, 500),
            'quantity' => fake()->numberBetween(2, 60),
            'low_stock_threshold' => fake()->numberBetween(1, 10),
        ];
    }
}
