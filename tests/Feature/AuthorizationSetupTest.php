<?php

namespace Tests\Feature;

use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthorizationSetupTest extends TestCase
{
    use RefreshDatabase;

    public function test_fixflow_roles_and_permissions_are_seeded(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $superAdminRole = Role::where('name', 'super_admin')->first();

        $this->assertNotNull($superAdminRole);
        $this->assertTrue($superAdminRole->hasPermissionTo('manage companies'));
        $this->assertDatabaseHas('roles', [
            'name' => 'dispatcher',
            'guard_name' => 'web',
        ]);
        $this->assertDatabaseHas('permissions', [
            'name' => 'update assigned visits',
            'guard_name' => 'web',
        ]);
    }
}
