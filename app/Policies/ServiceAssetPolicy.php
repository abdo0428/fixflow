<?php

namespace App\Policies;

use App\Models\ServiceAsset;
use App\Models\User;
use App\Policies\Concerns\HandlesTenantAuthorization;

class ServiceAssetPolicy
{
    use HandlesTenantAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        return $this->isSuperAdmin($user) ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->company_id !== null
            && ($this->hasPermission($user, 'manage assets') || $user->hasAnyRole(['technician', 'customer']));
    }

    public function view(User $user, ServiceAsset $serviceAsset): bool
    {
        if (! $this->sameCompany($user, $serviceAsset)) {
            return false;
        }

        if ($user->hasRole('customer')) {
            return (int) $serviceAsset->customer?->user_id === (int) $user->id;
        }

        if ($user->hasRole('technician')) {
            return $serviceAsset->serviceRequests()
                ->whereHas('visits', fn ($query) => $query->where('technician_id', $user->id))
                ->exists();
        }

        return $this->hasPermission($user, 'manage assets');
    }

    public function create(User $user): bool
    {
        return $user->company_id !== null && $this->hasPermission($user, 'manage assets');
    }

    public function update(User $user, ServiceAsset $serviceAsset): bool
    {
        return $this->sameCompany($user, $serviceAsset)
            && $this->hasPermission($user, 'manage assets');
    }

    public function delete(User $user, ServiceAsset $serviceAsset): bool
    {
        return $this->update($user, $serviceAsset);
    }
}
