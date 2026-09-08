<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class WorkManagementPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'Work Management Dashboard' => [
                'work-dashboard-view',
                'work-my-tasks-view',
                'work-team-tasks-view',
            ],
            'Work Projects' => [
                'work-projects-browse',
                'work-projects-read',
                'work-projects-add',
                'work-projects-edit',
                'work-projects-delete',
                'work-projects-assign',
            ],
            'Work Milestones' => [
                'work-milestones-browse',
                'work-milestones-read',
                'work-milestones-add',
                'work-milestones-edit',
                'work-milestones-delete',
            ],
            'Work Tasks & Subtasks' => [
                'work-tasks-browse',
                'work-tasks-read',
                'work-tasks-add',
                'work-tasks-edit',
                'work-tasks-delete',
                'work-tasks-assign',
                'work-tasks-delegate',
                'work-tasks-change-status',
                'work-tasks-override-custody',
            ],
            'Work Teams' => [
                'work-teams-browse',
                'work-teams-read',
                'work-teams-add',
                'work-teams-edit',
                'work-teams-delete',
                'work-teams-manage-members',
            ],
            'Work External Partners' => [
                'work-partners-browse',
                'work-partners-read',
                'work-partners-add',
                'work-partners-edit',
                'work-partners-delete',
            ],
            'Work Meetings' => [
                'work-meetings-browse',
                'work-meetings-read',
                'work-meetings-add',
                'work-meetings-edit',
                'work-meetings-delete',
            ],
            'Work Reports' => [
                'work-reports-view',
                'work-reports-export',
            ],
        ];

        foreach ($permissions as $moduleTitle => $perms) {
            foreach ($perms as $permName) {
                Permission::firstOrCreate(
                    ['name' => $permName, 'guard_name' => 'admin'],
                    ['module_title' => $moduleTitle]
                );
            }
        }

        // Give all to superadmin and admin roles if they exist
        $superAdminRole = Role::where('name', 'superadmin')->where('guard_name', 'admin')->first();
        if ($superAdminRole) {
            $allPerms = Permission::where('guard_name', 'admin')->get();
            $superAdminRole->syncPermissions($allPerms);
        }

        $adminRole = Role::where('name', 'admin')->where('guard_name', 'admin')->first();
        if ($adminRole) {
            $allPerms = Permission::where('guard_name', 'admin')->get();
            $adminRole->syncPermissions($allPerms);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
