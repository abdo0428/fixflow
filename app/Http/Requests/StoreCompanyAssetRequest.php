<?php

namespace App\Http\Requests;

use App\Models\Customer;
use App\Models\ServiceAsset;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCompanyAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', ServiceAsset::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', Rule::exists(Customer::class, 'id')],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'serial_number' => [
                'required',
                'string',
                'max:100',
                Rule::unique(ServiceAsset::class, 'serial_number')
                    ->where('company_id', $this->user()?->company_id),
            ],
            'purchase_date' => ['nullable', 'date'],
            'warranty_start_date' => ['nullable', 'date'],
            'warranty_end_date' => ['nullable', 'date', 'after_or_equal:warranty_start_date'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::in(['active', 'inactive', 'under_maintenance', 'retired'])],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $customer = Customer::withoutGlobalScope('company')->find($this->integer('customer_id'));

            if (! $customer || (int) $customer->company_id !== (int) $this->user()?->company_id) {
                $validator->errors()->add('customer_id', 'The selected customer must belong to your company.');
            }
        });
    }
}
