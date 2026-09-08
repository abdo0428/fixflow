<?php

namespace App\Policies\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

trait HandlesTenantAuthorization
{
    protected function isSuperAdmin(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    protected function sameCompany(User $user, Model $model): bool
    {
        return $user->company_id !== null
            && isset($model->company_id)
            && (int) $user->company_id === (int) $model->company_id;
    }

    protected function belongsToCompany(User $user, int $companyId): bool
    {
        return $user->company_id !== null && (int) $user->company_id === $companyId;
    }

    protected function hasPermission(User $user, string $permission): bool
    {
        try {
            return $user->hasPermissionTo($permission);
        } catch (PermissionDoesNotExist) {
            return false;
        }
    }
}
