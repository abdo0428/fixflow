<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\ServiceRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $serviceCost = fake()->randomFloat(2, 80, 1200);
        $partsTotal = fake()->randomFloat(2, 0, 300);
        $taxRate = 0.15;
        $subtotal = round($serviceCost + $partsTotal, 2);
        $tax = round($subtotal * $taxRate, 2);

        return [
            'company_id' => Company::factory(),
            'service_request_id' => ServiceRequest::factory(),
            'invoice_number' => 'INV-'.fake()->unique()->numberBetween(10000, 99999),
            'service_cost' => $serviceCost,
            'parts_total' => $partsTotal,
            'tax_rate' => $taxRate,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $subtotal + $tax,
            'status' => fake()->randomElement(['draft', 'issued', 'paid', 'cancelled']),
            'issued_at' => now()->subDays(fake()->numberBetween(1, 10)),
            'paid_at' => null,
        ];
    }
}
