<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\ServiceReport;
use App\Models\ServiceRequest;
use App\Models\ServiceVisit;
use App\Models\User;
use App\Notifications\NewCustomerServiceRequestNotification;
use App\Notifications\ServiceRequestStatusChangedNotification;
use App\Notifications\TechnicianAssignedNotification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ServiceRequestWorkflowService
{
    public const TRANSITIONS = [
        'new' => ['under_review', 'rejected'],
        'under_review' => ['scheduled', 'rejected'],
        'scheduled' => ['in_progress', 'cancelled'],
        'in_progress' => ['waiting_parts', 'completed'],
        'waiting_parts' => ['in_progress', 'completed'],
        'completed' => [],
        'cancelled' => [],
        'rejected' => [],
    ];

    /**
     * @return array<int, string>
     */
    public function availableTransitions(ServiceRequest $serviceRequest, User $actor): array
    {
        $targets = self::TRANSITIONS[$serviceRequest->status] ?? [];

        return array_values(array_filter(
            $targets,
            fn (string $target) => $this->actorMayMoveTo($serviceRequest, $actor, $target),
        ));
    }

    public function recordCreated(ServiceRequest $serviceRequest, User $actor, ?string $ipAddress = null): void
    {
        $this->audit($serviceRequest, $actor, 'service_request_created', null, [
            'status' => $serviceRequest->status,
            'priority' => $serviceRequest->priority,
            'customer_id' => $serviceRequest->customer_id,
            'service_asset_id' => $serviceRequest->service_asset_id,
        ], $ipAddress);

        if (! $actor->hasRole('customer')) {
            return;
        }

        User::role('dispatcher')
            ->where('company_id', $serviceRequest->company_id)
            ->where('status', 'active')
            ->get()
            ->each(fn (User $dispatcher) => $dispatcher->notify(
                new NewCustomerServiceRequestNotification($serviceRequest),
            ));
    }

    /**
     * @param  array<string, mixed>  $visitData
     */
    public function assignTechnician(ServiceRequest $serviceRequest, User $technician, User $actor, array $visitData, ?string $ipAddress = null): ServiceVisit
    {
        if (! $actor->can('assignTechnicians', $serviceRequest)) {
            throw new AuthorizationException;
        }

        if (! $technician->hasRole('technician') || $technician->status !== 'active') {
            throw ValidationException::withMessages([
                'technician_id' => 'The selected user must be an active technician.',
            ]);
        }

        if ((int) $technician->company_id !== (int) $serviceRequest->company_id) {
            throw ValidationException::withMessages([
                'technician_id' => 'The selected technician must belong to the same company.',
            ]);
        }

        if (! in_array($serviceRequest->status, ['under_review', 'scheduled'], true)) {
            throw ValidationException::withMessages([
                'status' => 'Technicians can only be assigned while the request is under review or scheduled.',
            ]);
        }

        return DB::transaction(function () use ($serviceRequest, $technician, $actor, $visitData, $ipAddress): ServiceVisit {
            $oldStatus = $serviceRequest->status;

            if ($serviceRequest->status === 'under_review') {
                $serviceRequest->forceFill([
                    'status' => 'scheduled',
                    'completed_at' => null,
                ])->save();

                $this->audit($serviceRequest, $actor, 'service_request_status_changed', [
                    'status' => $oldStatus,
                ], [
                    'status' => 'scheduled',
                ], $ipAddress);
            }

            $visit = $serviceRequest->visits()->create([
                'company_id' => $serviceRequest->company_id,
                'technician_id' => $technician->id,
                'scheduled_at' => $visitData['scheduled_at'],
                'started_at' => null,
                'finished_at' => null,
                'visit_status' => 'scheduled',
                'location_address' => $visitData['location_address'] ?? $serviceRequest->customer?->address,
                'technician_notes' => $visitData['technician_notes'] ?? null,
            ]);

            $this->audit($serviceRequest, $actor, 'technician_assigned', null, [
                'technician_id' => $technician->id,
                'service_visit_id' => $visit->id,
                'scheduled_at' => $visit->scheduled_at,
            ], $ipAddress);

            $technician->notify(new TechnicianAssignedNotification($serviceRequest->refresh(), $visit));

            return $visit;
        });
    }

    public function changeStatus(ServiceRequest $serviceRequest, string $newStatus, User $actor, ?string $notes = null, ?string $ipAddress = null): ServiceRequest
    {
        if (! $actor->can('changeStatus', $serviceRequest)) {
            throw new AuthorizationException;
        }

        if ($serviceRequest->status === $newStatus) {
            return $serviceRequest;
        }

        if (! in_array($newStatus, self::TRANSITIONS[$serviceRequest->status] ?? [], true)) {
            throw ValidationException::withMessages([
                'status' => "Cannot move service request from {$serviceRequest->status} to {$newStatus}.",
            ]);
        }

        if (! $this->actorMayMoveTo($serviceRequest, $actor, $newStatus)) {
            throw new AuthorizationException;
        }

        return DB::transaction(function () use ($serviceRequest, $newStatus, $actor, $notes, $ipAddress): ServiceRequest {
            $oldStatus = $serviceRequest->status;

            $serviceRequest->forceFill([
                'status' => $newStatus,
                'completed_at' => $newStatus === 'completed' ? now() : null,
            ])->save();

            $this->syncVisitStatus($serviceRequest, $actor, $newStatus);

            $this->audit($serviceRequest, $actor, 'service_request_status_changed', [
                'status' => $oldStatus,
            ], [
                'status' => $newStatus,
                'notes' => $notes,
            ], $ipAddress);

            if ($newStatus === 'completed') {
                $this->audit($serviceRequest, $actor, 'service_request_closed', [
                    'status' => $oldStatus,
                ], [
                    'status' => 'completed',
                    'completed_at' => $serviceRequest->completed_at?->toISOString(),
                ], $ipAddress);
            }

            $serviceRequest->customer?->user?->notify(
                new ServiceRequestStatusChangedNotification($serviceRequest, $oldStatus, $newStatus),
            );

            return $serviceRequest->refresh();
        });
    }

    public function recordReportAdded(ServiceReport $serviceReport, User $actor, ?string $ipAddress = null): void
    {
        $serviceRequest = $serviceReport->serviceRequest;

        $this->audit($serviceRequest, $actor, 'service_report_added', null, [
            'service_report_id' => $serviceReport->id,
            'technician_id' => $serviceReport->technician_id,
            'pdf_path' => $serviceReport->pdf_path,
        ], $ipAddress);
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

    private function actorMayMoveTo(ServiceRequest $serviceRequest, User $actor, string $targetStatus): bool
    {
        if ($actor->hasRole('super_admin') || $actor->hasRole('company_admin')) {
            return true;
        }

        if ($actor->hasRole('dispatcher')) {
            return in_array($targetStatus, ['under_review', 'scheduled', 'rejected', 'cancelled'], true);
        }

        if ($actor->hasRole('technician')) {
            return in_array($targetStatus, ['in_progress', 'waiting_parts', 'completed'], true)
                && $serviceRequest->visits()
                    ->where('technician_id', $actor->id)
                    ->exists();
        }

        return false;
    }

    private function syncVisitStatus(ServiceRequest $serviceRequest, User $actor, string $newStatus): void
    {
        $visit = $serviceRequest->visits()
            ->when(
                $actor->hasRole('technician'),
                fn ($query) => $query->where('technician_id', $actor->id),
            )
            ->latest()
            ->first();

        if (! $visit) {
            return;
        }

        if ($newStatus === 'in_progress') {
            $visit->forceFill([
                'visit_status' => 'in_progress',
                'started_at' => $visit->started_at ?? now(),
            ])->save();
        }

        if ($newStatus === 'completed') {
            $visit->forceFill([
                'visit_status' => 'completed',
                'started_at' => $visit->started_at ?? now(),
                'finished_at' => now(),
            ])->save();
        }
    }
}
