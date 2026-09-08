<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\ServiceRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_tenant_data_keeps_core_relationships_connected(): void
    {
        $this->seed();

        $company = Company::where('slug', 'fixflow-demo-maintenance')->firstOrFail();
        $request = ServiceRequest::where('title', 'Conference room AC is not cooling')->firstOrFail();
        $invoice = Invoice::where('invoice_number', 'INV-DEMO-0001')->firstOrFail();

        $this->assertSame($company->id, $request->company->id);
        $this->assertSame($company->id, $request->customer->company_id);
        $this->assertSame($company->id, $request->serviceAsset->company_id);
        $this->assertSame('dispatcher', $request->creator->roles()->first()->name);
        $this->assertTrue($request->visits()->exists());
        $this->assertTrue($request->partsUsed()->exists());
        $this->assertSame($company->id, $invoice->serviceRequest->company_id);
        $this->assertTrue($company->users()->where('email', 'technician@example.com')->exists());
    }
}
