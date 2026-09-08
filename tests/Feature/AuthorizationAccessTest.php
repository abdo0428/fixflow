<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Customer;
use App\Models\ServiceAsset;
use App\Models\ServiceRequest;
use App\Models\ServiceVisit;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_technician_cannot_view_unassigned_service_request(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $company = Company::factory()->create();
        $dispatcher = User::factory()->create(['company_id' => $company->id]);
        $dispatcher->assignRole('dispatcher');
        $technician = User::factory()->create(['company_id' => $company->id]);
        $technician->assignRole('technician');
        $customer = Customer::factory()->create(['company_id' => $company->id]);
        $asset = ServiceAsset::factory()->create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
        ]);
        $request = ServiceRequest::factory()->create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'service_asset_id' => $asset->id,
            'created_by' => $dispatcher->id,
        ]);

        $this->actingAs($technician);

        $this->assertFalse($technician->can('view', $request));

        ServiceVisit::factory()->create([
            'company_id' => $company->id,
            'service_request_id' => $request->id,
            'technician_id' => $technician->id,
        ]);

        $this->assertTrue($technician->can('view', $request));
    }

    public function test_company_admin_cannot_view_another_company_customer_and_queries_are_scoped(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $ownCompany = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        $admin = User::factory()->create(['company_id' => $ownCompany->id]);
        $admin->assignRole('company_admin');

        $ownCustomer = Customer::factory()->create(['company_id' => $ownCompany->id]);
        $otherCustomer = Customer::factory()->create(['company_id' => $otherCompany->id]);

        $this->actingAs($admin);

        $this->assertTrue($admin->can('view', $ownCustomer));
        $this->assertFalse($admin->can('view', $otherCustomer));
        $this->assertSame(1, Customer::count());
    }

    public function test_company_admin_cannot_open_service_request_from_another_company(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $ownCompany = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        $admin = User::factory()->create(['company_id' => $ownCompany->id]);
        $admin->assignRole('company_admin');

        $otherCustomer = Customer::factory()->create(['company_id' => $otherCompany->id]);
        $otherAsset = ServiceAsset::factory()->create([
            'company_id' => $otherCompany->id,
            'customer_id' => $otherCustomer->id,
        ]);
        $otherRequest = ServiceRequest::factory()->create([
            'company_id' => $otherCompany->id,
            'customer_id' => $otherCustomer->id,
            'service_asset_id' => $otherAsset->id,
            'created_by' => $admin->id,
            'title' => 'Other company request',
        ]);

        $this->actingAs($admin);

        $this->assertFalse($admin->can('view', $otherRequest));

        $this
            ->get(route('service-requests.show', $otherRequest))
            ->assertNotFound();
    }

    public function test_customer_portal_only_lists_own_requests_and_assets(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $company = Company::factory()->create();
        $customerUser = User::factory()->create(['company_id' => $company->id]);
        $customerUser->assignRole('customer');
        $dispatcher = User::factory()->create(['company_id' => $company->id]);
        $dispatcher->assignRole('dispatcher');

        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'user_id' => $customerUser->id,
            'name' => 'Allowed Customer',
        ]);
        $otherCustomer = Customer::factory()->create([
            'company_id' => $company->id,
            'name' => 'Hidden Customer',
        ]);
        $asset = ServiceAsset::factory()->create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'name' => 'Allowed Asset',
        ]);
        $otherAsset = ServiceAsset::factory()->create([
            'company_id' => $company->id,
            'customer_id' => $otherCustomer->id,
            'name' => 'Hidden Asset',
        ]);

        ServiceRequest::factory()->create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'service_asset_id' => $asset->id,
            'created_by' => $customerUser->id,
            'title' => 'Allowed Request',
        ]);
        ServiceRequest::factory()->create([
            'company_id' => $company->id,
            'customer_id' => $otherCustomer->id,
            'service_asset_id' => $otherAsset->id,
            'created_by' => $dispatcher->id,
            'title' => 'Hidden Request',
        ]);

        $response = $this->actingAs($customerUser)->get('/customer/portal');

        $response->assertOk();
        $response->assertSee('Allowed Asset');
        $response->assertSee('Allowed Request');
        $response->assertDontSee('Hidden Asset');
        $response->assertDontSee('Hidden Request');
    }

    public function test_role_routes_reject_users_from_other_workflows(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $company = Company::factory()->create();
        $technician = User::factory()->create(['company_id' => $company->id]);
        $technician->assignRole('technician');

        $this->actingAs($technician)
            ->get('/dispatch/service-requests')
            ->assertForbidden();
    }

    public function test_login_redirects_users_to_their_role_dashboard(): void
    {
        $this->seed();

        $this->post('/login', [
            'email' => 'dispatcher@example.com',
            'password' => 'password',
        ])->assertRedirect('/dispatch/dashboard');

        $this->post('/logout');

        $this->post('/login', [
            'email' => 'technician@example.com',
            'password' => 'password',
        ])->assertRedirect('/technician/dashboard');

        $this->post('/logout');

        $this->post('/login', [
            'email' => 'customer@example.com',
            'password' => 'password',
        ])->assertRedirect('/customer/dashboard');
    }

    public function test_each_role_dashboard_can_be_rendered_for_its_user(): void
    {
        $this->seed();

        $routes = [
            'super@example.com' => '/platform/dashboard',
            'admin@example.com' => '/company/dashboard',
            'dispatcher@example.com' => '/dispatch/dashboard',
            'technician@example.com' => '/technician/dashboard',
            'customer@example.com' => '/customer/dashboard',
        ];

        foreach ($routes as $email => $path) {
            $this->actingAs(User::where('email', $email)->firstOrFail())
                ->get($path)
                ->assertOk();
        }
    }

    public function test_inactive_users_cannot_authenticate(): void
    {
        $user = User::factory()->create(['status' => 'inactive']);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
    }
}
