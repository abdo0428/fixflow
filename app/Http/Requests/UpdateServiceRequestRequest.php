<?php

namespace App\Http\Requests;

use App\Models\Customer;
use App\Models\ServiceAsset;
use App\Models\ServiceRequest as ServiceRequestModel;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('serviceRequest')) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', Rule::exists(Customer::class, 'id')],
            'service_asset_id' => ['nullable', 'integer', Rule::exists(ServiceAsset::class, 'id')],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'priority' => ['required', Rule::in(ServiceRequestModel::PRIORITIES)],
            'preferred_date' => ['nullable', 'date'],
        ];
    }

    public function serviceAssetId(): ?int
    {
        return $this->filled('service_asset_id') ? $this->integer('service_asset_id') : null;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $serviceRequest = $this->route('serviceRequest');
            $companyId = (int) $serviceRequest->company_id;
            $customer = Customer::withoutGlobalScope('company')->find($this->integer('customer_id'));

            if (! $customer || (int) $customer->company_id !== $companyId) {
                $validator->errors()->add('customer_id', 'The selected customer does not belong to the request company.');
            }

            if (! $this->serviceAssetId()) {
                return;
            }

            $asset = ServiceAsset::withoutGlobalScope('company')->find($this->serviceAssetId());

            if (! $asset || (int) $asset->company_id !== $companyId) {
                $validator->errors()->add('service_asset_id', 'The selected asset does not belong to the request company.');
            }

            if ($customer && $asset && (int) $asset->customer_id !== (int) $customer->id) {
                $validator->errors()->add('service_asset_id', 'The selected asset does not belong to the selected customer.');
            }
        });
    }
}
