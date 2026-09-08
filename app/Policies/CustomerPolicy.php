<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;
use App\Policies\Concerns\HandlesTenantAuthorization;

class CustomerPolicy
{
    use HandlesTenantAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        return $this->isSuperAdmin($user) ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->company_id !== null
            && ($this->hasPermission($user, 'manage customers') || $user->hasRole('customer'));
    }

    public function view(User $user, Customer $customer): bool
    {
        if (! $this->sameCompany($user, $customer)) {
            return false;
        }

        if ($user->hasRole('customer')) {
            return (int) $customer->user_id === (int) $user->id;
        }

        return $this->hasPermission($user, 'manage customers');
    }

    public function create(User $user): bool
    {
        return $user->company_id !== null && $this->hasPermission($user, 'manage customers');
    }

    public function update(User $user, Customer $customer): bool
    {
        return $this->sameCompany($user, $customer)
            && $this->hasPermission($user, 'manage customers');
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $this->update($user, $customer);
    }
}
