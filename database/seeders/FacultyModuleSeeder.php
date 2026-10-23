<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class FacultyModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Permissions
        $permissions = [
            'faculties-browse',
            'faculties-read',
            'faculties-edit',
            'faculties-add',
            'faculties-delete',
            'faculty-designations-browse',
            'faculty-types-browse',
        ];


        foreach ($permissions as $permName) {
            Permission::firstOrCreate([
                'name' => $permName,
                'guard_name' => 'admin'
            ], [
                'module_title' => 'Faculties'
            ]);
        }

        $superadmin = Role::where('name', 'superadmin')->where('guard_name', 'admin')->first();
        if ($superadmin) {
            $superadmin->givePermissionTo($permissions);
        }

        $adminRole = Role::where('name', 'admin')->where('guard_name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($permissions);
        }

        // 2. Designations
        $designations = [
            ['name' => 'Chancellor', 'institution_category' => 'university', 'hierarchy_order' => 1],
            ['name' => 'Vice Chancellor', 'institution_category' => 'university', 'hierarchy_order' => 2],
            ['name' => 'Dean', 'institution_category' => 'university', 'hierarchy_order' => 3],
            ['name' => 'Director', 'institution_category' => 'university', 'hierarchy_order' => 4],
            ['name' => 'HOD', 'institution_category' => 'university', 'hierarchy_order' => 5],
            ['name' => 'Professor', 'institution_category' => 'university', 'hierarchy_order' => 6],
            ['name' => 'Associate Professor', 'institution_category' => 'university', 'hierarchy_order' => 7],
            ['name' => 'Assistant Professor', 'institution_category' => 'university', 'hierarchy_order' => 8],
            ['name' => 'Visiting Professor', 'institution_category' => 'university', 'hierarchy_order' => 9],
            ['name' => 'Adjunct Faculty', 'institution_category' => 'university', 'hierarchy_order' => 10],
            ['name' => 'Research Fellow', 'institution_category' => 'university', 'hierarchy_order' => 11],

            ['name' => 'Principal', 'institution_category' => 'school', 'hierarchy_order' => 1],
            ['name' => 'Vice Principal', 'institution_category' => 'school', 'hierarchy_order' => 2],
            ['name' => 'PGT', 'institution_category' => 'school', 'hierarchy_order' => 3],
            ['name' => 'TGT', 'institution_category' => 'school', 'hierarchy_order' => 4],
            ['name' => 'PRT', 'institution_category' => 'school', 'hierarchy_order' => 5],
            ['name' => 'Subject Teacher', 'institution_category' => 'school', 'hierarchy_order' => 6],
            ['name' => 'Coordinator', 'institution_category' => 'school', 'hierarchy_order' => 7],

            ['name' => 'Academic Director', 'institution_category' => 'coaching', 'hierarchy_order' => 1],
            ['name' => 'Senior Faculty', 'institution_category' => 'coaching', 'hierarchy_order' => 2],
            ['name' => 'Faculty', 'institution_category' => 'coaching', 'hierarchy_order' => 3],
            ['name' => 'Subject Expert', 'institution_category' => 'coaching', 'hierarchy_order' => 4],
            ['name' => 'Mentor', 'institution_category' => 'coaching', 'hierarchy_order' => 5],
            ['name' => 'Visiting Faculty', 'institution_category' => 'coaching', 'hierarchy_order' => 6],
        ];

        foreach ($designations as $d) {
            DB::table('faculty_designations')->updateOrInsert(
                ['name' => $d['name'], 'institution_category' => $d['institution_category']],
                [
                    'slug' => Str::slug($d['name'] . '-' . $d['institution_category']),
                    'hierarchy_order' => $d['hierarchy_order'],
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }

        // 3. Faculty Types
        $types = ['Permanent', 'Contractual', 'Visiting', 'Adjunct', 'Guest', 'Research Fellow', 'Emeritus'];
        foreach ($types as $t) {
            DB::table('faculty_types')->updateOrInsert(
                ['name' => $t],
                ['status' => 1, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
