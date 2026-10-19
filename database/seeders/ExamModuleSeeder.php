<?php

namespace Database\Seeders;

use App\Models\ExamModule\EventType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ExamModuleSeeder extends Seeder
{
    public function run(): void
    {
        // Event types: [name, status_source]. Results stay manual because they are often delayed.
        $types = [
            ['Registration', 'dates'], ['Late fee window', 'dates'], ['Correction window', 'dates'],
            ['City intimation slip', 'dates'], ['Admit card', 'dates'], ['Exam date', 'dates'],
            ['Provisional answer key', 'dates'], ['Answer key objection', 'dates'], ['Final answer key', 'dates'],
            ['Result', 'manual'], ['Scorecard', 'manual'], ['Merit list', 'manual'],
        ];
        foreach ($types as $i => [$name, $source]) {
            EventType::updateOrCreate(['slug' => Str::slug($name)], [
                'name' => $name, 'status_source' => $source, 'sort_order' => $i + 1, 'status' => true,
            ]);
        }

        // Edit and verify/publish are deliberately separate permissions.
        $perms = [
            'exm-exams-browse', 'exm-exams-add', 'exm-exams-edit', 'exm-exams-delete',
            'exm-exams-verify', 'exm-exams-publish', 'exm-cycles-manage',
            'exm-conducting-bodies-browse', 'exm-conducting-bodies-add', 'exm-conducting-bodies-edit', 'exm-conducting-bodies-delete',
            'exm-content-manage', 'exm-schema-manage', 'exm-cities-manage',
            'exm-event-types-manage','exm-attention-browse', 'exm-audit-browse',
        ];
        $role = Role::where('name', 'superadmin')->where('guard_name', 'admin')->first();
        foreach ($perms as $name) {
            $perm = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'admin']);
            $perm->module_title = 'Exam Module';
            $perm->save();
            $role?->givePermissionTo($perm);
        }
    }
}
