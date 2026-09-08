<?php

namespace App\Http\Requests;

use App\Models\ServiceRequest as ServiceRequestModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeServiceRequestStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('changeStatus', $this->route('serviceRequest')) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(ServiceRequestModel::STATUSES)],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
