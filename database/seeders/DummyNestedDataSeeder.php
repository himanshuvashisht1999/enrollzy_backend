<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DummyNestedDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all Top-Level Streams that do NOT have children yet
        $streams = \App\Models\CareerRoadmapSubModule::whereNull('parent_id')
            ->doesntHave('children')
            ->get();

        foreach ($streams as $stream) {
            // Add a blue alert message to the stream if it doesn't have one
            $cf = is_array($stream->custom_fields) ? $stream->custom_fields : json_decode($stream->custom_fields ?? '[]', true) ?? [];
            if (!isset($cf['alert_message'])) {
                $cf['alert_message'] = "Here is a complete roadmap to pursue a career in {$stream->title}. Early preparation and consistency will give you the best chance of success.";
                $stream->custom_fields = $cf;
                $stream->save();
            }

            // Create Timeline Group 1
            $group1 = \App\Models\CareerRoadmapSubModule::create([
                'stage_id' => $stream->stage_id,
                'parent_id' => $stream->id,
                'title' => 'Immediate Action Plan',
                'custom_fields' => ['Badge' => 'Start Now'],
                'status' => 1
            ]);

            // Add Cards to Group 1
            \App\Models\CareerRoadmapSubModule::create([
                'stage_id' => $stream->stage_id,
                'parent_id' => $group1->id,
                'title' => 'Understand the basics',
                'description' => "Begin exploring the foundational concepts of {$stream->title}.",
                'custom_fields' => ['Badge' => 'Exploration', 'Salary' => 'N/A'],
                'status' => 1
            ]);

            \App\Models\CareerRoadmapSubModule::create([
                'stage_id' => $stream->stage_id,
                'parent_id' => $group1->id,
                'title' => 'Enroll in foundation courses',
                'description' => "Join a coaching or online program tailored for early preparation.",
                'custom_fields' => ['Badge' => 'Preparation', 'Salary' => 'Investment'],
                'status' => 1
            ]);

            // Create Timeline Group 2
            $group2 = \App\Models\CareerRoadmapSubModule::create([
                'stage_id' => $stream->stage_id,
                'parent_id' => $stream->id,
                'title' => 'Long-term Goals',
                'custom_fields' => ['Badge' => 'Future Planning'],
                'status' => 1
            ]);

            // Add Cards to Group 2
            \App\Models\CareerRoadmapSubModule::create([
                'stage_id' => $stream->stage_id,
                'parent_id' => $group2->id,
                'title' => 'Advanced Preparation',
                'description' => "Start taking mock tests and analyzing previous year questions.",
                'custom_fields' => ['Badge' => 'Strategy', 'Salary' => 'High returns'],
                'status' => 1
            ]);

            \App\Models\CareerRoadmapSubModule::create([
                'stage_id' => $stream->stage_id,
                'parent_id' => $group2->id,
                'title' => 'Final Exam / Execution',
                'description' => "Appear for the main entrance exams and interviews.",
                'custom_fields' => ['Badge' => 'Execution', 'Salary' => 'Career defining'],
                'status' => 1
            ]);
        }
    }
}
