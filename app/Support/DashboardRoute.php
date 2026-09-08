<?php

namespace App\Support;

use App\Models\User;

class DashboardRoute
{
    public static function nameFor(User $user): string
    {
        return match (true) {
            $user->hasRole('super_admin') => 'platform.dashboard',
            $user->hasRole('company_admin') => 'company.dashboard',
            $user->hasRole('dispatcher') => 'dispatch.dashboard',
            $user->hasRole('technician') => 'technician.dashboard',
            $user->hasRole('customer') => 'customer.dashboard',
            default => 'dashboard',
        };
    }

    public static function pathFor(User $user): string
    {
        return route(self::nameFor($user), absolute: false);
    }
}
