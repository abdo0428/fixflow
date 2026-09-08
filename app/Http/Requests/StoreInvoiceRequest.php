<?php

namespace App\Http\Requests;

use App\Models\ServiceRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $serviceRequest = $this->route('serviceRequest');

        return $serviceRequest instanceof ServiceRequest
            && ($this->user()?->can('createInvoice', $serviceRequest) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'service_cost' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status' => ['nullable', Rule::in(['draft', 'issued'])],
        ];
    }

    public function serviceCost(): float
    {
        return (float) $this->validated('service_cost');
    }

    public function taxRateDecimal(): float
    {
        return round(((float) ($this->validated('tax_rate') ?? 15)) / 100, 4);
    }

    public function invoiceStatus(): string
    {
        return $this->validated('status') ?: 'draft';
    }
}
