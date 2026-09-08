<?php

namespace App\Http\Controllers;

use App\Models\ServiceAsset;
use App\Models\ServiceRequest;
use App\Services\CustomerPortalService;
use App\Services\ServiceAssetQrCodeService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ServiceAssetQrController extends Controller
{
    public function __construct(
        private readonly CustomerPortalService $customerPortal,
        private readonly ServiceAssetQrCodeService $qrCodes,
    ) {}

    public function show(Request $request, string $qrCode): View
    {
        $serviceAsset = ServiceAsset::withoutGlobalScope('company')
            ->where('qr_code', $qrCode)
            ->firstOrFail();

        $canViewSensitiveData = $request->user()?->can('view', $serviceAsset) ?? false;

        return view('service-assets.qr.show', [
            'asset' => $serviceAsset,
            'warrantyLabel' => $this->customerPortal->warrantyLabel($serviceAsset),
            'qrSvg' => $this->qrCodes->svgFor($serviceAsset),
            'canViewSensitiveData' => $canViewSensitiveData,
            'recentRequests' => $canViewSensitiveData
                ? ServiceRequest::withoutGlobalScope('company')
                    ->where('service_asset_id', $serviceAsset->id)
                    ->with(['customer', 'visits.technician', 'report', 'invoice'])
                    ->latest()
                    ->limit(5)
                    ->get()
                : collect(),
        ]);
    }
}
