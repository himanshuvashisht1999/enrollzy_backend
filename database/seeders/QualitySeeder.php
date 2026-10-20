<?php

namespace Database\Seeders;

use App\Models\Quality\AccreditationBody;
use App\Models\Quality\RankingBody;
use App\Models\Quality\RankingCategory;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/** Starter ranking / accreditation masters and permissions. Re-running never overwrites admin edits. */
class QualitySeeder extends Seeder
{
    public function run(): void
    {
        $all = null;   // null = recordable at any level
        $inst = ['organisation', 'campus'];

        // name => [short, type, country, trust, [categories as name => levels]]
        $bodies = [
            'National Institutional Ranking Framework' => ['NIRF', 'government', 'India', 'high', [
                'Overall' => $inst, 'University' => ['organisation'], 'Colleges' => ['organisation', 'campus'], 'Research Institutions' => $inst,
                'Engineering' => $all, 'Management' => $all, 'Pharmacy' => $all, 'Medical' => $all, 'Dental' => $all, 'Law' => $all,
                'Architecture' => $all, 'Agriculture' => $all, 'Innovation' => $inst, 'Open University' => ['organisation'],
                'State Public University' => ['organisation'], 'Skill University' => ['organisation'],
            ]],
            'QS World University Rankings' => ['QS', 'international', 'United Kingdom', 'high', [
                'World' => $inst, 'Asia' => $inst, 'India' => $inst, 'Subject' => $all,
            ]],
            'Times Higher Education' => ['THE', 'international', 'United Kingdom', 'high', [
                'World' => $inst, 'Asia' => $inst, 'Impact' => $inst, 'Subject' => $all,
            ]],
            'India Today' => ['India Today', 'media', 'India', 'medium', ['Overall' => $all, 'Engineering' => $all, 'Management' => $all, 'Medical' => $all, 'Law' => $all]],
            'Outlook-ICARE' => ['Outlook', 'media', 'India', 'medium', ['Overall' => $all, 'Engineering' => $all, 'Management' => $all, 'Law' => $all]],
            'The Week' => ['The Week', 'media', 'India', 'medium', ['Overall' => $all, 'Engineering' => $all, 'Management' => $all]],
            'Education World' => ['EW', 'media', 'India', 'low', ['Overall' => $all, 'Engineering' => $all, 'Management' => $all]],
        ];
        foreach (array_keys($bodies) as $i => $name) {
            [$short, $type, $country, $trust, $cats] = $bodies[$name];
            // Matched by name OR short name, so a body an admin has renamed is not created a second time.
            $body = RankingBody::where('name', $name)->orWhere('short_name', $short)->first()
                ?? RankingBody::create(['name' => $name, 'short_name' => $short, 'type' => $type, 'country' => $country, 'trust_level' => $trust, 'status' => true, 'sort_order' => $i + 1]);
            // Starter categories are only added to a body that has none yet: once an admin has shaped the list, it is theirs.
            if ($body->categories()->exists()) continue;
            $n = 0;
            foreach ($cats as $cat => $levels) {
                RankingCategory::create(['ranking_body_id' => $body->id, 'name' => $cat, 'applicable_levels' => $levels, 'status' => true, 'sort_order' => ++$n]);
            }
        }

        $naacGrades = ['A++', 'A+', 'A', 'B++', 'B+', 'B', 'C', 'D'];
        // short => [name, kind, levels, has_grade, grades, has_validity]
        $acc = [
            'NAAC' => ['National Assessment and Accreditation Council', 'accreditation', $inst, true, $naacGrades, true],
            'NBA' => ['National Board of Accreditation', 'accreditation', ['department', 'course'], false, null, true],
            'UGC' => ['University Grants Commission', 'recognition', ['organisation'], false, null, false],
            'AICTE' => ['All India Council for Technical Education', 'statutory_approval', ['organisation', 'campus', 'course'], false, null, true],
            'NMC' => ['National Medical Commission', 'statutory_approval', ['organisation', 'campus', 'course'], false, null, true],
            'BCI' => ['Bar Council of India', 'statutory_approval', ['organisation', 'campus', 'course'], false, null, true],
            'PCI' => ['Pharmacy Council of India', 'statutory_approval', ['organisation', 'campus', 'course'], false, null, true],
            'DCI' => ['Dental Council of India', 'statutory_approval', ['organisation', 'campus', 'course'], false, null, true],
            'COA' => ['Council of Architecture', 'statutory_approval', ['organisation', 'campus', 'course'], false, null, true],
            'NCTE' => ['National Council for Teacher Education', 'statutory_approval', ['organisation', 'campus', 'course'], false, null, true],
            'INC' => ['Indian Nursing Council', 'statutory_approval', ['organisation', 'campus', 'course'], false, null, true],
            'AIU' => ['Association of Indian Universities', 'recognition', ['organisation'], false, null, false],
            'ICAR' => ['Indian Council of Agricultural Research', 'accreditation', $inst, false, null, true],
            'AACSB' => ['AACSB International', 'accreditation', $all, false, null, true],
            'AMBA' => ['Association of MBAs', 'accreditation', $all, false, null, true],
            'ABET' => ['ABET', 'accreditation', ['department', 'course'], false, null, true],
        ];
        $i = 0;
        foreach ($acc as $short => [$name, $kind, $levels, $hasGrade, $grades, $hasValidity]) {
            ++$i;
            if (AccreditationBody::where('short_name', $short)->orWhere('name', $name)->exists()) continue;
            AccreditationBody::create([
                'short_name' => $short, 'name' => $name, 'kind' => $kind, 'applicable_levels' => $levels, 'has_grade' => $hasGrade, 'grade_options' => $grades,
                'has_validity' => $hasValidity, 'country' => in_array($short, ['AACSB', 'AMBA', 'ABET'], true) ? null : 'India',
                'status' => true, 'sort_order' => $i,
            ]);
        }

        // Recording, verifying and managing masters/identity are separate on purpose.
        $perms = ['quality-browse', 'quality-edit', 'quality-verify', 'quality-delete', 'quality-masters-manage', 'quality-identity-manage', 'quality-import'];
        // Only the admin and superadmin roles get these; give others access by hand.
        $roles = Role::whereIn('name', ['superadmin', 'admin'])->where('guard_name', 'admin')->get();
        foreach ($perms as $name) {
            $perm = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'admin']);
            $perm->module_title = 'Rankings & Accreditation';
            $perm->save();
            foreach ($roles as $role) $role->givePermissionTo($perm);
        }

        $this->command?->info('Ranking / accreditation masters and permissions seeded.');
    }
}
