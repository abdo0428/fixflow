<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Customer;
use App\Models\ServiceAsset;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceRequest>
 */
class ServiceRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(['new', 'under_review', 'scheduled', 'in_progress', 'waiting_parts', 'completed']);

        return [
            'company_id' => Company::factory(),
            'customer_id' => Customer::factory(),
            'service_asset_id' => ServiceAsset::factory(),
            'created_by' => User::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'priority' => fake()->randomElement(['low', 'medium', 'high', 'urgent']),
            'status' => $status,
            'preferred_date' => fake()->dateTimeBetween('now', '+14 days'),
            'completed_at' => $status === 'completed' ? now()->subDays(fake()->numberBetween(1, 5)) : null,
        ];
    }
}
