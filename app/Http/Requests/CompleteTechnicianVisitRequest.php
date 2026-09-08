<?php

namespace App\Http\Requests;

use App\Models\Part;
use App\Models\ServiceVisit;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompleteTechnicianVisitRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $parts = collect($this->input('parts', []))
            ->filter(fn ($part): bool => is_array($part) && filled($part['part_id'] ?? null))
            ->values()
            ->all();

        $this->merge(['parts' => $parts]);
    }

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
            'diagnosis' => ['required', 'string', 'max:5000'],
            'solution' => ['required', 'string', 'max:5000'],
            'parts' => ['nullable', 'array', 'max:20'],
            'parts.*.part_id' => ['required', 'integer', Rule::exists(Part::class, 'id')],
            'parts.*.quantity' => ['required', 'integer', 'min:1', 'max:1000'],
            'parts.*.unit_price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'before_images' => ['nullable', 'array', 'max:6'],
            'before_images.*' => ['image', 'max:4096'],
            'after_images' => ['nullable', 'array', 'max:6'],
            'after_images.*' => ['image', 'max:4096'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $serviceVisit = $this->route('serviceVisit');

            if (! $serviceVisit instanceof ServiceVisit) {
                return;
            }

            foreach ($this->input('parts', []) as $index => $partData) {
                $part = Part::withoutGlobalScope('company')->find($partData['part_id'] ?? null);

                if (! $part || (int) $part->company_id !== (int) $serviceVisit->company_id) {
                    $validator->errors()->add("parts.{$index}.part_id", 'The selected part must belong to the visit company.');
                }
            }
        });
    }
}
