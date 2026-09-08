<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignTechnicianRequest;
use App\Http\Requests\ChangeServiceRequestStatusRequest;
use App\Http\Requests\StoreServiceReportRequest;
use App\Http\Requests\StoreServiceRequestRequest;
use App\Http\Requests\UpdateServiceRequestRequest;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Customer;
use App\Models\ServiceAsset;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\ServiceRequestWorkflowService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ServiceRequestController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly ServiceRequestWorkflowService $workflow) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', ServiceRequest::class);

        $query = $this->visibleServiceRequests($request->user())
            ->with(['company', 'customer', 'serviceAsset', 'creator', 'visits.technician'])
            ->latest();

        if (in_array($request->query('status'), ServiceRequest::STATUSES, true)) {
            $query->where('status', $request->query('status'));
        }

        if (in_array($request->query('priority'), ServiceRequest::PRIORITIES, true)) {
            $query->where('priority', $request->query('priority'));
        }

        if ($request->filled('technician_id') && $this->canFilterByTechnician($request->user())) {
            $query->whereHas('visits', fn (Builder $query) => $query->where('technician_id', $request->integer('technician_id')));
        }

        return view('service-requests.index', [
            'serviceRequests' => $query->paginate(10)->withQueryString(),
            'statuses' => ServiceRequest::STATUSES,
            'priorities' => ServiceRequest::PRIORITIES,
            'technicians' => $this->technicianOptions($request->user()),
            'filters' => [
                'status' => $request->query('status'),
                'priority' => $request->query('priority'),
                'technician_id' => $request->query('technician_id'),
            ],
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', ServiceRequest::class);

        return view('service-requests.create', $this->formOptions($request->user()));
    }

    public function store(StoreServiceRequestRequest $request): RedirectResponse
    {
        $serviceRequest = ServiceRequest::create([
            'company_id' => $request->tenantCompanyId(),
            'customer_id' => $request->customerId(),
            'service_asset_id' => $request->serviceAssetId(),
            'created_by' => $request->user()->id,
            'title' => $request->validated('title'),
            'description' => $request->validated('description'),
            'priority' => $request->validated('priority'),
            'status' => 'new',
            'preferred_date' => $request->validated('preferred_date'),
            'completed_at' => null,
        ]);

        $this->workflow->recordCreated($serviceRequest, $request->user(), $request->ip());

        return redirect()
            ->route('service-requests.show', $serviceRequest)
            ->with('status', 'Service request created successfully.');
    }

    public function show(Request $request, ServiceRequest $serviceRequest): View
    {
        $this->authorize('view', $serviceRequest);

        $serviceRequest->load([
            'company',
            'customer.user',
            'serviceAsset',
            'creator',
            'visits.technician',
            'partsUsed.part',
            'report.technician',
            'invoice',
        ]);

        $partsTotal = $serviceRequest->partsUsed->sum(
            fn ($partUsed): float => $partUsed->quantity * (float) $partUsed->unit_price,
        );

        return view('service-requests.show', [
            'serviceRequest' => $serviceRequest,
            'availableStatuses' => $this->workflow->availableTransitions($serviceRequest, $request->user()),
            'technicians' => $this->technicianOptions($request->user()),
            'partsTotal' => $partsTotal,
            'reportPdfUrl' => $serviceRequest->report?->pdf_path
                ? Storage::disk('public')->url($serviceRequest->report->pdf_path)
                : null,
            'assetQrUrl' => $serviceRequest->serviceAsset?->qr_code
                ? route('assets.qr.show', ['qrCode' => $serviceRequest->serviceAsset->qr_code])
                : null,
            'timeline' => AuditLog::with('user')
                ->where('model_type', ServiceRequest::class)
                ->where('model_id', $serviceRequest->id)
                ->oldest()
                ->get(),
        ]);
    }

    public function edit(Request $request, ServiceRequest $serviceRequest): View
    {
        $this->authorize('update', $serviceRequest);

        return view('service-requests.edit', [
            'serviceRequest' => $serviceRequest->load(['customer', 'serviceAsset']),
            ...$this->formOptions($request->user(), $serviceRequest),
        ]);
    }

    public function update(UpdateServiceRequestRequest $request, ServiceRequest $serviceRequest): RedirectResponse
    {
        $serviceRequest->update([
            'customer_id' => $request->integer('customer_id'),
            'service_asset_id' => $request->serviceAssetId(),
            'title' => $request->validated('title'),
            'description' => $request->validated('description'),
            'priority' => $request->validated('priority'),
            'preferred_date' => $request->validated('preferred_date'),
        ]);

        return redirect()
            ->route('service-requests.show', $serviceRequest)
            ->with('status', 'Service request updated successfully.');
    }

    public function destroy(Request $request, ServiceRequest $serviceRequest): RedirectResponse
    {
        $this->authorize('delete', $serviceRequest);

        $serviceRequest->delete();

        return redirect()
            ->route('service-requests.index')
            ->with('status', 'Service request deleted successfully.');
    }

    public function changeStatus(ChangeServiceRequestStatusRequest $request, ServiceRequest $serviceRequest): RedirectResponse
    {
        $this->workflow->changeStatus(
            $serviceRequest,
            $request->validated('status'),
            $request->user(),
            $request->validated('notes'),
            $request->ip(),
        );

        return back()->with('status', 'Service request status changed successfully.');
    }

    public function assignTechnician(AssignTechnicianRequest $request, ServiceRequest $serviceRequest): RedirectResponse
    {
        $this->workflow->assignTechnician(
            $serviceRequest,
            User::findOrFail($request->integer('technician_id')),
            $request->user(),
            $request->validated(),
            $request->ip(),
        );

        return back()->with('status', 'Technician assigned successfully.');
    }

    public function storeReport(StoreServiceReportRequest $request, ServiceRequest $serviceRequest): RedirectResponse
    {
        $technicianId = $request->technicianId()
            ?: $serviceRequest->visits()->latest()->value('technician_id');

        $report = $serviceRequest->report()->create([
            'company_id' => $serviceRequest->company_id,
            'technician_id' => $technicianId,
            'diagnosis' => $request->validated('diagnosis'),
            'solution' => $request->validated('solution'),
            'customer_signature' => $request->validated('customer_signature'),
            'before_images' => $request->validated('before_images') ?? [],
            'after_images' => $request->validated('after_images') ?? [],
            'pdf_path' => null,
        ]);

        $this->workflow->recordReportAdded($report, $request->user(), $request->ip());

        return back()->with('status', 'Service report added successfully.');
    }

    private function visibleServiceRequests(User $user): Builder
    {
        $query = ServiceRequest::query();

        if ($user->hasRole('super_admin')) {
            return $query->withoutGlobalScope('company');
        }

        if ($user->hasRole('technician')) {
            return $query->whereHas('visits', fn (Builder $query) => $query->where('technician_id', $user->id));
        }

        if ($user->hasRole('customer')) {
            $customerId = $user->customerProfile?->id;

            return $customerId
                ? $query->where('customer_id', $customerId)
                : $query->whereRaw('1 = 0');
        }

        return $query;
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(User $user, ?ServiceRequest $serviceRequest = null): array
    {
        $companies = $user->hasRole('super_admin')
            ? Company::orderBy('name')->get()
            : collect();

        if ($user->hasRole('customer')) {
            $customer = $user->customerProfile;

            return [
                'companies' => $companies,
                'customers' => $customer ? collect([$customer]) : collect(),
                'assets' => $customer
                    ? ServiceAsset::where('customer_id', $customer->id)->orderBy('name')->get()
                    : collect(),
                'priorities' => ServiceRequest::PRIORITIES,
            ];
        }

        return [
            'companies' => $companies,
            'customers' => $user->hasRole('super_admin')
                ? Customer::withoutGlobalScope('company')->with('company')->orderBy('name')->get()
                : Customer::with('company')->orderBy('name')->get(),
            'assets' => $user->hasRole('super_admin')
                ? ServiceAsset::withoutGlobalScope('company')->with('customer')->orderBy('name')->get()
                : ServiceAsset::with('customer')->orderBy('name')->get(),
            'priorities' => ServiceRequest::PRIORITIES,
        ];
    }

    private function technicianOptions(User $user)
    {
        if ($user->hasRole('super_admin')) {
            return User::role('technician')->where('status', 'active')->orderBy('name')->get();
        }

        if (! $user->company_id) {
            return collect();
        }

        return User::role('technician')
            ->where('company_id', $user->company_id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    private function canFilterByTechnician(User $user): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasPermissionTo('assign technicians')
            || $user->hasPermissionTo('manage service requests');
    }
}
