<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Part;
use App\Models\ServiceAsset;
use App\Models\ServiceRequest;
use App\Models\ServiceVisit;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DashboardMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_dashboard_is_scoped_to_the_admin_company(): void
    {
        $this->seed(RolePermissionSeeder::class);
        Cache::flush();

        $company = Company::factory()->create(['name' => 'Visible Company']);
        $otherCompany = Company::factory()->create(['name' => 'Hidden Company']);

        $admin = User::factory()->create(['company_id' => $company->id]);
        $admin->assignRole('company_admin');

        $technician = User::factory()->create([
            'company_id' => $company->id,
            'name' => 'Top Technician',
            'status' => 'active',
        ]);
        $technician->assignRole('technician');

        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'name' => 'Visible Customer',
        ]);
        $asset = ServiceAsset::factory()->create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'name' => 'Visible AC',
            'type' => 'air_conditioner',
        ]);

        ServiceRequest::factory()->create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'service_asset_id' => $asset->id,
            'created_by' => $admin->id,
            'title' => 'Visible Open Dashboard Request',
            'priority' => 'urgent',
            'status' => 'new',
            'preferred_date' => today(),
        ]);

        ServiceRequest::factory()->create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'service_asset_id' => $asset->id,
            'created_by' => $admin->id,
            'title' => 'Visible Overdue Dashboard Request',
            'priority' => 'high',
            'status' => 'waiting_parts',
            'preferred_date' => today()->subDay(),
        ]);

        $completedRequest = ServiceRequest::factory()->create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'service_asset_id' => $asset->id,
            'created_by' => $admin->id,
            'title' => 'Visible Completed Dashboard Request',
            'priority' => 'medium',
            'status' => 'completed',
            'preferred_date' => today()->subDays(3),
            'completed_at' => now(),
        ]);
        ServiceVisit::factory()->create([
            'company_id' => $company->id,
            'service_request_id' => $completedRequest->id,
            'technician_id' => $technician->id,
            'visit_status' => 'completed',
            'scheduled_at' => now()->subDay(),
            'started_at' => now()->subDay()->addMinutes(10),
            'finished_at' => now()->subDay()->addHour(),
        ]);

        Part::factory()->create([
            'company_id' => $company->id,
            'name' => 'Visible Low Stock Relay',
            'quantity' => 1,
            'low_stock_threshold' => 3,
        ]);

        $otherCustomer = Customer::factory()->create(['company_id' => $otherCompany->id]);
        $otherAsset = ServiceAsset::factory()->create([
            'company_id' => $otherCompany->id,
            'customer_id' => $otherCustomer->id,
            'name' => 'Hidden Elevator',
            'type' => 'elevator',
        ]);
        ServiceRequest::factory()->create([
            'company_id' => $otherCompany->id,
            'customer_id' => $otherCustomer->id,
            'service_asset_id' => $otherAsset->id,
            'created_by' => $admin->id,
            'title' => 'Hidden Cross Company Request',
            'status' => 'new',
        ]);
        Part::factory()->create([
            'company_id' => $otherCompany->id,
            'name' => 'Hidden Low Stock Part',
            'quantity' => 0,
            'low_stock_threshold' => 5,
        ]);

        $response = $this->actingAs($admin)->get('/company/dashboard');

        $response->assertOk();
        $response->assertSee('لوحة الشركة');
        $response->assertSee('Visible Open Dashboard Request');
        $response->assertSee('Visible Overdue Dashboard Request');
        $response->assertSee('Visible Completed Dashboard Request');
        $response->assertSee('air_conditioner');
        $response->assertSee('Top Technician');
        $response->assertSee('Visible Low Stock Relay');
        $response->assertDontSee('Hidden Cross Company Request');
        $response->assertDontSee('Hidden Low Stock Part');
        $response->assertDontSee('elevator');
    }

    public function test_super_admin_platform_dashboard_renders_global_metrics(): void
    {
        $this->seed(RolePermissionSeeder::class);
        Cache::flush();

        $superAdmin = User::factory()->create(['company_id' => null]);
        $superAdmin->assignRole('super_admin');

        Company::factory()->create([
            'name' => 'Active Tenant',
            'status' => 'active',
        ]);
        Company::factory()->create([
            'name' => 'Suspended Tenant',
            'status' => 'suspended',
        ]);

        $response = $this->actingAs($superAdmin)->get('/platform/dashboard');

        $response->assertOk();
        $response->assertSee('لوحة المنصة');
        $response->assertSee('عدد الشركات');
        $response->assertSee('الشركات النشطة');
        $response->assertSee('عدد المستخدمين');
        $response->assertSee('طلبات الصيانة في النظام');
        $response->assertSee('Active Tenant');
        $response->assertSee('Suspended Tenant');
    }
}
