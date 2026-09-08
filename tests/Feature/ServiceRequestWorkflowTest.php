<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Customer;
use App\Models\ServiceAsset;
use App\Models\ServiceRequest;
use App\Models\ServiceVisit;
use App\Models\User;
use App\Services\ServiceRequestWorkflowService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ServiceRequestWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispatcher_can_move_new_request_to_under_review(): void
    {
        [$company, $dispatcher, $customer, $asset] = $this->tenantFixture();
        $serviceRequest = $this->serviceRequest($company, $dispatcher, $customer, $asset, 'new');

        app(ServiceRequestWorkflowService::class)->changeStatus($serviceRequest, 'under_review', $dispatcher);

        $this->assertSame('under_review', $serviceRequest->fresh()->status);
        $this->assertDatabaseHas('audit_logs', [
            'company_id' => $company->id,
            'action' => 'service_request_status_changed',
            'model_type' => ServiceRequest::class,
            'model_id' => $serviceRequest->id,
        ]);
    }

    public function test_invalid_status_transition_is_rejected(): void
    {
        [$company, $dispatcher, $customer, $asset] = $this->tenantFixture();
        $serviceRequest = $this->serviceRequest($company, $dispatcher, $customer, $asset, 'new');

        $this->expectException(ValidationException::class);

        app(ServiceRequestWorkflowService::class)->changeStatus($serviceRequest, 'completed', $dispatcher);
    }

    public function test_assigning_technician_creates_visit_notification_and_audit_log(): void
    {
        [$company, $dispatcher, $customer, $asset] = $this->tenantFixture();
        $technician = User::factory()->create(['company_id' => $company->id]);
        $technician->assignRole('technician');
        $serviceRequest = $this->serviceRequest($company, $dispatcher, $customer, $asset, 'under_review');

        app(ServiceRequestWorkflowService::class)->assignTechnician($serviceRequest, $technician, $dispatcher, [
            'scheduled_at' => now()->addDay(),
            'location_address' => $customer->address,
            'technician_notes' => 'Bring standard toolkit.',
        ]);

        $this->assertSame('scheduled', $serviceRequest->fresh()->status);
        $this->assertDatabaseHas('service_visits', [
            'company_id' => $company->id,
            'service_request_id' => $serviceRequest->id,
            'technician_id' => $technician->id,
            'visit_status' => 'scheduled',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'technician_assigned',
            'model_type' => ServiceRequest::class,
            'model_id' => $serviceRequest->id,
        ]);
        $this->assertSame(1, $technician->notifications()->count());
    }

    public function test_technician_can_complete_only_assigned_request(): void
    {
        [$company, $dispatcher, $customer, $asset] = $this->tenantFixture();
        $technician = User::factory()->create(['company_id' => $company->id]);
        $technician->assignRole('technician');
        $serviceRequest = $this->serviceRequest($company, $dispatcher, $customer, $asset, 'scheduled');

        $this->assertFalse($technician->can('changeStatus', $serviceRequest));

        ServiceVisit::factory()->create([
            'company_id' => $company->id,
            'service_request_id' => $serviceRequest->id,
            'technician_id' => $technician->id,
        ]);

        $workflow = app(ServiceRequestWorkflowService::class);
        $workflow->changeStatus($serviceRequest->fresh(), 'in_progress', $technician);
        $workflow->changeStatus($serviceRequest->fresh(), 'completed', $technician);

        $this->assertSame('completed', $serviceRequest->fresh()->status);
        $this->assertNotNull($serviceRequest->fresh()->completed_at);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'service_request_closed',
            'model_type' => ServiceRequest::class,
            'model_id' => $serviceRequest->id,
        ]);
    }

    public function test_customer_created_request_notifies_company_dispatchers(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $company = Company::factory()->create();
        $dispatcher = User::factory()->create(['company_id' => $company->id]);
        $dispatcher->assignRole('dispatcher');
        $customerUser = User::factory()->create(['company_id' => $company->id]);
        $customerUser->assignRole('customer');
        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'user_id' => $customerUser->id,
        ]);
        $asset = ServiceAsset::factory()->create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
        ]);

        $this->actingAs($customerUser)
            ->post('/service-requests', [
                'customer_id' => $customer->id,
                'service_asset_id' => $asset->id,
                'title' => 'Customer AC request',
                'description' => 'The AC is making a loud sound.',
                'priority' => 'medium',
                'preferred_date' => now()->addDay()->toDateString(),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('service_requests', [
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'status' => 'new',
            'title' => 'Customer AC request',
        ]);
        $this->assertSame(1, $dispatcher->notifications()->count());
    }

    public function test_completed_request_can_only_be_updated_by_company_admin(): void
    {
        [$company, $dispatcher, $customer, $asset] = $this->tenantFixture();
        $companyAdmin = User::factory()->create(['company_id' => $company->id]);
        $companyAdmin->assignRole('company_admin');
        $serviceRequest = $this->serviceRequest($company, $dispatcher, $customer, $asset, 'completed');

        $payload = [
            'customer_id' => $customer->id,
            'service_asset_id' => $asset->id,
            'title' => 'Updated completed request',
            'description' => 'Admin only edit.',
            'priority' => 'high',
            'preferred_date' => now()->addDay()->toDateString(),
        ];

        $this->actingAs($dispatcher)
            ->patch(route('service-requests.update', $serviceRequest), $payload)
            ->assertForbidden();

        $this->actingAs($companyAdmin)
            ->patch(route('service-requests.update', $serviceRequest), $payload)
            ->assertRedirect(route('service-requests.show', $serviceRequest, absolute: false));

        $this->assertSame('Updated completed request', $serviceRequest->fresh()->title);
    }

    /**
     * @return array{0: Company, 1: User, 2: Customer, 3: ServiceAsset}
     */
    private function tenantFixture(): array
    {
        $this->seed(RolePermissionSeeder::class);

        $company = Company::factory()->create();
        $dispatcher = User::factory()->create(['company_id' => $company->id]);
        $dispatcher->assignRole('dispatcher');
        $customer = Customer::factory()->create(['company_id' => $company->id]);
        $asset = ServiceAsset::factory()->create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
        ]);

        return [$company, $dispatcher, $customer, $asset];
    }

    private function serviceRequest(Company $company, User $creator, Customer $customer, ServiceAsset $asset, string $status): ServiceRequest
    {
        return ServiceRequest::factory()->create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'service_asset_id' => $asset->id,
            'created_by' => $creator->id,
            'status' => $status,
            'completed_at' => $status === 'completed' ? now() : null,
        ]);
    }
}
