<?php

namespace App\Policies;

use App\Models\Part;
use App\Models\User;
use App\Policies\Concerns\HandlesTenantAuthorization;

class PartPolicy
{
    use HandlesTenantAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        return $this->isSuperAdmin($user) ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->company_id !== null
            && ($this->hasPermission($user, 'manage parts') || $user->hasRole('technician'));
    }

    public function view(User $user, Part $part): bool
    {
        return $this->sameCompany($user, $part)
            && ($this->hasPermission($user, 'manage parts') || $user->hasRole('technician'));
    }

    public function create(User $user): bool
    {
        return $user->company_id !== null && $this->hasPermission($user, 'manage parts');
    }

    public function update(User $user, Part $part): bool
    {
        return $this->sameCompany($user, $part)
            && $this->hasPermission($user, 'manage parts');
    }

    public function delete(User $user, Part $part): bool
    {
        return $this->update($user, $part);
    }
}
