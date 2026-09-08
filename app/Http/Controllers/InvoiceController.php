<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Models\Invoice;
use App\Models\ServiceRequest;
use App\Services\InvoiceService;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly InvoiceService $invoices) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Invoice::class);

        return view('invoices.index', [
            'invoices' => Invoice::query()
                ->with(['company', 'serviceRequest.customer'])
                ->latest()
                ->paginate(12),
        ]);
    }

    public function store(StoreInvoiceRequest $request, ServiceRequest $serviceRequest): RedirectResponse
    {
        $invoice = $this->invoices->createFromServiceRequest(
            $serviceRequest,
            $request->user(),
            $request->serviceCost(),
            $request->taxRateDecimal(),
            $request->invoiceStatus(),
        );

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('status', 'تم إنشاء الفاتورة بنجاح.');
    }

    public function show(Request $request, Invoice $invoice): View
    {
        $this->authorize('view', $invoice);

        $invoice->load([
            'company',
            'serviceRequest.customer',
            'serviceRequest.serviceAsset',
            'serviceRequest.partsUsed.part',
            'serviceRequest.report.technician',
            'serviceRequest.visits.technician',
        ]);

        return view('invoices.show', [
            'invoice' => $invoice,
            'serviceRequest' => $invoice->serviceRequest,
        ]);
    }

    public function markAsPaid(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->invoices->markAsPaid($invoice, $request->user());

        return back()->with('status', 'تم تعليم الفاتورة كمدفوعة.');
    }
}
