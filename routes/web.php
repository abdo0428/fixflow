<?php

use App\Http\Controllers\CompanyAssetController;
use App\Http\Controllers\CompanyCustomerController;
use App\Http\Controllers\CustomerPortalController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServiceAssetQrController;
use App\Http\Controllers\ServiceReportPdfController;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\TechnicianPortalController;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\ServiceAsset;
use App\Models\ServiceRequest;
use App\Models\ServiceVisit;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/locale', [LocaleController::class, 'update'])
    ->name('locale.update');

Route::get('/assets/qr/{qrCode}', [ServiceAssetQrController::class, 'show'])
    ->name('assets.qr.show');

Route::middleware(['auth', 'verified', 'active.user'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'redirect'])->name('dashboard');

    Route::get('/platform/dashboard', [DashboardController::class, 'platform'])
        ->middleware(['role:super_admin', 'can:viewAny,'.Company::class])
        ->name('platform.dashboard');

    Route::get('/company/dashboard', [DashboardController::class, 'company'])
        ->middleware(['role:company_admin', 'can:viewAny,'.Company::class])
        ->name('company.dashboard');

    Route::get('/dispatch/dashboard', [DashboardController::class, 'dispatcher'])
        ->middleware(['role:dispatcher', 'can:viewAny,'.ServiceRequest::class])
        ->name('dispatch.dashboard');

    Route::get('/company/customers/create', [CompanyCustomerController::class, 'create'])
        ->middleware('can:create,'.Customer::class)
        ->name('company.customers.create');

    Route::post('/company/customers', [CompanyCustomerController::class, 'store'])
        ->middleware('can:create,'.Customer::class)
        ->name('company.customers.store');

    Route::get('/company/assets/create', [CompanyAssetController::class, 'create'])
        ->middleware('can:create,'.ServiceAsset::class)
        ->name('company.assets.create');

    Route::post('/company/assets', [CompanyAssetController::class, 'store'])
        ->middleware('can:create,'.ServiceAsset::class)
        ->name('company.assets.store');

    Route::get('/technician/dashboard', [TechnicianPortalController::class, 'dashboard'])
        ->middleware(['role:technician', 'can:viewAny,'.ServiceVisit::class])
        ->name('technician.dashboard');

    Route::get('/technician/visits', [TechnicianPortalController::class, 'visits'])
        ->middleware(['role:technician', 'can:viewAny,'.ServiceVisit::class])
        ->name('technician.visits.index');

    Route::get('/technician/visits/{serviceVisit}', [TechnicianPortalController::class, 'show'])
        ->middleware(['role:technician', 'can:view,serviceVisit'])
        ->name('technician.visits.show');

    Route::post('/technician/visits/{serviceVisit}/start', [TechnicianPortalController::class, 'start'])
        ->middleware(['role:technician', 'can:update,serviceVisit'])
        ->name('technician.visits.start');

    Route::patch('/technician/visits/{serviceVisit}/notes', [TechnicianPortalController::class, 'updateNotes'])
        ->middleware(['role:technician', 'can:update,serviceVisit'])
        ->name('technician.visits.notes');

    Route::post('/technician/visits/{serviceVisit}/finish', [TechnicianPortalController::class, 'finish'])
        ->middleware(['role:technician', 'can:update,serviceVisit'])
        ->name('technician.visits.finish');

    Route::get('/customer/dashboard', [CustomerPortalController::class, 'dashboard'])
        ->middleware(['role:customer', 'can:viewAny,'.Customer::class])
        ->name('customer.dashboard');

    Route::get('/customer/portal', [CustomerPortalController::class, 'dashboard'])
        ->middleware(['role:customer', 'can:viewAny,'.Customer::class])
        ->name('customer.portal');

    Route::get('/customer/assets', [CustomerPortalController::class, 'assets'])
        ->middleware(['role:customer', 'can:viewAny,'.Customer::class])
        ->name('customer.assets.index');

    Route::get('/customer/service-requests', [CustomerPortalController::class, 'requests'])
        ->middleware(['role:customer', 'can:viewAny,'.ServiceRequest::class])
        ->name('customer.service-requests.index');

    Route::get('/customer/service-requests/create', [CustomerPortalController::class, 'createRequest'])
        ->middleware(['role:customer', 'can:create,'.ServiceRequest::class])
        ->name('customer.service-requests.create');

    Route::post('/customer/service-requests', [CustomerPortalController::class, 'storeRequest'])
        ->middleware(['role:customer', 'can:create,'.ServiceRequest::class])
        ->name('customer.service-requests.store');

    Route::get('/dispatch/service-requests', [ServiceRequestController::class, 'index'])
        ->middleware(['role:dispatcher', 'can:viewAny,'.ServiceRequest::class])
        ->name('dispatch.service-requests.index');

    Route::patch('/service-requests/{serviceRequest}/status', [ServiceRequestController::class, 'changeStatus'])
        ->middleware('can:changeStatus,serviceRequest')
        ->name('service-requests.change-status');

    Route::post('/service-requests/{serviceRequest}/assign-technician', [ServiceRequestController::class, 'assignTechnician'])
        ->middleware('can:assignTechnicians,serviceRequest')
        ->name('service-requests.assign-technician');

    Route::post('/service-requests/{serviceRequest}/report', [ServiceRequestController::class, 'storeReport'])
        ->middleware('can:addReport,serviceRequest')
        ->name('service-requests.report.store');

    Route::post('/service-requests/{serviceRequest}/report/pdf', [ServiceReportPdfController::class, 'store'])
        ->middleware('can:generateReportPdf,serviceRequest')
        ->name('service-requests.report.pdf');

    Route::post('/service-requests/{serviceRequest}/invoice', [InvoiceController::class, 'store'])
        ->middleware('can:createInvoice,serviceRequest')
        ->name('service-requests.invoice.store');

    Route::get('/invoices', [InvoiceController::class, 'index'])
        ->middleware('can:viewAny,'.Invoice::class)
        ->name('invoices.index');

    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])
        ->middleware('can:view,invoice')
        ->name('invoices.show');

    Route::post('/invoices/{invoice}/mark-paid', [InvoiceController::class, 'markAsPaid'])
        ->middleware('can:update,invoice')
        ->name('invoices.mark-paid');

    Route::resource('service-requests', ServiceRequestController::class)
        ->parameters(['service-requests' => 'serviceRequest'])
        ->middleware('can:viewAny,'.ServiceRequest::class);
});

Route::middleware(['auth', 'active.user'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
