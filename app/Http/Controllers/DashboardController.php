<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Customer;
use App\Models\ServiceAsset;
use App\Models\ServiceRequest;
use App\Models\ServiceVisit;
use App\Services\DashboardService;
use App\Support\DashboardRoute;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly DashboardService $dashboards) {}

    public function redirect(Request $request): View|RedirectResponse
    {
        $routeName = DashboardRoute::nameFor($request->user());

        if ($routeName === 'dashboard') {
            return view('dashboard');
        }

        return redirect()->route($routeName);
    }

    public function platform(): View
    {
        $this->authorize('viewAny', Company::class);

        return view('dashboards.platform', $this->dashboards->platformDashboard());
    }

    public function company(Request $request): View
    {
        $company = $request->user()->company;

        abort_unless($company, 403);

        $this->authorize('view', $company);

        return view('company.dashboard', [
            'company' => $company,
            'title' => __('ui.dashboards.company_title'),
            'subtitle' => __('ui.dashboards.company_subtitle'),
            ...$this->dashboards->companyDashboard($company, $request->user()),
        ]);
    }

    public function dispatcher(Request $request): View
    {
        $this->authorize('viewAny', ServiceRequest::class);

        $company = $request->user()->company;

        abort_unless($company, 403);

        return view('company.dashboard', [
            'company' => $company,
            'title' => __('ui.dashboards.dispatch_title'),
            'subtitle' => __('ui.dashboards.dispatch_subtitle'),
            ...$this->dashboards->companyDashboard($company, $request->user()),
        ]);
    }

    public function technician(Request $request): View
    {
        $this->authorize('viewAny', ServiceVisit::class);

        return view('dashboards.technician', [
            'stats' => [
                'scheduled' => ServiceVisit::where('technician_id', $request->user()->id)
                    ->where('visit_status', 'scheduled')
                    ->count(),
                'inProgress' => ServiceVisit::where('technician_id', $request->user()->id)
                    ->where('visit_status', 'in_progress')
                    ->count(),
                'completed' => ServiceVisit::where('technician_id', $request->user()->id)
                    ->where('visit_status', 'completed')
                    ->count(),
            ],
            'visits' => ServiceVisit::with(['serviceRequest.customer', 'serviceRequest.serviceAsset'])
                ->where('technician_id', $request->user()->id)
                ->orderBy('scheduled_at')
                ->limit(10)
                ->get(),
        ]);
    }

    public function customer(Request $request): View
    {
        $this->authorize('viewAny', Customer::class);

        $customer = Customer::with(['serviceAssets', 'serviceRequests.invoice'])
            ->where('user_id', $request->user()->id)
            ->first();

        if ($customer) {
            $this->authorize('view', $customer);
        }

        return view('dashboards.customer', [
            'customer' => $customer,
            'assets' => $customer
                ? ServiceAsset::where('customer_id', $customer->id)->latest()->limit(6)->get()
                : collect(),
            'requests' => $customer
                ? ServiceRequest::with(['serviceAsset', 'invoice'])->where('customer_id', $customer->id)->latest()->limit(8)->get()
                : collect(),
        ]);
    }
}
