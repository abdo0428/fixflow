<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Part;
use App\Models\PartUsed;
use App\Models\ServiceRequest;
use App\Models\ServiceVisit;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TechnicianVisitService
{
    public function __construct(private readonly ServiceRequestWorkflowService $workflow) {}

    public function start(ServiceVisit $serviceVisit, User $actor, ?string $ipAddress = null): ServiceVisit
    {
        $this->ensureAssignedTechnician($serviceVisit, $actor);

        if (! in_array($serviceVisit->visit_status, ['scheduled', 'on_the_way'], true)) {
            throw ValidationException::withMessages([
                'visit_status' => 'Only scheduled visits can be started.',
            ]);
        }

        return DB::transaction(function () use ($serviceVisit, $actor, $ipAddress): ServiceVisit {
            $oldValues = [
                'visit_status' => $serviceVisit->visit_status,
                'started_at' => $serviceVisit->started_at?->toISOString(),
            ];

            $serviceVisit->forceFill([
                'visit_status' => 'in_progress',
                'started_at' => $serviceVisit->started_at ?? now(),
            ])->save();

            $serviceRequest = $serviceVisit->serviceRequest()->firstOrFail();

            if ($serviceRequest->status === 'scheduled') {
                $this->workflow->changeStatus($serviceRequest, 'in_progress', $actor, 'Visit started by technician.', $ipAddress);
            }

            $this->audit($serviceRequest, $actor, 'service_visit_started', $oldValues, [
                'service_visit_id' => $serviceVisit->id,
                'visit_status' => 'in_progress',
                'started_at' => $serviceVisit->started_at?->toISOString(),
            ], $ipAddress);

            return $serviceVisit->refresh();
        });
    }

    public function updateNotes(ServiceVisit $serviceVisit, User $actor, ?string $notes, ?string $ipAddress = null): ServiceVisit
    {
        $this->ensureAssignedTechnician($serviceVisit, $actor);

        $oldNotes = $serviceVisit->technician_notes;

        $serviceVisit->forceFill(['technician_notes' => $notes])->save();

        $this->audit($serviceVisit->serviceRequest()->firstOrFail(), $actor, 'technician_notes_updated', [
            'technician_notes' => $oldNotes,
        ], [
            'service_visit_id' => $serviceVisit->id,
            'technician_notes' => $notes,
        ], $ipAddress);

        return $serviceVisit->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $beforeImages
     * @param  array<int, string>  $afterImages
     */
    public function finish(ServiceVisit $serviceVisit, User $actor, array $data, array $beforeImages = [], array $afterImages = [], ?string $ipAddress = null): ServiceVisit
    {
        $this->ensureAssignedTechnician($serviceVisit, $actor);

        $serviceRequest = $serviceVisit->serviceRequest()->with('report')->firstOrFail();

        if (! in_array($serviceRequest->status, ['scheduled', 'in_progress', 'waiting_parts'], true)) {
            throw ValidationException::withMessages([
                'status' => 'This service request is not ready to be completed by the assigned technician.',
            ]);
        }

        return DB::transaction(function () use ($serviceVisit, $serviceRequest, $actor, $data, $beforeImages, $afterImages, $ipAddress): ServiceVisit {
            $serviceVisit->forceFill([
                'visit_status' => 'completed',
                'started_at' => $serviceVisit->started_at ?? now(),
                'finished_at' => now(),
                'technician_notes' => $data['technician_notes'] ?? $serviceVisit->technician_notes,
            ])->save();

            foreach ($data['parts'] ?? [] as $index => $partData) {
                $part = Part::withoutGlobalScope('company')->find($partData['part_id']);

                if (! $part || (int) $part->company_id !== (int) $serviceRequest->company_id) {
                    throw ValidationException::withMessages([
                        "parts.{$index}.part_id" => 'The selected part must belong to the visit company.',
                    ]);
                }

                PartUsed::query()->updateOrCreate(
                    [
                        'company_id' => $serviceRequest->company_id,
                        'service_request_id' => $serviceRequest->id,
                        'part_id' => $part->id,
                    ],
                    [
                        'quantity' => $partData['quantity'],
                        'unit_price' => $partData['unit_price'] ?? $part->unit_price,
                    ],
                );
            }

            $existingReport = $serviceRequest->report;

            $report = $serviceRequest->report()->updateOrCreate(
                ['service_request_id' => $serviceRequest->id],
                [
                    'company_id' => $serviceRequest->company_id,
                    'technician_id' => $actor->id,
                    'diagnosis' => $data['diagnosis'],
                    'solution' => $data['solution'],
                    'customer_signature' => $existingReport?->customer_signature,
                    'before_images' => array_values(array_filter([
                        ...($existingReport?->before_images ?? []),
                        ...$beforeImages,
                    ])),
                    'after_images' => array_values(array_filter([
                        ...($existingReport?->after_images ?? []),
                        ...$afterImages,
                    ])),
                    'pdf_path' => $existingReport?->pdf_path,
                ],
            );

            $this->workflow->recordReportAdded($report, $actor, $ipAddress);

            $serviceRequest->refresh();

            if ($serviceRequest->status === 'scheduled') {
                $serviceRequest = $this->workflow->changeStatus($serviceRequest, 'in_progress', $actor, 'Visit completed by technician.', $ipAddress);
            }

            if (in_array($serviceRequest->status, ['in_progress', 'waiting_parts'], true)) {
                $this->workflow->changeStatus($serviceRequest, 'completed', $actor, 'Visit completed and report submitted.', $ipAddress);
            }

            $this->audit($serviceRequest->refresh(), $actor, 'service_visit_completed', null, [
                'service_visit_id' => $serviceVisit->id,
                'service_report_id' => $report->id,
            ], $ipAddress);

            return $serviceVisit->refresh();
        });
    }

    private function ensureAssignedTechnician(ServiceVisit $serviceVisit, User $actor): void
    {
        if ((int) $serviceVisit->technician_id !== (int) $actor->id || ! $actor->can('update', $serviceVisit)) {
            throw new AuthorizationException;
        }
    }

    /**
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     */
    private function audit(ServiceRequest $serviceRequest, User $actor, string $action, ?array $oldValues, ?array $newValues, ?string $ipAddress): void
    {
        AuditLog::create([
            'company_id' => $serviceRequest->company_id,
            'user_id' => $actor->id,
            'action' => $action,
            'model_type' => ServiceRequest::class,
            'model_id' => $serviceRequest->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $ipAddress,
        ]);
    }
}
