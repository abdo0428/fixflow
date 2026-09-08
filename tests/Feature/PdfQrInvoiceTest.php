<?php

namespace Tests\Feature;

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
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PdfQrInvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_asset_qr_page_shows_limited_public_data_and_authorized_history(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $company = Company::factory()->create();
        $customerUser = User::factory()->create(['company_id' => $company->id]);
        $customerUser->assignRole('customer');
        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'user_id' => $customerUser->id,
        ]);
        $asset = ServiceAsset::factory()->create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'name' => 'QR Test AC',
            'serial_number' => 'QR-SN-1001',
            'qr_code' => null,
        ]);
        $serviceRequest = ServiceRequest::factory()->create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'service_asset_id' => $asset->id,
            'created_by' => $customerUser->id,
            'title' => 'Private AC repair history',
        ]);

        $this->assertNotEmpty($asset->fresh()->qr_code);

        $this->get(route('assets.qr.show', ['qrCode' => $asset->qr_code]))
            ->assertOk()
            ->assertSee('QR Test AC')
            ->assertSee('QR-SN-1001')
            ->assertDontSee($serviceRequest->title);

        $this->actingAs($customerUser)
            ->get(route('assets.qr.show', ['qrCode' => $asset->qr_code]))
            ->assertOk()
            ->assertSee($serviceRequest->title);
    }

    public function test_completed_service_report_pdf_can_be_generated(): void
    {
        Storage::fake('public');

        [$company, $admin, $technician, $customer, $asset, $serviceRequest] = $this->completedRequestFixture();

        ServiceVisit::factory()->create([
            'company_id' => $company->id,
            'service_request_id' => $serviceRequest->id,
            'technician_id' => $technician->id,
            'visit_status' => 'completed',
            'scheduled_at' => now()->subDay(),
        ]);
        ServiceReport::factory()->create([
            'company_id' => $company->id,
            'service_request_id' => $serviceRequest->id,
            'technician_id' => $technician->id,
            'diagnosis' => 'Compressor capacitor failed.',
            'solution' => 'Replaced capacitor and tested cooling.',
        ]);

        $this->actingAs($admin)
            ->post(route('service-requests.report.pdf', $serviceRequest))
            ->assertRedirect();

        $report = $serviceRequest->fresh()->report;

        $this->assertNotNull($report->pdf_path);
        Storage::disk('public')->assertExists($report->pdf_path);
        $this->assertDatabaseHas('audit_logs', [
            'company_id' => $company->id,
            'action' => 'service_report_pdf_generated',
            'model_type' => ServiceRequest::class,
            'model_id' => $serviceRequest->id,
        ]);
    }

    public function test_invoice_can_be_created_from_request_and_marked_as_paid(): void
    {
        [$company, $admin, $technician, $customer, $asset, $serviceRequest] = $this->completedRequestFixture();

        $part = Part::factory()->create(['company_id' => $company->id, 'unit_price' => 10]);
        PartUsed::factory()->create([
            'company_id' => $company->id,
            'service_request_id' => $serviceRequest->id,
            'part_id' => $part->id,
            'quantity' => 2,
            'unit_price' => 10,
        ]);

        $this->actingAs($admin)
            ->post(route('service-requests.invoice.store', $serviceRequest), [
                'service_cost' => 100,
                'tax_rate' => 15,
                'status' => 'issued',
            ])
            ->assertRedirect();

        $invoice = Invoice::firstOrFail();

        $this->assertSame('100.00', $invoice->service_cost);
        $this->assertSame('20.00', $invoice->parts_total);
        $this->assertSame('120.00', $invoice->subtotal);
        $this->assertSame('18.00', $invoice->tax);
        $this->assertSame('138.00', $invoice->total);
        $this->assertSame('issued', $invoice->status);

        $this->actingAs($admin)
            ->post(route('invoices.mark-paid', $invoice))
            ->assertRedirect();

        $this->assertSame('paid', $invoice->fresh()->status);
        $this->assertNotNull($invoice->fresh()->paid_at);
    }

    public function test_invoice_cannot_be_created_from_incomplete_request(): void
    {
        [$company, $admin, $technician, $customer, $asset, $serviceRequest] = $this->completedRequestFixture();

        $serviceRequest->forceFill([
            'status' => 'in_progress',
            'completed_at' => null,
        ])->save();

        $this->actingAs($admin)
            ->post(route('service-requests.invoice.store', $serviceRequest), [
                'service_cost' => 100,
                'tax_rate' => 15,
                'status' => 'issued',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('invoices', [
            'company_id' => $company->id,
            'service_request_id' => $serviceRequest->id,
        ]);
    }

    /**
     * @return array{0: Company, 1: User, 2: User, 3: Customer, 4: ServiceAsset, 5: ServiceRequest}
     */
    private function completedRequestFixture(): array
    {
        $this->seed(RolePermissionSeeder::class);

        $company = Company::factory()->create();
        $admin = User::factory()->create(['company_id' => $company->id]);
        $admin->assignRole('company_admin');
        $technician = User::factory()->create(['company_id' => $company->id]);
        $technician->assignRole('technician');
        $customer = Customer::factory()->create(['company_id' => $company->id]);
        $asset = ServiceAsset::factory()->create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
        ]);
        $serviceRequest = ServiceRequest::factory()->create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'service_asset_id' => $asset->id,
            'created_by' => $admin->id,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return [$company, $admin, $technician, $customer, $asset, $serviceRequest];
    }
}
