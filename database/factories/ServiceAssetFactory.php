<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Customer;
use App\Models\ServiceAsset;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceAsset>
 */
class ServiceAssetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $purchaseDate = fake()->dateTimeBetween('-3 years', '-3 months');
        $warrantyStartDate = fake()->dateTimeBetween($purchaseDate, '+1 month');

        return [
            'company_id' => Company::factory(),
            'customer_id' => Customer::factory(),
            'name' => fake()->randomElement(['Office AC Unit', 'Laptop Workstation', 'Solar Inverter', 'Elevator Panel']),
            'type' => fake()->randomElement(['air_conditioner', 'computer', 'solar', 'elevator', 'appliance']),
            'brand' => fake()->company(),
            'model' => strtoupper(fake()->bothify('MDL-###')),
            'serial_number' => strtoupper(fake()->unique()->bothify('SN-########')),
            'qr_code' => null,
            'purchase_date' => $purchaseDate,
            'warranty_start_date' => $warrantyStartDate,
            'warranty_end_date' => (clone $warrantyStartDate)->modify('+2 years'),
            'notes' => fake()->optional()->sentence(),
            'status' => fake()->randomElement(['active', 'active', 'under_maintenance', 'retired']),
        ];
    }
}
