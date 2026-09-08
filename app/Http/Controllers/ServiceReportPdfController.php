<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use App\Services\ServiceReportPdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ServiceReportPdfController extends Controller
{
    public function __construct(private readonly ServiceReportPdfService $reports) {}

    public function store(Request $request, ServiceRequest $serviceRequest): RedirectResponse
    {
        $this->reports->generate($serviceRequest, $request->user());

        return back()->with('status', 'تم توليد تقرير PDF بنجاح.');
    }
}
