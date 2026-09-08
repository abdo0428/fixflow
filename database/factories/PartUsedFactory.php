<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Part;
use App\Models\PartUsed;
use App\Models\ServiceRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PartUsed>
 */
class PartUsedFactory extends Factory
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
            'service_request_id' => ServiceRequest::factory(),
            'part_id' => Part::factory(),
            'quantity' => fake()->numberBetween(1, 4),
            'unit_price' => fake()->randomFloat(2, 5, 500),
        ];
    }
}
