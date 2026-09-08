<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\ServiceRequest;
use App\Models\ServiceVisit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceVisit>
 */
class ServiceVisitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(['scheduled', 'on_the_way', 'in_progress', 'completed', 'cancelled']);
        $scheduledAt = fake()->dateTimeBetween('now', '+10 days');
        $startedAt = in_array($status, ['in_progress', 'completed'], true)
            ? (clone $scheduledAt)->modify('+'.fake()->numberBetween(15, 90).' minutes')
            : null;

        return [
            'company_id' => Company::factory(),
            'service_request_id' => ServiceRequest::factory(),
            'technician_id' => User::factory(),
            'scheduled_at' => $scheduledAt,
            'started_at' => $startedAt,
            'finished_at' => $status === 'completed' && $startedAt
                ? (clone $startedAt)->modify('+'.fake()->numberBetween(30, 180).' minutes')
                : null,
            'visit_status' => $status,
            'location_address' => fake()->address(),
            'technician_notes' => fake()->optional()->paragraph(),
        ];
    }
}
