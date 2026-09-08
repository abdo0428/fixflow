<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('addReport', $this->route('serviceRequest')) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'technician_id' => ['nullable', 'integer', Rule::exists(User::class, 'id')],
            'diagnosis' => ['required', 'string', 'max:5000'],
            'solution' => ['required', 'string', 'max:5000'],
            'customer_signature' => ['nullable', 'string', 'max:255'],
            'before_images' => ['nullable', 'array'],
            'before_images.*' => ['string', 'max:255'],
            'after_images' => ['nullable', 'array'],
            'after_images.*' => ['string', 'max:255'],
        ];
    }

    public function technicianId(): ?int
    {
        if ($this->user()?->hasRole('technician')) {
            return $this->user()->id;
        }

        return $this->filled('technician_id') ? $this->integer('technician_id') : null;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $serviceRequest = $this->route('serviceRequest');
            $technicianId = $this->technicianId()
                ?: $serviceRequest->visits()->latest()->value('technician_id');

            if (! $technicianId) {
                $validator->errors()->add('technician_id', 'A technician is required for the service report.');

                return;
            }

            $technician = User::find($technicianId);

            if (! $technician || ! $technician->hasRole('technician')) {
                $validator->errors()->add('technician_id', 'The report author must be a technician.');

                return;
            }

            if ((int) $technician->company_id !== (int) $serviceRequest->company_id) {
                $validator->errors()->add('technician_id', 'The technician must belong to the same company.');
            }
        });
    }
}
