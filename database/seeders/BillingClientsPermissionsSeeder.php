<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class BillingClientsPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $perms = [
            'billing-clients-browse',
            'billing-clients-read',
            'billing-clients-add',
            'billing-clients-edit',
            'billing-clients-delete',
        ];

        foreach ($perms as $p) {
            Permission::firstOrCreate(
                ['name' => $p, 'guard_name' => 'admin'],
                ['module_title' => 'Billing Clients']
            );
        }

        $super = Role::where('name', 'superadmin')->where('guard_name', 'admin')->first();
        if ($super) {
            $super->givePermissionTo($perms);
        }

        $admin = Role::where('name', 'admin')->where('guard_name', 'admin')->first();
        if ($admin) {
            $admin->givePermissionTo($perms);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
