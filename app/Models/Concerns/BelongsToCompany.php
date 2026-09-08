<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait BelongsToCompany
{
    protected static function bootBelongsToCompany(): void
    {
        static::addGlobalScope('company', function (Builder $builder): void {
            $user = Auth::user();

            if (! $user || $user->hasRole('super_admin') || ! $user->company_id) {
                return;
            }

            $builder->where(
                $builder->getModel()->getTable().'.company_id',
                $user->company_id,
            );
        });
    }

    public function scopeForCompany(Builder $builder, int $companyId): Builder
    {
        return $builder->withoutGlobalScope('company')->where(
            $builder->getModel()->getTable().'.company_id',
            $companyId,
        );
    }
}
