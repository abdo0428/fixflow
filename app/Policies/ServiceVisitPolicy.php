<?php

namespace App\Policies;

use App\Models\ServiceVisit;
use App\Models\User;
use App\Policies\Concerns\HandlesTenantAuthorization;

class ServiceVisitPolicy
{
    use HandlesTenantAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        return $this->isSuperAdmin($user) ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->company_id !== null
            && ($this->hasPermission($user, 'assign technicians')
                || $this->hasPermission($user, 'update assigned visits')
                || $user->hasRole('technician'));
    }

    public function view(User $user, ServiceVisit $serviceVisit): bool
    {
        if (! $this->sameCompany($user, $serviceVisit)) {
            return false;
        }

        if ((int) $serviceVisit->technician_id === (int) $user->id) {
            return true;
        }

        return $this->hasPermission($user, 'assign technicians')
            || $this->hasPermission($user, 'manage service requests');
    }

    public function create(User $user): bool
    {
        return $user->company_id !== null && $this->hasPermission($user, 'assign technicians');
    }

    public function update(User $user, ServiceVisit $serviceVisit): bool
    {
        if (! $this->sameCompany($user, $serviceVisit)) {
            return false;
        }

        return $this->hasPermission($user, 'assign technicians')
            || (
                (int) $serviceVisit->technician_id === (int) $user->id
                && $this->hasPermission($user, 'update assigned visits')
            );
    }

    public function delete(User $user, ServiceVisit $serviceVisit): bool
    {
        return $this->sameCompany($user, $serviceVisit)
            && $this->hasPermission($user, 'assign technicians');
    }
}
