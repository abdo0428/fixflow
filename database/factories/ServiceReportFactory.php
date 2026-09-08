<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\ServiceReport;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceReport>
 */
class ServiceReportFactory extends Factory
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
            'technician_id' => User::factory(),
            'diagnosis' => fake()->paragraph(),
            'solution' => fake()->paragraph(),
            'customer_signature' => null,
            'before_images' => [],
            'after_images' => [],
            'pdf_path' => null,
        ];
    }
}
