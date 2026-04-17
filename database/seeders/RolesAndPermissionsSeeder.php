<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'tickets.viewAny',
            'tickets.view',
            'tickets.create',
            'tickets.update',
            'tickets.delete',
            'payments.viewAny',
            'payments.view',
            'payments.create',
            'payments.update',
            'payments.delete',
            'api.tickets.view',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $adminRole = Role::findOrCreate('admin', 'web');
        $userRole = Role::findOrCreate('user', 'web');

        $adminRole->syncPermissions(Permission::all());

        $userRole->syncPermissions([
            'tickets.viewAny',
            'tickets.view',
            'tickets.create',
            'tickets.update',
            'tickets.delete',
            'payments.viewAny',
            'payments.view',
            'payments.create',
            'payments.update',
            'api.tickets.view',
        ]);
    }
}
