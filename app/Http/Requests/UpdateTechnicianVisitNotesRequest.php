<?php

namespace App\Http\Requests;

use App\Models\ServiceVisit;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTechnicianVisitNotesRequest extends FormRequest
{
    public function authorize(): bool
    {
        $serviceVisit = $this->route('serviceVisit');

        return $serviceVisit instanceof ServiceVisit
            && (int) $serviceVisit->technician_id === (int) $this->user()?->id
            && ($this->user()?->can('update', $serviceVisit) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'technician_notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
