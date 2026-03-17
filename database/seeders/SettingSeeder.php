<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Setting::updateOrCreate(
            ['id' => 1],
            [
                'site_name' => 'College Website',
                'meta_title' => 'Best Online College Degrees & Education Support',
                'meta_description' => 'Compare top universities, get expert guidance, and advance your career with our online degree programs.',
                'meta_keywords' => 'online degree, college comparison, expert career guidance, distance learning',
                'footer_text' => '© 2025 College Website. All rights reserved.',
                'contact_email' => 'info@collegewebsite.com',
                'contact_phone' => '+91 98765 43210'
            ]
        );
    }
}
