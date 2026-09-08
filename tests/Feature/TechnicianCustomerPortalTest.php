<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Part;
use App\Models\PartUsed;
use App\Models\ServiceAsset;
use App\Models\ServiceReport;
use App\Models\ServiceRequest;
use App\Models\ServiceVisit;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TechnicianCustomerPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_technician_portal_only_lists_assigned_visits(): void
    {
        [$company, $dispatcher, $technician, $customer, $asset] = $this->fixture();
        $otherTechnician = User::factory()->create(['company_id' => $company->id]);
        $otherTechnician->assignRole('technician');

        $assignedRequest = $this->requestFor($company, $dispatcher, $customer, $asset, 'scheduled', 'Assigned AC Visit');
        $hiddenRequest = $this->requestFor($company, $dispatcher, $customer, $asset, 'scheduled', 'Hidden AC Visit');

        $assignedVisit = ServiceVisit::factory()->create([
            'company_id' => $company->id,
            'service_request_id' => $assignedRequest->id,
            'technician_id' => $technician->id,
            'visit_status' => 'scheduled',
            'scheduled_at' => today()->setTime(10, 0),
        ]);
        $hiddenVisit = ServiceVisit::factory()->create([
            'company_id' => $company->id,
            'service_request_id' => $hiddenRequest->id,
            'technician_id' => $otherTechnician->id,
            'visit_status' => 'scheduled',
            'scheduled_at' => today()->setTime(11, 0),
        ]);

        $this->actingAs($technician)
            ->get('/technician/dashboard')
            ->assertOk()
            ->assertSee('زيارات اليوم')
            ->assertSee('Assigned AC Visit')
            ->assertDontSee('Hidden AC Visit');

        $this->actingAs($technician)
            ->get('/technician/visits')
            ->assertOk()
            ->assertSee('Assigned AC Visit')
            ->assertDontSee('Hidden AC Visit');

        $this->actingAs($technician)
            ->get(route('technician.visits.show', $assignedVisit))
            ->assertOk();

        $this->actingAs($technician)
            ->get(route('technician.visits.show', $hiddenVisit))
            ->assertForbidden();
    }

    public function test_technician_can_finish_assigned_visit_with_report_and_parts(): void
    {
        [$company, $dispatcher, $technician, $customer, $asset] = $this->fixture(withCustomerUser: true);
        $part = Part::factory()->create([
            'company_id' => $company->id,
            'name' => 'AC Capacitor 45uF',
            'unit_price' => 18.50,
        ]);
        $serviceRequest = $this->requestFor($company, $dispatcher, $customer, $asset, 'scheduled', 'Finishable AC Visit');
        $visit = ServiceVisit::factory()->create([
            'company_id' => $company->id,
            'service_request_id' => $serviceRequest->id,
            'technician_id' => $technician->id,
            'visit_status' => 'scheduled',
            'scheduled_at' => today()->setTime(12, 0),
        ]);

        $this->actingAs($technician)
            ->post(route('technician.visits.start', $visit))
            ->assertRedirect();

        $this->assertSame('in_progress', $serviceRequest->fresh()->status);
        $this->assertSame('in_progress', $visit->fresh()->visit_status);

        $this->actingAs($technician)
            ->post(route('technician.visits.finish', $visit), [
                'diagnosis' => 'Weak capacitor caused compressor startup failure.',
                'solution' => 'Replaced capacitor and tested cooling cycle.',
                'technician_notes' => 'System is stable after replacement.',
                'parts' => [
                    [
                        'part_id' => $part->id,
                        'quantity' => 1,
                        'unit_price' => 18.50,
                    ],
                ],
            ])
            ->assertRedirect();

        $this->assertSame('completed', $serviceRequest->fresh()->status);
        $this->assertSame('completed', $visit->fresh()->visit_status);
        $this->assertNotNull($serviceRequest->fresh()->completed_at);
        $this->assertSame(1, ServiceReport::where('service_request_id', $serviceRequest->id)->count());
        $this->assertSame(1, PartUsed::where('service_request_id', $serviceRequest->id)->count());
        $this->assertTrue(AuditLog::where('model_id', $serviceRequest->id)->where('action', 'service_visit_completed')->exists());
        $this->assertSame(2, $customer->user->notifications()->count());
    }

    public function test_customer_portal_lists_own_assets_and_requests_only(): void
    {
        [$company, $dispatcher, $technician, $customer, $asset] = $this->fixture(withCustomerUser: true);
        $otherCustomer = Customer::factory()->create(['company_id' => $company->id, 'name' => 'Hidden Customer']);
        $otherAsset = ServiceAsset::factory()->create([
            'company_id' => $company->id,
            'customer_id' => $otherCustomer->id,
            'name' => 'Hidden Asset',
        ]);

        $ownRequest = $this->requestFor($company, $customer->user, $customer, $asset, 'new', 'Allowed Customer Request');
        $this->requestFor($company, $dispatcher, $otherCustomer, $otherAsset, 'new', 'Hidden Customer Request');

        $this->actingAs($customer->user)
            ->get('/customer/assets')
            ->assertOk()
            ->assertSee($asset->name)
            ->assertDontSee('Hidden Asset');

        $this->actingAs($customer->user)
            ->get('/customer/service-requests')
            ->assertOk()
            ->assertSee($ownRequest->title)
            ->assertDontSee('Hidden Customer Request');
    }

    public function test_customer_can_create_request_for_own_asset_only(): void
    {
        [$company, $dispatcher, $technician, $customer, $asset] = $this->fixture(withCustomerUser: true);
        $otherCustomer = Customer::factory()->create(['company_id' => $company->id]);
        $otherAsset = ServiceAsset::factory()->create([
            'company_id' => $company->id,
            'customer_id' => $otherCustomer->id,
        ]);

        $this->actingAs($customer->user)
            ->post('/customer/service-requests', [
                'customer_id' => $customer->id,
                'service_asset_id' => $asset->id,
                'title' => 'Customer portal AC request',
                'description' => 'Cooling drops during the afternoon.',
                'priority' => 'medium',
                'preferred_date' => today()->addDay()->toDateString(),
            ])
            ->assertRedirect(route('customer.service-requests.index', absolute: false));

        $this->assertDatabaseHas('service_requests', [
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'service_asset_id' => $asset->id,
            'title' => 'Customer portal AC request',
            'status' => 'new',
        ]);
        $this->assertSame(1, $dispatcher->notifications()->count());

        $this->actingAs($customer->user)
            ->from('/customer/service-requests/create')
            ->post('/customer/service-requests', [
                'customer_id' => $customer->id,
                'service_asset_id' => $otherAsset->id,
                'title' => 'Invalid customer asset request',
                'description' => 'This should not be accepted.',
                'priority' => 'medium',
            ])
            ->assertRedirect('/customer/service-requests/create')
            ->assertSessionHasErrors('service_asset_id');
    }

    /**
     * @return array{0: Company, 1: User, 2: User, 3: Customer, 4: ServiceAsset}
     */
    private function fixture(bool $withCustomerUser = false): array
    {
        $this->seed(RolePermissionSeeder::class);

        $company = Company::factory()->create();
        $dispatcher = User::factory()->create(['company_id' => $company->id]);
        $dispatcher->assignRole('dispatcher');
        $technician = User::factory()->create(['company_id' => $company->id]);
        $technician->assignRole('technician');
        $customerUser = $withCustomerUser ? User::factory()->create(['company_id' => $company->id]) : null;
        $customerUser?->assignRole('customer');

        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'user_id' => $customerUser?->id,
            'name' => 'Allowed Customer',
        ]);
        $asset = ServiceAsset::factory()->create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'name' => 'Allowed Asset',
            'warranty_start_date' => today()->subMonth(),
            'warranty_end_date' => today()->addYear(),
        ]);

        return [$company, $dispatcher, $technician, $customer, $asset];
    }

    private function requestFor(Company $company, User $creator, Customer $customer, ServiceAsset $asset, string $status, string $title): ServiceRequest
    {
        return ServiceRequest::factory()->create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'service_asset_id' => $asset->id,
            'created_by' => $creator->id,
            'status' => $status,
            'title' => $title,
            'completed_at' => $status === 'completed' ? now() : null,
        ]);
    }
}
