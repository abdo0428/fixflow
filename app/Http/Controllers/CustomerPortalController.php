<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerServiceRequestRequest;
use App\Models\Customer;
use App\Models\ServiceAsset;
use App\Models\ServiceRequest;
use App\Services\CustomerPortalService;
use App\Services\ServiceRequestWorkflowService;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CustomerPortalController extends Controller
{
    use AuthorizesRequests;

    private const REQUEST_LABELS = [
        'new' => 'جديد',
        'under_review' => 'قيد المراجعة',
        'scheduled' => 'مجدول',
        'in_progress' => 'قيد التنفيذ',
        'waiting_parts' => 'بانتظار قطع',
        'completed' => 'مكتمل',
        'cancelled' => 'ملغى',
        'rejected' => 'مرفوض',
    ];

    private const PRIORITY_LABELS = [
        'low' => 'منخفضة',
        'medium' => 'متوسطة',
        'high' => 'عالية',
        'urgent' => 'عاجلة',
    ];

    public function __construct(
        private readonly CustomerPortalService $portal,
        private readonly ServiceRequestWorkflowService $workflow,
    ) {}

    public function dashboard(Request $request): View
    {
        $this->authorize('viewAny', Customer::class);

        $customer = $this->customerFor($request);

        $assetSummaries = $this->portal->assetSummaries($customer);
        $latestRequests = $this->portal->requestsFor($customer)
            ->with(['serviceAsset', 'report', 'invoice'])
            ->latest()
            ->limit(6)
            ->get();

        return view('customer.dashboard', [
            'customer' => $customer,
            'stats' => [
                'assets' => $assetSummaries->count(),
                'openRequests' => $this->portal->requestsFor($customer)
                    ->whereIn('status', CustomerPortalService::OPEN_STATUSES)
                    ->count(),
                'allRequests' => $this->portal->requestsFor($customer)->count(),
            ],
            'assets' => $assetSummaries->take(6),
            'latestRequests' => $latestRequests,
            'statusLabels' => self::REQUEST_LABELS,
            'priorityLabels' => self::PRIORITY_LABELS,
        ]);
    }

    public function assets(Request $request): View
    {
        $this->authorize('viewAny', Customer::class);

        $customer = $this->customerFor($request);

        return view('customer.assets.index', [
            'customer' => $customer,
            'assets' => $this->portal->assetSummaries($customer),
        ]);
    }

    public function createRequest(Request $request): View
    {
        $this->authorize('create', ServiceRequest::class);

        $customer = $this->customerFor($request);

        return view('customer.requests.create', [
            'customer' => $customer,
            'assets' => ServiceAsset::query()
                ->where('customer_id', $customer->id)
                ->orderBy('name')
                ->get(),
            'priorities' => ServiceRequest::PRIORITIES,
            'priorityLabels' => self::PRIORITY_LABELS,
        ]);
    }

    public function storeRequest(StoreCustomerServiceRequestRequest $request): RedirectResponse
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
            ->route('customer.service-requests.index')
            ->with('status', 'تم إرسال طلب الصيانة بنجاح.');
    }

    public function requests(Request $request): View
    {
        $this->authorize('viewAny', ServiceRequest::class);

        $customer = $this->customerFor($request);

        $serviceRequests = $this->portal->requestsFor($customer)
            ->with(['serviceAsset', 'partsUsed.part', 'report.technician', 'invoice'])
            ->latest()
            ->paginate(8);

        return view('customer.requests.index', [
            'customer' => $customer,
            'requests' => $serviceRequests,
            'timelines' => $this->portal->timelinesFor($serviceRequests->getCollection()->pluck('id')),
            'statusLabels' => self::REQUEST_LABELS,
            'priorityLabels' => self::PRIORITY_LABELS,
        ]);
    }

    private function customerFor(Request $request): Customer
    {
        $customer = $this->portal->customerFor($request->user());

        abort_unless($customer, 403);

        $this->authorize('view', $customer);

        return $customer;
    }
}
