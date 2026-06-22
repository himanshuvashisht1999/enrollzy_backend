<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NestedCareerRoadmapSubModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            // Graduation -> Civil services / UPSC
            [
                'stage' => 'Graduation',
                'stream' => 'Civil services / UPSC',
                'alert' => 'Graduation is the minimum qualification for UPSC Civil Services. The exam is open to all streams and all graduation subjects. Most successful candidates begin Prelims preparation in their final year of graduation.',
                'timeline_groups' => [
                    [
                        'title' => 'During / after graduation',
                        'badge' => 'Start now',
                        'cards' => [
                            ['title' => 'UPSC optional subject — choose wisely', 'desc' => 'Pick optional aligned with graduation subject for advantage', 'badge' => 'Strategy', 'salary' => 'Critical decision'],
                            ['title' => 'GS Prelims foundation — Paper 1 + CSAT', 'desc' => 'General Studies + Aptitude — 1 to 2 years preparation', 'badge' => 'Preparation', 'salary' => '₹56,100+ on selection'],
                            ['title' => 'State PCS — parallel preparation', 'desc' => 'State civil services share 70% syllabus with UPSC', 'badge' => 'State Govt', 'salary' => '₹40,000–₹1.5L/month'],
                        ]
                    ],
                    [
                        'title' => 'Attempt years — 1 to 3 attempts',
                        'badge' => '1-4 years ahead',
                        'cards' => [
                            ['title' => 'UPSC Prelims -> Mains -> Interview', 'desc' => '3-stage process — 9 to 12 months per attempt cycle', 'badge' => 'Government', 'salary' => null],
                            ['title' => 'SSC CGL — Group B & C services', 'desc' => 'Inspector, Assistant, Auditor — faster selection', 'badge' => 'Government', 'salary' => null],
                            ['title' => 'RBI / NABARD / SEBI Grade A', 'desc' => 'Regulatory bodies — prestigious and well-paying govt jobs', 'badge' => 'Finance', 'salary' => '₹8-18L/year'],
                        ]
                    ]
                ]
            ],
            // Class 5 -> Defence track
            [
                'stage' => 'Class 5',
                'stream' => 'Defence track',
                'alert' => 'Your child can begin defence preparation RIGHT NOW. Sainik School Class 6 entrance (AISSEE) is one of the most competitive exams in India — early preparation in Maths, English, and GK is the key differentiator.',
                'timeline_groups' => [
                    [
                        'title' => 'Now — Class 5 to 6',
                        'badge' => 'Immediate action',
                        'cards' => [
                            [
                                'title' => 'Sainik School entrance — AISSEE', 
                                'desc' => 'All India Sainik School Entrance Exam for Class 6 admission', 
                                'badge' => 'Defence', 
                                'salary' => '₹56,100-₹2.5L/month as officer',
                                'sub_cards' => [
                                    ['title' => 'Exam Pattern & Syllabus', 'desc' => 'Maths (150 marks), GK (50 marks), Language (50 marks), Intelligence (50 marks). Total 300 marks.', 'badge' => 'Details'],
                                    ['title' => 'Eligibility Criteria', 'desc' => 'Age between 10-12 years as of 31st March of admission year. Boys and girls both eligible.', 'badge' => 'Requirement'],
                                    ['title' => 'Application Timeline', 'desc' => 'Forms usually out in October-November. Exam conducted in January.', 'badge' => 'Important Dates'],
                                    ['title' => 'Medical Standards', 'desc' => 'Strict medical guidelines including perfect vision, no flat foot or knock knees.', 'badge' => 'Medical'],
                                    ['title' => 'Preparation Strategy', 'desc' => 'Solving past 5-10 years question papers is highly recommended for time management.', 'badge' => 'Preparation']
                                ]
                            ],
                            ['title' => 'RIMC Dehradun — Class 8 prep start', 'desc' => 'Begin Maths and English strengthening for RIMC entrance', 'badge' => 'Defence', 'salary' => '₹56,100+ on commission'],
                            ['title' => 'Foundation coaching enrolment', 'desc' => 'Join pre-defence coaching institutes for early preparation', 'badge' => 'Preparation', 'salary' => 'Invest early'],
                            ['title' => 'Physical fitness regimen', 'desc' => 'Start basic stamina and endurance training (running, swimming)', 'badge' => 'Health', 'salary' => null],
                            ['title' => 'Current affairs habit', 'desc' => 'Develop daily newspaper reading and GK awareness', 'badge' => 'Skill building', 'salary' => null],
                        ]
                    ],
                    [
                        'title' => 'Class 7 to 9',
                        'badge' => '2-4 years ahead',
                        'cards' => [
                            ['title' => 'RIMC entrance exam — Class 8 entry', 'desc' => 'Rashtriya Indian Military College, Dehradun', 'badge' => 'Defence', 'salary' => '₹56,100+ as officer'],
                            ['title' => 'NTSE / Olympiads', 'desc' => 'Scholarship exams that build academic strength for defence', 'badge' => 'Merit', 'salary' => 'Scholarship + recognition'],
                            ['title' => 'NCC (National Cadet Corps) Junior Division', 'desc' => 'Join school NCC for direct exposure to military training and discipline', 'badge' => 'Activity', 'salary' => 'A-Certificate'],
                            ['title' => 'Rashtriya Military Schools (RMS)', 'desc' => 'Class 9 entrance exam for prestigious military boarding schools', 'badge' => 'Defence', 'salary' => 'Early career edge'],
                            ['title' => 'Soft skills & English communication', 'desc' => 'Crucial for future SSB interviews, focus on group discussions', 'badge' => 'Skill building', 'salary' => null],
                        ]
                    ],
                    [
                        'title' => 'Class 11 to 12',
                        'badge' => '6-7 years ahead',
                        'cards' => [
                            ['title' => 'NDA entrance exam', 'desc' => 'National Defence Academy — Army, Navy, Air Force', 'badge' => 'Defence', 'salary' => null],
                            ['title' => 'PCM stream mandatory', 'desc' => 'Physics, Chemistry, Maths required for NDA', 'badge' => 'Requirement', 'salary' => null],
                        ]
                    ]
                ]
            ],
            // Class 9 -> Defence track
            [
                'stage' => 'Class 9',
                'stream' => 'Defence track',
                'alert' => 'Class 10 is the last comfortable window to commit to the defence track. NDA exam opens after Class 12 — you have exactly 2 years to prepare. PCM stream selection in Class 11 is mandatory.',
                'timeline_groups' => [
                    [
                        'title' => 'Right now — Class 10',
                        'badge' => 'Immediate decision',
                        'cards' => [
                            ['title' => 'Choose PCM in Class 11 — mandatory', 'desc' => 'Physics, Chemistry, Maths required for NDA eligibility', 'badge' => 'Requirement', 'salary' => 'Non-negotiable for NDA'],
                            ['title' => 'Start NDA foundation coaching', 'desc' => 'Maths + General Ability Test preparation begins now', 'badge' => 'Preparation', 'salary' => '2-year preparation window'],
                            ['title' => 'Physical fitness programme', 'desc' => 'NDA SSB requires top physical standards — start now', 'badge' => 'Defence', 'salary' => 'Parallel preparation'],
                        ]
                    ],
                    [
                        'title' => 'Class 11 to 12',
                        'badge' => '2 years ahead',
                        'cards' => [
                            ['title' => 'NDA entrance — written exam', 'desc' => 'Mathematics + General Ability — twice a year (UPSC)', 'badge' => 'Defence', 'salary' => null],
                            ['title' => 'SSB interview', 'desc' => '5-day Services Selection Board — psychology, GTO, interview', 'badge' => 'Defence', 'salary' => null],
                            ['title' => 'Alternate: Territorial Army / CAPF', 'desc' => 'Paramilitary option if NDA not secured', 'badge' => 'Government', 'salary' => null],
                        ]
                    ]
                ]
            ]
        ];

        foreach ($data as $item) {
            $stage = \App\Models\CareerRoadmapStage::where('title', $item['stage'])->first();
            if (!$stage) continue;

            $stream = \App\Models\CareerRoadmapSubModule::where('stage_id', $stage->id)
                ->where('title', $item['stream'])
                ->whereNull('parent_id')
                ->first();

            if (!$stream) continue;

            // Update stream with alert message
            $cf = is_array($stream->custom_fields) ? $stream->custom_fields : json_decode($stream->custom_fields ?? '[]', true) ?? [];
            $cf['alert_message'] = $item['alert'];
            $stream->custom_fields = $cf;
            $stream->save();

            foreach ($item['timeline_groups'] as $groupData) {
                $groupCf = ['Badge' => $groupData['badge']];
                $group = \App\Models\CareerRoadmapSubModule::firstOrCreate([
                    'stage_id' => $stage->id,
                    'parent_id' => $stream->id,
                    'title' => $groupData['title']
                ], [
                    'custom_fields' => $groupCf,
                    'status' => 1
                ]);

                foreach ($groupData['cards'] as $cardData) {
                    $cardCf = ['Badge' => $cardData['badge']];
                    if ($cardData['salary']) {
                        $cardCf['Salary'] = $cardData['salary'];
                    }

                    $createdCard = \App\Models\CareerRoadmapSubModule::firstOrCreate([
                        'stage_id' => $stage->id,
                        'parent_id' => $group->id,
                        'title' => $cardData['title']
                    ], [
                        'description' => $cardData['desc'],
                        'custom_fields' => $cardCf,
                        'status' => 1
                    ]);

                    if (isset($cardData['sub_cards'])) {
                        foreach ($cardData['sub_cards'] as $subCard) {
                            \App\Models\CareerRoadmapSubModule::firstOrCreate([
                                'stage_id' => $stage->id,
                                'parent_id' => $createdCard->id,
                                'title' => $subCard['title']
                            ], [
                                'description' => $subCard['desc'],
                                'custom_fields' => ['Badge' => $subCard['badge']],
                                'status' => 1
                            ]);
                        }
                    }
                }
            }
        }
    }
}
