<?php

namespace App\Policies;

use App\Models\ServiceRequest;
use App\Models\User;
use App\Policies\Concerns\HandlesTenantAuthorization;

class ServiceRequestPolicy
{
    use HandlesTenantAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        return $this->isSuperAdmin($user) ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->company_id !== null
            && ($this->hasPermission($user, 'manage service requests') || $user->hasAnyRole(['technician', 'customer']));
    }

    public function view(User $user, ServiceRequest $serviceRequest): bool
    {
        if (! $this->sameCompany($user, $serviceRequest)) {
            return false;
        }

        if ($user->hasRole('customer')) {
            return (int) $serviceRequest->customer?->user_id === (int) $user->id;
        }

        if ($user->hasRole('technician')) {
            return $serviceRequest->visits()
                ->where('technician_id', $user->id)
                ->exists();
        }

        return $this->hasPermission($user, 'manage service requests');
    }

    public function create(User $user): bool
    {
        return $user->company_id !== null
            && ($this->hasPermission($user, 'manage service requests') || $user->hasRole('customer'));
    }

    public function update(User $user, ServiceRequest $serviceRequest): bool
    {
        if ($serviceRequest->status === 'completed' && ! $user->hasRole('company_admin')) {
            return false;
        }

        return $this->sameCompany($user, $serviceRequest)
            && $this->hasPermission($user, 'manage service requests');
    }

    public function delete(User $user, ServiceRequest $serviceRequest): bool
    {
        return $this->sameCompany($user, $serviceRequest)
            && $user->hasRole('company_admin')
            && $this->hasPermission($user, 'manage service requests');
    }

    public function assignTechnicians(User $user, ServiceRequest $serviceRequest): bool
    {
        return $this->sameCompany($user, $serviceRequest)
            && $this->hasPermission($user, 'assign technicians');
    }

    public function changeStatus(User $user, ServiceRequest $serviceRequest): bool
    {
        if (! $this->sameCompany($user, $serviceRequest)) {
            return false;
        }

        if ($serviceRequest->status === 'completed' && ! $user->hasRole('company_admin')) {
            return false;
        }

        if ($user->hasRole('technician')) {
            return $serviceRequest->visits()
                ->where('technician_id', $user->id)
                ->exists();
        }

        return $this->hasPermission($user, 'manage service requests');
    }

    public function addReport(User $user, ServiceRequest $serviceRequest): bool
    {
        if (! $this->sameCompany($user, $serviceRequest) || $serviceRequest->report()->exists()) {
            return false;
        }

        if ($user->hasRole('technician')) {
            return $serviceRequest->visits()
                ->where('technician_id', $user->id)
                ->exists();
        }

        return $user->hasRole('company_admin');
    }

    public function generateReportPdf(User $user, ServiceRequest $serviceRequest): bool
    {
        if (! $this->sameCompany($user, $serviceRequest)
            || $serviceRequest->status !== 'completed'
            || ! $serviceRequest->report()->exists()
        ) {
            return false;
        }

        if ($user->hasRole('customer')) {
            return (int) $serviceRequest->customer?->user_id === (int) $user->id
                && $this->hasPermission($user, 'view reports');
        }

        if ($user->hasRole('technician')) {
            return $serviceRequest->visits()
                ->where('technician_id', $user->id)
                ->exists()
                && $this->hasPermission($user, 'view reports');
        }

        return $this->hasPermission($user, 'view reports');
    }

    public function createInvoice(User $user, ServiceRequest $serviceRequest): bool
    {
        return $this->sameCompany($user, $serviceRequest)
            && $this->hasPermission($user, 'manage invoices')
            && $serviceRequest->status === 'completed'
            && ! $serviceRequest->invoice()->exists();
    }
}
