<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        Permission::create(['name' => 'manage users']);
        Permission::create(['name' => 'view rfcs']);
        Permission::create(['name' => 'create rfcs']);
        Permission::create(['name' => 'approve rfcs']);
        Permission::create(['name' => 'view audit']);

        $admin = Role::create(['name' => 'admin']);
        $manager = Role::create(['name' => 'manager']);
        $user = Role::create(['name' => 'user']);

        $admin->givePermissionTo(Permission::all());
        $manager->givePermissionTo(['view rfcs', 'create rfcs', 'approve rfcs', 'view audit']);
        $user->givePermissionTo(['view rfcs', 'create rfcs']);
    }
}
