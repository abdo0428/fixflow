<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Seed the base roles and permissions expected by FixFlow.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'manage companies',
            'manage company users',
            'manage customers',
            'manage assets',
            'manage service requests',
            'assign technicians',
            'update assigned visits',
            'manage parts',
            'manage invoices',
            'view reports',
            'manage settings',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $rolePermissions = [
            'super_admin' => $permissions,
            'company_admin' => [
                'manage company users',
                'manage customers',
                'manage assets',
                'manage service requests',
                'assign technicians',
                'update assigned visits',
                'manage parts',
                'manage invoices',
                'view reports',
                'manage settings',
            ],
            'dispatcher' => [
                'manage customers',
                'manage assets',
                'manage service requests',
                'assign technicians',
                'view reports',
            ],
            'technician' => [
                'update assigned visits',
                'view reports',
            ],
            'customer' => [
                'view reports',
            ],
        ];

        foreach ($rolePermissions as $roleName => $rolePermissionNames) {
            Role::findOrCreate($roleName, 'web')->syncPermissions($rolePermissionNames);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
