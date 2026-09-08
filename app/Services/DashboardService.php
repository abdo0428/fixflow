<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Part;
use App\Models\ServiceAsset;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public const CACHE_TTL_SECONDS = 300;

    public const OPEN_STATUSES = [
        'new',
        'under_review',
        'scheduled',
        'in_progress',
        'waiting_parts',
    ];

    /**
     * @return array<string, mixed>
     */
    public function companyDashboard(Company $company, User $user): array
    {
        $data = Cache::remember(
            "dashboard:company:{$company->id}",
            self::CACHE_TTL_SECONDS,
            fn (): array => $this->buildCompanyDashboard($company),
        );

        return [
            ...$data,
            'quickLinks' => $this->quickLinksFor($user),
            'cachedAt' => now(),
            'cacheMinutes' => intdiv(self::CACHE_TTL_SECONDS, 60),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function platformDashboard(): array
    {
        return Cache::remember(
            'dashboard:platform',
            self::CACHE_TTL_SECONDS,
            fn (): array => [
                'stats' => [
                    'companies' => Company::count(),
                    'activeCompanies' => Company::where('status', 'active')->count(),
                    'users' => User::count(),
                    'serviceRequests' => ServiceRequest::withoutGlobalScope('company')->count(),
                    'openRequests' => ServiceRequest::withoutGlobalScope('company')->whereIn('status', self::OPEN_STATUSES)->count(),
                ],
                'companies' => Company::latest()->limit(6)->get(),
                'requestsByStatus' => $this->fillCounts(
                    ServiceRequest::withoutGlobalScope('company')
                        ->selectRaw('status, count(*) as aggregate')
                        ->groupBy('status')
                        ->pluck('aggregate', 'status'),
                    ServiceRequest::STATUSES,
                ),
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function buildCompanyDashboard(Company $company): array
    {
        $requests = ServiceRequest::withoutGlobalScope('company')
            ->where('company_id', $company->id);

        $statusCounts = $this->fillCounts(
            ServiceRequest::withoutGlobalScope('company')
                ->where('company_id', $company->id)
                ->selectRaw('status, count(*) as aggregate')
                ->groupBy('status')
                ->pluck('aggregate', 'status'),
            ServiceRequest::STATUSES,
        );

        $priorityCounts = $this->fillCounts(
            ServiceRequest::withoutGlobalScope('company')
                ->where('company_id', $company->id)
                ->selectRaw('priority, count(*) as aggregate')
                ->groupBy('priority')
                ->pluck('aggregate', 'priority'),
            ServiceRequest::PRIORITIES,
        );

        return [
            'stats' => [
                'requestsToday' => (clone $requests)->whereDate('created_at', today())->count(),
                'openRequests' => (clone $requests)->whereIn('status', self::OPEN_STATUSES)->count(),
                'completedThisMonth' => (clone $requests)
                    ->where('status', 'completed')
                    ->whereBetween('completed_at', [now()->startOfMonth(), now()->endOfMonth()])
                    ->count(),
                'overdueRequests' => (clone $requests)
                    ->whereIn('status', self::OPEN_STATUSES)
                    ->whereNotNull('preferred_date')
                    ->whereDate('preferred_date', '<', today())
                    ->count(),
                'activeTechnicians' => User::role('technician')
                    ->where('company_id', $company->id)
                    ->where('status', 'active')
                    ->count(),
            ],
            'topAssetTypes' => $this->topAssetTypes($company),
            'topTechnicians' => $this->topTechnicians($company),
            'requestsByStatus' => $statusCounts,
            'requestsByPriority' => $priorityCounts,
            'lowStockParts' => Part::withoutGlobalScope('company')
                ->where('company_id', $company->id)
                ->whereColumn('quantity', '<=', 'low_stock_threshold')
                ->orderBy('quantity')
                ->limit(8)
                ->get(),
            'recentRequests' => ServiceRequest::withoutGlobalScope('company')
                ->where('company_id', $company->id)
                ->with(['customer', 'serviceAsset', 'visits.technician'])
                ->latest()
                ->limit(8)
                ->get(),
            'overdueRequests' => ServiceRequest::withoutGlobalScope('company')
                ->where('company_id', $company->id)
                ->whereIn('status', self::OPEN_STATUSES)
                ->whereNotNull('preferred_date')
                ->whereDate('preferred_date', '<', today())
                ->with(['customer', 'serviceAsset'])
                ->orderBy('preferred_date')
                ->limit(8)
                ->get(),
        ];
    }

    /**
     * @return Collection<int, object{type: string, total: int}>
     */
    private function topAssetTypes(Company $company): Collection
    {
        return DB::table('service_requests')
            ->join('service_assets', 'service_assets.id', '=', 'service_requests.service_asset_id')
            ->where('service_requests.company_id', $company->id)
            ->where('service_assets.company_id', $company->id)
            ->selectRaw('service_assets.type as type, count(*) as total')
            ->groupBy('service_assets.type')
            ->orderByDesc('total')
            ->limit(5)
            ->get();
    }

    /**
     * @return Collection<int, object{technician_id: int, name: string, completed_requests: int}>
     */
    private function topTechnicians(Company $company): Collection
    {
        return DB::table('service_visits')
            ->join('service_requests', 'service_requests.id', '=', 'service_visits.service_request_id')
            ->join('users', 'users.id', '=', 'service_visits.technician_id')
            ->where('service_requests.company_id', $company->id)
            ->where('service_visits.company_id', $company->id)
            ->where('service_requests.status', 'completed')
            ->selectRaw('users.id as technician_id, users.name as name, count(distinct service_requests.id) as completed_requests')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('completed_requests')
            ->limit(5)
            ->get();
    }

    /**
     * @param  Collection<string, int|string>  $counts
     * @param  array<int, string>  $labels
     * @return Collection<string, int>
     */
    private function fillCounts(Collection $counts, array $labels): Collection
    {
        return collect($labels)
            ->mapWithKeys(fn (string $label): array => [$label => (int) ($counts[$label] ?? 0)]);
    }

    /**
     * @return array<int, array{label: string, url: string, enabled: bool}>
     */
    private function quickLinksFor(User $user): array
    {
        return [
            [
                'label' => 'إنشاء طلب جديد',
                'url' => route('service-requests.create'),
                'enabled' => $user->can('create', ServiceRequest::class),
            ],
            [
                'label' => 'إضافة عميل',
                'url' => route('company.customers.create'),
                'enabled' => $user->can('create', Customer::class),
            ],
            [
                'label' => 'إضافة جهاز',
                'url' => route('company.assets.create'),
                'enabled' => $user->can('create', ServiceAsset::class),
            ],
            [
                'label' => 'جدولة زيارة',
                'url' => route('service-requests.index', ['status' => 'under_review']),
                'enabled' => $user->can('viewAny', ServiceRequest::class),
            ],
            [
                'label' => 'عرض الفواتير',
                'url' => route('invoices.index'),
                'enabled' => $user->can('viewAny', Invoice::class),
            ],
        ];
    }
}
