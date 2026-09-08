<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompanyCustomerRequest;
use App\Models\Customer;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CompanyCustomerController extends Controller
{
    use AuthorizesRequests;

    public function create(Request $request): View
    {
        $this->authorize('create', Customer::class);

        return view('company.customers.create');
    }

    public function store(StoreCompanyCustomerRequest $request): RedirectResponse
    {
        $customer = Customer::create([
            'company_id' => $request->user()->company_id,
            'user_id' => null,
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'phone' => $request->validated('phone'),
            'address' => $request->validated('address'),
            'city' => $request->validated('city'),
            'notes' => $request->validated('notes'),
        ]);

        return redirect()
            ->route('company.assets.create', ['customer_id' => $customer->id])
            ->with('status', 'تم إضافة العميل بنجاح.');
    }
}
