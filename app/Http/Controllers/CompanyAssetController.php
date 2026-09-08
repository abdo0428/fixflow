<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompanyAssetRequest;
use App\Models\Customer;
use App\Models\ServiceAsset;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CompanyAssetController extends Controller
{
    use AuthorizesRequests;

    public function create(Request $request): View
    {
        $this->authorize('create', ServiceAsset::class);

        return view('company.assets.create', [
            'customers' => Customer::query()->orderBy('name')->get(),
            'selectedCustomerId' => $request->query('customer_id'),
            'statuses' => ['active', 'inactive', 'under_maintenance', 'retired'],
        ]);
    }

    public function store(StoreCompanyAssetRequest $request): RedirectResponse
    {
        $asset = ServiceAsset::create([
            'company_id' => $request->user()->company_id,
            'customer_id' => $request->integer('customer_id'),
            'name' => $request->validated('name'),
            'type' => $request->validated('type'),
            'brand' => $request->validated('brand'),
            'model' => $request->validated('model'),
            'serial_number' => $request->validated('serial_number'),
            'purchase_date' => $request->validated('purchase_date'),
            'warranty_start_date' => $request->validated('warranty_start_date'),
            'warranty_end_date' => $request->validated('warranty_end_date'),
            'notes' => $request->validated('notes'),
            'status' => $request->validated('status'),
        ]);

        return redirect()
            ->route('assets.qr.show', ['qrCode' => $asset->qr_code])
            ->with('status', 'تم إضافة الجهاز وتوليد QR Code بنجاح.');
    }
}
