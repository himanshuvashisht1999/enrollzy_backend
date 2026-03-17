<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HomepageSectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sections = [
            ['section_key' => 'hero_banner', 'name' => 'Hero Banner', 'sort_order' => 1],
            ['section_key' => 'expert_carousel', 'name' => 'Expert Carousel', 'sort_order' => 2],
            ['section_key' => 'university_comparison', 'name' => 'University Comparison', 'sort_order' => 3],
            ['section_key' => 'trending_skills', 'name' => 'Trending Skills', 'sort_order' => 4],
            ['section_key' => 'noteworthy_mentions', 'name' => 'Noteworthy Mentions', 'sort_order' => 5],
            ['section_key' => 'faq', 'name' => 'FAQ', 'sort_order' => 6],
            ['section_key' => 'company_marquee', 'name' => 'Company Marquee', 'sort_order' => 7],
            ['section_key' => 'talk_to_alumni', 'name' => 'Talk to Alumni', 'sort_order' => 8],
            ['section_key' => 'university_grid', 'name' => 'University Grid', 'sort_order' => 9],
            ['section_key' => 'video_testimonials', 'name' => 'Video Testimonials', 'sort_order' => 10],
            ['section_key' => 'student_form', 'name' => 'Student Form', 'sort_order' => 11],
            ['section_key' => 'testimonials', 'name' => 'Testimonials', 'sort_order' => 12],
            ['section_key' => 'blogs', 'name' => 'Blogs', 'sort_order' => 13],
            ['section_key' => 'ques_ans', 'name' => 'Ques Ans', 'sort_order' => 14],
        ];

        foreach ($sections as $section) {
            \App\Models\HomepageSection::updateOrCreate(
                ['section_key' => $section['section_key']],
                $section
            );
        }
    }
}
