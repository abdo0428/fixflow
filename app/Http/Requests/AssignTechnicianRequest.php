<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignTechnicianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('assignTechnicians', $this->route('serviceRequest')) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'technician_id' => ['required', 'integer', Rule::exists(User::class, 'id')],
            'scheduled_at' => ['required', 'date'],
            'location_address' => ['nullable', 'string', 'max:2000'],
            'technician_notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $serviceRequest = $this->route('serviceRequest');
            $technician = User::find($this->integer('technician_id'));

            if (! $technician || ! $technician->hasRole('technician')) {
                $validator->errors()->add('technician_id', 'The selected user must be a technician.');

                return;
            }

            if ((int) $technician->company_id !== (int) $serviceRequest->company_id) {
                $validator->errors()->add('technician_id', 'The selected technician must belong to the same company.');
            }

            if ($technician->status !== 'active') {
                $validator->errors()->add('technician_id', 'The selected technician must be active.');
            }
        });
    }
}
