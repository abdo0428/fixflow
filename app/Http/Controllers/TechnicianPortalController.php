<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompleteTechnicianVisitRequest;
use App\Http\Requests\UpdateTechnicianVisitNotesRequest;
use App\Models\AuditLog;
use App\Models\Part;
use App\Models\ServiceRequest;
use App\Models\ServiceVisit;
use App\Services\TechnicianVisitService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

class TechnicianPortalController extends Controller
{
    use AuthorizesRequests;

    private const VISIT_LABELS = [
        'scheduled' => 'مجدولة',
        'on_the_way' => 'في الطريق',
        'in_progress' => 'قيد التنفيذ',
        'completed' => 'مكتملة',
        'cancelled' => 'ملغاة',
    ];

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

    public function __construct(private readonly TechnicianVisitService $visits) {}

    public function dashboard(Request $request): View
    {
        $this->authorize('viewAny', ServiceVisit::class);

        $user = $request->user();

        $todayVisits = $this->assignedVisits($user->id)
            ->whereDate('scheduled_at', today())
            ->orderBy('scheduled_at')
            ->get();

        $upcomingVisits = $this->assignedVisits($user->id)
            ->whereDate('scheduled_at', '>', today())
            ->orderBy('scheduled_at')
            ->limit(6)
            ->get();

        return view('technician.dashboard', [
            'stats' => [
                'todayVisits' => $todayVisits->count(),
                'upcomingVisits' => $upcomingVisits->count(),
                'inProgressRequests' => $this->assignedRequests($user->id)->where('status', 'in_progress')->count(),
                'waitingPartsRequests' => $this->assignedRequests($user->id)->where('status', 'waiting_parts')->count(),
            ],
            'todayVisits' => $todayVisits,
            'upcomingVisits' => $upcomingVisits,
            'visitLabels' => self::VISIT_LABELS,
            'requestLabels' => self::REQUEST_LABELS,
            'priorityLabels' => self::PRIORITY_LABELS,
        ]);
    }

    public function visits(Request $request): View
    {
        $this->authorize('viewAny', ServiceVisit::class);

        $filters = $request->validate([
            'status' => ['nullable', Rule::in(ServiceVisit::VISIT_STATUSES)],
            'date' => ['nullable', 'date'],
        ]);

        $query = $this->assignedVisits($request->user()->id)
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('visit_status', $status))
            ->when($filters['date'] ?? null, fn (Builder $query, string $date) => $query->whereDate('scheduled_at', $date))
            ->orderBy('scheduled_at');

        return view('technician.visits.index', [
            'visits' => $query->paginate(10)->withQueryString(),
            'filters' => $filters,
            'visitStatuses' => ServiceVisit::VISIT_STATUSES,
            'visitLabels' => self::VISIT_LABELS,
            'requestLabels' => self::REQUEST_LABELS,
            'priorityLabels' => self::PRIORITY_LABELS,
        ]);
    }

    public function show(Request $request, ServiceVisit $serviceVisit): View
    {
        $this->authorize('view', $serviceVisit);

        $serviceVisit->load([
            'serviceRequest.customer',
            'serviceRequest.serviceAsset',
            'serviceRequest.partsUsed.part',
            'serviceRequest.report',
            'serviceRequest.invoice',
        ]);

        $serviceRequest = $serviceVisit->serviceRequest;

        return view('technician.visits.show', [
            'visit' => $serviceVisit,
            'serviceRequest' => $serviceRequest,
            'parts' => Part::query()->orderBy('name')->get(),
            'timeline' => AuditLog::with('user')
                ->where('model_type', ServiceRequest::class)
                ->where('model_id', $serviceRequest->id)
                ->oldest()
                ->get(),
            'canStart' => in_array($serviceVisit->visit_status, ['scheduled', 'on_the_way'], true),
            'canFinish' => in_array($serviceRequest->status, ['scheduled', 'in_progress', 'waiting_parts'], true),
            'visitLabels' => self::VISIT_LABELS,
            'requestLabels' => self::REQUEST_LABELS,
            'priorityLabels' => self::PRIORITY_LABELS,
        ]);
    }

    public function start(Request $request, ServiceVisit $serviceVisit): RedirectResponse
    {
        $this->visits->start($serviceVisit, $request->user(), $request->ip());

        return back()->with('status', 'تم بدء الزيارة بنجاح.');
    }

    public function updateNotes(UpdateTechnicianVisitNotesRequest $request, ServiceVisit $serviceVisit): RedirectResponse
    {
        $this->visits->updateNotes(
            $serviceVisit,
            $request->user(),
            $request->validated('technician_notes'),
            $request->ip(),
        );

        return back()->with('status', 'تم حفظ الملاحظات الفنية.');
    }

    public function finish(CompleteTechnicianVisitRequest $request, ServiceVisit $serviceVisit): RedirectResponse
    {
        $this->visits->finish(
            $serviceVisit,
            $request->user(),
            $request->validated(),
            $this->storeImages($request->file('before_images', []), 'service-reports/before'),
            $this->storeImages($request->file('after_images', []), 'service-reports/after'),
            $request->ip(),
        );

        return back()->with('status', 'تم إنهاء الزيارة وإغلاق الطلب.');
    }

    private function assignedVisits(int $technicianId): Builder
    {
        return ServiceVisit::query()
            ->with(['serviceRequest.customer', 'serviceRequest.serviceAsset'])
            ->where('technician_id', $technicianId);
    }

    private function assignedRequests(int $technicianId): Builder
    {
        return ServiceRequest::query()
            ->whereHas('visits', fn (Builder $query) => $query->where('technician_id', $technicianId));
    }

    /**
     * @param  array<int, UploadedFile>  $files
     * @return array<int, string>
     */
    private function storeImages(array $files, string $directory): array
    {
        return collect($files)
            ->filter()
            ->map(fn ($file): string => $file->store($directory, 'public'))
            ->values()
            ->all();
    }
}
