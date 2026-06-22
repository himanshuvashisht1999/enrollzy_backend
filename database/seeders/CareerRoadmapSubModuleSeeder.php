<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CareerRoadmapSubModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'Class 5' => [
                ['title' => 'Defence track', 'description' => 'Sainik School • RIMC • NDA'],
                ['title' => 'Science & technology', 'description' => 'IIT JEE • NEET pre-foundation'],
                ['title' => 'Olympiad / talent', 'description' => 'NTSE • NSO • IMO • KVPY'],
                ['title' => 'Still exploring', 'description' => 'Keep all options open'],
            ],
            'Class 9' => [
                ['title' => 'Defence track', 'description' => 'Sainik • RIMC • NDA prep'],
                ['title' => 'Science — engineering', 'description' => 'IIT JEE foundation'],
                ['title' => 'Science — medical', 'description' => 'NEET foundation'],
                ['title' => 'Commerce', 'description' => 'CA Foundation • Banking • MBA'],
                ['title' => 'Arts / humanities', 'description' => 'UPSC • Law • Journalism'],
                ['title' => 'Vocational / ITI', 'description' => 'Skill-based careers'],
            ],
            'Class 12' => [
                ['title' => 'Science — engineering', 'description' => 'JEE Mains & Advanced'],
                ['title' => 'Science — medical', 'description' => 'NEET UG'],
                ['title' => 'Commerce', 'description' => 'CA • CS • BBA • CUET'],
                ['title' => 'Arts / humanities', 'description' => 'CLAT • CUET • BA Hons'],
                ['title' => 'Defence', 'description' => 'NDA • CDS • AFCAT'],
                ['title' => 'Design / architecture', 'description' => 'NID • NIFT • JEE Arch'],
                ['title' => 'Hospitality / hotel mgmt', 'description' => 'NCHMCT JEE'],
            ],
            'Graduation' => [
                ['title' => 'Civil services / UPSC', 'description' => 'IAS • IPS • IFS • State PCS'],
                ['title' => 'Management / MBA', 'description' => 'CAT • XAT • GMAT'],
                ['title' => 'Technology / engineering', 'description' => 'GATE • Campus • Startups'],
                ['title' => 'Law', 'description' => 'LLB • Judiciary • Practice'],
                ['title' => 'Banking & finance', 'description' => 'IBPS • RBI • CFA'],
                ['title' => 'Defence (graduate entry)', 'description' => 'CDS • AFCAT • NCC'],
                ['title' => 'Research / academia', 'description' => 'PhD • NET • Teaching'],
            ]
        ];

        foreach ($data as $stageTitle => $modules) {
            $stage = \App\Models\CareerRoadmapStage::where('title', $stageTitle)->first();
            if ($stage) {
                foreach ($modules as $module) {
                    \App\Models\CareerRoadmapSubModule::firstOrCreate([
                        'stage_id' => $stage->id,
                        'parent_id' => null,
                        'title' => $module['title']
                    ], [
                        'description' => $module['description'],
                        'status' => 1
                    ]);
                }
            }
        }
    }
}
