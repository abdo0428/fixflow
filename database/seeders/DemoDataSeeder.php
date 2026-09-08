<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Part;
use App\Models\PartUsed;
use App\Models\ServiceAsset;
use App\Models\ServiceReport;
use App\Models\ServiceRequest;
use App\Models\ServiceVisit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    /**
     * Seed a coherent demo tenant with users, customers, assets, requests, parts, and billing.
     */
    public function run(): void
    {
        $company = Company::query()->updateOrCreate(
            ['slug' => 'fixflow-demo-maintenance'],
            [
                'name' => 'FixFlow Demo Maintenance',
                'email' => 'operations@fixflow.test',
                'phone' => '+1-555-0100',
                'address' => '125 Field Service Ave, Demo City',
                'logo' => null,
                'status' => 'active',
                'trial_ends_at' => now()->addDays(21),
            ],
        );

        $superAdmin = User::query()->updateOrCreate(
            ['email' => 'super@example.com'],
            [
                'company_id' => null,
                'name' => 'Super Admin',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'phone' => '+1-555-0101',
                'avatar' => null,
                'status' => 'active',
            ],
        );
        $superAdmin->syncRoles('super_admin');

        $companyAdmin = User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'company_id' => $company->id,
                'name' => 'Company Admin',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'phone' => '+1-555-0102',
                'avatar' => null,
                'status' => 'active',
            ],
        );
        $companyAdmin->syncRoles('company_admin');

        $dispatcher = User::query()->updateOrCreate(
            ['email' => 'dispatcher@example.com'],
            [
                'company_id' => $company->id,
                'name' => 'Service Dispatcher',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'phone' => '+1-555-0103',
                'avatar' => null,
                'status' => 'active',
            ],
        );
        $dispatcher->syncRoles('dispatcher');

        $technician = User::query()->updateOrCreate(
            ['email' => 'technician@example.com'],
            [
                'company_id' => $company->id,
                'name' => 'Field Technician',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'phone' => '+1-555-0104',
                'avatar' => null,
                'status' => 'active',
            ],
        );
        $technician->syncRoles('technician');

        $customerUser = User::query()->updateOrCreate(
            ['email' => 'customer@example.com'],
            [
                'company_id' => $company->id,
                'name' => 'Demo Customer',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'phone' => '+1-555-0105',
                'avatar' => null,
                'status' => 'active',
            ],
        );
        $customerUser->syncRoles('customer');

        $customer = Customer::query()->updateOrCreate(
            [
                'company_id' => $company->id,
                'email' => 'customer@example.com',
            ],
            [
                'user_id' => $customerUser->id,
                'name' => 'Demo Customer',
                'phone' => '+1-555-0105',
                'address' => '88 Customer Plaza, Demo City',
                'city' => 'Demo City',
                'notes' => 'Primary demo customer with multiple service assets.',
            ],
        );

        $assets = collect([
            [
                'serial_number' => 'FF-AC-0001',
                'name' => 'Conference Room AC',
                'type' => 'air_conditioner',
                'brand' => 'CoolAir',
                'model' => 'CA-24000',
                'qr_code' => 'asset:fixflow-demo:ff-ac-0001',
                'purchase_date' => now()->subYear()->toDateString(),
                'warranty_start_date' => now()->subYear()->toDateString(),
                'warranty_end_date' => now()->addYear()->toDateString(),
                'notes' => 'Installed in the main conference room.',
                'status' => 'under_maintenance',
            ],
            [
                'serial_number' => 'FF-LT-0002',
                'name' => 'Accounting Laptop',
                'type' => 'computer',
                'brand' => 'NorthTech',
                'model' => 'NT-14P',
                'qr_code' => 'asset:fixflow-demo:ff-lt-0002',
                'purchase_date' => now()->subMonths(8)->toDateString(),
                'warranty_start_date' => now()->subMonths(8)->toDateString(),
                'warranty_end_date' => now()->addMonths(16)->toDateString(),
                'notes' => 'Used by the accounting desk.',
                'status' => 'active',
            ],
            [
                'serial_number' => 'FF-SL-0003',
                'name' => 'Roof Solar Inverter',
                'type' => 'solar',
                'brand' => 'SunGrid',
                'model' => 'SG-5K',
                'qr_code' => 'asset:fixflow-demo:ff-sl-0003',
                'purchase_date' => now()->subMonths(18)->toDateString(),
                'warranty_start_date' => now()->subMonths(18)->toDateString(),
                'warranty_end_date' => now()->addMonths(42)->toDateString(),
                'notes' => 'Connected to the east roof panels.',
                'status' => 'active',
            ],
        ])->map(fn (array $asset) => ServiceAsset::query()->updateOrCreate(
            [
                'company_id' => $company->id,
                'serial_number' => $asset['serial_number'],
            ],
            array_merge($asset, [
                'company_id' => $company->id,
                'customer_id' => $customer->id,
            ]),
        ));

        $parts = collect([
            ['name' => 'AC Capacitor 45uF', 'sku' => 'AC-CAP-45', 'unit_price' => 18.50, 'quantity' => 24, 'low_stock_threshold' => 5],
            ['name' => 'Thermal Paste Pack', 'sku' => 'IT-TP-001', 'unit_price' => 7.25, 'quantity' => 40, 'low_stock_threshold' => 8],
            ['name' => 'Solar DC Fuse', 'sku' => 'SL-FUSE-DC', 'unit_price' => 12.00, 'quantity' => 12, 'low_stock_threshold' => 4],
            ['name' => 'Control Relay', 'sku' => 'EL-RELAY-24', 'unit_price' => 22.75, 'quantity' => 2, 'low_stock_threshold' => 3],
        ])->map(fn (array $part) => Part::query()->updateOrCreate(
            [
                'company_id' => $company->id,
                'sku' => $part['sku'],
            ],
            array_merge($part, ['company_id' => $company->id]),
        ));

        $firstAsset = $assets->first();
        $firstPart = $parts->first();

        $request = ServiceRequest::query()->updateOrCreate(
            [
                'company_id' => $company->id,
                'title' => 'Conference room AC is not cooling',
            ],
            [
                'customer_id' => $customer->id,
                'service_asset_id' => $firstAsset->id,
                'created_by' => $dispatcher->id,
                'description' => 'Customer reported warm air and unusual compressor noise.',
                'priority' => 'high',
                'status' => 'in_progress',
                'preferred_date' => today()->toDateString(),
                'completed_at' => null,
            ],
        );
        $this->recordRequestAudit($request, $dispatcher, 'service_request_created', null, [
            'status' => 'new',
            'priority' => $request->priority,
        ]);
        $this->recordRequestAudit($request, $dispatcher, 'service_request_status_changed', [
            'status' => 'new',
        ], [
            'status' => 'in_progress',
        ]);

        $visit = ServiceVisit::query()->updateOrCreate(
            [
                'service_request_id' => $request->id,
                'technician_id' => $technician->id,
            ],
            [
                'company_id' => $company->id,
                'scheduled_at' => today()->setTime(10, 0),
                'started_at' => now()->subMinutes(30),
                'finished_at' => null,
                'visit_status' => 'in_progress',
                'location_address' => $customer->address,
                'technician_notes' => 'Bring capacitor tester and refrigerant gauges.',
            ],
        );
        $this->recordRequestAudit($request, $dispatcher, 'technician_assigned', null, [
            'technician_id' => $technician->id,
            'service_visit_id' => $visit->id,
        ]);

        PartUsed::query()->updateOrCreate(
            [
                'company_id' => $company->id,
                'service_request_id' => $request->id,
                'part_id' => $firstPart->id,
            ],
            [
                'quantity' => 1,
                'unit_price' => $firstPart->unit_price,
            ],
        );

        $completedRequest = ServiceRequest::query()->updateOrCreate(
            [
                'company_id' => $company->id,
                'title' => 'Accounting laptop overheating',
            ],
            [
                'customer_id' => $customer->id,
                'service_asset_id' => $assets->get(1)->id,
                'created_by' => $dispatcher->id,
                'description' => 'Laptop shuts down during accounting exports.',
                'priority' => 'medium',
                'status' => 'completed',
                'preferred_date' => now()->subDays(3)->toDateString(),
                'completed_at' => now()->subDay(),
            ],
        );
        $this->recordRequestAudit($completedRequest, $dispatcher, 'service_request_created', null, [
            'status' => 'new',
            'priority' => $completedRequest->priority,
        ]);
        $this->recordRequestAudit($completedRequest, $technician, 'service_request_closed', [
            'status' => 'in_progress',
        ], [
            'status' => 'completed',
            'completed_at' => $completedRequest->completed_at?->toISOString(),
        ]);

        ServiceVisit::query()->updateOrCreate(
            [
                'service_request_id' => $completedRequest->id,
                'technician_id' => $technician->id,
            ],
            [
                'company_id' => $company->id,
                'scheduled_at' => now()->subDays(3)->setTime(9, 30),
                'started_at' => now()->subDays(3)->setTime(9, 35),
                'finished_at' => now()->subDays(3)->setTime(10, 45),
                'visit_status' => 'completed',
                'location_address' => $customer->address,
                'technician_notes' => 'Cooling path cleaned and thermal readings returned to normal.',
            ],
        );

        collect([
            [
                'title' => 'Customer opened AC filter cleaning',
                'asset' => $assets->get(0),
                'created_by' => $customerUser,
                'priority' => 'low',
                'status' => 'new',
                'description' => 'Customer requested regular AC filter cleaning.',
                'completed_at' => null,
            ],
            [
                'title' => 'Solar inverter warning light',
                'asset' => $assets->get(2),
                'created_by' => $dispatcher,
                'priority' => 'urgent',
                'status' => 'under_review',
                'description' => 'The inverter warning light is blinking after peak production hours.',
                'completed_at' => null,
            ],
            [
                'title' => 'Laptop battery replacement schedule',
                'asset' => $assets->get(1),
                'created_by' => $dispatcher,
                'priority' => 'medium',
                'status' => 'scheduled',
                'description' => 'Battery health is below acceptable field threshold.',
                'completed_at' => null,
            ],
            [
                'title' => 'AC compressor waiting for parts',
                'asset' => $assets->get(0),
                'created_by' => $dispatcher,
                'priority' => 'high',
                'status' => 'waiting_parts',
                'description' => 'Compressor relay requires replacement and is waiting for inventory.',
                'preferred_date' => now()->subDays(2)->toDateString(),
                'completed_at' => null,
            ],
            [
                'title' => 'Cancelled elevator inspection',
                'asset' => null,
                'created_by' => $dispatcher,
                'priority' => 'medium',
                'status' => 'cancelled',
                'description' => 'Customer cancelled the inspection before scheduling.',
                'completed_at' => null,
            ],
            [
                'title' => 'Rejected unsupported appliance request',
                'asset' => null,
                'created_by' => $dispatcher,
                'priority' => 'low',
                'status' => 'rejected',
                'description' => 'The requested appliance is outside the company service scope.',
                'completed_at' => null,
            ],
        ])->each(function (array $requestData) use ($company, $customer, $technician): void {
            $demoRequest = ServiceRequest::query()->updateOrCreate(
                [
                    'company_id' => $company->id,
                    'title' => $requestData['title'],
                ],
                [
                    'customer_id' => $customer->id,
                    'service_asset_id' => $requestData['asset']?->id,
                    'created_by' => $requestData['created_by']->id,
                    'description' => $requestData['description'],
                    'priority' => $requestData['priority'],
                    'status' => $requestData['status'],
                    'preferred_date' => $requestData['preferred_date'] ?? now()->addDays(2)->toDateString(),
                    'completed_at' => $requestData['completed_at'],
                ],
            );
            $this->recordRequestAudit($demoRequest, $requestData['created_by'], 'service_request_created', null, [
                'status' => 'new',
                'priority' => $requestData['priority'],
            ]);

            if ($requestData['status'] !== 'new') {
                $this->recordRequestAudit($demoRequest, $requestData['created_by'], 'service_request_status_changed', [
                    'status' => 'new',
                ], [
                    'status' => $requestData['status'],
                ]);
            }

            if (in_array($requestData['status'], ['scheduled', 'waiting_parts'], true)) {
                $demoVisit = ServiceVisit::query()->updateOrCreate(
                    [
                        'service_request_id' => $demoRequest->id,
                        'technician_id' => $technician->id,
                    ],
                    [
                        'company_id' => $company->id,
                        'scheduled_at' => now()->addDays(2)->setTime(11, 0),
                        'started_at' => $requestData['status'] === 'waiting_parts' ? now()->subHours(2) : null,
                        'finished_at' => null,
                        'visit_status' => $requestData['status'] === 'waiting_parts' ? 'in_progress' : 'scheduled',
                        'location_address' => $customer->address,
                        'technician_notes' => 'Generated as part of the workflow demo dataset.',
                    ],
                );
                $this->recordRequestAudit($demoRequest, $requestData['created_by'], 'technician_assigned', null, [
                    'technician_id' => $technician->id,
                    'service_visit_id' => $demoVisit->id,
                ]);
            }
        });

        $serviceReport = ServiceReport::query()->updateOrCreate(
            ['service_request_id' => $completedRequest->id],
            [
                'company_id' => $company->id,
                'technician_id' => $technician->id,
                'diagnosis' => 'Dust buildup blocked the laptop cooling path.',
                'solution' => 'Cleaned the fan, renewed thermal paste, and verified normal temperatures.',
                'customer_signature' => 'signatures/demo-customer.png',
                'before_images' => ['reports/demo/laptop-before.jpg'],
                'after_images' => ['reports/demo/laptop-after.jpg'],
                'pdf_path' => 'reports/demo/service-report-0001.pdf',
            ],
        );
        $this->recordRequestAudit($completedRequest, $technician, 'service_report_added', null, [
            'service_report_id' => $serviceReport->id,
            'technician_id' => $technician->id,
            'pdf_path' => $serviceReport->pdf_path,
        ]);

        Invoice::query()->updateOrCreate(
            [
                'company_id' => $company->id,
                'invoice_number' => 'INV-DEMO-0001',
            ],
            [
                'service_request_id' => $completedRequest->id,
                'service_cost' => 95.00,
                'parts_total' => 0.00,
                'tax_rate' => 0.1500,
                'subtotal' => 95.00,
                'tax' => 14.25,
                'total' => 109.25,
                'status' => 'issued',
                'issued_at' => now()->subDay(),
                'paid_at' => null,
            ],
        );

        AuditLog::query()->updateOrCreate(
            [
                'action' => 'seeded',
                'model_type' => Company::class,
                'model_id' => $company->id,
            ],
            [
                'company_id' => $company->id,
                'user_id' => $superAdmin->id,
                'old_values' => null,
                'new_values' => ['slug' => $company->slug, 'status' => $company->status],
                'ip_address' => '127.0.0.1',
            ],
        );
    }

    /**
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     */
    private function recordRequestAudit(ServiceRequest $serviceRequest, User $actor, string $action, ?array $oldValues, ?array $newValues): void
    {
        AuditLog::query()->updateOrCreate(
            [
                'action' => $action,
                'model_type' => ServiceRequest::class,
                'model_id' => $serviceRequest->id,
                'user_id' => $actor->id,
            ],
            [
                'company_id' => $serviceRequest->company_id,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'ip_address' => '127.0.0.1',
            ],
        );
    }
}
