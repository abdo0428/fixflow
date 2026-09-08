<?php

namespace App\Providers;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Part;
use App\Models\ServiceAsset;
use App\Models\ServiceRequest;
use App\Models\ServiceVisit;
use App\Policies\CompanyPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\InvoicePolicy;
use App\Policies\PartPolicy;
use App\Policies\ServiceAssetPolicy;
use App\Policies\ServiceRequestPolicy;
use App\Policies\ServiceVisitPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register authorization services.
     */
    public function boot(): void
    {
        Gate::policy(Company::class, CompanyPolicy::class);
        Gate::policy(Customer::class, CustomerPolicy::class);
        Gate::policy(ServiceAsset::class, ServiceAssetPolicy::class);
        Gate::policy(ServiceRequest::class, ServiceRequestPolicy::class);
        Gate::policy(ServiceVisit::class, ServiceVisitPolicy::class);
        Gate::policy(Invoice::class, InvoicePolicy::class);
        Gate::policy(Part::class, PartPolicy::class);
    }
}
