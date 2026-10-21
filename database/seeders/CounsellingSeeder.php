<?php

namespace Database\Seeders;

use App\Models\Counselling\CandidateAction;
use App\Models\Counselling\DocumentType;
use App\Models\Counselling\Quota;
use App\Models\Counselling\RoundType;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/** Starter data and permissions for the Counselling module. Re-running never overwrites admin edits. */
class CounsellingSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['All India', 'State', 'Management', 'NRI', 'Minority', 'Deemed', 'Central Universities', 'ESIC', 'AFMS',
            'Open State', 'Home State', 'Other State', 'Other'] as $i => $name) {
            Quota::firstOrCreate(['name' => $name], ['sort_order' => $i + 1, 'status' => true]);
        }

        // [name, is_fresh_registration_default]
        // Regular rounds accept fresh registrations unless a round says otherwise.
        foreach ([['Regular', true], ['Mop-up', false], ['Stray vacancy', true], ['Spot', true], ['Special', false]] as $i => [$name, $fresh]) {
            RoundType::firstOrCreate(['name' => $name], ['is_fresh_registration_default' => $fresh, 'sort_order' => $i + 1, 'status' => true]);
        }

        foreach ([
            'Allotted and not reported', 'Reported and joined', 'Resigned within window', 'Resigned after window',
            'Upgraded', 'Did not participate in next round', 'Exited',
        ] as $i => $name) {
            CandidateAction::firstOrCreate(['name' => $name], ['sort_order' => $i + 1, 'status' => true]);
        }

        foreach ([
            ['Class 10 marksheet', false], ['Class 12 marksheet', false], ['Domicile certificate', false], ['Category certificate', false],
            ['NRI sponsor affidavit', true], ['Gap affidavit', true], ['Anti-ragging undertaking', true],
        ] as [$name, $affidavit]) {
            DocumentType::firstOrCreate(['name' => $name], ['is_affidavit' => $affidavit, 'status' => true]);
        }

        // Edit and verify/publish are deliberately separate permissions.
        $perms = [
            'cns-counsellings-browse', 'cns-counsellings-add', 'cns-counsellings-edit', 'cns-counsellings-delete',
            'cns-counsellings-verify', 'cns-counsellings-publish', 'cns-masters-manage', 'cns-codes-manage',
            'cns-import-manage', 'cns-import-undo',
        ];
        $role = Role::where('name', 'superadmin')->where('guard_name', 'admin')->first();
        foreach ($perms as $name) {
            $perm = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'admin']);
            $perm->module_title = 'Counselling Module';
            $perm->save();
            $role?->givePermissionTo($perm);
        }

        $this->command?->info('Counselling masters and permissions seeded.');
    }
}
