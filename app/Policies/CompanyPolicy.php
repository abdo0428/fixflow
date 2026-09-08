<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;
use App\Policies\Concerns\HandlesTenantAuthorization;

class CompanyPolicy
{
    use HandlesTenantAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        return $this->isSuperAdmin($user) ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'manage companies')
            || $this->hasPermission($user, 'manage settings');
    }

    public function view(User $user, Company $company): bool
    {
        return $this->belongsToCompany($user, $company->id)
            && $this->hasPermission($user, 'manage settings');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'manage companies');
    }

    public function update(User $user, Company $company): bool
    {
        return $this->view($user, $company);
    }

    public function delete(User $user, Company $company): bool
    {
        return $this->hasPermission($user, 'manage companies');
    }
}
