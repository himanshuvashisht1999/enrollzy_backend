<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Faq;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faqs = [
            [
                'question' => 'How do I choose the right college for my career?',
                'answer' => 'Focus on accreditation, course curriculum, faculty expertise, placement support, and alignment with your long-term goals.',
                'category' => 'General',
                'sort_order' => 1,
            ],
            [
                'question' => 'Are online and distance learning degrees valid?',
                'answer' => 'Yes. Degrees approved by UGC, AICTE, or other authorities are valid for jobs, higher studies, and government exams.',
                'category' => 'Degrees',
                'sort_order' => 2,
            ],
            [
                'question' => 'What is the admission process for online programs?',
                'answer' => 'Admissions usually include online registration, document upload, fee payment, and university verification.',
                'category' => 'Admissions',
                'sort_order' => 3,
            ],
            [
                'question' => 'Do colleges provide placement assistance?',
                'answer' => 'Most reputed colleges offer placement assistance, resume building, mock interviews, and career counselling.',
                'category' => 'Placements',
                'sort_order' => 4,
            ],
            [
                'question' => 'Can I get expert counselling before admission?',
                'answer' => 'Yes. Expert counselling helps you understand course scope, eligibility, and choose the best-fit college.',
                'category' => 'Counselling',
                'sort_order' => 5,
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::updateOrCreate(['question' => $faq['question']], $faq);
        }
    }
}
