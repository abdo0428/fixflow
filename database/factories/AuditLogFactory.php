<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
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
            'user_id' => User::factory(),
            'action' => fake()->randomElement(['created', 'updated', 'completed', 'cancelled']),
            'model_type' => User::class,
            'model_id' => 1,
            'old_values' => null,
            'new_values' => ['status' => 'active'],
            'ip_address' => fake()->ipv4(),
        ];
    }
}
